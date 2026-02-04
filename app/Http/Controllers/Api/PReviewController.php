<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Utils\ContentManager;
use App\Models\Category;
use App\Models\VirtualCategory;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\PReview;
use App\Models\Design;
use App\Models\NewCategory;
use App\Models\SpecialPage;
use App\Models\SpecialKeyword;

class PReviewController extends ApiController
{

    private static array $defaultReviews = [
        ["user_id" => null, "name" => "Priya Mehta", "email" => null, "photo_uri" => null, "rate" => 4, "feedback" => "I absolutely love using Crafty Art for all my invitation needs! Crafty Art has a wide variety of editable templates that are not only beautiful but also super easy to customize. The quality and creativity that Crafty Art offers are unmatched. It's my go-to design platform every time I need something special. Highly recommended for anyone looking to create stunning invitations with ease!", "created_at" => "19-04-2025"],
        ["user_id" => null, "name" => "Rahul Deshmukh", "email" => null, "photo_uri" => null, "rate" => 5, "feedback" => "If you're looking for unique and professional-looking designs, Crafty Art is the best place to go. I recently used Crafty Art to make an invitation story and was amazed by how smooth the entire process was. The site is user-friendly, affordable, and filled with options for every occasion. Thanks to Crafty Art, I could create something memorable without hiring a designer. I’ll definitely be back for more templates!", "created_at" => "04-11-2024"],
        ["user_id" => null, "name" => "Heta Jariwala", "email" => null, "photo_uri" => null, "rate" => 5, "feedback" => "Crafty Art is one of the best platform for graphic design. They have a lot of designers, so they gave modern & new designs for every ceremony. For customization on Crafty Art’s editor was really good, it is easy to use & has a user-friendly interface. Great Experience working with Crafty Art.", "created_at" => "18-1-2025"],
        ["user_id" => null, "name" => "Ramesh Pawar", "email" => null, "photo_uri" => null, "rate" => 4.5, "feedback" => "Crafty Art is the best solution for every ceremony invitation card. This platform is updated daily, they provide new designs daily for every ceremony & and all designs are easy to use. Crafty Art also offers a video invitation for any ceremony. To edit any designs, not need any type of design skills. Love the smooth editing platform, Crafty Art.", "created_at" => "7-7-2024"],
        ["user_id" => null, "name" => "Arjun Mehra", "email" => null, "photo_uri" => null, "rate" => 5, "feedback" => "Crafty Art is amazing! Whether you need something simple or something really unique, they have it all. The designs are creative, the quality is top-notch, and the service is super reliable. Highly recommended!", "created_at" => "30-06-2025"],
    ];

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
            $userReview = PReviewController::getUserInfo($review);
        } else {
            $userReview = null; // Handle the case where no review is found
        }

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, 'Review sent successfully. Your review will be displayed after approval.', ['data' => $userReview]));
    }

    public function getReviews(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $data = PReviewController::getPReviews($this->uid, $request->type, $request->id, $request->input('page', 1));

        if (!$data['success']) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, $data['msg']));
        } else {
            return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Loaded", $data['data']));
        }
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
                $userReview = PReviewController::getUserInfo($review);
            } else {
                $userReview = null;
            }
            return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Review updated successfully. Your review will be displayed after approval.", ['data' => $userReview]));
        }

        return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Invalid request"));
    }

    public static function getPReviews($userId, $type, $id, $page): array
    {

        if (is_null($id) || is_null($type) || !in_array($type, PReviewController::$types) || !PReviewController::isValidType($type, $id)) {
            return ResponseHandler::sendRealResponse(new ResponseInterface(401, false, "Invalid params"));
        }

        $limit = HelperController::getPaginationLimit(size: 50);

        $reviewsQuery = PReview::query()->with(['user'])->where('p_type', $type)->where('p_id', $id)->where('is_approve', 1);
        if ($userId) {
//            $reviewsQuery = $reviewsQuery->where('user_id', "!=", $userId);
        }
        $reviewsQuery = $reviewsQuery->orderBy('id', 'DESC')->paginate($limit, ['*'], 'page', $page);

        $reviews = [];

        if ($page == 1 && $reviewsQuery->total() === 0) {
            $defaultReviews = json_decode(json_encode(self::$defaultReviews));
            foreach ($defaultReviews as $review) {
                $review->created_at = Carbon::createFromFormat('d-m-Y', $review->created_at);
                $reviews[] = PReviewController::getUserInfo($review);
            }
        }

        foreach ($reviewsQuery->items() as $review) {
            $reviews[] = PReviewController::getUserInfo($review);
        }

        $userReview = null;
        if ($userId) {
            $review = PReview::where('p_type', $type)->where('p_id', $id)->where('user_id', $userId)->first();
            if ($review) {
                $userReview = PReviewController::getUserInfo($review);
            }
        }

        return ResponseHandler::sendRealResponse(new ResponseInterface(200, true, "Loaded", [
            "data" => [
                'analytics' => PReviewController::getTotalRating($type, $id),
                'userReview' => $userReview,
                'datas' => $reviews,
                'isLastPage' => $reviewsQuery->currentPage() >= $reviewsQuery->lastPage(),
            ]
        ]));
    }

    private static function getUserInfo($review): array
    {
        $url = HelperController::$mediaUrl;

        if ($review->user_id == null) {
            $user = [
                'id' => 'anonymous',
                'name' => $review->name,
                'email' => $review->email,
                'profile_photo' => ContentManager::getStorageLink($review->photo_uri),
            ];
        } else {
            $profile_photo = $review->user->photo_uri;
            if (str_contains($review->user->photo_uri, 'uploadedFiles/')) {
                $profile_photo = $url . $review->user->photo_uri;
            }
            $user = [
                'id' => $review->user->uid,
                'name' => $review->user->name,
                'email' => $review->user->email,
                'profile_photo' => $profile_photo,
            ];
        }

        return [
            'id' => $review->id ?? "",
            'feedback' => $review->feedback,
            'rate' => $review->rate,
            'date' => $review->created_at->format('Y/m/d'),
            'user' => $user,
        ];
    }

    private static function getTotalRating($type, $id): array
    {
        $reviewsQuery = PReview::query()->where('p_type', $type)->where('p_id', $id)->where('is_approve', 1);

        $totalReviews = (clone $reviewsQuery)->count();

        if ($totalReviews === 0) {
            $defaultReviews = collect(self::$defaultReviews);
            return self::calculateRatingStats($defaultReviews);
        }

        $realReviews = (clone $reviewsQuery)->get(['rate']);
        $combinedReviews = $realReviews->map(fn($r) => ['rate' => $r->rate])->values();

        return self::calculateRatingStats($combinedReviews);

//        if ($totalReviews == 0) {
//            return [
//                'total_approved_user_reviews' => 0,
//                'overall_rating' => 0,
//                'rating_percentages' => [
//                    '1_star' => 0,
//                    '2_star' => 0,
//                    '3_star' => 0,
//                    '4_star' => 0,
//                    '5_star' => 0,
//                ],
//            ];
//        }
//
//        $ratingCounts = (clone $reviewsQuery)->selectRaw('rate, COUNT(*) as count')->groupBy('rate')->pluck('count', 'rate');
//        $ratingPercentages = [
//            '1_star' => 0,
//            '2_star' => 0,
//            '3_star' => 0,
//            '4_star' => 0,
//            '5_star' => 0,
//        ];
//
//        // Fill in the counts
//        foreach ($ratingCounts as $rate => $count) {
//            $ratingPercentages["{$rate}_star"] = round(($count / $totalReviews) * 100, 1);
//        }
//        $totalPercentage = array_sum($ratingPercentages);
//        $diff = 100 - $totalPercentage;
//        if ($diff != 0) {
//            $maxKey = array_keys($ratingPercentages, max($ratingPercentages))[0];
//            $ratingPercentages[$maxKey] += $diff;
//        }
//        $overallRating = (clone $reviewsQuery)->avg('rate');
//
//        return [
//            'total_approved_user_reviews' => $totalReviews,
//            'overall_rating' => round($overallRating, 1),
//            'rating_percentages' => $ratingPercentages,
//        ];
    }

    public static function isValidType($type, $id): bool
    {
        if ($type == 0 || $type == '0') {
            return (bool)Design::where("string_id", $id)->exists();
        } else if ($type == 1 || $type == '1') {
            return (bool)NewCategory::where("string_id", $id)->exists();
        } else if ($type == 2 || $type == '2') {
            return (bool)SpecialPage::where("string_id", $id)->exists();
        } else if ($type == 3 || $type == '3') {
            return (bool)SpecialKeyword::where("string_id", $id)->exists();
        } else if ($type == 4 || $type == '4') {
            return (bool)Category::where("string_id", $id)->exists();
        } else if ($type == 5 || $type == '5') {
            return (bool)VirtualCategory::where("string_id", $id)->exists();
        } else {
            return false;
        }
    }

    private static function calculateRatingStats($reviews): array
    {
        $total = $reviews->count();

        if ($total === 0) {
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

        // Count how many for each star level
        $ratingCounts = $reviews->groupBy(fn($r) => floor($r['rate'] ?? $r->rate))->map->count();

        $ratingPercentages = [
            '1_star' => 0,
            '2_star' => 0,
            '3_star' => 0,
            '4_star' => 0,
            '5_star' => 0,
        ];

        foreach ($ratingCounts as $rate => $count) {
            $key = $rate . '_star';
            if (isset($ratingPercentages[$key])) {
                $ratingPercentages[$key] = round(($count / $total) * 100, 1);
            }
        }

        // Adjust percentage to total exactly 100%
        $totalPercentage = array_sum($ratingPercentages);
        $diff = 100 - $totalPercentage;
        if ($diff != 0) {
            $maxKey = array_keys($ratingPercentages, max($ratingPercentages))[0];
            $ratingPercentages[$maxKey] += $diff;
        }

        // Calculate overall rating
        $overallRating = round($reviews->avg(fn($r) => $r['rate'] ?? $r->rate), 1);

        return [
            'total_approved_user_reviews' => $total,
            'overall_rating' => $overallRating,
            'rating_percentages' => $ratingPercentages,
        ];
    }
}

