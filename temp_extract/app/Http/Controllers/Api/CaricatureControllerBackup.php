<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Utils\ContentManager;
use App\Models\Caricature\Attire;
use App\Models\Caricature\CaricatureCategory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Utils\StorageUtils;
use Illuminate\Support\Str;

class CaricatureControllerBackup extends ApiController
{

    public function categoryAddOrUpdate(Request $request): array|string
    {
        $validator = Validator::make($request->all(), [
            'category_name' => 'required|string|max:255',
            'id_name' => 'required|string|max:255|alpha_dash',
            'parent_category_id' => 'nullable|integer',
            'primary_keyword' => 'required|string|max:255',
            'tag_line' => 'nullable|string|max:500',
            'meta_title' => 'required|string|max:60',
            'h1_tag' => 'required|string|max:255',
            'h2_tag' => 'nullable|string|max:255',
            'meta_desc' => 'required|string|max:160',
            'short_desc' => 'required|string|max:500',
            'long_desc' => 'required|string',
            'category_thumb' => 'required|string',
            'banner' => 'nullable|string',
            'mockup' => 'required|string',
            'app_id' => 'nullable|integer',
            'sequence_number' => 'nullable|integer',
            'status' => 'required|integer|in:0,1',
            'canonical_link' => 'nullable|url',
            'contents' => 'nullable|json',
            'faqs_title' => 'required_if:faqs,!=,null|string|max:255',
            'faqs' => 'nullable|json',
            'keyword_name' => 'nullable|array',
            'keyword_link' => 'nullable|array',
            'keyword_target' => 'nullable|array',
            'keyword_rel' => 'nullable|array',
            'keyword_name.*' => 'string|max:255',
            'keyword_link.*' => 'url',
            'keyword_target.*' => 'in:_blank,_self',
            'keyword_rel.*' => 'in:nofollow,follow',
        ], [
            'category_name.required' => 'Category name is required',
            'id_name.required' => 'ID name is required',
            'meta_title.required' => 'Meta title is required',
            'meta_desc.required' => 'Meta description is required',
            'category_thumb.required' => 'Category thumbnail is required',
            'mockup.required' => 'Mockup image is required',
            'faqs_title.required_if' => 'FAQ title is required when FAQs are provided',
        ]);

        if ($validator->fails()) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(422, false, $validator->errors()->first()));
        }

        $categoryName =  $request->input('category_name');
        $idName = $request->input('id_name');
        $id = $request->input('id') ?? 0;

        $data = CaricatureCategory::where('id', "!=", $id)->where("category_name", $categoryName)->first();
        $data2 = CaricatureCategory::where('id', "!=", $id)->where("id_name", $idName)->first();
        if ($data || $data2) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, "Category Name or Id Name Already exist."));
        }

        $cat = CaricatureCategory::find($request->input('parent_category_id'));
        if ($cat && $cat->parent_category_id != 0) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, "Use Parent Category."));
        }

        $contentError = ContentManager::validateContent($request->contents, $request->long_desc, $request->h2_tag);
        if ($contentError) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, $contentError));
        }

        $base64Images = [...ContentManager::getBase64Contents($request->contents), ['img' => $request->category_thumb, 'name' => "Category Thumb", 'required' => true], ['img' => $request->banner, 'name' => "Banner", 'required' => false], ['img' => $request->mockup, 'name' => "Mockup", 'required' => true]];
        $validationError = ContentManager::validateBase64Images($base64Images);
        if ($validationError) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, $validationError));
        }

        if (isset($request->faqs) && str_replace(['[', ']'], '', $request->faqs) != '') {
            if (!isset($request->faqs_title)) {
                return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, "Please Add Faq Title"));
            }
        }

        $keywordNames = $request->input('keyword_name');
        $keywordLinks = $request->input('keyword_link');
        $keywordTargets = $request->input('keyword_target');
        $keywordRels = $request->input('keyword_rel');

        $topKeywords = [];
        for ($i = 0; $i < count($keywordNames); $i++) {
            $keyword['value'] = $keywordNames[$i];
            $keyword['link'] = $keywordLinks[$i];
            $keyword['openinnewtab'] = $keywordTargets[$i];
            $keyword['nofollow'] = $keywordRels[$i];
            $topKeywords[] = $keyword;
        }

        $slug = '';
        if ($request->input('parent_category_id') != null && $request->input('parent_category_id') != 0) {
            $idName = CaricatureCategory::where('id', $request->input('parent_category_id'))->value('id_name') ?? '';
            if (isset($idName))
                $slug = $idName . "/" . $request->input('id_name');
        } else {
            $slug = $request->input('id_name');
        }

        $canonical_link = $request->input('canonical_link');
        $canonicalError = "";
        if(isset($canonical_link)){
            if(!str_starts_with($canonical_link, HelperController::$frontendUrl)) {
                $canonicalError = "Canonical link must be start with ".HelperController::$frontendUrl;
            }
        }
        if ($canonicalError) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, $canonicalError));
        }


        if($id && $id != 0){
            $res = CaricatureCategory::find($id);
            if (!$res) {
                return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, "Category not found."));
            }
        } else {
            $res = new CaricatureCategory();
        }


        $res->string_id = $res->string_id ?? HelperController::generateId();
        $res->canonical_link = $canonical_link;
        $res->cat_link = $slug;
        $res->category_name = $request->input('category_name');
        $res->primary_keyword = $request->input('primary_keyword');
        $res->id_name = $request->input('id_name');
        $res->tag_line = $request->input('tag_line');
        $res->meta_title = $request->input('meta_title');
        $res->h1_tag = $request->input('h1_tag');
        $res->h2_tag = $request->input('h2_tag');
        $res->meta_desc = $request->input('meta_desc');
        $res->short_desc = $request->input('short_desc');
        $res->long_desc = $request->input('long_desc');

        $fldrStr = $res->fldr_str;
        if (empty($fldrStr)) {
            $fldrStr = HelperController::generateID('', 10);
            while (CaricatureCategory::where('fldr_str', $fldrStr)->exists()) {
                $fldrStr = HelperController::generateID('', 10);
            }
            $res->fldr_str = $fldrStr;
        }

        $res->category_thumb = ContentManager::saveImageToPath($request->category_thumb, 'caricature/category/'.$fldrStr."/" . bin2hex(random_bytes(20)) . Carbon::now()->timestamp);
        $res->banner = ContentManager::saveImageToPath($request->banner, 'caricature/category/'.$fldrStr."/" . bin2hex(random_bytes(20)) . Carbon::now()->timestamp);
        $res->mockup = ContentManager::saveImageToPath($request->mockup, 'caricature/category/'.$fldrStr."/" . bin2hex(random_bytes(20)) . Carbon::now()->timestamp);

        $res->top_keywords = json_encode($topKeywords);
        $res->sequence_number = $request->input('sequence_number');
        $res->status = $request->input('status');
        $res->parent_category_id = $request->input('parent_category_id');

        $res->emp_id = 0;

        $res->seo_emp_id = 0;


        if ($request->input('contents')) {
            $contents = ContentManager::getContents($request->input('contents'), $fldrStr);
            $contentPath = 'caricature/category/'.$fldrStr . '/jn/' . StorageUtils::getNewName() . ".json";
            StorageUtils::put($contentPath, $contents);
            $res->contents = $contentPath;
        }

        if (isset($request->faqs)) {
            $faqPath = 'caricature/category/'.$fldrStr. '/fq/' . StorageUtils::getNewName() . ".json";
            $faqs = [];
            $faqs['title'] = $request->faqs_title;
            $faqs['faqs'] = json_decode($request->faqs);
            StorageUtils::put($faqPath, json_encode($faqs));
            $res->faqs = $faqPath;
        }

//        $res->child_updated_at = HelperController::newCatChildUpdatedAt($res->parent_category_id);

        $res->save();


        self::updateParentChildRelation($res->id, $res->parent_category_id);

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, $id && $id != 0 ? "Updated Successfully" : "Successfully Added"));
    }

    public static function updateParentChildRelation($childId, $newParentId, $oldParentId = null, $isUpdate = false): void
    {
        if ($isUpdate && $oldParentId !== null && $oldParentId != $newParentId) {
            $oldParent = CaricatureCategory::where('id', $oldParentId)
                ->where('parent_category_id', 0)
                ->where('child_cat_ids', 'like', '%' . $childId . '%')
                ->first();

            if ($oldParent) {
                $childIds = json_decode($oldParent->child_cat_ids, true) ?? [];

                if (($key = array_search($childId, $childIds)) !== false) {
                    unset($childIds[$key]);
                    $oldParent->child_cat_ids = json_encode(array_values($childIds)); // Reindex
                    $oldParent->save();
                }
            }
        }

        if (!empty($newParentId) && $newParentId != 0) {
            $newParent = CaricatureCategory::find($newParentId);
            if ($newParent) {
                $childIds = json_decode($newParent->child_cat_ids, true) ?? [];
                if (!in_array($childId, $childIds)) {
                    $childIds[] = $childId;
                    $newParent->child_cat_ids = json_encode($childIds);
                    $newParent->save();
                }
            }
        }
    }

    public function getCaricatureCategory(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);

            $categories = CaricatureCategory::where('deleted', 0)
                ->select([
                    'id',
                    'category_name',
                    'parent_category_id',
                    'h1_tag',
                    'category_thumb',
                    'id_name',
                    'sequence_number',
                    'status',
                    'no_index'
                ])
                ->with(['parentCategory' => function($query) {
                    $query->select('id', 'category_name');
                }])
                ->paginate($perPage, ['*'], 'page', $page);

            // Transform data to include parent category name
            $transformedCategories = $categories->getCollection()->map(function($category) {
                return [
                    'id' => $category->id,
                    'category_name' => $category->category_name,
                    'parent_category_name' => $category->parentCategory ? $category->parentCategory->category_name : null,
                    'h1_tag' => $category->h1_tag,
                    'category_thumb' => HelperController::generatePublicUrl($category->category_thumb),
                    'id_name' => $category->id_name,
                    'sequence_number' => $category->sequence_number,
                    'status' => $category->status,
                    'no_index' => $category->no_index
                ];
            });

            $paginationData = [
                'data' => $transformedCategories,
                'current_page' => $categories->currentPage(),
                'per_page' => $categories->perPage(),
                'prev_page_url' => $categories->previousPageUrl(),
                'total' => $categories->total(),
            ];

            return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Categories retrieved successfully", $paginationData));

        } catch (\Exception $e) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(500, false, $e->getMessage()));
        }
    }

    public function getCaricatureCategoryById(Request $request, $id): array|string
    {
        try {
            $category = CaricatureCategory::where('id', $id)
                ->where('deleted', 0)
                ->with(['parentCategory' => function($query) {
                    $query->select('id', 'category_name');
                }])
                ->first();

            if (!$category) {
                return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, "Category not found"));
            }

            $categoryData = [
                'id' => $category->id,
                'category_name' => $category->category_name,
                'parent_category_id' => $category->parent_category_id,
                'parent_category_name' => $category->parentCategory ? $category->parentCategory->category_name : null,
                'id_name' => $category->id_name,
                'primary_keyword' => $category->primary_keyword,
                'tag_line' => $category->tag_line,
                'meta_title' => $category->meta_title,
                'h1_tag' => $category->h1_tag,
                'h2_tag' => $category->h2_tag,
                'meta_desc' => $category->meta_desc,
                'short_desc' => $category->short_desc,
                'long_desc' => $category->long_desc,
                'category_thumb' => HelperController::generatePublicUrl($category->category_thumb),
                'banner' => HelperController::generatePublicUrl($category->banner),
                'mockup' => HelperController::generatePublicUrl($category->mockup),
                'sequence_number' => $category->sequence_number,
                'status' => $category->status,
                'no_index' => $category->no_index,
                'canonical_link' => $category->canonical_link,
                'contents' => (isset($category->contents)) ? ContentManager::getContentsPath(json_decode(StorageUtils::get($category->contents)), $this->uid) : [],
                'faqs' => $category->faqs ? json_decode(StorageUtils::get($category->faqs)) : null,
                'top_keywords' => $category->top_keywords ? json_decode($category->top_keywords, true) : [],
                'created_at' => $category->created_at,
                'updated_at' => $category->updated_at
            ];

            return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Category retrieved successfully", $categoryData));

        } catch (\Exception $e) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(500, false, "Failed to retrieve category"));
        }
    }

    public function changeStatusCaricatureCategory(Request $request): array|string
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists',
            'field' => 'required|string|in:status,no_index'
        ], [
            'id.required' => 'Category ID is required',
            'field.required' => 'Field name is required',
            'field.in' => 'Field must be either status or no_index'
        ]);

        if ($validator->fails()) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(422, false, $validator->errors()->first()));
        }

        try {
            $category = CaricatureCategory::find($request->id);

            if (!$category) {
                return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, "Category not found"));
            }

            $field = $request->field;
            $currentValue = $category->$field;
            $newValue = $currentValue == 1 ? 0 : 1;

            $category->$field = $newValue;
            $category->save();

            $fieldName = str_replace('_', ' ', $field);
            $fieldName = ucwords($fieldName);

            return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "$fieldName updated successfully", [
                'id' => $category->id,
                'field' => $field,
                'new_value' => $newValue
            ]));

        } catch (\Exception $e) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(500, false, "Failed to update category status"));
        }
    }

    public function attireAdd(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_name' => 'required|string|max:255',
            'preview_url' => 'required|string',
            'attire_url' => 'required|string',
            'thumbnail_url' => 'required|string',
            'coordinate_image' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(422, false, $validator->errors()->first()));
        }

        // Validate images using FormData validation
        $imageFields = [
            ['img' => $request->preview_url, 'name' => "Preview Image", 'required' => true],
            ['img' => $request->attire_url, 'name' => "Attire Image", 'required' => true],
            ['img' => $request->thumbnail_url, 'name' => "Watermark Image", 'required' => true],
            ['img' => $request->coordinate_image, 'name' => "Coordinate Image", 'required' => true]
        ];

        $validationError = ContentManager::validateBase64Images($imageFields);
        if ($validationError) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, $validationError));
        }

        try {
            $res = new Attire();
            $res->post_name = $request->post_name;
            $res->string_id = HelperController::generateId();
            $slug = preg_replace('/[^a-z0-9\-]/', '', strtolower(str_replace(' ', '-', $res->post_name)));
            $res->id_name = $res->string_id . '-' . $slug;
            $fldrStr = HelperController::generateID('', 10);
            while (Attire::where('fldr_str', $fldrStr)->exists()) {
                $fldrStr = HelperController::generateID('', 10);
            }
            $res->fldr_str = $fldrStr;

            $fldrStr = HelperController::generateID('', 10);
            while (Attire::where('fldr_str', $fldrStr)->exists()) {
                $fldrStr = HelperController::generateID('', 10);
            }
            $res->fldr_str = $fldrStr;

            $basePath = 'caricature/attire/' . $fldrStr;

            $res->preview_url = ContentManager::saveImageToPath($request->preview_url, 'caricature/attire/'.$fldrStr."/thumb_file/" . bin2hex(random_bytes(20)) . Carbon::now()->timestamp);
            $res->attire_url = ContentManager::saveImageToPath($request->attire_url, 'caricature/attire/'.$fldrStr."/thumb_file/" . bin2hex(random_bytes(20)) . Carbon::now()->timestamp);
            $res->thumbnail_url = ContentManager::saveImageToPath($request->thumbnail_url, 'caricature/attire/'.$fldrStr."/thumb_file/" . bin2hex(random_bytes(20)) . Carbon::now()->timestamp);
            $res->coordinate_image = ContentManager::saveImageToPath($request->coordinate_image, 'caricature/attire/'.$fldrStr."/thumb_file/" . bin2hex(random_bytes(20)) . Carbon::now()->timestamp);

            $res->json = json_encode($request->json());
            $res->head_count = $request->head_count;

            $res->save();

            return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, 'Attire added successfully'));

        } catch (\Exception $e) {
//            \Log::error("Attire add error: " . $e->getMessage());
            return ResponseHandler::sendResponse($request, new ResponseInterface(500, false, $e->getMessage()));
        }
    }

    public function updateSeoAttire(Request $request): array|string
    {
        // Add validation rules
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'id_name' => 'required|string|max:255',
            'category_id' => 'required|integer',
            'post_name' => 'required|string|max:255',
            'meta_title' => 'required|string|max:60',
            'h2_tag' => 'nullable|string|max:255',
            'long_desc' => 'nullable|string',
            'meta_description' => 'nullable|string|max:160',
            'status' => 'required|integer|in:0,1',
            'is_premium' => 'required|integer|in:0,1',
            'is_freemium' => 'required|integer|in:0,1',
            'no_index' => 'required|integer|in:0,1',
            'canonical_link' => 'nullable|url',
            'contents' => 'nullable|json',
            'faqs_title' => 'required_if:faqs,!=,null|string|max:255',
            'faqs' => 'nullable|json',
        ], [
            'id.required' => 'Attire ID is required',
            'id.exists' => 'Attire not found',
            'id_name.required' => 'ID name is required',
            'category_id.required' => 'Category is required',
            'category_id.exists' => 'Selected category does not exist',
            'post_name.required' => 'Post name is required',
            'meta_title.required' => 'Meta title is required',
            'meta_title.max' => 'Meta title must not exceed 60 characters',
            'meta_description.max' => 'Meta description must not exceed 160 characters',
            'status.required' => 'Status is required',
            'is_premium.required' => 'Premium status is required',
            'is_freemium.required' => 'Freemium status is required',
            'no_index.required' => 'No-index status is required',
            'faqs_title.required_if' => 'FAQ title is required when FAQs are provided',
        ]);

        if ($validator->fails()) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(422, false, $validator->errors()));
        }

        $attire = Attire::find($request->id);
        if (!$attire) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, "Attire Data not Found"));
        }

        $id_name = $request->input('id_name');
        if (!$id_name) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, "Id name not Found"));
        }

        $id_name = str_replace($attire->string_id . '-', '', $id_name);
        $idNameError = "";
        if (preg_match('/\s/', $id_name))
            $idNameError = 'ID Name should not contain whitespace.';
        if (preg_match('/[A-Z]/', $id_name))
            $idNameError = 'ID Name should not contain capital letters.';
        if (preg_match('/[^\w\s-]/', $id_name))
            $idNameError = 'ID Name should not contain special characters.';

        if ($idNameError) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, $idNameError));
        }

        // Check if ID name already exists for other attires
        $fullIdName = $attire->string_id . '-' . $id_name;
        $existingAttire = Attire::where('id_name', $fullIdName)
            ->where('id', '!=', $request->id)
            ->first();

        if ($existingAttire) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, "ID name already exists for another attire."));
        }

        // Validate content
        $contentError = ContentManager::validateContent($request->contents, $request->long_desc, $request->h2_tag);
        if ($contentError) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, $contentError));
        }

        // Validate base64 images in content
        $base64Images = [...ContentManager::getBase64Contents($request->contents)];
        $validationError = ContentManager::validateBase64Images($base64Images);
        if ($validationError) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, $validationError));
        }

        // Validate FAQs
        if (isset($request->faqs) && str_replace(['[', ']'], '', $request->faqs) != '') {
            if (!isset($request->faqs_title)) {
                return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, "Please Add Faq Title"));
            }

            // Validate FAQ JSON structure
            $faqs = json_decode($request->faqs, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, "Invalid FAQ JSON format"));
            }

            if (!is_array($faqs)) {
                return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, "FAQs must be a valid JSON array"));
            }
        }

        // Validate canonical link
        $canonical_link = $request->input('canonical_link');
        $canonicalError = "";
        if (isset($canonical_link)) {
            if (!str_starts_with($canonical_link, HelperController::$frontendUrl)) {
                $canonicalError = "Canonical link must be start with " . HelperController::$frontendUrl;
            }
        }
        if ($canonicalError) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, $canonicalError));
        }

        // Validate category
        $cat = CaricatureCategory::find($request->input('category_id'));
        if ($cat && $cat->parent_category_id == 0) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, 'Use Child Category.'));
        }

        // Validate post name and meta title
        if (strcasecmp($request->input('post_name'), $request->input('meta_title')) === 0) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, "Post Name and Meta Title should not be the same."));
        }

        // Validate no_index requirements
        if ($request->input('no_index') == 0) {
            // If indexed, all SEO fields are required
            if (empty($request->input('h2_tag'))) {
                return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, 'H2 tag is required for indexed pages'));
            }
            if (empty($request->input('long_desc'))) {
                return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, 'Description is required for indexed pages'));
            }
            if (empty($request->input('meta_description'))) {
                return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, 'Meta description is required for indexed pages'));
            }
        } else {
            // If no_index, either all SEO fields should be empty or all should be filled
            $hasH2 = !empty($request->input('h2_tag'));
            $hasDescription = !empty($request->input('long_desc'));
            $hasMetaDescription = !empty($request->input('meta_description'));

            // If any one field is filled, all must be filled
            if ($hasH2 || $hasDescription || $hasMetaDescription) {
                if (!$hasH2) {
                    return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, "H2 tag is required when other SEO fields are filled"));
                }
                if (!$hasDescription) {
                    return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, "Description is required when other SEO fields are filled"));
                }
                if (!$hasMetaDescription) {
                    return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, "Meta description is required when other SEO fields are filled"));
                }
            }
        }

//        // Validate keywords format
//        $keywords = $request->input('keywords');
//        if ($keywords) {
//            $keywordArray = explode(',', $keywords);
//            if (count($keywordArray) > 10) {
//                return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, "Maximum 10 keywords allowed"));
//            }
//
//            foreach ($keywordArray as $keyword) {
//                if (strlen(trim($keyword)) > 50) {
//                    return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, "Each keyword must be less than 50 characters"));
//                }
//            }
//        }

        // Update the attire record
        try {
            $attire->id_name = $fullIdName;
            $attire->canonical_link = $canonical_link;
            $attire->category_id = $request->input('category_id');
            $attire->post_name = $request->input('post_name');
            $attire->meta_title = $request->input('meta_title');
            $attire->h2_tag = $request->input('h2_tag');
            $attire->long_desc = $request->input('long_desc');
            $attire->meta_description = $request->input('meta_description');

            // Handle keywords as array
            $keywordsArray = $request->input('keywords', []);
            $attire->related_tags = !empty($keywordsArray) ? json_encode($keywordsArray) : null;

            $religionIds = $request->input('religion_id', []);
            $attire->religion_id = !empty($religionIds) ? json_encode($religionIds) : null;

            $themeIds = $request->input('theme_id', []);
            $attire->theme_id = !empty($themeIds) ? json_encode($themeIds) : null;

//            $keywordsArray = $keywords ? explode(',', $keywords) : [];
//            $attire->related_tags = json_encode($keywordsArray);
//            $attire->religion_id = $request->input('religion_id');
//            $attire->theme_id = $request->input('theme_id');
            $attire->status = $request->input('status');
            $attire->is_premium = $request->input('is_premium');
            $attire->is_freemium = $request->input('is_freemium');
            $attire->no_index = $request->input('no_index');

            // Handle contents
            if ($request->input('contents')) {
                $contents = ContentManager::getContents($request->input('contents'), $attire->fldr_str);
                $contentPath = 'caricature/attire/' . $attire->fldr_str . '/jn/' . StorageUtils::getNewName() . ".json";
                StorageUtils::put($contentPath, $contents);
                $attire->contents = $contentPath;
            }

            // Handle FAQs
            if (isset($request->faqs)) {
                $faqPath = 'caricature/attire/' . $attire->fldr_str . '/fq/' . StorageUtils::getNewName() . ".json";
                $faqs = [];
                $faqs['title'] = $request->faqs_title;
                $faqs['faqs'] = json_decode($request->faqs);
                StorageUtils::put($faqPath, json_encode($faqs));
                $attire->faqs = $faqPath;
            }

            $attire->save();

            return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "SEO updated successfully"));

        } catch (\Exception $e) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(500, false, $e->getMessage()));
        }
    }

    public function getAttireData(Request $request): array|string
    {
        try {
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);

            $attires = Attire::select([
                'id',
                'post_name',
                'id_name',
                'meta_title',
                'preview_url',
                'category_id',
                'status',
                'is_premium',
                'is_freemium',
                'no_index',
                'head_count',
                'created_at',
                'updated_at'
            ])
                ->with(['category' => function($query) {
                    $query->select('id', 'category_name');
                }])
                ->orderBy('id', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            // Transform data to include category name
            $transformedAttires = $attires->getCollection()->map(function($attire) {
                return [
                    'id' => $attire->id,
                    'post_name' => $attire->post_name,
                    'id_name' => $attire->id_name,
                    'meta_title' => $attire->meta_title,
                    'preview_url' => HelperController::generatePublicUrl($attire->preview_url),
                    'category_id' => $attire->category_id,
                    'category_name' => $attire->category ? $attire->category->category_name : null,
                    'status' => $attire->status,
                    'is_premium' => $attire->is_premium,
                    'is_freemium' => $attire->is_freemium,
                    'no_index' => $attire->no_index,
                    'head_count' => $attire->head_count,
                    'created_at' => $attire->created_at,
                    'updated_at' => $attire->updated_at
                ];
            });

            $paginationData = [
                'data' => $transformedAttires,
                'current_page' => $attires->currentPage(),
                'per_page' => $attires->perPage(),
                'prev_page_url' => $attires->previousPageUrl(),
                'total' => $attires->total(),
            ];

            return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Attires retrieved successfully", $paginationData));

        } catch (\Exception $e) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(500, false, "Failed to retrieve attires"));
        }
    }

    public function getAttireDataById(Request $request, $id)
    {
        try {
            $attire = Attire::with(['category' => function($query) {
                $query->select('id', 'category_name');
            }])
                ->find($id);

            if (!$attire) {
                return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, "Attire not found"));
            }

            $attireData = [
                'id' => $attire->id,
                'string_id' => $attire->string_id,
                'post_name' => $attire->post_name,
                'id_name' => $attire->id_name,
                'meta_title' => $attire->meta_title,
                'meta_description' => $attire->meta_description,
                'h2_tag' => $attire->h2_tag,
                'long_desc' => $attire->long_desc,
                'description' => $attire->description,
                'preview_url' => HelperController::generatePublicUrl($attire->preview_url),
                'attire_url' => HelperController::generatePublicUrl($attire->attire_url),
                'thumbnail_url' => HelperController::generatePublicUrl($attire->thumbnail_url),
                'coordinate_image' => HelperController::generatePublicUrl($attire->coordinate_image),
                'category_id' => $attire->category_id,
                'category_name' => $attire->category ? $attire->category->category_name : null,
                'status' => $attire->status,
                'is_premium' => $attire->is_premium,
                'is_freemium' => $attire->is_freemium,
                'no_index' => $attire->no_index,
                'head_count' => $attire->head_count,
                'religion_id' => $attire->religion_id ? json_decode($attire->religion_id, true) : [],
                'theme_id' => $attire->theme_id ? json_decode($attire->theme_id, true) : [],
                'related_tags' => $attire->related_tags ? json_decode($attire->related_tags, true) : [],
                'json' => $attire->json ? json_decode($attire->json, true) : null,
                'contents' => (isset($attire->contents)) ? ContentManager::getContentsPath(json_decode(StorageUtils::get($attire->contents)), $this->uid) : [],
                'faqs' => $attire->faqs ? json_decode(StorageUtils::get($attire->faqs)) : null,
                'canonical_link' => $attire->canonical_link,
                'fldr_str' => $attire->fldr_str,
                'created_at' => $attire->created_at,
                'updated_at' => $attire->updated_at
            ];

            return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Attire retrieved successfully", $attireData));

        } catch (\Exception $e) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(500, false, "Failed to retrieve attire"));
        }
    }

    public function changeStatusAttire(Request $request): array|string
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'field' => 'required|string|in:status,no_index,is_premium,is_freemium,is_deleted'
        ], [
            'id.required' => 'Attire ID is required',
            'field.required' => 'Field name is required',
            'field.in' => 'Field must be status, no_index, is_premium, is_freemium, or is_deleted'
        ]);

        if ($validator->fails()) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(422, false, $validator->errors()->first()));
        }

        try {
            $attire = Attire::find($request->id);

            if (!$attire) {
                return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, "Attire not found"));
            }

            $field = $request->field;
            $currentValue = $attire->$field;
            $newValue = $currentValue == 1 ? 0 : 1;

            $attire->$field = $newValue;
            $attire->save();

            $fieldName = str_replace('_', ' ', $field);
            $fieldName = ucwords($fieldName);

            return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "$fieldName updated successfully", [
                'id' => $attire->id,
                'field' => $field,
                'new_value' => $newValue
            ]));

        } catch (\Exception $e) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(500, false, "Failed to update attire status"));
        }
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

        $checkParentCat = CaricatureCategory::where('id_name', $forCheckCategory)->first();
        if ($checkParentCat) {
            $checkCat = CaricatureCategory::where('id_name', $category)->where('parent_category_id', $checkParentCat->id)->first();
        } else {
            $checkCat = CaricatureCategory::where('id_name', $category)->first();
        }

        if (!$checkCat->checkIsLive()) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, "Data not found", ["redirectUrl" => $redirectUrl]));
        }

//        if (
//            ($forCheckCategory == "templates" && $checkCat->parent_category_id != 0) ||
//            ($forCheckCategory !== "templates" && (!($parentCat = NewCategory::find($checkCat->parent_category_id)) || $forCheckCategory !== $parentCat->id_name))
//        ) {
//            return $this->getCategoryPostersForWeb($request, $forCheckCategory, $category, $page, $limit);
//            // return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Datas are incorrect"));
//        }

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




















    public function saveMultipleImages(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'images' => 'required|array|min:1',
            'images.*' => 'required|file',
            'path_name' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $savedPaths = [];
        $errors = [];

        // First pass: validate all images
        foreach ($request->file('images') as $index => $image) {
            $validationResult = $this->validateImage($image);
            if ($validationResult !== true) {
                $errors["image_$index"] = $validationResult;
            }
        }

        if (!empty($errors)) {
            return response()->json([
                'success' => false,
                'message' => 'Image validation failed',
                'errors' => $errors
            ], 422);
        }

        // Second pass: save all images
        try {
            foreach ($request->file('images') as $index => $image) {
                $savedPath = $this->saveImage($image, $request->path_name, $index);
                $savedPaths[] = $savedPath;
            }

            return response()->json([
                'success' => true,
                'message' => 'Images saved successfully',
                'data' => [
                    'saved_paths' => $savedPaths,
                    'total_saved' => count($savedPaths)
                ]
            ]);

        } catch (\Exception $e) {
            $this->rollbackSavedImages($savedPaths);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save images',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function validateImage($image)
    {
        // Get both extension and MIME type for better validation
        $extension = strtolower($image->getClientOriginalExtension());
        $mimeType = strtolower($image->getMimeType());
        $imageSize = $image->getSize();

        // Define valid types with both extensions and MIME types
        $validExtensions = ['jpg', 'jpeg', 'svg', 'webp', 'gif', 'png'];
        $validMimeTypes = [
            'image/jpeg',
            'image/jpg',
            'image/svg+xml',
            'image/svg',
            'image/webp',
            'image/gif',
            'image/png'
        ];

        // Check if extension is valid
        if (!in_array($extension, $validExtensions)) {
            return 'All images must be jpg, jpeg, png, svg, webp, or gif files. Invalid extension: ' . $extension;
        }

        // Check if MIME type is valid
        if (!in_array($mimeType, $validMimeTypes)) {
            return 'All images must be jpg, jpeg, png, svg, webp, or gif files. Invalid MIME type: ' . $mimeType;
        }

        // Normalize image type for size validation
        $imageType = $extension;
        if ($mimeType === 'image/svg+xml' || $mimeType === 'image/svg') {
            $imageType = 'svg';
        } elseif ($mimeType === 'image/jpeg' || $mimeType === 'image/jpg') {
            $imageType = 'jpg';
        }

        // Size validation
        if ($imageType == 'webp' && $imageSize > 200 * 1024) {
            return 'Webp images must be less than 200KB.';
        }

        if ($imageType == 'gif' && $imageSize > 300 * 1024) {
            return 'GIF images must be less than 300KB.';
        }

        if (in_array($imageType, ['jpg', 'jpeg', 'svg', 'png']) && $imageSize > 50 * 1024) {
            return 'Jpg, jpeg, png, and svg images must be less than 50KB.';
        }

        return true;
    }

    private function saveImage($image, $pathName, $index)
    {
        $extension = strtolower($image->getClientOriginalExtension());
        $mimeType = strtolower($image->getMimeType());
        $imageData = file_get_contents($image->getRealPath());

        // Handle SVG MIME type for proper extension
        $fileExtension = $extension;
        if ($mimeType === 'image/svg+xml' && $extension !== 'svg') {
            $fileExtension = 'svg';
        }

        $fileName = 'image_' . $index . '_' . time() . '_' . Str::random(10) . '.' . $fileExtension;
        $filePath = $pathName . '/' . $fileName;

        StorageUtils::put($filePath, $imageData);

        return $filePath;
    }

    public function deleteMultipleImages(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image_paths' => 'required|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $deletedPaths = [];
        foreach ($request->image_paths as $path) {
            if (StorageUtils::exists($path)) {
                StorageUtils::delete($path);
                $deletedPaths[] = $path;
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Images deleted successfully',
            'data' => [
                'deleted_paths' => $deletedPaths
            ]
        ]);
    }

    private function rollbackSavedImages(array $savedPaths)
    {
        foreach ($savedPaths as $path) {
            try {
                if (StorageUtils::exists($path)) {
                    StorageUtils::delete($path);
                }
            } catch (\Exception $e) {
                // Log error if needed
            }
        }
    }

    public function saveJson(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'json_data' => 'required|json',
            'folder_name' => 'required|string|max:255',
            'file_name' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $filePath = $request->folder_name . '/' . $request->file_name;
            StorageUtils::put($filePath, $request->json_data);

            return response()->json([
                'success' => true,
                'message' => 'JSON saved successfully',
                'data' => [
                    'file_path' => $filePath
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save JSON',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteMultipleJson(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file_paths' => 'required|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $deletedPaths = [];
        foreach ($request->file_paths as $path) {
            if (StorageUtils::exists($path)) {
                StorageUtils::delete($path);
                $deletedPaths[] = $path;
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'JSON files deleted successfully',
            'data' => [
                'deleted_paths' => $deletedPaths
            ]
        ]);
    }
}