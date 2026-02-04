<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Utils\StorageUtils;
use App\Models\Caricature\Attire;
use App\Models\Api\Caricature\CaricatureCategory;
use App\Models\UserData;
use Cache;
use Illuminate\Http\Request;

class CaricatureController extends ApiController
{
    public static string $defaultTagLine = 'Beautiful and elegant designs with customizable templates - perfect for any special celebrations!';

    public function getCategories(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) return $this->failed(msg: "Unauthorized");

        $page = $request->input('page', 1);
        $categories = CaricatureCategory::getAllCatsWithChilds(page: $page);

        $isLastPage = $categories->lastPage() === $categories->currentPage();

        $datas = [];
        $cats = [];

        $rates = RateController::getRates();
        foreach ($categories->items() as $category) {
            $categoryId = $category->id;
            $allIds = array_merge([$categoryId], $category->child_cat_ids ?? []);
            $templates = Attire::whereIn('category_id', $allIds)->where('status', 1)->latest()->take(1)->get();

            if ($templates->isEmpty()) continue;

            $allCategoryMap = [];

            $commonData = [
                'category_id' => $categoryId,
                'id_name' => $category->id_name,
                'category_name' => $category->category_name,
                'category_thumb' => config('filesystems.storage_url') . $category->category_thumb,
                'category_mockup' => $category->mockup ? config('filesystems.storage_url') . $category->mockup : null,
                "pageNo" => 1,
                'isLastPage' => $category->total_templates <= 1
            ];

            $processedTemplates = $templates->map(function ($template) use ($allCategoryMap, &$usedCategoryMap, $rates) {
                $cateRow = $allCategoryMap[$template->category_id] ?? null;
                return HelperController::getCaricatureData(
                    catRow: $cateRow,
                    item: $template,
                    rates: $rates
                );
            });

            $cats[] = $commonData;

            $datas[] = array_merge($commonData, [
                'attires' => $processedTemplates,
            ]);
        }

        return $this->successed(datas: [
            "pageNo" => $page,
            "isLastPage" => $isLastPage,
            "cats" => $cats,
            "datas" => $datas
        ]);
    }

    public function getCategory(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) return $this->failed(msg: "Unauthorized");

        $oldSlug = $request->slug;
        if (!$oldSlug) return $this->failed(msg: "Parameters missing!");

        $hasPageInRequest = $request->has('page');
        $data = HelperController::extractAndRemoveTrailingNumber($oldSlug);
        $page = $hasPageInRequest ? $request->input('page', 1) : $data['number'] ?? 1;
        $slug = $data['string'] ?? 1;

        $limit = 1;

        $filter = isset($request->filter) ? $request->filter : [];

        $cacheTag = CaricatureCategory::$preCacheTag . "category_$slug";
        $contentCacheKey = 'category_content_' . $slug;
        $faqCacheKey = 'category_faq_' . $slug;

        $cacheKey = 'categories_' . $slug . md5(json_encode(['filter' => json_encode($filter), 'page' => $page]));

        $checkCat = CaricatureCategory::findCatLink(isStatus: 1, id: $slug);

        if (!$checkCat) return $this->failed(msg: "Parameters missing!");

        $callback = function ($doCache = true) use ($cacheTag, $contentCacheKey, $faqCacheKey, $filter, $checkCat, $limit, $page, $hasPageInRequest) {

            $usedCategoryMap = array_merge([$checkCat->id], $checkCat->child_cat_ids ?? []);

            $attiresQuery = Attire::whereIn('category_id', $usedCategoryMap)->where('status', 1);

            $attires = $attiresQuery->orderByRaw('id DESC')->paginate($limit, ['*'], 'page', $page);

            if ($attires->total() == 0) return $this->failed(msg: "Parameters missing!");

            $allCategoryMap = [];

            if ($this->uid) {
                $allCategoryMap[$checkCat->id] = $checkCat;
                foreach ($checkCat->subcategories ?? [] as $sub) {
                    $allCategoryMap[$sub->id] = $sub;
                }
            }

            $rates = RateController::getRates();

            $templateDatas = [];
            foreach ($attires->items() as $attire) {
                $cateRow = $allCategoryMap[$attire->category_id] ?? null;
                $templateDatas[] = HelperController::getCaricatureData(catRow: $cateRow, item: $attire, rates: $rates);
            }

            $response = [
                "new_api" => true,
                "page_link" => $checkCat->cat_link,
                "filter_link" => $checkCat->cat_link,
                "templateCount" => HelperController::getTemplateCount($attires->total(), $checkCat->primary_keyword),
                "isLastPage" => $attires->currentPage() >= $attires->lastPage(),
                "pageNo" => $page,
                "category_id" => $checkCat->id,
                "banner" => $checkCat->banner ? config('filesystems.storage_url') . $checkCat->banner : null,
                "string_id" => $checkCat->string_id,
                "datas" => $templateDatas,
                "pagination" => PaginationController::getPagination($attires, $filter, $checkCat->cat_link)
            ];

            $subCatArray = CaricatureController::getSubCategories($checkCat);

            $response['parent_cats'] = $subCatArray['parentTags'];
            $response['sub_category'] = $subCatArray['subCats'];

            $seoDatas = collect($checkCat)->only(['h1_tag', 'h2_tag', 'meta_title', 'meta_desc', 'short_desc', 'long_desc', 'tag_line']);
            $seoDatas['tag_line'] = $seoDatas->get('tag_line') ?? CaricatureController::$defaultTagLine;

            $response['seo'] = $seoDatas;
            $response['top_keywords'] = (isset($checkCat->top_keywords)) ? HelperController::getTopKeywords(json_decode($checkCat->top_keywords)) : [];
            $response['contents'] = isset($checkCat->contents) ? ContentManager::getContentsPath(rates: $rates, contents: json_decode(StorageUtils::get($checkCat->contents)), uid: $this->uid, cacheTag: $cacheTag, cacheKey: $contentCacheKey, doCache: $doCache) : [];

            $faqsResponse = ContentManager::faqsResponse(faqs: $checkCat->faqs, premiumKeyword: $checkCat->primary_keyword, cacheTag: $cacheTag, cacheKey: $faqCacheKey, doCache: $doCache);
            $response['faqs'] = $faqsResponse['faqs'];
            $response['faqs_title'] = $faqsResponse['faqs_title'];
            $response['canonical_link'] = PaginationController::buildCanonicalLink($checkCat->canonical_link, $checkCat->cat_link, $page);
            $response['pre_breadcrumb'] = self::getCategoryBreadcrumbs($checkCat);

            $data = PReviewController::getPReviews($this->uid, 1, $checkCat->string_id, 1);
            if ($data['success']) $response['reviews'] = $data['data'];

            return ResponseHandler::sendRealResponse(new ResponseInterface(200, true, "Loading Success!", $response));
        };

        if (HelperController::$cacheEnabled) {
            $response = Cache::tags([$cacheTag])->remember($cacheKey, HelperController::$cacheTimeOut, $callback);
        } else {
            $response = $callback(false);
        }

        if (!$response['success'] || count($response['datas']) == 0) $response = $callback(false);

        if (isset($response['success']) && $response['success']) {
            $user_data = UserData::where("uid", $this->uid)->first();
            $url = $checkCat->cat_link;
//            FbPixel::trackEvent(FacebookEvent::VIEW_CONTENT, $request, $user_data?->name, $user_data?->email, null, $url);
        }

        return ResponseHandler::sendEncryptedResponse($request, $response);
    }

    public function getAttire(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) return $this->failed(msg: "Unauthorized");

        $id_name = $request->id;
        if (!$id_name) return $this->failed(msg: "Parameters missing!");

        $string_id = explode('-', $id_name)[0];
        $itemData = Attire::whereStringId($string_id)->whereStatus(1)->first();

        if (!$itemData) {
            if (is_numeric($id_name)) {
                $itemData = Attire::whereIdName($id_name)->whereStatus(1)->first();
            }
        }

        if (!$itemData) return $this->failed(msg: "Parameters missing");

        $rates = RateController::getRates();
        $catRow = CaricatureCategory::findId(select: null, isStatus: 1, id: $itemData->category_id);
        $item_rows = HelperController::getCaricatureData(
            catRow: $catRow,
            item: $itemData,
            rates: $rates
        );

        $cacheTag = CaricatureCategory::$preCacheTag . "attire_$itemData->string_id";
        $contentCacheKey = 'attire_content_' . $itemData->string_id;
        $faqCacheKey = 'attire_faq_' . $itemData->string_id;

        $pageUrl = $item_rows['page_link'];

        $response['data'] = $item_rows;
        $response['canonical_link'] = PaginationController::buildCanonicalLink($itemData->canonical_link, $pageUrl, 1);
        $response['contents'] = isset($itemData->contents) ? ContentManager::getContentsPath(rates: $rates, contents: json_decode(StorageUtils::get($itemData->contents)), uid: $this->uid, cacheTag: $cacheTag, cacheKey: $contentCacheKey, doCache: true) : [];

        $faqsResponse = ContentManager::faqsResponse(faqs: $itemData->faqs, premiumKeyword: "", cacheTag: $cacheTag, cacheKey: $faqCacheKey, doCache: true);
        $response['faqs'] = $faqsResponse['faqs'];
        $response['faqs_title'] = $faqsResponse['faqs_title'];

        return $this->successed(datas: $response);

    }

    public static function getSubCategories(CaricatureCategory|int|null $category): array
    {
        if (is_int($category)) $category = CaricatureCategory::findId(select: null, isStatus: 1, id: $category);

        $parents = CaricatureCategory::query()->select(['id', 'id_name', 'category_name', 'category_thumb', 'cat_link'])->whereParentCategoryId(0)->where('total_templates', '>', 0)->whereStatus(1)->get();
        $parentTags = [];
        foreach ($parents as $parent) {
            $parentTags[] = [
                'id' => $parent->id,
                'category_name' => $parent->category_name,
                'category_thumb' => config('filesystems.storage_url') . $parent->category_thumb,
                'url' => $parent->cat_link,
                'link' => $parent->cat_link,
                'id_name' => $parent->id_name,
                'status' => 1,
            ];
        }

        if ($category) {
            if ($category->parent) {
                $parentCat = CaricatureCategory::findId(select: null, isStatus: 1, id: $category->parent['id']);
                return ["parentTags" => array_values($parentTags), "subCats" => self::getChilds($parentCat)];
            } else {
                return ["parentTags" => array_values($parentTags), "subCats" => self::getChilds($category)];
            }
        }

        return ["parentTags" => array_values($parentTags), "subCats" => []];
    }

    private static function getChilds(CaricatureCategory|null $category): array
    {
        $childs = [];
        if ($category && !$category->parent) {
            foreach ($category->subcategories as $subcategory) {
                $childs[] = [
                    'id' => $subcategory->id,
                    'category_name' => $subcategory->category_name,
                    'category_thumb' => config('filesystems.storage_url') . $subcategory->category_thumb,
                    'url' => $subcategory->cat_link,
                    'link' => $subcategory->cat_link,
                    'id_name' => $subcategory->id_name,
                    'status' => $subcategory->status,
                ];
            }
        }

        return array_values($childs);
    }

    public static function getCategoryBreadcrumbs(CaricatureCategory $cat = null, $last = null, $link = null): array
    {

        $pre_breadcrumb[] = [
            'value' => "Crafty Art",
            "link" => "https://www.craftyartapp.com",
            "openinnewtab" => 0,
            "nofollow" => 0
        ];

        $pre_breadcrumb[] = [
            'value' => "Caricatures",
            "link" => "https://www.craftyartapp.com/caricature",
            "openinnewtab" => 0,
            "nofollow" => 0
        ];

        if ($cat) {
            if ($cat->parent) {
                $pre_breadcrumb[] = [
                    'value' => $cat->parent['category_name'],
                    "link" => $cat->parent['cat_link'],
                    "openinnewtab" => 0,
                    "nofollow" => 0
                ];
            }
            $pre_breadcrumb[] = [
                'value' => $cat->category_name,
                "link" => $cat->cat_link,
                "openinnewtab" => 0,
                "nofollow" => 0
            ];
        }

        if (is_null($last) && !empty($pre_breadcrumb)) {
//            $lastIndex = count($pre_breadcrumb) - 1;
//            unset($pre_breadcrumb[$lastIndex]['link']);
//            unset($pre_breadcrumb[$lastIndex]['openinnewtab']);
//            unset($pre_breadcrumb[$lastIndex]['nofollow']);
        } else {
            if ($last) $pre_breadcrumb[] = ['value' => $last, "link" => $link];
        }

        return $pre_breadcrumb;
    }

}
