<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Utils\ContentManager;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\Draft;
use App\Models\LikedProduct;
use App\Models\NewCategory;
use App\Models\NewSearchTag;
use App\Models\Size;
use App\Models\SpecialKeyword;
use App\Models\SpecialPage;
use Exception;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Design;
use App\Models\UserData;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class TemplateApiController extends ApiController
{

    public static array $templateSeo = [
        "short_desc" => "Crafty Art offers creative and customizable digital invitation templates designs for all events, including weddings, birthdays, engagements, baby showers, and more. Create stunning online invitations effortlessly and make every celebration special with unique designs tailored to your style. Perfect for any occasion!",
        "h1_tag" => "All Templates Designs",
        "h2_tag" => "Discover Unique Templates and Designs for Every Occasion with Crafty Art",
        "meta_title" => "Creative Templates Designs for Crafty Art Invitations",
        "meta_desc" => "Explore creative templates designs for digital invitations at Crafty Art. Perfect for weddings, birthdays, engagements, baby showers, and all your special events.",
        "long_desc" => "<p><span style=\"font-weight: 400;\">Crafty Art is your ultimate destination for stunning and customizable digital invitation templates. Whether you are planning a wedding, birthday celebration, engagement, or any special occasion, Crafty Art offers a wide range of designs to suit every event. Our templates are crafted with care to ensure they are not only visually appealing but also easy to personalize.</span></p>
<h3><strong>Why Choose Crafty Art for Your Memorable Events?</strong></h3>
<p><span style=\"font-weight: 400;\">When it comes to celebrations of any event, Crafty Art stands out for its attention to detail and creativity. Here are a few reasons why our templates are perfect for your needs:</span></p>
<ul>
    <li style=\"font-weight: 400;\"><strong>Customizable Designs:</strong><span style=\"font-weight: 400;\"> Each template is fully customizable, allowing you to add your personal touch with names, dates, colors, and fonts.</span></li>
    <li style=\"font-weight: 400;\"><strong>Wide Range of Occasions:</strong><span style=\"font-weight: 400;\"> From weddings and birthdays to baby showers and housewarming parties, our collection covers every event imaginable.</span></li>
    <li style=\"font-weight: 400;\"><strong>High-Quality Visuals:</strong><span style=\"font-weight: 400;\"> Our templates are designed by professionals to ensure they look elegant and modern.</span></li>
    <li style=\"font-weight: 400;\"><strong>Digital Convenience:</strong><span style=\"font-weight: 400;\"> Save time and effort with </span><span style=\"color: #0000ff;\"><a style=\"color: #0000ff;\" href=\"https://www.craftyartapp.com/templates/invitation\" target=\"_blank\"><span style=\"font-weight: 400;\">digital invitations</span></a></span><span style=\"font-weight: 400;\"> that can be sent instantly via email or shared on social media.</span></li>
</ul>
<h3><strong>Explore Our Diverse Collection of Templates</strong></h3>
<h4><strong>Wedding Invitations</strong></h4>
<p><span style=\"font-weight: 400;\">Celebrate love with our beautiful </span><span style=\"color: #0000ff;\"><a style=\"color: #0000ff;\" href=\"https://www.craftyartapp.com/k/wedding-invitation-template\" target=\"_blank\"><span style=\"font-weight: 400;\">wedding invitation templates</span></a></span><span style=\"font-weight: 400;\">. Choose from classic, modern, or floral designs to match your wedding theme. These templates feature intricate patterns and elegant typography, making them perfect for announcing your special day.</span></p>
<h4><strong>Birthday Invitations</strong></h4>
<p><span style=\"font-weight: 400;\">Make birthdays extra special with vibrant and fun designs tailored for all ages. Whether it&rsquo;s a child&rsquo;s party or a milestone celebration, our </span><span style=\"color: #0000ff;\"><a style=\"color: #0000ff;\" href=\"https://www.craftyartapp.com/k/birthday-invitation\" target=\"_blank\"><span style=\"font-weight: 400;\">birthday invitation </span></a></span><span style=\"font-weight: 400;\">templates are customizable to reflect the joy of the occasion.</span></p>
<h4><strong>Engagement Invitations</strong></h4>
<p><span style=\"font-weight: 400;\">Announce your engagement in style with sophisticated templates. From minimalist designs to luxurious </span><span style=\"color: #0000ff;\"><a style=\"color: #0000ff;\" href=\"https://www.craftyartapp.com/k/engagement-invitation\" target=\"_blank\"><span style=\"font-weight: 400;\">engagement invitation</span></a></span><span style=\"font-weight: 400;\"> themes, you&rsquo;ll find the perfect way to share your exciting news with family and friends.</span></p>
<h4><strong>Baby Shower Invitations</strong></h4>
<p><span style=\"font-weight: 400;\">Welcome a new bundle of joy with adorable </span><span style=\"color: #0000ff;\"><a style=\"color: #0000ff;\" href=\"https://www.craftyartapp.com/k/baby-shower-invitations\" target=\"_blank\"><span style=\"font-weight: 400;\">baby shower invitation</span></a></span><span style=\"font-weight: 400;\"> templates. Featuring soft pastel colors and cute graphics, these designs are ideal for celebrating this precious moment.</span></p>
<p><strong>Bridal Shower Invitations</strong></p>
<p><span style=\"font-weight: 400;\">Gather your closest friends and family with stunning </span><span style=\"color: #0000ff;\"><a style=\"color: #0000ff;\" href=\"https://www.craftyartapp.com/k/bridal-shower-invitation-templates\" target=\"_blank\"><span style=\"font-weight: 400;\">bridal shower invitations</span></a></span><span style=\"font-weight: 400;\">. Our collection includes elegant and chic designs to suit every bride&rsquo;s style.</span></p>
<h4><strong>Housewarming Invitations</strong></h4>
<p><span style=\"font-weight: 400;\">Invite loved ones to your new home with our charming </span><span style=\"color: #0000ff;\"><a style=\"color: #0000ff;\" href=\"https://www.craftyartapp.com/k/house-warming-invitation\" target=\"_blank\"><span style=\"font-weight: 400;\">housewarming invitation</span></a></span><span style=\"font-weight: 400;\"> templates. They are designed to convey warmth and excitement, making your guests feel special.</span></p>
<h3><strong>How to Customize Your Invitation with Crafty Art</strong></h3>
<p><span style=\"font-weight: 400;\">Personalizing your invitation is simple and fun with Crafty Art. Follow these steps:</span></p>
<ol>
    <li style=\"font-weight: 400;\"><strong>Choose a Template:</strong><span style=\"font-weight: 400;\"> Browse our extensive collection and pick a design that fits your event.</span></li>
    <li style=\"font-weight: 400;\"><strong>Edit the Details:</strong><span style=\"font-weight: 400;\"> Use our user-friendly editing tools to add names, dates, and other event details.</span></li>
    <li style=\"font-weight: 400;\"><strong>Customize the Look:</strong><span style=\"font-weight: 400;\"> Adjust colors, fonts, and images to match your theme.</span></li>
    <li style=\"font-weight: 400;\"><strong>Download or Share:</strong><span style=\"font-weight: 400;\"> Once you&rsquo;re satisfied, download the invitation or share it digitally.</span></li>
</ol>
<h3><strong>Make Every Event Memorable with Crafty Art</strong></h3>
<p style=\"text-align: justify;\"><span style=\"font-weight: 400;\">Crafty Art is more than just a platform for digital invitations&mdash;it&rsquo;s a place where creativity meets convenience. Our </span><span style=\"color: #0000ff;\"><a style=\"color: #0000ff;\" href=\"https://www.craftyartapp.com/templates/latest\" target=\"_blank\"><span style=\"font-weight: 400;\">latest templates</span></a></span><span style=\"font-weight: 400;\"> are designed to help you create lasting memories for every occasion. Explore our collection today and find the perfect design to celebrate life&rsquo;s special moments.</span></p>",
    ];

    function favourite(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $id = $request->get("id");

        if (!Design::where("string_id", $id)->where("status", 1)->exists()) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Invalid request"));
        }

        $isExists = LikedProduct::where("user_id", $this->uid)->where("product_id", $id)->exists();

        if ($isExists) {
            $msg = "Unstarred";
            $isSuccess = LikedProduct::where("user_id", $this->uid)->where("product_id", $id)->delete();
        } else {
            $msg = "Starred";
            $isSuccess = LikedProduct::insert(['user_id' => $this->uid, 'product_id' => $id]);
        }

        if ($isSuccess) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, $msg));
        } else {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Invalid request"));
        }
    }

    function getAllFavourite(Request $request): array|string
    {

        if ($this->isFakeRequestAndUser($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $page = $request->has('page') ? $request->get('page') : 1;
        $limit = 20;

        $datas = LikedProduct::where("user_id", $this->uid)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->offset(($page - 1) * $limit)
            ->get();

        $item_rows = [];
        foreach ($datas as $likedItem) {
            $item = Design::where("string_id", $likedItem->product_id)->where("status", 1)->first();
            if ($item) {
                $catRow = Category::find($item->category_id);
                if ($catRow != null) {
                    $item_rows[] = HelperController::getItemData($this->uid, $catRow, $item, json_decode($item->thumb_array));
                }
            }
        }

        $msg = 'Loading Success!';

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, $msg,
            [
                'isLastPage' => count($item_rows) < $limit,
                'datas' => $item_rows
            ]
        ));

    }

    function getAllFab(Request $request): array|string
    {

        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $refWidth = $request->get('w', 1);
        $refHeight = $request->get('h', 1);
        $page = $request->has('page') ? $request->get('page') : 1;
        $limit = 20;

        $tempRatio = $refWidth / $refHeight;
        $tempRatio = round($tempRatio, 2);

        $itemData = Design::where("status", 1)
            ->where("ratio", $tempRatio)
            ->whereHas('parent', function ($query) {
                $query->where('status', 1);
            })
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->offset(($page - 1) * $limit)
            ->get();

        $item_rows = [];
        if ($itemData != null) {
            foreach ($itemData as $item) {
                $catRow = Category::find($item->category_id);
                if ($catRow != null) {
                    $item_rows[] = HelperController::getItemData($this->uid, $catRow, $item, json_decode($item->thumb_array), false, false, false, null, true);
                }
            }

            $msg = 'Loading Success!';
        } else {
            $msg = 'Data not found!';
        }

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, $msg,
            [
                'isLastPage' => count($item_rows) < $limit,
                'datas' => $item_rows
            ]
        ));

    }

    function getTemplates(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $limit = 20;

        $response = [];

        // only cat section
        $categories = NewCategory::getAllCategoriesWithSubcategories(1);
        $catDatas = [];
        foreach ($categories as $key => $category) {

            $catDatas[$key]['category_id'] = $category->id;
            $catDatas[$key]['id_name'] = $category->id_name;
            $catDatas[$key]['category_name'] = $category->category_name;
            $catDatas[$key]['category_thumb'] = HelperController::$mediaUrl . $category->category_thumb;
            $catDatas[$key]['category_mockup'] = $category->mockup != null ? HelperController::$mediaUrl . $category->mockup : null;

            $allCateIds = CategoryTemplatesApiController::getAllIds($category->toArray());

            $query = Design::whereIn('new_category_id', $allCateIds)->where('status', 1)->count();

            if ($query <= 0) {
                unset($catDatas[$key]);
            }
        }
        $response['catlist'] = $catDatas;
        // end of only cat section

        // cats section
        $response['cats'] = array_merge(CategoryTemplatesApiController::getAllNewCategories($this->uid, true), CategoryTemplatesApiController::getAllImpOldCategories($this->uid));
        // end cats section

        // inspired section
        if ($this->uid) {
            $draft = Draft::where('user_id', $this->uid)->whereNotNull('template_id')->latest()->first();
            if ($draft) {
                $item = Design::where('string_id', $draft->template_id)->where('status', 1)->first();
                if ($item) {
                    $SearchApi = new SearchApiController($request);
                    $searchData = $SearchApi->searchTemplates(json_decode($item->related_tags)[0], 1, null, $limit, $item->id_name, $item->ratio);
                    $response['inspired'] = $searchData['datas'];
                }
            }
        } else {
            $itemData = Design::where("status", 1)->whereHas('parent', function ($query) {
                $query->where('status', 1);
            })->orderBy('id', 'DESC')->paginate($limit, ['*'], 'page', 1);
            $item_rows = array();
            foreach ($itemData->items() as $item) {
                $catRow = Category::find($item->category_id);
                if ($catRow != null) {
                    $item_rows[] = HelperController::getItemData($this->uid, $catRow, $item, json_decode($item->thumb_array));
                }
            }
            $response['latest'] = $item_rows;
        }
        // end of inspired section

        // trending section
        $itemData = Design::where("trending_views", ">", 0)->where("status", 1)->whereHas('parent', function ($query) {
            $query->where('status', 1);
        })->orderBy('trending_views', 'DESC')->paginate($limit, ['*'], 'page', 1);
        $item_rows = array();
        foreach ($itemData->items() as $item) {
            $catRow = Category::find($item->category_id);
            if ($catRow != null) {
                $item_rows[] = HelperController::getItemData($this->uid, $catRow, $item, json_decode($item->thumb_array));
            }
        }
        $response['trending'] = $item_rows;
        // end trending section

        // upcoming event section
        $itemData = Design::whereRaw("STR_TO_DATE(end_date, '%m/%d/%Y') > ?", [now()])->where("status", 1)->whereHas('parent', function ($query) {
            $query->where('status', 1);
        })->orderByRaw("STR_TO_DATE(end_date, '%m/%d/%Y') ASC")->paginate($limit, ['*'], 'page', 1);
        $item_rows = array();
        foreach ($itemData->items() as $item) {
            $catRow = Category::find($item->category_id);
            if ($catRow != null) {
                $item_rows[] = HelperController::getItemData($this->uid, $catRow, $item, json_decode($item->thumb_array));
            }
        }
        $response['upcomingEvents'] = $item_rows;
        // end of upcoming event section

        // upcoming video section
        $itemData = Design::whereNotNull("video_thumb")->where("status", 1)->whereHas('parent', function ($query) {
            $query->where('status', 1);
        })->orderBy("id", "DESC")->paginate($limit, ['*'], 'page', 1);
        $item_rows = array();
        foreach ($itemData->items() as $item) {
            $catRow = Category::find($item->category_id);
            if ($catRow != null) {
                $item_rows[] = HelperController::getItemData($this->uid, $catRow, $item, json_decode($item->thumb_array));
            }
        }
        $response['videos'] = $item_rows;
        // end of upcoming video section

        $response['seo'] = self::$templateSeo;

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, 'Loaded!', $response));
    }

    function getKeyTemplates(Request $request): array|string
    {

        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized", ["page_slug_history" => []]));
        }

        $keyName = $request->get('id');
        $filter = isset($request->filter) ? $request->filter : [];
        $page = $request->has('page') ? $request->get('page') : 1;

        $keyData = SpecialKeyword::where('name', $keyName)->where('status', '1')->first();

        if (!$keyData) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, "Data not found", ["page_slug_history" => PageSlugHistoryController::get(0)]));
        }

        $keyDataId = "\"" . $keyData->id . "\"";

        $limit = 20;

        $templatesQuery = Design::where("status", 1)
            ->whereHas('parent', function ($query) {
                $query->where('status', 1);
            });

        $cat = NewCategory::find($keyData->cat_id);

        if (empty($filter)) {
            $templatesQuery = $templatesQuery->where('special_keywords', 'like', '%' . $keyDataId . '%');
        } else {
            if (!$cat || $cat->parent_category_id != 0) {
                $templatesQuery = $templatesQuery->where('new_category_id', $keyData->cat_id);
            } else {
                $ids = NewCategory::where("parent_category_id", $keyData->cat_id)->pluck('id')->unique();
                $templatesQuery = $templatesQuery->whereIn('new_category_id', $ids);
            }

            $templatesQuery = CategoryTemplatesApiController::getFilterQuery($templatesQuery, $filter);
        }

        $templateCount = $templatesQuery->count();

        $itemData = $templatesQuery->orderByRaw('pinned DESC, web_views DESC, id DESC')->paginate($limit, ['*'], 'page', $page)->onEachSide(-1);

        $item_rows = [];

        foreach ($itemData->items() as $item) {
            $catRow = Category::find($item->category_id);
            if ($catRow != null) {
                $item_rows[] = HelperController::getItemData($this->uid, $catRow, $item, json_decode($item->thumb_array));
            }
        }

//        $pre_breadcrumb = [];

//        if ($cat) {
//            if ($cat->parent_category_id != 0) {
//                $parentCat = NewCategory::find($cat->parent_category_id);
//                if ($parentCat) {
//                    $pre_breadcrumb[] = [
//                        'value' => $parentCat->category_name,
//                        "link" => "https://www.craftyartapp.com/templates/$parentCat->id_name",
//                        "openinnewtab" => 0,
//                        "nofollow" => 0
//                    ];
//                    $pre_breadcrumb[] = [
//                        'value' => $cat->category_name,
//                        "link" => "https://www.craftyartapp.com/templates/$parentCat->id_name/$cat->id_name",
//                        "openinnewtab" => 0,
//                        "nofollow" => 0
//                    ];
//                }
//            } else {
//                $pre_breadcrumb[] = [
//                    'value' => $cat->category_name,
//                    "link" => "https://www.craftyartapp.com/templates/$cat->id_name",
//                    "openinnewtab" => 0,
//                    "nofollow" => 0
//                ];
//            }
//        }

        $msg = 'Loading Success!';


        $response['pagination'] = PaginationController::getPagination($itemData);
        $response['total_page'] = $itemData->lastPage();
        $response['templateCount'] = \App\Http\Controllers\HelperController::getTemplateCount($templateCount,$keyData->primary_keyword);
        $response['category_id'] = $keyData->cat_id;
        $response['pre_breadcrumb'] = self::getCategoryBreadcrumbs($cat);
        $response['sub_category'] = array_values(CategoryTemplatesApiController::getSubCategories($keyData->cat_id));
        $response['new_related_tags'] = array_values(CategoryTemplatesApiController::getSubCategoriesTags($keyData->cat_id));
        $response['datas'] = $item_rows;
        $response['title'] = $keyData->title;
        $response['string_id'] = $keyData->string_id;
        $response['h2_tag'] = $keyData->h2_tag;
        $response['meta_title'] = $keyData->meta_title;
        $response['meta_desc'] = $keyData->meta_desc;
        $response['short_desc'] = $keyData->short_desc;
        $response['long_desc'] = $keyData->long_desc;
        $response['contents'] = isset($keyData->contents) ? ContentManager::getContentsPath(json_decode(StorageUtils::get($keyData->contents)), $this->uid) : [];

        $faqsResponse = ContentManager::faqsResponse($keyData->faqs,$keyData->primary_keyword);
        $response['faqs'] = $faqsResponse['faqs'];
        $response['faqs_title'] = $faqsResponse['faqs_title'];
        $response['top_keywords'] = isset($keyData->top_keywords) ? HelperController::getTopKeywords(json_decode($keyData->top_keywords)) : [];
//        $response['cta'] = isset($keyData->cta) ? HelperController::getCTA($keyData->cta) : null;
        $response['page_slug_history'] = PageSlugHistoryController::get(0);
        $response['canonical_link'] = HelperController::buildCanonicalLink($keyData->canonical_link,HelperController::getFrontendPageUrl(3,$keyData->name),$page);

        $data = PReviewController::getPReviews($this->uid, 3, $keyData->string_id, 1);
        if ($data['success']) {
            $response['reviews'] = $data['data'];
        }

        if ($page == 1) {
            $user_data = UserData::where("uid", $this->uid)->first();
//            FbPixel::trackEvent(FacebookEvent::VIEW_CONTENT, $request, $user_data?->name, $user_data?->email, null, 'https://www.craftyartapp.com/k/' . $keyData->name);
        }

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, $msg, $response));
    }



    public static function getCategoryBreadcrumbs($cat): array
    {
        $pre_breadcrumb = [];
//        $cat = NewCategory::find($cat_id);

        if ($cat) {
            if ($cat->parent_category_id != 0) {
                $parentCat = NewCategory::find($cat->parent_category_id);
                if ($parentCat) {
                    $pre_breadcrumb[] = [
                        'value' => $parentCat->category_name,
                        "link" => "https://www.craftyartapp.com/templates/{$parentCat->id_name}",
                        "openinnewtab" => 0,
                        "nofollow" => 0
                    ];
                    $pre_breadcrumb[] = [
                        'value' => $cat->category_name,
                        "link" => "https://www.craftyartapp.com/templates/{$parentCat->id_name}/{$cat->id_name}",
                        "openinnewtab" => 0,
                        "nofollow" => 0
                    ];
                }
            } else {
                $pre_breadcrumb[] = [
                    'value' => $cat->category_name,
                    "link" => "https://www.craftyartapp.com/templates/{$cat->id_name}",
                    "openinnewtab" => 0,
                    "nofollow" => 0
                ];
            }
        }

        return $pre_breadcrumb;
    }

//    function getSpecialTemplates(Request $request): array|string
//    {
//        if ($this->isFakeRequest($request)) {
//            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
//        }
//
//        $relatedTags = $request->get('id');
//        $page = $request->has('page') ? $request->get('page') : 1;
//        $onlyVideo = $request->has('v') && $request->get('v');
//        $filter = isset($request->filter) ? $request->filter : [];
//
//        if (!$relatedTags) {
//            return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, "Parameters missing"));
//        }
//
//        $description = str_replace(', ', ',', $relatedTags);
//        $desc_array = explode(',', $description);
//
//        $limit = 20;
//        $templatesQuery = Design::query();
//        if ($onlyVideo) {
//            $templatesQuery->where('animation', 1);
//        }
//
//        $templatesQuery->whereHas('parent', function ($query) {
//            $query->where('status', 1);
//        });
//
//        if (empty($filter)) {
//            $templatesQuery = $templatesQuery->where(function ($q) use ($desc_array) {
//                foreach ($desc_array as $kw) {
//                    $q->orWhere('related_tags', 'LIKE', '%"' . $kw . '"%');
//                }
//            });
//        } else {
//            $pageObj = SpecialPage::wherePageSlug($request->slug)->first();
//            if ($pageObj) {
//                $cat = NewCategory::find($pageObj->cat_id);
//                if (!$cat || $cat->parent_category_id != 0) {
//                    $templatesQuery = $templatesQuery->where('new_category_id', $pageObj->cat_id);
//                } else {
//                    $ids = NewCategory::where("parent_category_id", $pageObj->cat_id)->pluck('id')->unique();
//                    $templatesQuery = $templatesQuery->whereIn('new_category_id', $ids);
//                }
//            }
//            $templatesQuery = CategoryTemplatesApiController::getFilterQuery($templatesQuery, $filter);
//        }
//
//        $templates = $templatesQuery->whereStatus(1)->orderByRaw('pinned DESC, web_views DESC, id DESC')->paginate($limit, ['*'], 'page', $page);
//
//        $item_rows = [];
//
//        foreach ($templates->items() as $item) {
//            $catRow = Category::find($item->category_id);
//            if ($catRow != null) {
//                $item_rows[] = HelperController::getItemData($this->uid, $catRow, $item, json_decode($item->thumb_array));
//            }
//        }
//        $msg = 'Loading Success!';
//
//        $response['pagination'] = PaginationController::getPagination($templates);
//        $response['isLastPage'] = $templates->lastPage() == $templates->currentPage();
//        $response['datas'] = $item_rows;
//        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, $msg, $response));
//    }

    public function getSpecialTemplates(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        if (!$request->get('id')) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, "Parameters missing"));
        }

        $page = $request->get('page', 1);
        $limit = 20;
        $slug = $request->get('slug');
        $relatedTags = $request->get('id');
        $onlyVideo = $request->has('v') && $request->get('v');
        $filter = $request->get('filter', []);
        $responseData = $this->fetchSpecialTemplatesData($relatedTags,$onlyVideo,$slug,$filter, $page, $limit);

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, 'Loading Success!', $responseData));
    }

    public static function fetchSpecialTemplatesData($relatedTags,$onlyVideo,$slug,$filter, int $page = 1, int $limit = 20, bool $showTemplateCount = false): array
    {
        $description = str_replace(', ', ',', $relatedTags);
        $desc_array = explode(',', $description);

        $templatesQuery = Design::query();

        if ($onlyVideo) {
            $templatesQuery->where('animation', 1);
        }

        $templatesQuery->whereHas('parent', function ($query) {
            $query->where('status', 1);
        });

        if (empty($filter)) {
            $templatesQuery->where(function ($q) use ($desc_array) {
                foreach ($desc_array as $kw) {
                    $q->orWhere('related_tags', 'LIKE', '%"' . $kw . '"%');
                }
            });
        } else {
            $pageObj = SpecialPage::wherePageSlug($slug)->first();
            if ($pageObj) {
                $cat = NewCategory::find($pageObj->cat_id);
                if (!$cat || $cat->parent_category_id != 0) {
                    $templatesQuery->where('new_category_id', $pageObj->cat_id);
                } else {
                    $ids = NewCategory::where("parent_category_id", $pageObj->cat_id)->pluck('id')->unique();
                    $templatesQuery->whereIn('new_category_id', $ids);
                }
            }

            $templatesQuery = CategoryTemplatesApiController::getFilterQuery($templatesQuery, $filter);
        }

        $data = [];

        if ($showTemplateCount) {
            $data['count'] = $templatesQuery->count();
        }

        $templates = $templatesQuery
            ->whereStatus(1)
            ->orderByRaw('pinned DESC, web_views DESC, id DESC')
            ->paginate($limit, ['*'], 'page', $page);

        $item_rows = [];

        foreach ($templates->items() as $item) {
            $catRow = Category::find($item->category_id);
            if ($catRow !== null) {
                $item_rows[] = HelperController::getItemData(auth()->id() ?? null, $catRow, $item, json_decode($item->thumb_array));
            }
        }
//        dd($item_rows);
        $data['pagination'] = PaginationController::getPagination($templates);
        $data['isLastPage'] = $templates->lastPage() === $templates->currentPage();
        $data['datas'] = $item_rows;

        return $data;
    }


    function getPosterPage(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $id_name = $request->get('id');

        if (!$id_name) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, "Parameters missing"));
        }

//        $redirectUrl = PageSlugHistoryController::findOne($id_name,5);
        $redirectUrl = "";

        $hasShowAll = false;
        $user_data = UserData::where("uid", $this->uid)->first();

        $status_condition = "=";
        $status = "1";
        if ($user_data && ($user_data->can_update == 1 || $user_data->can_update == '1' || $user_data->web_update == 1 || $user_data->web_update == '1')) {
            $status_condition = "!=";
            $status = "-1";
        }

        $string_id = explode('-', $id_name)[0];
        $itemData = Design::where("string_id", $string_id)->where('status', $status_condition, $status)->first();

        if (!$itemData) {
            if (is_numeric($id_name)) {
                $itemData = Design::where("id", $id_name)->where('status', $status_condition, $status)->first();
            }
            if (!$itemData) {
                return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, "Data not found", ["redirectUrl" => $redirectUrl]));
            }
        }

//        if ($this->isTester()) {
//            WebTemplateViewHistory::where('created_at', '<=', now()->subDay())->delete();
//        }

//        $ipData = HelperController::getIpAndCountry($request);
//        $userIp = $ipData['ip'];

//        WebTemplateViewHistory::create([
//            'user_id' => $this->uid,
//            'product_id' => $itemData->string_id,
//            'ip_address' => $userIp == '89.116.134.215' ? null : $userIp,
//            'country' => $ipData['cn'],
//            'fbc' => $request->cookie('_fbclid'),
//            'fbp' => $request->cookie('_caid'),
//            'gclid' => $request->cookie('_gclid'),
//            'gcl_au' => $request->cookie('_gcl_au'),
//            'ga' => $request->cookie('_ga'),
//            'userAgent' => $request->header('User-Agent', 'Unknown'),
//        ]);

        $last_24_hour_views = 25;

        $catRow = Category::find($itemData->category_id);
        $item_rows = HelperController::getItemData($this->uid, $catRow, $itemData, json_decode($itemData->thumb_array), false);

        $newTags = [];
        $newCatRow = NewCategory::find($itemData->new_category_id);
        if ($newCatRow) {
            $parentCat = NewCategory::find($newCatRow->parent_category_id);
            if ($parentCat && isset($itemData->new_related_tags)) {

                $value = json_decode($itemData->new_related_tags, true);
                $dataRes = NewSearchTag::whereIn('id', $value)->where('status', 1)->get();

                if ($dataRes) {
                    $newTags = collect($dataRes)->map(function ($newSearchTag) use ($parentCat, $newCatRow) {
                        return [
                            'id' => $newSearchTag['id'],
                            'id_name' => $newSearchTag['id_name'],
                            'link' => '/templates/' . $parentCat->id_name . '/' . $newCatRow->id_name . '?query=',
                            'name' => $newSearchTag['name']
                        ];
                    })->toArray();
                }
            }
        }
        $response['pre_breadcrumb'] = self::getCategoryBreadcrumbs($newCatRow);

        $item_rows['url'] = HelperController::$mediaUrl;
        $item_rows['category_id_name'] = $catRow->id_name;
        $item_rows['ratio'] = $itemData->ratio;
        $item_rows['h2_tag'] = $itemData->h2_tag;
        $item_rows['description'] = $itemData->description;
        $item_rows['meta_description'] = $itemData->meta_description;
        $item_rows['status'] = $itemData->status;
        $item_rows['last_24_hour_views'] = $last_24_hour_views;
        $item_rows['new_tags'] = $newTags;
//        $item_rows['currency'] = $ipData['cur'];
        $size = Size::find($itemData->template_size);
        if ($size) {
            $item_rows['paper_size'] = $size->paper_size;
        }

        try {
            $SearchApi = new SearchApiController($request);
            $searchData = $SearchApi->exactKeywordTemplates($item_rows['related_tags'][0], 1, 50, $itemData->string_id);
        } catch (QueryException|Exception $e) {
            $searchData['datas'] = [];
        }

        $response['data'] = $item_rows;
        $response['suggested'] = $searchData['datas'];
        $response['cta'] = isset($itemData->cta) ? HelperController::getCTA($itemData->cta) : null;
        $response['redirectUrl'] = $redirectUrl;
        $response['canonical_link'] = HelperController::buildCanonicalLink($itemData->canonical_link,HelperController::getFrontendPageUrl(0,$id_name),1);

        $data = PReviewController::getPReviews($this->uid, 0, $itemData->string_id, 1);
        if ($data['success']) {
            $response['reviews'] = $data['data'];
        }

//        FbPixel::trackEvent(FacebookEvent::VIEW_CONTENT, $request, $user_data?->name, $user_data?->email, null, 'https://www.craftyartapp.com/templates/p/' . $itemData->id_name);

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Loaded", $response));

    }

    function getPosterDetail(Request $request): array|string
    {

        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $template_id = $request->get('id');

        if ($template_id == null) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Parameters Missing!"));
        }

        $fieldName = is_numeric($template_id) ? "id" : "id_name";
        $itemData = Design::where($fieldName, $template_id)->first();

        if (!$itemData) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, "Data not found"));
        }

        $res = Design::find($itemData->id);
        $res->web_views = $res->web_views + 1;
        $res->trending_views = $res->trending_views + 1;
        $res->save();

        $data = FabricJsController::getPosterDetail($request, true);
        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Loaded",
            [
                'data' => JSONUtils::applyPageStringId($data, $itemData->string_id)
            ]
        ));
    }
}
