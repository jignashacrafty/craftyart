<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Utils\ContentManager;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\NewCategory;
use App\Models\SpecialPage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageApiController extends ApiController
{
    public function getPage(Request $request): array|string
    {

        //        if ($this->isFakeRequest($request)) {
//            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
//        }

        if ($request->has('showForce') && $request->input('showForce')) {
            $page = SpecialPage::wherePageSlug($request->slug)->first();
        } else {
            $page = SpecialPage::wherePageSlug($request->slug)->whereStatus(1)->first();
        }

        if (!$page) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, "Data not found", ["page_slug_history" => PageSlugHistoryController::get(1)]));
        }
        $page->image = isset($page->image) ? ContentManager::getStorageLink($page->image) : '';
        $page->contents = StorageUtils::exists($page->contents) ? ContentManager::getContentsPath(json_decode(file_get_contents(StorageUtils::get($page->contents))),$this->uid) : [];


//        $page->faqs = json_decode(file_get_contents(StorageUtils::get($page->faqs)));
//        $pageArray = $page->toArray();
//        HelperController::faqsResponse($page->faqs, $pageArray);
        $faqsResponse = ContentManager::faqsResponse($page->faqs);
        $page->faqs = $faqsResponse['faqs'];
        $page->faqs_title = $faqsResponse['faqs_title'];
        $page->hero_background_image = isset($page->hero_background_image) ? ContentManager::getStorageLink($page->hero_background_image) : '';
        $page->body_background_image = isset($page->body_background_image) ? ContentManager::getStorageLink($page->body_background_image) : '';

        if (isset($page->banner) && $page->banner != null && $page->banner != "") {
            $page->banner = ContentManager::getStorageLink($page->banner);
        } else {
            unset($page['banner']);
        }

        $data = PReviewController::getPReviews($this->uid, 2, $page->string_id, 1);
        if ($data['success']) {
            $page->reviews = $data['data'];
        }

        $keyword = null;
        $onlyVideo = false;
        foreach ($page->contents as $content) {
            if (isset($content->type) && $content->type === "api" && isset($content->value)) {
                $value = $content->value;
                $keyword = $value->keyword;
                $onlyVideo = isset($value->only_video) && ($value->only_video == 1 || $value->only_video === "1");
            }
        }
        $filter = $request->get('filter', []);
        $pageNo = $request->get('page', 0);

        $templateDatas = TemplateApiController::fetchSpecialTemplatesData(
            $keyword,
            $onlyVideo,
            $request->slug,
            $filter,
            $pageNo,
            20,
            true
        );
        $page->datas = $templateDatas['datas'];
        $page->pagination = $templateDatas['pagination'];
        $page->isLastPage = $templateDatas['isLastPage'];
        $page->templateCount = \App\Http\Controllers\HelperController::getTemplateCount($templateDatas['count'],$page->primary_keyword);
        $page->category_id = $page->cat_id;
        $page->pre_breadcrumb = TemplateApiController::getCategoryBreadcrumbs(NewCategory::find($page->cat_id));
        $page->sub_category = array_values(CategoryTemplatesApiController::getSubCategories($page->cat_id));
        $page->new_related_tags = array_values(CategoryTemplatesApiController::getSubCategoriesTags($page->cat_id));
        $page->canonical_link = HelperController::buildCanonicalLink($page->canonical_link,HelperController::getFrontendPageUrl(2,$request->slug),$pageNo);
        $page->top_keywords = isset($page->top_keywords) ? HelperController::getTopKeywords(json_decode($page->top_keywords)) : [];

        $page->page_slug_history = PageSlugHistoryController::get(1);

        unset($page['cat_id']);

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "loaded", $page->toArray()));
    }
}