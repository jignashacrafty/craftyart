<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\Caricature\Attire;
use App\Models\CaricatureCategory;
use App\Models\LikedProduct;
use Illuminate\Http\Request;
use App\Models\Video\VideoCat;
use App\Models\AppCategory;
use App\Models\Category;
use App\Models\StickerCategory;
use App\Models\StickerMode;
use App\Models\BgCategory;
use App\Models\Design;
use App\Models\UserData;
use App\Models\InAppType;
use App\Models\User;
use App\Models\FontFamily;
use App\Models\NewCategory;
use App\Models\PurchaseHistory;
use App\Models\TransactionLog;
use App\Models\TemplateRate;
use App\Models\NewSearchTag;
use App\Models\Video\VideoPurchaseHistory;
use App\Models\Video\VideoTemplate;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class HelperController extends Controller
{

    public static array $oldMediaUrl = ["https://panel.craftyartapp.com/templates/", "https://media.craftyartapp.com/","http://192.168.29.17/templates2/"];
    public static string $mediaUrl = "https://media.craftyartapp.com/";
    public static string $webPageUrl = "https://www.craftyartapp.com/";
    public static $frontendUrl = "https://www.craftyartapp.com/";


    public static function getItemData($uid, $catRow, $item, $thumbArray, $swap = true, $isNewCategory = false, $useTemplateLink = false, $idOfPage = null): array
    {
        if ($swap) {
            $thumbArray = HelperController::swapThumbs($thumbArray, $item->default_thumb_pos);
        }

        foreach ($thumbArray as $key => $value) {
            $thumbArray[$key] = HelperController::$mediaUrl . $value;
        }

        $size = sizeof($thumbArray);
        $payment = HelperController::getTemplateRates($size, $item->is_premium == 1);

        $newTags = [];

        if (isset($item->new_related_tags)) {
            $new_related_tags = implode(",", json_decode($item->new_related_tags, true));
            foreach (explode(',', $new_related_tags) as $value) {
                $dataRes = NewSearchTag::where('id', $value)->first();
                if ($dataRes) {
                    $newTags[] = $dataRes->name;
                }
            }
        }

        //         https://marketplace.canva.com/EAGAQCh3KfU/3/0/1143w/canva-6abqbwiBcF4.jpg 1143w,
//         https://marketplace.canva.com/EAGAQCh3KfU/3/0/286w/canva-r5VZ_q46RqE.jpg 286w,
//         https://marketplace.canva.com/EAGAQCh3KfU/3/0/571w/canva-RSlPgmv8sOw.jpg 571w,
//         https://marketplace.canva.com/EAGAQCh3KfU/3/0/71w/canva-Eix8JKMOYFk.jpg 71w

        if (isset($item->additional_thumb)) {
            array_unshift($thumbArray, HelperController::$mediaUrl . $item->additional_thumb);
        }

        $newSrcSet = [];
        foreach ($thumbArray as $value) {
            // $srcSet = $value . "?width=" . 1143 . "&height=" . HelperController::calculateHeight($item->width, $item->height, 1143) . " 1143w, ";
            // $srcSet = $srcSet . $value . "?width=" . 571 . "&height=" . HelperController::calculateHeight($item->width, $item->height, 571) . " 571w, ";
            // $srcSet = $srcSet . $value . "?width=" . 286 . "&height=" . HelperController::calculateHeight($item->width, $item->height, 286) . " 286w, ";
            // $srcSet = $srcSet . $value . "?width=" . 252 . "&height=" . HelperController::calculateHeight($item->width, $item->height, 252) . " 252w, ";
            // $srcSet = $srcSet . $value . "?width=" . 150 . "&height=" . HelperController::calculateHeight($item->width, $item->height, 150) . " 150w, ";
            // $srcSet = $srcSet . $value . "?width=" . 100 . "&height=" . HelperController::calculateHeight($item->width, $item->height, 100) . " 100w, ";
            // $srcSet = $srcSet . $value . "?width=" . 71 . "&height=" . HelperController::calculateHeight($item->width, $item->height, 71) . " 71w";

            $srcSet = $value . " 1143w, ";
            $srcSet = $value . " 571w, ";
            $srcSet = $value . " 286w, ";
            $srcSet = $value . " 252w, ";
            $srcSet = $value . " 150w, ";
            $srcSet = $value . " 100w, ";
            $srcSet = $value . " 71w";

            $data = [];
            $data['img'] = $value;
            $data['srcSet'] = $srcSet;
            $data['sizes'] = "(max-width: 400px) 80vw, (max-width: 600px) 50vw, (max-width: 900px) 33vw, (max-width: 1200px) 25vw, (max-width: 1500px) 20vw, 17vw";
            $newSrcSet[] = $data;
        }

        if ($useTemplateLink) {
            $category_id_name = 'p/' . $item->id_name;
            if ($idOfPage && $idOfPage != $catRow->id) {
                $category_id_name = HelperController::getRedirectLink($catRow, $isNewCategory);
            }
        } else {
            $category_id_name = HelperController::getRedirectLink($catRow, $isNewCategory);
        }


        $isStarred = LikedProduct::where("user_id", $uid)->where("product_id", $item->string_id)->exists();

        return array(
            'category_id' => $catRow->id,
            'category_id_name' => $category_id_name,
            'category_name' => $catRow->category_name,
//            'category_size' => $catRow->size,
//            'template_id' => $item->id,
//            'string_id' => $item->string_id,
//            'id_name' => $item->id_name ?? $item->string_id,
//            'template_name' => $item->post_name,
//            'template_thumb' => HelperController::$mediaUrl . $item->post_thumb,
//            'thumbArray' => $thumbArray,
//            'thumbSrcSets' => $newSrcSet,
//            'color' => "#efefef",
//            'video' => $item->video_thumb != null ? HelperController::$mediaUrl . $item->video_thumb : null,
//            'width' => $item->width,
//            'height' => $item->height,
//            'pages' => sizeof($thumbArray),
//            'tags' => json_decode($item->related_tags),
            'related_tags' => json_decode($item->related_tags),
//            'new_tags' => $newTags,
//            'latest' => false,
//            'isStarred' => (bool) $isStarred,
//            'is_premium' => true,
//            'auto_create' => $item->auto_create == 1,
//            'created_at' => Carbon::parse($item->created_at)->toAtomString(),
//            'payment' => $payment,
//            'inrVal' => $payment['inrVal'],
//            'usdVal' => $payment['usdVal'],
//            'inrAmount' => $payment['inrAmount'],
//            'usdAmount' => $payment['usdAmount'],
        );
    }


    private static function getRedirectLink($catRow, $isNewCategory)
    {
        if (!$catRow) {
            return null;
        }
        $category_id_name = $catRow->id_name;
        if ($isNewCategory) {
            $parentCateRow = NewCategory::find($catRow->parent_category_id);
            if ($parentCateRow) {
                $category_id_name = $parentCateRow->id_name . '/' . $catRow->id_name;
            }
        }
        return $category_id_name;
    }

    public static function calculateHeight($width, $height, $newWidth): float|int
    {
        return ($height / $width) * $newWidth;
    }

    public static function getTemplateRates($size, $isPremium = true): array
    {
        $res = TemplateRate::find(1);
        if ($size == 0) {
            $size = 1;
        }

        $extraPage = $size - 1;

        // if ($isPremium) {
        //     $inrAmount = $res->tmp_base_inr + ($res->tmp_page_inr * $extraPage);
        //     $usdAmount = $res->tmp_base_usd + ($res->tmp_page_usd * $extraPage);
        //     if ($inrAmount > $res->tmp_max_inr) {
        //         $inrAmount = $res->tmp_max_inr;
        //     }
        //     if ($usdAmount > $res->tmp_max_usd) {
        //         $usdAmount = $res->tmp_max_usd;
        //     }
        // } else {
        //     $inrAmount = $res->free_tmp_base_inr + ($res->free_tmp_page_inr * $extraPage);
        //     $usdAmount = $res->free_tmp_base_usd + ($res->free_tmp_page_usd * $extraPage);
        //     if ($inrAmount > $res->free_tmp_max_inr) {
        //         $inrAmount = $res->free_tmp_max_inr;
        //     }
        //     if ($usdAmount > $res->free_tmp_max_usd) {
        //         $usdAmount = $res->free_tmp_max_usd;
        //     }
        // }

        $payment['inrVal'] = 999;
        $payment['usdVal'] = 9.99;
        $payment['inrAmount'] = '₹' . 999;
        $payment['usdAmount'] = '$' . 9.99;

        return $payment;
    }

    public static function getVideoRates($size): array
    {
        $res = TemplateRate::find(1);
        if ($size == 0) {
            $size = 1;
        }

        if ($size > 2) {
            $extraPage = $size - 2;
            $inrAmount = min($res->vid_max_inr, $res->vid_base_inr + ($res->vid_page_inr * $extraPage));
            $usdAmount = min($res->vid_max_usd, $res->vid_base_usd + ($res->vid_page_usd * $extraPage));
        } else {
            $inrAmount = $res->vid_base_inr;
            $usdAmount = $res->vid_base_usd;
        }

        $payment['inrVal'] = $inrAmount;
        $payment['usdVal'] = $usdAmount;
        $payment['inrAmount'] = '₹' . $inrAmount;
        $payment['usdAmount'] = '$' . $usdAmount;

        return $payment;
    }

    public static function calculateTemplateRate($size, $currency, $isPremium)
    {
        $payment = HelperController::getTemplateRates($size, $isPremium);
        return $currency == 'INR' ? $payment['inrVal'] : $payment['usdVal'];
    }

    public static function calculateVideoRate($size, $currency)
    {
        $payment = HelperController::getVideoRates($size);
        return $currency == 'INR' ? $payment['inrVal'] : $payment['usdVal'];
    }

    public static function templateSize($size): string
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $power = $size > 0 ? floor(log($size, 1024)) : 0;
        return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
    }

    public static function getFontFamilyName($id)
    {
        $res = FontFamily::find($id);
        if ($res != null) {
            return $res->fontFamily;
        }
        return "";
    }

    public static function getCatName($id)
    {
        $res = Category::find($id);
        if ($res != null) {
            return $res->category_name;
        }
        return "NA";
    }

    public static function getNewCatName($id)
    {
        $res = NewCategory::find($id);
        if ($res != null) {
            return $res->category_name;
        }
        return "NA";
    }

    public static function getNewCatNames($id, $isJoin = false)
    {


        $res = NewCategory::whereIn('id', $id)->pluck('category_name');
        if ($isJoin && $res != null) {
            return $res->implode(',');
        } else if ($res != null) {
            return $res->implode(',');
        }
        return "NA";
    }

    public static function getParentNewCatName($id, $isFromNewCategory = false)
    {

        $res = NewCategory::with('parentCategory')->where('id', $id)->first();
        if ($res) {
            if ($isFromNewCategory && $id != 0) {
                return $res->category_name;
            }
            if (isset($res->parentCategory->category_name)) {
                return $res->parentCategory->category_name;
            }
        }
        return "NA";
    }

    public static function checkStringFormat($string, $convert = false): string
    {

        if (preg_match("/^\['(.+)'\]$/", $string, $matches)) {
            if ($convert) {
                return $matches[1];
            }
            return $string;
        }
        return $string;
    }

    public static function getVCatName($id)
    {
        $res = VideoCat::find($id);
        if ($res != null) {
            return $res->category_name;
        }
        return "";
    }

    public static function getStickerCatName($id)
    {
        $res = StickerCategory::find($id);
        return $res->stk_category_name;
    }

    public static function getStickerMode($value)
    {
        $res = StickerMode::where('value', $value)->first();
        return $res->type;
    }

    public static function getBgCatName($id)
    {
        $res = BgCategory::find($id);
        return $res->bg_category_name;
    }

    public static function getUserName($id)
    {
        $res = DB::table('user_data')->where("uid", $id)->first();
        if ($res != null) {
            return $res->name;
        } else {
            return 'NONE';
        }
    }

    public static function getAppName($id)
    {
        $res = AppCategory::find($id);
        if (isset($res->app_name) && $res->app_name != null) {
            return $res->app_name;
        } else {
            return 'NONE';
        }
    }

    public static function getInAppType($type)
    {
        $res = InAppType::where('type', $type)->first();
        return $res->value;
    }

    public static function checkPlan($features, $rowFeatureId): bool
    {
        $isFlag = false;
        foreach ($features as $key => $feature) {
            Log::info('match == ' . ($feature->id == $rowFeatureId));
            if ($feature->id == $rowFeatureId) {
                $isFlag = true;
            }
        }
        if ($isFlag) {
            return true;
        }
        return false;
    }

    public static function stringContain($mainString, $containString): bool
    {
        if ($mainString == null) {
            return false;
        }

        $jsonArray = json_decode($mainString, true);

        if (is_array($jsonArray)) {
            foreach ($jsonArray as $json) {
                if ($containString == $json) {
                    return true;
                }
            }
        }
        return false;
        // if (str_contains($mainString, $containString)) {
        //     return true;
        // }
        // return false;
    }

    public static function randomNameGenerator(): float|int|string
    {
        return \Carbon\Carbon::now()->timestamp;
    }

    public static function getOrientations(): array
    {
        return [
            "portrait",
            "landscape",
            "square"
        ];
    }

    public static function getPrice(): array
    {
        return [
            "Free",
            "Premium",
        ];
    }

    public static function getAnimation(): array
    {
        return [
            "true" => 1,
            "false" => 0,
        ];
    }

    public static function filterArrayOrder($selectedValuesString, $mainArray, $column, $tg = false): array
    {
        if (is_string($selectedValuesString)) {
            $selectionArray = json_decode($selectedValuesString);
            $themesMap = [];
            foreach ($mainArray as $theme) {
                $themesMap[$theme->{$column}] = $theme;
            }
            $reorderedThemes = [];
            if (is_array($selectionArray)) {
                foreach ($selectionArray as $themeName) {
                    if (isset($themesMap[$themeName])) {
                        $reorderedThemes[] = $themesMap[$themeName];
                    }
                }
                foreach ($mainArray as $theme) {
                    if (!in_array($theme->{$column}, $selectionArray)) {
                        $reorderedThemes[] = $theme;
                    }
                }
            } else {
                $reorderedThemes = $mainArray;
            }
            return $reorderedThemes;
        }
        return [];
    }

    public static function redirectToLogin()
    {
        return view('home');
    }

    public static function dateRange($startDate, $endDate): string
    {
        if ($startDate != '') {
            return $startDate . ' - ' . $endDate;
        } else {
            return "None";
        }
    }

    public static function isAdminOrFenil($id): bool
    {
        $user = User::find($id);
        if ($user->user_type == 1 || $id === 40) {
            return true;
        }
        return false;
    }

    public static function isBanner($isBanner): string
    {
        if ($isBanner == '1') {
            return "TRUE";
        } else {
            return "FALSE";
        }
    }

    public static function canCancle($canCancle): string
    {
        if ($canCancle == '1') {
            return "TRUE";
        } else {
            return "FALSE";
        }
    }

    public static function isPremium($isPremium): string
    {
        if ($isPremium == '1') {
            return "TRUE";
        } else {
            return "FALSE";
        }
    }

    public static function checkStatus($status): string
    {
        if ($status == '1') {
            return "LIVE";
        } else {
            return "NOT LIVE";
        }
    }

    public static function checkSubsStatus($status): string
    {
        if ($status == '1') {
            return "Active";
        } else {
            return "Expired";
        }
    }

    public static function getSubsColor($status): string
    {
        if ($status == '1') {
            return "#2EC4B6";
        } else {
            return "#FF0000";
        }
    }

    public static function getMailOrNumber($user)
    {
        if ($user->email == '' || $user->email == null) {
            return $user->country_code . ' ' . $user->number;
        } else {
            return $user->email;
        }
    }

    public static function isAdminOrManger($id): bool
    {
        $user = User::find($id);
        if (isset($user) && $user->user_type == 1 || $user->user_type == 2 || $user->user_type == 5) {
            return true;
        }
        return false;
    }

    public static function isAdmin($userType): bool
    {
        if ($userType == 1) {
            return true;
        }
        return false;
    }

    public static function isManager($userType): bool
    {
        if ($userType == 2) {
            return true;
        }
        return false;
    }

    public static function isEmployee($userType): bool
    {
        if ($userType == 2) {
            return true;
        }
        return false;
    }

    public static function getUserType($userType): string
    {
        if ($userType == 1) {
            return "Admin";
        } else if ($userType == 2) {
            return "Manager";
        } else if ($userType == 3) {
            return "Designer";
        } else {
            return "Unknown";
        }
    }

    public static function getEmployeeName($id)
    {
        if ($id == 0) {
            return "Admin";
        }
        $res = User::find($id);
        if ($res) {
            return $res->name;
        } else {
            return "Admin";
        }
    }

    public static function getUserClass($userId)
    {
        return UserData::where('uid', $userId)->first();
    }

    public static function getOfferWithPrice($actual_price, $price): string
    {

        $returnString = "(" . $actual_price . ") (" . $price . ") ";

        $offer_msg = "No offer";
        try {
            if ($actual_price != $price) {
                $disc = (($actual_price - $price) / $actual_price) * 100;
                $discount = (int) ($disc);
                $offer_msg = "(" . $discount . "% off)";
            }
        } catch (\Exception $e) {
            $offer_msg = "No offer";
        }

        return $returnString . $offer_msg;
    }

    public static function checkRequestFields($request, $fieldArray): array
    {
        $missingArray = '';
        foreach ($fieldArray as $field) {
            if (!$request->has($field)) {
                if ($missingArray == '') {
                    $missingArray = $field;
                } else {
                    $missingArray = $missingArray . ', ' . $field;
                }
            }
        }

        $response['success'] = 1;

        if ($missingArray != '') {
            $response['success'] = 0;
            $response['message'] = 'Missing parameters';
            $response['params'] = $missingArray;
        }

        return $response;
    }

    public static function getUploaderName($id)
    {
        if (isset($id) && $id != null) {
            $user = User::find($id);
            return (isset($user->name) && $user->name != null) ? $user->name : "";
        }
    }

    public static function getTemplateName($tempId)
    {
        $design = Design::where('id', $tempId)->first();
        return ($design) ? $design->post_name : '';
    }

    public static function getVideoTemplateName($tempId)
    {
        $videoTemplate = VideoTemplate::where('id', $tempId)->first();
        return ($videoTemplate) ? $videoTemplate->video_name : '';
    }

    public static function getAllActiveSubscribers($request)
    {
        $currentDate = Carbon::now();
        $currentDate = now();
        $temp_data_count = DB::table('user_data')
            ->select('user_data.*', 'transaction_logs.id as log_id', 'transaction_logs.expired_at')
            ->join(DB::raw('(SELECT MAX(id) as max_log_id, user_id FROM transaction_logs WHERE expired_at > "' . $currentDate . '" GROUP BY user_id) as latest_logs'), function ($join) {
                $join->on(DB::raw('BINARY user_data.uid'), '=', DB::raw('BINARY latest_logs.user_id'));
            })
            ->join('transaction_logs', function ($join) {
                $join->on('transaction_logs.id', '=', 'latest_logs.max_log_id');
            })->orderBy('user_data.created_at', 'desc')->count();

        return $temp_data_count;
    }

    public static function getAllExpiredSubscribers($request)
    {
        $currentDate = Carbon::now();
        $currentDate = now();
        $temp_data_count = DB::table('user_data')
            ->select('user_data.*', 'transaction_logs.id as log_id', 'transaction_logs.expired_at')
            ->join(DB::raw('(SELECT MAX(id) as max_log_id, user_id FROM transaction_logs WHERE expired_at < "' . $currentDate . '" GROUP BY user_id) as latest_logs'), function ($join) {
                $join->on(DB::raw('BINARY user_data.uid'), '=', DB::raw('BINARY latest_logs.user_id'));
            })
            ->join('transaction_logs', function ($join) {
                $join->on('transaction_logs.id', '=', 'latest_logs.max_log_id');
            })->orderBy('user_data.created_at', 'desc')->count();

        return $temp_data_count;
    }

    public static function getAllUpCommingExpiredSubscribers($request)
    {
        $temp_data = [];
        $temp_data_count = 0;
        $currentDate = now();
        $daysBefore = 7;
        $expirationDate = $currentDate->copy()->addDays($daysBefore);
        $temp_data_count = DB::table('user_data')->select('user_data.*', 'transaction_logs.id as log_id', 'transaction_logs.expired_at')
            ->join(DB::raw('(SELECT MAX(id) as max_log_id, user_id FROM transaction_logs WHERE expired_at > "' . $currentDate . '" AND expired_at <= "' . $expirationDate . '" GROUP BY user_id) as latest_logs'), function ($join) {
                $join->on(DB::raw('BINARY user_data.uid'), '=', DB::raw('BINARY latest_logs.user_id'));
            })
            ->join('transaction_logs', function ($join) {
                $join->on('transaction_logs.id', '=', 'latest_logs.max_log_id');
            })->orderBy('user_data.created_at', 'desc')->count();
        return $temp_data_count;
    }

    public static function getParentCatName($parentCategoryId, $model, $fieldName = "")
    {
        $parentData = $model->where('id', $parentCategoryId)->first();
        if (!$parentData) {
            return '';
        }
        if (!empty($fieldName) && isset($parentData->$fieldName)) {
            return $parentData->$fieldName;
        }

        return isset($parentData->category_name) ? $parentData->category_name : '';
    }

    public static function generateStars($rate)
    {
        $fullStar = '<span class="fa fa-star checked"></span>';
        $halfStar = '<span class="fa fa-star-half-alt checked"></span>';
        $emptyStar = '<span class="fa fa-star"></span>';
        $ratingHtml = '';
        $wholeStars = floor($rate);
        $hasHalfStar = ($rate - $wholeStars) >= 0.5;
        for ($i = 0; $i < $wholeStars; $i++) {
            $ratingHtml .= $fullStar;
        }
        if ($hasHalfStar) {
            $ratingHtml .= $halfStar;
        }

        for ($i = $wholeStars + ($hasHalfStar ? 1 : 0); $i < 5; $i++) {
            $ratingHtml .= $emptyStar;
        }
        return $ratingHtml;
    }

    public static function getUserSubscriptionCount($userID)
    {
        $countSubscription = TransactionLog::where('user_id', $userID)->count();
        return $countSubscription;
    }

    public static function getUserTemplateCount($userID)
    {
        $countTemplate = PurchaseHistory::where('user_id', $userID)->count();
        return $countTemplate;
    }

    public static function getUserVideoCount($userID)
    {
        $countTemplate = VideoPurchaseHistory::where('user_id', $userID)->count();
        return $countTemplate;
    }

    public static function swapThumbs($thumbArray, $j)
    {
        if ($j < 0 || $j >= count($thumbArray)) {
            return $thumbArray;
        }

        $element = $thumbArray[$j];
        unset($thumbArray[$j]);

        $thumbArray = array_values($thumbArray);

        array_unshift($thumbArray, $element);

        return $thumbArray;
    }

    public static function generateID($id = "", $length = 10): string
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return $id . substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
    }

    public static function getIpAndCountry(Request $request): array
    {
        $ipAddress = $request->ip();

        $location = GeoIP($ipAddress);

        $countryCode = $location['iso_code'];
        $countryName = $location['country'];
        $currency = $location['currency'];

        return [
            'ip' => $ipAddress,
            'cc' => $countryCode,
            'cn' => $countryName,
            'cur' => $currency
        ];
    }

    public static function getTopKeywords($topKeywords): array
    {
        if (!empty($topKeywords)) {
            foreach ($topKeywords as &$keyword) {
                if (isset($keyword->openinnewtab)) {
                    $keyword->openinnewtab = (int) $keyword->openinnewtab;
                }
                if (isset($keyword->nofollow)) {
                    $keyword->nofollow = (int) $keyword->nofollow;
                }
            }
            unset($keyword);
        }

        return $topKeywords;
    }

    public static function generatePublicUrl($path): string
    {
        return config('filesystems.storage_url').$path;
//        return env("APP_ENV") == 'local' ? asset($path) : HelperController::$mediaUrl . $path;
    }

    public static function getCTA($cta)
    {
        if (!$cta) {
            return null;
        }

        $ctaData = json_decode($cta, true);
        foreach ($ctaData as $key => &$ctaItem) {
            if (isset($ctaItem['image']['src'])) {
                $ctaItem['image']['src'] = HelperController::generatePublicUrl($ctaItem['image']['src']);
            }
            if (isset($ctaItem['images']) && is_array($ctaItem['images'])) {
                foreach ($ctaItem['images'] as &$imageItem) {
                    if (isset($imageItem['src'])) {
                        $imageItem['src'] = HelperController::generatePublicUrl($imageItem['src']);
                    }
                }
            }
            if (isset($ctaItem['stepsection']) && is_array($ctaItem['stepsection'])) {
                foreach ($ctaItem['stepsection'] as &$step) {
                    if (isset($step['image']['src'])) {
                        $step['image']['src'] = HelperController::generatePublicUrl($step['image']['src']);
                    }
                }
            }
        }
        return $ctaData;
    }

    public static function generateStringIds($length = 10, $prefix = '', $source = null, $column = 'string_id')
    {
        $stringId = Str::lower(Str::random($length));
        $finalId = $prefix ? $prefix . '_' . $stringId : $stringId;
        if ($source && class_exists($source)) {
            $modelClass = $source;
            $modelInstance = new $modelClass;
            $table = $modelInstance->getTable();
            if (Schema::hasColumn($table, $column)) {
                do {
                    $stringId = Str::lower(Str::random($length));
                    $finalId = $prefix ? $prefix . '_' . $stringId : $stringId;
                    $exists = $modelClass::where($column, $finalId)->exists();
                } while ($exists);
            }
        }
        return $finalId;
    }

    public static function getFrontendPageUrl($type, $slug): string
    {
        if ($type == 0) {
            return self::$frontendUrl . 'templates/p/' . $slug;
        } else if ($type == 1 || $type == 4) {
            return self::$frontendUrl . 'templates/' . $slug;
        } else if ($type == 2) {
            return self::$frontendUrl.$slug;
        } else if ($type == 3) {
            return self::$frontendUrl . 'k/' . $slug;
        }
        return self::$frontendUrl;
    }

    public static function getTemplateCount($count,$primaryKeyword):string
    {
        if($primaryKeyword){
            return $count."+ ".$primaryKeyword;
        } else {
            if($count > 20){
                return $count."+ ".$primaryKeyword." Templates";
            } else {
                if($count <= 1 )
                    return $count." ".$primaryKeyword." Template";
                else return $count." ".$primaryKeyword." Templates";
            }
        }
    }

    public static function buildCanonicalLink($canonicalLink,$frontendUrl,$page): string
    {
        if(!empty($canonicalLink)){
            return $canonicalLink;
        }
        if($page != 1){
            return $frontendUrl."?page=".$page;
        }
        return $frontendUrl;
    }

    public static function newCatChildUpdatedAt($parentCatId): string
    {
        $time = now()->toDateTimeString();
        if (isset($parentCatId)) {
            $parentCat = CaricatureCategory::find($parentCatId);
            if ($parentCat) {
                $parentCat->child_updated_at = $time;
                $parentCat->save();
                if (isset($parentCat->parent_category_id) && $parentCat->parent_category_id !== 0) {
                    $grandParentCat = CaricatureCategory::find($parentCat->parent_category_id);
                    if ($grandParentCat) {
                        $grandParentCat->child_updated_at = $time;
                        $grandParentCat->save();
                    }
                }
            }
        }
        return $time;
    }

    public static function processCTA($request,$path = 'uploadedFiles/cta_images/')
    {
        $cta = [];
        $ctaDatas = $request->input('cta_data');
        if ($ctaDatas) {
            foreach ($ctaDatas as $ctaDataJson) {
                $ctaData = json_decode(base64_decode($ctaDataJson), true);
                foreach ($ctaData as $key => $value) {
                    $ctavar = [];
                    $ctavar = $value;
                    if ($key == "cta_convert" || $key == "cta_hero" || $key == "cta_process") {
                        $ctavar['image']['src'] = HelperController::processBase64Image($value['image']['src'],$path);
                    } else if ($key == "cta_ads") {
                        $ctavar['image']['src'] = HelperController::processBase64Image($value['image']['src'],$path);
                        $ctavar['button']['src'] = HelperController::processBase64Image($value['button']['src'],$path);
                    } else if ($key == "cta_scrollable") {
                        if (isset($value['images']) && is_array($value['images'])) {
                            $processedSteps = array_map(function ($step) {
                                $step['src'] = HelperController::processBase64Image($step['src'],$path);
                                return $step;
                            }, $value['images']);
                            $ctavar['images'] = $processedSteps;
                        }
                    } else if ($key == "cta_how_to_make") {
                        if (isset($value['stepsection']) && is_array($value['stepsection'])) {
                            $processedSteps = array_map(function ($step) {
                                $step['image']['src'] = HelperController::processBase64Image($step['image']['src'],$path);
                                return $step;
                            }, $value['stepsection']);
                            $ctavar['stepsection'] = $processedSteps;
                        }
                    }
                    $cta[$key] = $ctavar;
                }
            }
        }
        return $cta;
    }

    public static function processBase64Image($base64String, $directory = 'uploadedFiles/cta_images/')
    {
        if (preg_match('/^data:image\/([a-zA-Z0-9]+);base64,/', $base64String, $matches)) {
            $extension = $matches[1];
            $imageData = substr($base64String, strpos($base64String, ',') + 1);
            $imageData = base64_decode($imageData);
            $imageName = Str::random(40) . '.' . $extension;
            $imagePath = $directory . $imageName;
            StorageUtils::put($imagePath, $imageData);
            return $imagePath;
        }
        return $base64String;
    }

    public static function getCaricatureData(
        CaricatureCategory|null $catRow,
        Attire                  $item,
        Collection              $rates = null): array
    {

        $payment = RateController::getCaricatureRates($rates, 1, false, $item->editor_choice == 1);

        $isFreemium = $item->is_premium == 0 && $item->is_freemium == 1;

        return array(
            'id_name' => $item->id_name ?? $item->string_id,
            'post_name' => $item->post_name,
            'meta_title' => $item->meta_title,
            'meta_description' => $item->meta_description,
            'h2_tag' => $item->h2_tag,
            'long_desc' => $item->long_desc,
            'width' => $item->width,
            'height' => $item->height,
            'thumb' => $item->thumbnail_url,
            'editor_choice' => $item->editor_choice == 1,
            'is_premium' => ($item->is_premium == 1 || $item->is_freemium == 1),
            'is_freemium' => $isFreemium,
            'views' => $item->trending_views,
            'payment' => $payment,
            'page_link' => HelperController::$webPageUrl . "caricatures/p/$item->id_name",
        );
    }

    public static function extractAndRemoveTrailingNumber($string): array
    {
        if (preg_match('/\/(\d+)$/', $string, $matches)) {
            $number = intval($matches[1]);
            $cleaned = preg_replace('/\/\d+$/', '', $string);
            return [
                'number' => $number,
                'string' => $cleaned
            ];
        }
        return [
            'number' => null,
            'string' => $string
        ];
    }

}