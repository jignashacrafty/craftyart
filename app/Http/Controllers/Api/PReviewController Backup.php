<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\PReview;
use App\Models\Design;
use App\Models\NewCategory;
use App\Models\SpecialPage;
use App\Models\SpecialKeyword;
use App\Models\UserData;
use Illuminate\Support\Str;

class PReviewController extends ApiController
{
    private static array $types = [0, 1, 2, 3, 4];  //"template", "category", "spage", "kpage"

    public function postReview(Request $request): array|string
    {

        if ($this->isFakeRequestAndUser($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $id = $request->get("id");
        $type = $request->get("type");

        if (is_null($id) || is_null($type) || !in_array($type, PReviewController::$types) || !PReviewController::isValidType($type, $id)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Invalid params"));
        }

        $exists = PReview::where('p_type', $type)->where('p_id', $id)->where('user_id', $this->uid)->exists();
        if ($exists) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(422, false, "Review already exists"));
        }

        $validator = Validator::make($request->all(), [
            'feedback' => 'required|string|max:1000',
            'rate' => 'required|numeric|between:1,5',
        ]);

        if ($validator->fails()) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(422, false, $validator->errors()->first()));
        }

        $feedback = $request->feedback;
        $rate = $request->rate;

        $res = new PReview();
        $res->user_id = $this->uid;
        $res->p_type = $type;
        $res->p_id = $id;
        $res->feedback = $feedback;
        $res->rate = $rate;
        $res->save();

        $review = PReview::where('p_type', $type)->where('p_id', $id)->where('user_id', $this->uid)->first();
        if ($review) {
            $userReview = \App\Http\Controllers\HelperController::getUserInfo($review);
        } else {
            $userReview = null; // Handle the case where no review is found
        }

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, 'Review sent successfully. Your review will be displayed after approval.', ['data' => $userReview]));
    }


    public function deleteReview(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $delete = PReview::where('id', $request->id)->where('user_id', $this->uid)->delete();

        if ($delete) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Delete Record Successfully."));
        }

        return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Invalid request"));
    }

    public function editReview(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $validator = Validator::make($request->all(), [
            'feedback' => 'required|string|max:1000',
            'rate' => 'required|numeric|between:1,5',
        ]);

        if ($validator->fails()) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(422, false, $validator->errors()->first()));
        }

        $input = [
            'feedback' => $request->feedback,
            'rate' => $request->rate,
            'is_approve' => 0
        ];

        $update = PReview::where('id', $request->id)->where('user_id', $this->uid)->update($input);

        if ($update) {
            $review = PReview::where('id', $request->id)->where('user_id', $this->uid)->first();
            if ($review) {
                $userReview = \App\Http\Controllers\HelperController::getUserInfo($review);
            } else {
                $userReview = null;
            }
            return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Review updated successfully. Your review will be displayed after approval.", ['data' => $userReview]));
        }

        return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Invalid request"));
    }

    public function getReviews(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $data = PReviewController::getPReviews(
            $this->uid,
            $request->type,
            $request->id,
            $request->input('page', 1),
            $request->filter_type,
            $request->rating_type
        );

        if (!$data['success']) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, $data['msg']));
        }

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Loaded", $data['data']));
    }

    public static function getPReviews($userId, $type, $id, $page, $filter_type = 1, $rating_type = 0): array
    {
        if (
            is_null($id) ||
            is_null($type) ||
            !PReviewController::isValidType($type, $id)
        ) {
            return ResponseHandler::sendRealResponse(new ResponseInterface(401, false, "Invalid params"));
        }

        $limit = 2;

        $reviewsQuery = PReview::query()
            ->with(['user'])
            ->where('p_type', $type)
            ->where('p_id', $id)
            ->where('is_approve', 1)
            ->where(function ($query) use ($userId) {
                $query->whereNull('user_id')->orWhere('user_id', '!=', $userId);
            });

        if (!is_null($rating_type) && $rating_type !== 'all' && in_array((int) $rating_type, [1, 2, 3, 4, 5])) {
            $reviewsQuery->where('rate', (int) $rating_type);
        }

        if ($filter_type == 1) {
            $reviewsQuery->orderByRaw("CASE WHEN suggestion_type = 1 THEN 0 ELSE 1 END")->orderBy('id', 'DESC');
        } elseif ($filter_type == 2) {
            $reviewsQuery->orderBy('id', 'DESC');
        } elseif ($filter_type == 3) {
            $reviewsQuery->orderBy('rate', 'DESC');
        } elseif ($filter_type == 4) {
            $reviewsQuery->orderBy('rate', 'ASC');
        }



        $reviewsPaginated = $reviewsQuery->paginate($limit, ['*'], 'page', $page);

        $reviews = [];
        foreach ($reviewsPaginated->items() as $review) {
            $reviews[] = \App\Http\Controllers\HelperController::getUserInfo($review);
        }

        // Count of reviews in this response page

        $allSummarised = PReview::where('p_type', $type)
            ->where('p_id', $id)
            ->where('is_approve', 1)
            ->pluck('summarised');

        $summarised = [];
        foreach ($allSummarised as $entry) {
            $decoded = json_decode($entry, true);
            if (is_array($decoded)) {
                $summarised = array_merge($summarised, $decoded);
            }
        }

        $summarised = array_unique($summarised);

        $review = PReview::where('p_type', $type)
            ->where('p_id', $id)
            ->where('user_id', $userId)
            ->first();

        $userReview = $review ? \App\Http\Controllers\HelperController::getUserInfo($review) : null;

        return ResponseHandler::sendRealResponse(new ResponseInterface(200, true, "Loaded", [
            "data" => [
                'total_pages' => $reviewsPaginated->lastPage(),
                'review_count' => $reviewsQuery->count(),
                'analytics' => array_merge(
                    PReviewController::getTotalRating($type, $id)
                ),
                'userReview' => $userReview,
                'datas' => $reviews,
                'summarised' => $summarised,
                'isLastPage' => $reviewsPaginated->currentPage() === $reviewsPaginated->lastPage(),
            ]
        ]));
    }

    private static function getTotalRating($type, $id): array
    {
        $reviewsQuery = PReview::query()->where('p_type', $type)->where('p_id', $id)->where('is_approve', 1);
        $totalReviews = (clone $reviewsQuery)->count();
        $countUserReview = PReview::query()->select('user_id', DB::raw('count(*) as total_reviews'))->where('p_type', $type)->where('p_id', $id)->where('is_approve', 1)->groupBy('user_id')->get();
        if ($totalReviews == 0) {
            return [
                'total_approved_user_reviews' => 0,
                'overall_rating' => 0,
                'rating_percentages' => [
                    '1_star' => 0,
                    '2_star' => 0,
                    '3_star' => 0,
                    '4_star' => 0,
                    '5_star' => 0,
                ],
            ];
        }

        $ratingCounts = (clone $reviewsQuery)->selectRaw('rate, COUNT(*) as count')->groupBy('rate')->pluck('count', 'rate');
        $ratingPercentages = [
            '1_star' => 0,
            '2_star' => 0,
            '3_star' => 0,
            '4_star' => 0,
            '5_star' => 0,
        ];

        // Fill in the counts
        foreach ($ratingCounts as $rate => $count) {
            $ratingPercentages["{$rate}_star"] = round(($count / $totalReviews) * 100);
        }
        $totalPercentage = array_sum($ratingPercentages);
        $diff = 100 - $totalPercentage;
        if ($diff != 0) {
            $maxKey = array_keys($ratingPercentages, max($ratingPercentages))[0];
            $ratingPercentages[$maxKey] += $diff;
        }
        $overallRating = (clone $reviewsQuery)->avg('rate');
        $totalApprovedReviews = count($countUserReview);

        return [
            'total_approved_user_reviews' => $totalApprovedReviews,
            'overall_rating' => round($overallRating, 2),
            'rating_percentages' => $ratingPercentages,
        ];
    }

    public static function isValidType($type, $id): bool
    {
        if ($type == 0 || $type == '0') {
            return (bool) Design::where("string_id", $id)->exists();
        } else if ($type == 1 || $type == '1') {
            return (bool) NewCategory::where("string_id", $id)->exists();
        } else if ($type == 2 || $type == '2') {
            return (bool) SpecialPage::where("string_id", $id)->exists();
        } else if ($type == 3 || $type == '3') {
            return (bool) SpecialKeyword::where("string_id", $id)->exists();
        } else if ($type == 4 || $type == '4') {
            return (bool) Category::where("string_id", $id)->exists();
        } else {
            return false;
        }
    }


    public function getUniqueName(Request $request)
    {
        $request->validate([
            'uid' => 'required'
        ]);

        $uid = $request->input('uid');

        $user = UserData::where('uid', $uid)->first();

        if (!$user || !isset($user->email)) {
            return response()->json(['error' => 'User not found or email missing'], 404);
        }

        $emailPrefix = strstr($user->email, '@', true);

        do {
            $randomSuffix = Str::random(5);
            $uniqueName = $emailPrefix . '_' . $randomSuffix;
            $exists = UserData::where('user_name', $uniqueName)->where('uid', '!=', $uid)->exists();
        } while ($exists);

        $user->name = $uniqueName;
        $user->save();

        return response()->json([
            'unique_name' => $uniqueName
        ]);
    }
}