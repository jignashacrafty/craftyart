<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Utils\ContentManager;
use App\Http\Controllers\Utils\QueryManager;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\Interest;
use App\Models\Language;
use App\Models\NewCategory;
use App\Models\NewSearchTag;
use App\Models\PromoCode;
use App\Models\Religion;
use App\Models\Size;
use App\Models\Style;
use App\Models\Theme;
use App\Models\UserData;
use App\Models\VirtualCategory;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Design;
use Carbon\Carbon;

class CategoryTemplatesApiController extends ApiController
{

    public static string $defaultTagLine = 'Beautiful and elegant designs with customizable templates - perfect for any special celebrations!';
    public static array $latestSeo = [
        "short_desc" => "Stay ahead with our collection of latest templates. From cutting-edge designs to contemporary trends, find the perfect fit for your next events.",
        "h1_tag" => "Latest Templates",
        "h2_tag" => "Dive into Creativity: Latest Template Designs",
        "meta_title" => "Latest Template Collection for Every Need | Get Started Now!",
        "meta_desc" => "Enhance your next events with the latest templates. Discover a wide range of Latest Templates for various needs. Get started today!",
        "long_desc" => "In today's fast-paced digital landscape, staying up-to-date with the latest trends and tools is crucial for any creative endeavor. Whether you're a designer, marketer, blogger, or business owner, having access to the most recent templates can significantly boost your projects' impact and efficiency. This is where the world of Latest Templates comes into play.\n\nThe term Latest Templates encompasses a wide array of design resources that span various industries and purposes. These templates can range from website designs, graphic assets, presentation layouts, email designs, social media graphics, and much more. They serve as pre-designed frameworks that can be customized to suit your specific needs, allowing you to save valuable time while maintaining a professional and polished appearance.\n\nThe beauty of these latest templates lies in their adaptability. No matter the nature of your project, whether it's a cutting-edge tech startup pitch, a cozy corner cafe's promotional materials, or a fashion blog's Instagram posts, there are templates available that align with your vision. With a multitude of styles, color schemes, typography choices, and layouts to choose from, you have the freedom to make each template your own.\n\nOne of the key advantages of utilizing the latest templates is the speed they bring to your workflow. Traditional design processes can be time-consuming, often requiring you to start from scratch. With templates, the foundation is already set, and you're simply adding your unique touch. This expedites the design process, allowing you to meet tight deadlines without compromising quality.\n\nMoreover, these templates often come crafted by experienced designers who understand the principles of aesthetics, visual hierarchy, and user experience. This means you're starting with a design that's not only visually appealing but also strategically effective. Even if you're not a design expert yourself, these templates empower you to create materials that resonate with your audience.\n\nLet's delve into some of the most popular categories where the latest templates prove to be game-changers:\n\nWeb Design Templates: In the digital age, your website is often the first point of contact with potential customers. Utilizing the latest web design templates ensures your site is modern, user-friendly, and responsive across devices.\n\nGraphic Design Templates: From business cards to brochures, these templates cover a wide range of print and digital materials. They help you maintain a consistent brand identity across all touch points.\n\nPresentation Templates: Whether for business pitches or educational purposes, presentation templates make your content engaging and impactful. Creative slides and visually appealing graphics keep your audience attentive.\n\nSocial Media Templates: Consistency is key on social platforms. With these templates, you can maintain a cohesive brand presence and share eye-catching content that stops users from scrolling.\n\nEmail Marketing Templates: Crafting effective emails can be challenging. The latest email templates ensure your messages are well-designed and optimized for better open and click-through rates.\n\nE-commerce Templates: If you're an online retailer, e-commerce templates help showcase your products in the best light, leading to higher conversion rates and increased sales.\n\nIn conclusion, the world of Latest Templates opens doors to a universe of creativity and efficiency. Whether you're a design professional or a novice, these templates offer a shortcut to stunning visuals and effective communication. By harnessing the power of the latest templates, you're not just keeping up with trends; you're setting new standards for your projects and leaving a lasting impression on your audience. Explore, customize, and elevate your creations with the ever-evolving realm of the latest templates.",
    ];

    public static array $trendingSeo = [
        "short_desc" => "Explore the latest design trends with our collection of trending templates. Stay ahead in the world of design with these contemporary and sought-after layouts.",
        "h1_tag" => "Trending Templates",
        "h2_tag" => "Popular Trending Design Templates",
        "meta_title" => "Discover the Hottest Trending Templates for Captivating Creations",
        "meta_desc" => "Elevate your content with trending templates! Explore CapCut templates, Instagram trends, and download popular templates to stand out.",
        "long_desc" => "Introducing our curated selection of Trending Templates, designed to help you create captivating and cutting-edge visuals that capture the essence of the moment. Whether you're a content creator, social media enthusiast, or business owner, these templates are your gateway to unleashing creativity like never before.\n\nTrending CapCut Templates:\n\nAre you a fan of video editing? Dive into the world of Trending CapCut Templates, where each frame is an opportunity to express your unique style. CapCut, a popular video editing application, offers a plethora of templates that can transform your raw footage into mesmerizing works of art with Trending Templates. From seamless transitions to eye-catching effects, Trending Templates are designed to elevate your videos to a level of professionalism that stands out in a sea of ordinary content. Whether you're creating content for personal enjoyment or for your audience's delight, CapCut templates provide a canvas for innovation.\n\nRiding the Wave of Trends:\n\nStaying relevant in the digital realm means keeping up with the latest trends. Our Trending Templates are carefully curated to align with the current digital landscape, ensuring that your content resonates with your audience. Whether it's the trending color palettes, typography styles, or visual motifs, these templates are imbued with elements that capture the spirit of the times. Riding the wave of trends isn't just about following the crowd – it's about infusing your unique voice into what's popular, creating a harmonious blend that's both familiar and refreshingly original.\n\nInstagram Trending Templates:\n\nInstagram, as a visual-centric platform, thrives on innovation and aesthetics. Our collection of Instagram Trending Templates is designed to make your profile stand out amidst the noise. From story templates that enhance your engagement to post templates that deliver your message with impact, these designs are tailored for the Instagram-savvy individual. Elevate your stories, enhance your posts, and leave an indelible mark on your followers' feeds with templates that scream scroll-stopping content.\n\nThe Power of Downloadable Creativity:\n\nExploring new horizons of creativity is made easy with our downloadable templates. With just a few clicks, you can infuse your projects with the power of Trending Templates designs. The convenience of downloading these templates empowers you to focus more on content creation and less on design intricacies. Whether you're a seasoned creator or just starting, our templates provide a launching pad for your creative journey, giving you the tools to translate your ideas into tangible, visually appealing realities.Instagram Trending Template Download:\n\nAccessing trending designs for your Instagram feed has never been easier. Our platform offers a seamless Instagram Trending Templates Download experience, granting you instant access to designs that are making waves in the digital sphere. Elevate your grid, stories, and highlights with visuals that reflect the pulse of the online community. Whether it's for personal branding or business promotion, these templates are your secret weapon in making a memorable digital statement.\n\nUnleash Your Creativity:\n\nIn the realm of content creation, creativity knows no bounds. Our Trending Templates serve as the catalyst that propels your imagination to new heights. Whether you're looking to craft visually striking videos, enhance your social media presence, or make your mark in the digital universe, these templates are your companions in the journey. Unleash your creativity, embrace the trends, and craft content that captivates, inspires, and leaves a lasting impact.",
    ];

    public function getAllCategoriesList(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $categories = NewCategory::where('parent_category_id', 0)
            ->where('status', 1)
            ->get();

        $categoryChildMap = $categories->mapWithKeys(function ($category) {
            $childIds = json_decode($category->child_cat_id ?? '[]', true);
            return [$category->id => $childIds];
        });

        $allChildIds = $categoryChildMap->flatten()->unique()->values()->all();

        $designCounts = Design::whereIn('new_category_id', $allChildIds)
            ->where('status', 1)
            ->select('new_category_id', \DB::raw('COUNT(*) as count'))
            ->groupBy('new_category_id')
            ->pluck('count', 'new_category_id');
        $datas = [];

        foreach ($categories as $category) {
            $childIds = $categoryChildMap[$category->id] ?? [];

            $totalDesigns = collect($childIds)->sum(function ($id) use ($designCounts) {
                return $designCounts[$id] ?? 0;
            });

            if ($totalDesigns > 0) {
                $datas[] = [
                    'category_id' => $category->id,
                    'id_name' => $category->id_name,
                    'category_name' => $category->category_name,
                    'category_thumb' => HelperController::$mediaUrl . $category->category_thumb,
                ];
            }
        }

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, 'Loaded!', [
            "datas" => $datas
        ]));
    }

    public static function getAllImpOldCategories($uid): array
    {
        $categories = Category::where("status", 1)->where('imp', 1)->orderBy('sequence_number', 'ASC')->get();

        $datas = [];

        foreach ($categories as $key => $category) {

            $datas[$key]['category_id'] = $category->id;
            $datas[$key]['id_name'] = $category->id_name;
            $datas[$key]['category_name'] = $category->category_name;
            $datas[$key]['category_thumb'] = HelperController::$mediaUrl . $category->category_thumb;
            $datas[$key]['category_mockup'] = $category->mockup != null ? HelperController::$mediaUrl . $category->mockup : null;

            $query = Design::where('category_id', $category->id)->where('status', 1)->latest()->take(12);

            if ($query->count() > 0) {
                $templates = $query->get();
                $templateDatas = $templates->map(function ($template) use ($uid, $category) {
                    return HelperController::getItemData($uid, $category, $template, json_decode($template->thumb_array, true, 512, JSON_UNESCAPED_SLASHES), true, true);
                });
                $datas[$key]['template_model'] = $templateDatas;
            } else {
                unset($datas[$key]);
            }

        }
        return $datas;
    }

    public static function getAllNewCategories($uid, $isImp = false): array
    {
        if ($isImp) {
            $categories = NewCategory::where('parent_category_id', 0)->where('imp', 1)->where('status', 1)->get();
        } else {
            $categories = NewCategory::where('parent_category_id', 0)->where('status', 1)->get();
        }

        $datas = [];

        foreach ($categories as $key => $category) {
            $allCateIds = array_merge([$category->id], json_decode($category->child_cat_id ?? '[]', true));

            $query = Design::whereIn('new_category_id', $allCateIds)->where('status', 1)->latest()->take(12);

            if ($query->count() > 0) {
                $data['category_id'] = $category->id;
                $data['id_name'] = $category->id_name;
                $data['category_name'] = $category->category_name;
                $data['category_thumb'] = HelperController::$mediaUrl . $category->category_thumb;
                $data['category_mockup'] = $category->mockup != null ? HelperController::$mediaUrl . $category->mockup : null;
                $templates = $query->get();
                $templateDatas = $templates->map(function ($template) use ($uid, $category) {
                    return HelperController::getItemData($uid, $category, $template, json_decode($template->thumb_array, true, 512, JSON_UNESCAPED_SLASHES), true, true);
                });
                $data['template_model'] = $templateDatas;
                $datas[] = $data;
            }
        }
        return $datas;
    }

    function getDashboardDatas(Request $request): array|string
    {

        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $cat_rows = array();

        $page = (int) $request->has('page') ? $request->get('page') : 1;

        $status_condition = "=";
        $status = "1";

        $limit = 10;

        $catDataRaws = Category::where("status", $status_condition, $status)->orderBy('sequence_number', 'ASC')->paginate($limit, ['*'], 'page', $page);

        if ($catDataRaws != null) {
            foreach ($catDataRaws->items() as $row) {

                $itemData = Design::where("status", $status_condition, $status)
                    ->where("category_id", $row->id)
                    ->orderBy('created_at', 'DESC')->take(15)->get();

                if ($itemData != null && $itemData->count() != 0) {
                    $item_rows = array();
                    foreach ($itemData as $item) {
                        $item_rows[] = HelperController::getItemData($this->uid, $row, $item, json_decode($item->thumb_array));
                    }

                    $cat_rows[] = array(
                        'category_id' => $row->id,
                        'id_name' => $row->id_name,
                        'category_name' => $row->category_name,
                        'category_thumb' => HelperController::$mediaUrl . $row->category_thumb,
                        'template_model' => $item_rows,
                    );
                }
            }
        }

        $response['isLastPage'] = sizeof($cat_rows) < $limit;
        $response['datas'] = $this->getAllNewCategories($this->uid);
        $response['oldDatas'] = $cat_rows;
        $response['seo'] = self::$latestSeo;

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, 'Loaded!', $response));

    }

    private function getCategoryPostersForWeb($request, $forCheckCategory, $category_id, $page, $limit): array|string
    {

        if ($forCheckCategory && $forCheckCategory !== "templates") {
            $redirectUrl = PageSlugHistoryController::findOne("$forCheckCategory/$category_id", 3);
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Invalid params!", ["redirectUrl" => $redirectUrl]));
        }

        $redirectUrl = PageSlugHistoryController::findOne($category_id, 4);
        if ($redirectUrl) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Invalid params!", ["redirectUrl" => $redirectUrl]));
        }

        $item_rows = array();

        $catRow = null;

        if ($category_id == "latest") {
            $itemData = Design::where("status", 1)->whereHas('parent', function ($query) {
                $query->where('status', 1);
            })->orderBy('created_at', 'DESC')->paginate($limit, ['*'], 'page', $page);

            $isLastPage = $itemData->currentPage() >= $itemData->lastPage();

        } else if ($category_id == "trending") {
            $limit = 20;
            $itemData = Design::where("trending_views", ">", 0)->where("status", 1)->whereHas('parent', function ($query) {
                $query->where('status', 1);
            })->orderBy('trending_views', 'DESC')->paginate($limit, ['*'], 'page', $page);
            $isLastPage = true;

        } else {
            $fieldName = is_numeric($category_id) ? "id" : "id_name";
            $catRow = Category::where($fieldName, $category_id)->where('status', 1)->first();
            if ($catRow == null) {
                return self::getVirtualCategory($request, $category_id, $page, $limit, $redirectUrl);
            }

            $itemData = Design::where("category_id", $catRow->id)->where("status", 1)->whereHas('parent', function ($query) {
                $query->where('status', 1);
            })->orderBy('created_at', 'DESC')->paginate($limit, ['*'], 'page', $page);
            $isLastPage = $itemData->currentPage() >= $itemData->lastPage();
        }

        if ($category_id == "latest" || $category_id == "trending") {
            foreach ($itemData->items() as $item) {
                $catRow_ = Category::find($item->category_id);
                if ($catRow_ != null) {
                    $item_rows[] = HelperController::getItemData($this->uid, $catRow_, $item, json_decode($item->thumb_array));
                }
            }
        } else {
            if ($catRow != null) {
                foreach ($itemData->items() as $item) {
                    $item_rows[] = HelperController::getItemData($this->uid, $catRow, $item, json_decode($item->thumb_array), true, false, true);
                }
            }
        }

        if ($category_id == "latest") {
            $seo = self::$latestSeo;
        } else if ($category_id == "trending") {
            $seo = self::$trendingSeo;
        } else {
            $seo['meta_title'] = $catRow != null ? $catRow->meta_title : null;
            $seo['meta_desc'] = $catRow != null ? $catRow->meta_desc : null;
            $seo['h1_tag'] = $catRow != null ? $catRow->h1_tag : null;
            $seo['h2_tag'] = $catRow != null ? $catRow->h2_tag : null;
            $seo['short_desc'] = $catRow != null ? $catRow->short_desc : null;
            $seo['long_desc'] = $catRow != null ? $catRow->long_desc : null;
            $seo['tag_line'] = $catRow != null ? $catRow->tag_line : null;
        }

        $seo['tag_line'] = $seo['tag_line'] ?? CategoryTemplatesApiController::$defaultTagLine;

        $responsePayload = [
            "templateCount" => HelperController::getTemplateCount($itemData->total(), $catRow != null ? $catRow->primary_keyword : $category_id),
            "new_api" => false,
            "isLastPage" => $isLastPage,
            "category_id" => $catRow != null ? $catRow->id : -1,
            "datas" => $item_rows,
            "seo" => $seo,
            "contents" => ($catRow != null && isset($catRow->contents)) ? ContentManager::getContentsPath(json_decode(StorageUtils::get($catRow->contents)), $this->uid) : [],
            "top_keywords" => ($catRow != null && isset($catRow->top_keywords)) ? HelperController::getTopKeywords(json_decode($catRow->top_keywords)) : [],

            "pagination" => PaginationController::getPagination($itemData),
            "redirectUrl" => $redirectUrl
        ];

        $responsePayload['pre_breadcrumb'] = TemplateApiController::getCategoryBreadcrumbs(null, true, $catRow != null ? $catRow->category_name : ucwords($category_id));

        if ($catRow != null) {
            $faqsResponse = ContentManager::faqsResponse($catRow->faqs, $catRow->primary_keyword);
            $responsePayload['faqs'] = $faqsResponse['faqs'];
            $responsePayload['faqs_title'] = $faqsResponse['faqs_title'];
            $responsePayload['canonical_link'] = HelperController::buildCanonicalLink($catRow->canonical_link, HelperController::getFrontendPageUrl(1, $catRow->id_name), $page);
            $responsePayload['string_id'] = $catRow->string_id;
            $responsePayload['banner'] = $catRow->banner ? HelperController::$mediaUrl . $catRow->banner : null;
            $data = PReviewController::getPReviews($this->uid, 4, $catRow->string_id, 1);
            if ($data['success']) {
                $responsePayload['reviews'] = $data['data'];
            }
        }

        if ($page == 1) {
            $user_data = UserData::where("uid", $this->uid)->first();
        }

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Loading Success!", $responsePayload));
    }

    private function getVirtualCategory($request, $category_id, $page, $limit, $redirectUrl): array|string
    {
        $item_rows = array();
        $fieldName = is_numeric($category_id) ? "id" : "id_name";
        $catRow = VirtualCategory::where($fieldName, $category_id)->where('status', 1)->first();
        if ($catRow == null) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Virtual Data not found", ["redirectUrl" => $redirectUrl]));
        }
        $query = Design::whereStatus(1)::query();
        QueryManager::applyConditionToQuery($query, explode(' && ', $catRow->virtual_query));
//        dd( $query->getBindings(),"--",$query->toSql());
        $itemData = $query->paginate($limit, ['*'], 'page', $page);
        $isLastPage = $itemData->currentPage() >= $itemData->lastPage();
        foreach ($itemData->items() as $item) {
            $item_rows[] = HelperController::getItemData($this->uid, $catRow, $item, json_decode($item->thumb_array), true, false, true);
        }

//        $seo['meta_title'] = $catRow->meta_title;
//        $seo['meta_desc'] = $catRow->meta_desc;
//        $seo['h1_tag'] = $catRow->h1_tag;
//        $seo['h2_tag'] = $catRow->h2_tag;
//        $seo['short_desc'] = $catRow->short_desc;
//        $seo['long_desc'] = $catRow->long_desc;
//        $seo['tag_line'] = $catRow->tag_line ?? CategoryTemplatesApiController::$defaultTagLine;

        $responsePayload = [
            "new_api" => false,
//            "templateCount" => HelperController::getTemplateCount($itemData->total(), $catRow->primary_keyword),
            "isLastPage" => $isLastPage,
            "category_id" => $catRow->id,
            "datas" => $item_rows,
            "toSql" => $query->toSql(),
            "bindings"=> $query->getBindings(),
//            "seo" => $seo,
//            "top_keywords" => (isset($catRow->top_keywords)) ? HelperController::getTopKeywords(json_decode($catRow->top_keywords)) : [],
//            "contents" => (isset($catRow->contents)) ? ContentManager::getContentsPath(json_decode(StorageUtils::get($catRow->contents)), $this->uid) : [],
            "pagination" => PaginationController::getPagination($itemData),
            "redirectUrl" => $redirectUrl
        ];

        $responsePayload['pre_breadcrumb'] = TemplateApiController::getCategoryBreadcrumbs(null, true, $catRow->category_name);

//        $faqsResponse = ContentManager::faqsResponse($catRow->faqs, $catRow->primary_keyword);
//        $responsePayload['faqs'] = $faqsResponse['faqs'];
//        $responsePayload['faqs_title'] = $faqsResponse['faqs_title'];
//        $responsePayload['canonical_link'] = HelperController::buildCanonicalLink($catRow->canonical_link, HelperController::getFrontendPageUrl(1, $catRow->id_name), $page);
//        $responsePayload['string_id'] = $catRow->string_id;
//        $responsePayload['banner'] = $catRow->banner ? HelperController::$mediaUrl . $catRow->banner : null;
//        $data = PReviewController::getPReviews($this->uid, 4, $catRow->string_id, 1);
//        if ($data['success']) {
//            $responsePayload['reviews'] = $data['data'];
//        }
        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Loading Success!", $responsePayload));
    }

    public function getCategories(Request $request): array|string
    {
        $page = $request->input('page', 1);
        $limit = 20;

        $isChild = $request->child;
        $forCheckCategory = $request->id;
        $category = $request->sub_id;

        if (!$forCheckCategory || !$category) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Parameters missing!"));
        }

        if ($isChild && $forCheckCategory == "templates") {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Invalid params!"));
        }

        $redirectUrl = PageSlugHistoryController::findOne("$forCheckCategory/$category", 3);

        $filter = isset($request->filter) ? $request->filter : [];

        $checkParentCat = NewCategory::where('id_name', $forCheckCategory)->first();
        if ($checkParentCat) {
            $checkCat = NewCategory::where('id_name', $category)->where('parent_category_id', $checkParentCat->id)->first();
        } else {
            $checkCat = NewCategory::where('id_name', $category)->first();
        }

        if (!$checkCat) {
            return $this->getCategoryPostersForWeb($request, $forCheckCategory, $category, $page, $limit);
        }

        if (!$checkCat->checkIsLive()) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, "Data not found", ["redirectUrl" => $redirectUrl]));
        }

        if (
            ($forCheckCategory == "templates" && $checkCat->parent_category_id != 0) ||
            ($forCheckCategory !== "templates" && (!($parentCat = NewCategory::find($checkCat->parent_category_id)) || $forCheckCategory !== $parentCat->id_name))
        ) {
            return $this->getCategoryPostersForWeb($request, $forCheckCategory, $category, $page, $limit);
            // return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Datas are incorrect"));
        }

        //        $categories = NewCategory::getCategoriesWithSubcategories($checkCat->id, 1);
//        $cateId = $categories[0]->id;
        $seoDatas = collect($checkCat)->only(['h1_tag', 'h2_tag', 'meta_title', 'meta_desc', 'short_desc', 'long_desc', 'tag_line']);
        //        $extractSubcategories = function ($subcategories) use (&$extractSubcategories) {
//            return collect($subcategories)->map(function ($subcategory) use ($extractSubcategories) {
//                return [
//                    'id' => $subcategory['id'],
//                    'category_name' => $subcategory['category_name'],
//                    'category_thumb' => HelperController::$mediaUrl . $subcategory['category_thumb'],
//                    'id_name' => $subcategory['id_name'],
//                    'status' => $subcategory['status'],
//                ];
//            })->toArray();
//        };

        //        $allCateIds = $this->getAllIds($categories[0]->toArray());
        $allCateIds = json_decode($checkCat->child_cat_id ?? '[]', true);
        //        $subCategoryData = $extractSubcategories($categories[0]['subcategories']);
//        foreach ($subCategoryData as $key => $subCategoryRow) {
//            if ($subCategoryRow['status'] == 0) {
//                unset($subCategoryData[$key]);
//            }
//        }
//
//        foreach ($subCategoryData as $key => $subCategoryRow) {
//            $subCatCount = Design::where('new_category_id', $subCategoryRow['id'])->where('status', 1)->count();
//            if ($subCatCount == 0) {
//                unset($subCategoryData[$key]);
//            }
//        }


        $newSearchTags = NewSearchTag::where('new_category_id', 'LIKE', "%\"" . $checkCat->id . "\"%")->where('status', 1)->get();
        $newSearchTags = collect($newSearchTags)->map(function ($newSearchTag) {
            return [
                'id' => $newSearchTag['id'],
                'id_name' => $newSearchTag['id_name'],
                'name' => $newSearchTag['name']
            ];
        })->toArray();

        foreach ($newSearchTags as $key => $newSearchTagRow) {
            $newSearchTagsCount = Design::whereJsonContains('new_related_tags', $newSearchTagRow['id'])->where('status', 1)->count();
            if ($newSearchTagsCount == 0) {
                unset($newSearchTags[$key]);
            }
        }

        $templatesQuery = Design::whereIn('new_category_id', $allCateIds)->where('status', 1);

        if (!empty($filter)) {
            $templatesQuery = CategoryTemplatesApiController::getFilterQuery($templatesQuery, $filter);
        }

        //        $templates = $templatesQuery->orderByRaw('pinned DESC, id DESC')->paginate($limit, ['*'], 'page', $page);
        $templates = $templatesQuery->orderByRaw('id DESC')->paginate($limit, ['*'], 'page', $page);

        $templateDatas = collect($templates->items())->map(function ($template) use ($checkCat) {
            $cateRow = NewCategory::find($template->new_category_id);
            return HelperController::getItemData($this->uid, $cateRow, $template, json_decode($template->thumb_array, true, 512, JSON_UNESCAPED_SLASHES), true, true, true, $checkCat->id);
        });

        if ($forCheckCategory == "templates" && empty($filter) && !isset($templateDatas[0])) {
            return $this->getCategoryPostersForWeb($request, $forCheckCategory, $category, $page, $limit);
        }

        //        $seoDatas['tag_line'] = $seoDatas->get('tag_line', CategoryTemplatesApiController::$defaultTagLine);
        $seoDatas['tag_line'] = $seoDatas->get('tag_line') ?? CategoryTemplatesApiController::$defaultTagLine;
        if ($checkParentCat) {
            $frontendUrl = HelperController::getFrontendPageUrl(1, $checkParentCat->id_name . "/" . $checkCat->id_name);
        } else {
            $frontendUrl = HelperController::getFrontendPageUrl(1, $checkCat->id_name);
        }
        $response = [
            "new_api" => true,
            "templateCount" => HelperController::getTemplateCount($templates->total(), $checkCat->primary_keyword),
            "isLastPage" => $templates->currentPage() >= $templates->lastPage(),
            "category_id" => $checkCat->id,
            "banner" => $checkCat->banner ? HelperController::$mediaUrl . $checkCat->banner : null,
            "string_id" => $checkCat->string_id,
            "seo" => $seoDatas,
            "datas" => $templateDatas,
            "sub_category" => array_values(CategoryTemplatesApiController::getSubCategories2(json_decode($checkCat->child_cat_id ?? '[]', true), $checkCat->id_name)),
            "new_related_tags" => array_values(CategoryTemplatesApiController::getSubCategoriesTags($checkCat->id)),
            "contents" => isset($checkCat->contents) ? ContentManager::getContentsPath(json_decode(StorageUtils::get($checkCat->contents)), $this->uid) : [],
            "top_keywords" => (isset($checkCat->top_keywords)) ? HelperController::getTopKeywords(json_decode($checkCat->top_keywords)) : [],
            //            "cta" => ($checkCat != null && isset($checkCat->cta)) ? HelperController::getCTA($checkCat->cta) : null,
            "pagination" => PaginationController::getPagination($templates, $filter),
            "redirectUrl" => $redirectUrl
        ];

        $faqsResponse = ContentManager::faqsResponse($checkCat->faqs, $checkCat->primary_keyword);
        $response['faqs'] = $faqsResponse['faqs'];
        $response['faqs_title'] = $faqsResponse['faqs_title'];
        $response['canonical_link'] = HelperController::buildCanonicalLink($checkCat->canonical_link, $frontendUrl, $page);
        $response['pre_breadcrumb'] = TemplateApiController::getCategoryBreadcrumbs($checkCat, true);

        $data = PReviewController::getPReviews($this->uid, 1, $checkCat->string_id, 1);
        if ($data['success']) {
            $response['reviews'] = $data['data'];
        }

        if ($page == 1) {
            $user_data = UserData::where("uid", $this->uid)->first();

            $url = 'https://www.craftyartapp.com/templates/' . $checkCat->id_name;
            if ($checkParentCat) {
                $url = 'https://www.craftyartapp.com/templates/' . $checkCat->checkParentCat . '/' . $checkCat->id_name;
            }

            //            FbPixel::trackEvent(FacebookEvent::VIEW_CONTENT, $request, $user_data?->name, $user_data?->email, null, $url);
        }

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Loading Success!", $response));
    }

    public static function getAllIds(array $categories, array &$ids = []): array
    {
        // Add the current category ID to the list
        if (isset($categories['id'])) {
            $ids[] = $categories['id'];
        }

        // Recursively process each subcategory
        if (isset($categories['subcategories'])) {
            foreach ($categories['subcategories'] as $subcategory) {
                CategoryTemplatesApiController::getAllIds($subcategory->toArray(), $ids);
            }
        }

        return $ids;
    }

    public static function getFilterQuery($templatesQuery, $filter)
    {
        if (!empty($filter)) {
            foreach ($filter as $key => $value) {
                switch ($key) {
                    case 'language':
                        preg_match_all("/'([^']+)'/", $value, $matches);
                        $languages = $matches[1];
                        $templatesQuery->where(function ($query) use ($languages) {
                            foreach ($languages as $language) {
                                $language = Language::where('id_name', $language)->first();
                                $langId = $language->id ?? "";
                                $query->orWhereJsonContains('lang_id', json_encode($langId));
                            }
                        });
                        break;
                    case 'style':
                        preg_match_all("/'([^']+)'/", $value, $matches);
                        $styles = $matches[1];
                        $templatesQuery->where(function ($query) use ($styles) {
                            foreach ($styles as $style) {
                                $style = Style::where('id_name', $style)->first();
                                $styleId = $style->id ?? "";
                                $query->orWhereJsonContains('style_id', json_encode($styleId));
                            }
                        });
                        break;
                    case 'size':
                        preg_match_all("/'([^']+)'/", $value, $matches);
                        $values = $matches[1];
                        $templatesQuery->where(function ($query) use ($values) {
                            foreach ($values as $value) {
                                $tempSize = Size::where('id_name', $value)->first();
                                $templateSize = $tempSize->id ?? "";
                                if ($templateSize) {
                                    $query->orWhere('template_size', $templateSize);
                                }
                            }
                        });
                        break;
                    case 'tags':
                        $value = HelperController::checkStringFormat($value, true);
                        $newSearchTag = NewSearchTag::where('id_name', $value)->first();
                        $newSearchTagName = $newSearchTag->id ?? "";
                        $templatesQuery->whereJsonContains('new_related_tags', $newSearchTagName);
                        break;
                    case 'is_premium':
                        //                        $value = ($value === "true") ? 1 : 0;
                        if ($value === "true") {
                            $templatesQuery->where(function ($query) {
                                $query->where('is_premium', 1)
                                    ->orWhere(function ($subQuery) {
                                        $subQuery->where('is_premium', 0)->where('is_freemium', 0);
                                    });
                            });
                            //                            $templatesQuery->where('is_freemium', '!=', 1);
                        } else {
                            $templatesQuery->where('is_premium', 0)->where('is_freemium', 1);
                        }
                        break;

                    case 'interest':
                        preg_match_all("/'([^']+)'/", $value, $matches);
                        $interests = $matches[1];
                        $templatesQuery->where(function ($query) use ($interests) {
                            foreach ($interests as $interest) {
                                $interest = Interest::where('id_name', $interest)->first();
                                $interestId = $interest->id ?? "";
                                if ($interestId) {
                                    $query->orWhereJsonContains('interest_id', json_encode($interestId));
                                }
                            }
                        });
                        break;

                    case 'color':
                        $lowerValue = strtolower($value);
                        $templatesQuery->whereJsonContains('color_id', $lowerValue);
                        break;

                    case 'religion':
                        preg_match_all("/'([^']+)'/", $value, $matches);
                        $religions = $matches[1];
                        $templatesQuery->where(function ($query) use ($religions) {
                            foreach ($religions as $religion) {
                                $religion = Religion::where('id_name', $religion)->first();
                                $religionId = $religion->id ?? "";
                                if ($religionId) {
                                    $query->orWhereJsonContains('religion_id', json_encode($religionId));
                                }
                            }
                        });
                        break;
                    case 'orientation':
                        preg_match_all("/'([^']+)'/", $value, $matches);
                        $orientations = $matches[1];
                        $templatesQuery->where(function ($query) use ($orientations) {
                            foreach ($orientations as $orientation) {
                                if ($orientation) {
                                    $query->orWhere('orientation', $orientation);
                                }
                            }
                        });
                        break;
                    case 'animation':
                        $value = ($value === "true") ? 1 : 0;
                        $templatesQuery->where('animation', $value);
                        break;
                    case 'theme':
                        preg_match_all("/'([^']+)'/", $value, $matches);
                        $themes = $matches[1];
                        $templatesQuery->where(function ($query) use ($themes) {
                            foreach ($themes as $theme) {
                                $theme = Theme::where('id_name', $theme)->first();
                                $themName = $theme->id ?? "";
                                if ($themName) {
                                    $query->orWhereJsonContains('theme_id', json_encode($themName));
                                }
                            }
                        });
                        break;
                }
            }
        }
        return $templatesQuery;
    }

    public static function getDesignCountBySubCat($subcategories)
    {
        return Design::whereIn('new_category_id', $subcategories->pluck('id'))
            ->where('status', 1)
            ->select('new_category_id', \DB::raw('COUNT(*) as count'))
            ->groupBy('new_category_id')
            ->pluck('count', 'new_category_id');
    }

    public static function getSubCategories2(array $childCatIds, $idName): array
    {
        $subCategoryData = [];

        $subcategories = collect(NewCategory::getCategoriesWithSubcategories2($childCatIds, 1));
        $designCounts = self::getDesignCountBySubCat($subcategories);

        foreach ($subcategories as $subcategory) {
            $designCount = $designCounts[$subcategory['id']] ?? 0;

            if ($designCount === 0) {
                continue;
            }

            $subCategoryData[] = [
                'id' => $subcategory['id'],
                'category_name' => $subcategory['category_name'],
                'category_thumb' => HelperController::$mediaUrl . $subcategory['category_thumb'],
                'url' => '/templates/' . $idName . '/',
                'link' => '/templates/' . $idName . '/',
                'id_name' => $subcategory['id_name'],
                'status' => $subcategory['status'],
            ];
        }

        return $subCategoryData;
    }


    public static function getSubCategories(array|int $categories): array
    {
        if (is_int($categories)) {
            $categories = NewCategory::getCategoriesWithSubcategories($categories, 1);
        }

        $subCats = [];
        $subCatTags = [];
        if (isset($categories[0])) {
            $cat = $categories[0];
            $parentCat = $cat->parent;
            if (!$parentCat) {
                $extractSubcategories = function ($subcategories) use (&$extractSubcategories, $cat) {
                    return collect($subcategories)->map(function ($subcategory) use ($extractSubcategories, $cat) {
                        return [
                            'id' => $subcategory['id'],
                            'category_name' => $subcategory['category_name'],
                            'category_thumb' => HelperController::$mediaUrl . $subcategory['category_thumb'],
                            'url' => '/templates/' . $cat->id_name . '/',
                            'link' => '/templates/' . $cat->id_name . '/',
                            'id_name' => $subcategory['id_name'],
                            'status' => $subcategory['status'],
                        ];
                    })->toArray();
                };

                $subCategoryData = $extractSubcategories($categories[0]['subcategories']);
                $subCatIds = collect($subCategoryData)->pluck('id')->all();

                $designCountsBySubCat = Design::whereIn('new_category_id', $subCatIds)
                    ->where('status', 1)
                    ->select('new_category_id', DB::raw('COUNT(*) as count'))
                    ->groupBy('new_category_id')
                    ->pluck('count', 'new_category_id'); // [sub_cat_id => count]

                $subCats = array_filter($subCategoryData, function ($subCategory) use ($designCountsBySubCat) {
                    return isset($designCountsBySubCat[$subCategory['id']]) && $designCountsBySubCat[$subCategory['id']] > 0;
                });
            } else {
                $id_name = $cat->id_name;
                $newSearchTags = NewSearchTag::where('new_category_id', 'LIKE', "%\"" . $cat->id . "\"%")->where('status', 1)->get();
                $newSearchTags = collect($newSearchTags)->map(function ($newSearchTag) use ($id_name, $parentCat) {
                    return [
                        'id' => $newSearchTag['id'],
                        'url' => '/templates/' . $parentCat->id_name . '/' . $id_name . '/?query=',
                        'link' => '/templates/' . $parentCat->id_name . '/' . $id_name . '/?query=',
                        'id_name' => $newSearchTag['id_name'],
                        'name' => $newSearchTag['name']
                    ];
                });

                $tagIds = $newSearchTags->pluck('id')->all();

                $designsWithTags = Design::where('status', 1)
                    ->where(function ($q) use ($tagIds) {
                        foreach ($tagIds as $tagId) {
                            $q->orWhereJsonContains('new_related_tags', $tagId);
                        }
                    })->get();

                $tagCounts = [];
                foreach ($tagIds as $tagId) {
                    $tagCounts[$tagId] = $designsWithTags->filter(function ($design) use ($tagId) {
                        return in_array($tagId, $design->new_related_tags ?? []);
                    })->count();
                }

                $subCatTags = $newSearchTags->filter(function ($tag) use ($tagCounts) {
                    return isset($tagCounts[$tag['id']]) && $tagCounts[$tag['id']] > 0;
                })->values()->toArray();
            }

        }
        return ["subCats" => $subCats, "subCatTags" => $subCatTags];
    }

    public static function getSubCategoriesTags($catId): array
    {
        $cat = NewCategory::where('id', $catId)->where('status', 1)->where('parent_category_id', "!=", 0)->first();
        if (!$cat) {
            return [];
        }

        $parentCatIdName = NewCategory::where('id', $cat->parent_category_id)->where('status', 1)->value('id_name');

        $id_name = $cat->id_name;

        // Fetch all relevant tags
        $newSearchTags = NewSearchTag::where('new_category_id', 'LIKE', '%"' . $catId . '"%')
            ->where('status', 1)
            ->get();

        $tagIds = $newSearchTags->pluck('id');

        // Fetch counts for each tag in one query using JSON_CONTAINS
        $designTagCounts = Design::where('status', 1)
            ->where(function ($query) use ($tagIds) {
                foreach ($tagIds as $tagId) {
                    $query->orWhereJsonContains('new_related_tags', $tagId);
                }
            })
            ->selectRaw('JSON_EXTRACT(new_related_tags, "$") as tag_ids')
            ->get();

        $tagUsageMap = [];
        foreach ($designTagCounts as $design) {
            $tags = json_decode($design->tag_ids, true);
            if (!is_array($tags))
                continue;
            foreach ($tags as $tag) {
                if (in_array($tag, $tagIds->all())) {
                    $tagUsageMap[$tag] = ($tagUsageMap[$tag] ?? 0) + 1;
                }
            }
        }

        $filteredTags = $newSearchTags->filter(function ($tag) use ($tagUsageMap) {
            return ($tagUsageMap[$tag->id] ?? 0) > 0;
        })->map(function ($tag) use ($id_name, $parentCatIdName) {
            return [
                'id' => $tag->id,
                'url' => '/templates/' . $parentCatIdName . '/' . $id_name . '/?query=',
                'link' => '/templates/' . $parentCatIdName . '/' . $id_name . '/?query=',
                'id_name' => $tag->id_name,
                'name' => $tag->name
            ];
        })->values()->toArray();

        return $filteredTags;
    }


    public function applyPromoCode(Request $request): array|string
    {
        $amount = $request->amount;
        $code = $request->promo_code;
        $currency = $request->currency;
        $purchaseType = $request->type;
        $fail = fn($msg) => ResponseHandler::sendResponse($request, new ResponseInterface(401, false, $msg));

        if (!$amount || !$code || !$currency || !$purchaseType)
            return $fail("Parameters missing!");
        if (!is_numeric($amount))
            return $fail("Amount invalid!");

        $promo = PromoCode::where('promo_code', $code)->where("status", 1)->first();
        if (!$promo)
            return $fail("Code is invalid!");
        if ($promo->expiry_date && Carbon::parse($promo->expiry_date)->lt(Carbon::today()))
            return $fail("Code is expired!");
        if ($promo->type != 0 && $promo->type != $purchaseType)
            return $fail("Promo code is not valid for this Subscription type!");


        $allowedUsers = json_decode($promo->user_id, true);
        if ($allowedUsers && !in_array((string) $this->uid, $allowedUsers))
            return $fail("You are not valid for this Promo!");


        $minCart = $currency === 'INR' ? $promo->min_cart_inr : $promo->min_cart_usd;

        if ($amount < $minCart)
            return $fail("Minimum amount for this promo is " . ($currency === 'INR' ? "₹{$minCart}" : "\${$minCart}"));

        $discount = round($amount * $promo->disc / 100);
        $maxDiscount = $currency === 'INR' ? $promo->disc_upto_inr : $promo->disc_upto_usd;
        $discount = $maxDiscount == 0 ? $discount : min($discount, $maxDiscount);

        $response = [
            'discount' => $promo->disc,
            'amount' => $amount - $discount,
            'saving' => $discount,
            'msg' => "You have saved $discount",
        ];

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Promo code applied", $response));
    }
    //    public static function getSubCategoriesTags($catId): array
//    {
//        $categories = NewCategory::getCategoriesWithSubcategories($catId, 1);
//
//        if (isset($categories[0])) {
//
//            $parentCat = $categories[0]->parent;
//
//            if (!$parentCat) {
//                return []; // Or handle this case appropriately
//            }
//
//            $id_name = $categories[0]['id_name'];
//            $newSearchTags = NewSearchTag::where('new_category_id', 'LIKE', "%\"" . $categories[0]['id'] . "\"%")->where('status', 1)->get();
//            $newSearchTags = collect($newSearchTags)->map(function ($newSearchTag) use ($id_name, $parentCat) {
//                return [
//                    'id' => $newSearchTag['id'],
//                    'url' => '/templates/' . $parentCat->id_name . '/' . $id_name . '/?query=',
//                    'link' => '/templates/' . $parentCat->id_name . '/' . $id_name . '/?query=',
//                    'id_name' => $newSearchTag['id_name'],
//                    'name' => $newSearchTag['name']
//                ];
//            })->toArray();
//
//            foreach ($newSearchTags as $key => $newSearchTagRow) {
//                $newSearchTagsCount = Design::whereJsonContains('new_related_tags', $newSearchTagRow['id'])->where('status', 1)->count();
//                if ($newSearchTagsCount == 0) {
//                    unset($newSearchTags[$key]);
//                }
//            }
//            return $newSearchTags;
//        }
//        return [];
//    }



}
