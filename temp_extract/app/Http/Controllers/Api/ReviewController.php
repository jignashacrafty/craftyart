<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Review;

class ReviewController extends ApiController
{
    public function postReview(Request $request): array|string
    {

        if ($this->isFakeRequestAndUser($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $exists = Review::where('user_id', $this->uid)->exists();
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

        $res = new Review();
        $res->user_id = $this->uid;
        $res->feedback = $feedback;
        $res->rate = $rate;
        $res->save();

        $review = Review::where('user_id', $this->uid)->first();
        if ($review) {
            $userReview = \App\Http\Controllers\HelperController::getUserInfo($review);
        } else {
            $userReview = null; // Handle the case where no review is found
        }

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, 'Review sent successfully. Your review will be displayed after approval.', ['data' => $userReview]));
    }

    public function allAnalyticReviews(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Loaded", $this->getTotalRating()));
    }

    public function getUserReview(Request $request): array|string
    {

        if ($this->isFakeRequestAndUser($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $review = Review::where('user_id', $this->uid)->first();
        if ($review) {
            $userReview = \App\Http\Controllers\HelperController::getUserInfo($review);
        } else {
            $userReview = null; // Handle the case where no review is found
        }

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Loaded", ['data' => $userReview]));

    }

    public function getReviews(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $userId = $this->uid;
        $limit = 5;

        $page = $request->input('page', 1);

        $reviewsQuery = Review::query()->with(['user'])->where('is_approve', 1);
        if ($userId) {
            $reviewsQuery = $reviewsQuery->where('user_id', "!=", $userId);
        }
        $reviewsQuery = $reviewsQuery->orderBy('id', 'DESC')->paginate($limit, ['*'], 'page', $page);

        $reviews = [];

        foreach ($reviewsQuery->items() as $review) {
            $reviews[] = \App\Http\Controllers\HelperController::getUserInfo($review);
        }

        $review = Review::where('user_id', $this->uid)->first();
        if ($review) {
            $userReview = \App\Http\Controllers\HelperController::getUserInfo($review);
        } else {
            $userReview = null; // Handle the case where no review is found
        }

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Loaded", [
            'analytics' => $this->getTotalRating(),
            'userReview' => $userReview,
            'datas' => $reviews,
            'isLastPage' => $reviewsQuery->currentPage() >= $reviewsQuery->lastPage(),
        ]));
    }

    public function deleteReview(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $delete = Review::where('id', $request->id)->where('user_id', $this->uid)->delete();

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

        $update = Review::where('id', $request->id)->where('user_id', $this->uid)->update($input);

        if ($update) {
            $review = Review::where('user_id', $this->uid)->first();
            if ($review) {
                $userReview = \App\Http\Controllers\HelperController::getUserInfo($review);
            } else {
                $userReview = null;
            }
            return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Review updated successfully. Your review will be displayed after approval.", ['data' => $userReview]));
        }

        return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Invalid request"));
    }

    private function getTotalRating(): array
    {
        $reviewsQuery = Review::query()->where('is_approve', 1);
        $totalReviews = (clone $reviewsQuery)->count();
        $countUserReview = Review::query()->select('user_id', DB::raw('count(*) as total_reviews'))->where('is_approve', 1)->groupBy('user_id')->get();
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
            'overall_rating' => round($overallRating * 2) / 2,
            'rating_percentages' => $ratingPercentages,
        ];
    }
}

