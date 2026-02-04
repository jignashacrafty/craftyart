<?php

namespace App\Http\Controllers\Api;

use App\Models\Design;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\UserData;

class SearchApiController extends ApiController
{
    private array $extra = ["aboard", "about", "above", "across", "after", "against", "along", "amid", "amidst", "among", "amongst", "around, as, at", "before", "behind", "below", "beneath", "beside", "between", "beyond", "but, by", "concerning", "considering", "despite", "down", "during", "except", "for", "from, in", "inside", "into", "like", "near, of", "off, on", "onto", "out", "outside", "over", "past", "regarding", "round", "since", "through", "throughout", "till", "until, to", "toward", "towards", "under", "underneath", "unlike", "until", "unto, up", "upon", "with", "within", "without, am, is", "are", "was", "were", "been", "being", "have", "has", "had, do", "does", "did", "can", "could", "may", "might", "shall", "should", "will", "would", "must"];

    public function exactKeywordTemplates($keywords, $page, $limit, $excludeTemplate = null): array
    {

        $hasShowAll = false;

        $user_data = UserData::where("uid", $this->uid)->first();

        if ($user_data && ($user_data->can_update == 1 || $user_data->can_update == '1')) {
            $hasShowAll = true;
        }

        $status_condition = $hasShowAll ? "!=" : "=";
        $status = $hasShowAll ? "-1" : "1";

        $itemData = Design::where("string_id", "!=", $excludeTemplate)
            ->where('status', $status_condition, $status)
            ->whereJsonContains('related_tags', $keywords)
            ->orderByRaw('pinned DESC, web_views DESC, id DESC')
            ->limit($limit)
            ->get();

        $categoryIds = $itemData->pluck('category_id')->unique();
        $categories = Category::whereIn('id', $categoryIds)->get()->keyBy('id');

        $item_rows = [];

        foreach ($itemData as $item) {
            $catRow = $categories[$item->category_id] ?? null;
            if ($catRow != null) {
                $item_rows[] = HelperController::getItemData($this->uid, $catRow, $item, json_decode($item->thumb_array));
            }
        }

        return [
            //            'isLastPage' => count($item_rows) < $limit,
            'isLastPage' => true,
            'datas' => $item_rows,
        ];

    }

    public function searchTemplates($keywords, $page, $cat_id, $limit, $excludeIdName = "love", $ratio = null): array
    {

        $hasShowAll = false;

        $user_data = UserData::where("uid", $this->uid)->first();

        if ($user_data && ($user_data->can_update == 1 || $user_data->can_update == '1')) {
            $hasShowAll = true;
        }

        $status_condition = "=";
        $status = "1";
        if ($hasShowAll) {
            $status_condition = "!=";
            $status = "-1";
        }

        $ratioCondition = "!=";
        if ($ratio) {
            $ratio = strval($ratio);
            $ratioCondition = "=";
        } else {
            $ratio = strval(-1);
        }

        $item_rows = array();
        $keywords = str_replace('-', ' ', $keywords);
        $description = str_replace(',', ' ', $keywords);
        $desc_array = explode(' ', $description);

        $fieldName = is_numeric($cat_id) ? "id" : "id_name";

        $newCond = $fieldName == "id_name" ? null : -1;

        $itemData = null;

        if ($cat_id != null && $cat_id != $newCond) {
            $catRow = Category::where($fieldName, $cat_id)->first();
            if ($catRow != null) {

                $sql = "SELECT *,  CASE ";

                $sql .= "WHEN related_tags LIKE '%\"" . $keywords . "\"%' THEN " . (count($desc_array) + 2) . " ";
                $sql .= "WHEN related_tags LIKE '%\"" . implode(' ', array_reverse($desc_array)) . "\"%' THEN " . (count($desc_array) + 1) . " ";

                foreach ($desc_array as $index => $keyword) {
                    $sql .= "WHEN related_tags LIKE '%\"" . $keyword . "\"%' THEN " . (count($desc_array) - $index) . " ";
                }

                foreach ($desc_array as $index => $keyword) {
                    $sql .= "WHEN related_tags LIKE '% " . $keyword . " %' THEN " . (count($desc_array) - $index) . " ";
                    $sql .= "WHEN related_tags LIKE '% " . $keyword . "%' THEN " . (count($desc_array) - $index) . " ";
                    $sql .= "WHEN related_tags LIKE '%" . $keyword . " %' THEN " . (count($desc_array) - $index) . " ";
                }

                $sql .= "ELSE 0 END AS keyword_match_priority
                            FROM designs
                            WHERE category_id = " . $catRow->id . "
                            AND id_name != '" . $excludeIdName . "'
                            AND ratio " . $ratioCondition . " " . $ratio . "
                            AND status " . $status_condition . " " . $status . "
                            AND (
                                related_tags LIKE '%\"" . $keywords . "\"%' OR related_tags LIKE '%\"" . implode(' ', array_reverse($desc_array)) . "\"%' ";

                foreach ($desc_array as $keyword) {
                    $sql .= "OR related_tags LIKE '%\"" . $keyword . "\"%' ";
                }

                foreach ($desc_array as $keyword) {
                    $sql .= "OR related_tags LIKE '% " . $keyword . " %' ";
                    $sql .= "OR related_tags LIKE '% " . $keyword . "%' ";
                    $sql .= "OR related_tags LIKE '%" . $keyword . " %' ";
                }

                $sql .= ") ORDER BY keyword_match_priority DESC, created_at DESC";

                $res = DB::select($sql);

                $itemData = $this->limitArray($res, $limit, ($page - 1) * $limit);

            }

        } else {

            $sql = "SELECT *,  CASE ";

            $sql .= "WHEN related_tags LIKE '%\"" . $keywords . "\"%' THEN " . (count($desc_array) + 2) . " ";
            $sql .= "WHEN related_tags LIKE '%\"" . implode(' ', array_reverse($desc_array)) . "\"%' THEN " . (count($desc_array) + 1) . " ";

            foreach ($desc_array as $index => $keyword) {
                $sql .= "WHEN related_tags LIKE '%\"" . $keyword . "\"%' THEN " . (count($desc_array) - $index) . " ";
            }

            foreach ($desc_array as $index => $keyword) {
                $sql .= "WHEN related_tags LIKE '% " . $keyword . " %' THEN " . (count($desc_array) - $index) . " ";
                $sql .= "WHEN related_tags LIKE '% " . $keyword . "%' THEN " . (count($desc_array) - $index) . " ";
                $sql .= "WHEN related_tags LIKE '%" . $keyword . " %' THEN " . (count($desc_array) - $index) . " ";
            }

            $sql .= "ELSE 0 END AS keyword_match_priority
                        FROM designs
                        WHERE id_name != '" . $excludeIdName . "'
                        AND ratio " . $ratioCondition . " " . $ratio . "
                        AND status " . $status_condition . " " . $status . "
                        AND (
                            related_tags LIKE '%\"" . $keywords . "\"%' OR related_tags LIKE '%\"" . implode(' ', array_reverse($desc_array)) . "\"%' ";

            foreach ($desc_array as $keyword) {
                $sql .= "OR related_tags LIKE '%\"" . $keyword . "\"%' ";
            }

            foreach ($desc_array as $keyword) {
                $sql .= "OR related_tags LIKE '% " . $keyword . " %' ";
                $sql .= "OR related_tags LIKE '% " . $keyword . "%' ";
                $sql .= "OR related_tags LIKE '%" . $keyword . " %' ";
            }

            $sql .= ") ORDER BY keyword_match_priority DESC, created_at DESC";

            $res = DB::select($sql);

            $itemData = $this->limitArray($res, $limit, ($page - 1) * $limit);
        }
        if ($itemData != null) {
            foreach ($itemData as $item) {
                $catRow = Category::find($item->category_id);
                $item_rows[] = HelperController::getItemData($this->uid, $catRow, $item, json_decode($item->thumb_array));
            }
        }

        return [
            'isLastPage' => count($item_rows) < $limit,
            'datas' => $item_rows,
        ];

    }

    function searchReferTemplates(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $refWidth = $request->get('w');
        $refHeight = $request->get('h');
        $keywords = $request->get('keywords');
        $page = $request->has('page') ? $request->get('page') : 1;
        $limit = $request->has('limit') ? $request->get('limit') : 50;

        $item_rows = array();

        if ($keywords == null) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Parameters missing!"));
        }

        if (!$refWidth || !$refHeight) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Loaded", $this->searchTemplates($keywords, $page, $request->get('id'), $limit)));
        }

        $keywords = str_replace('-', ' ', $keywords);
        $description = str_replace(',', ' ', $keywords);
        $desc_array = explode(' ', $description);

        if (count($desc_array) > 1) {
            $desc_array = array_filter($desc_array, function ($word) {
                return !in_array($word, $this->extra);
            });
        }
        $tempRatio = $refWidth / $refHeight;
        $tempRatio = round($tempRatio, 2);

        $sql = "SELECT *,  CASE ";

        $sql .= "WHEN related_tags LIKE '%\"" . $keywords . "\"%' THEN " . (count($desc_array) + 2) . " ";
        $sql .= "WHEN related_tags LIKE '%\"" . implode(' ', array_reverse($desc_array)) . "\"%' THEN " . (count($desc_array) + 1) . " ";

        foreach ($desc_array as $index => $keyword) {
            $sql .= "WHEN related_tags LIKE '%\"" . $keyword . "\"%' THEN " . (count($desc_array) - $index) . " ";
        }
        foreach ($desc_array as $index => $keyword) {
            $sql .= "WHEN related_tags LIKE '% " . $keyword . " %' THEN " . (count($desc_array) - $index) . " ";
            $sql .= "WHEN related_tags LIKE '% " . $keyword . "%' THEN " . (count($desc_array) - $index) . " ";
            $sql .= "WHEN related_tags LIKE '%" . $keyword . " %' THEN " . (count($desc_array) - $index) . " ";
        }
        $sql .= "ELSE 0 END AS keyword_match_priority
                    FROM designs
                    WHERE ratio = " . $tempRatio . "
                    AND status=1
                    AND (
                        related_tags LIKE '%\"" . $keywords . "\"%' OR related_tags LIKE '%\"" . implode(' ', array_reverse($desc_array)) . "\"%' ";

        foreach ($desc_array as $keyword) {
            $sql .= "OR related_tags LIKE '%\"" . $keyword . "\"%' ";
        }
        foreach ($desc_array as $keyword) {
            $sql .= "OR related_tags LIKE '% " . $keyword . " %' ";
            $sql .= "OR related_tags LIKE '% " . $keyword . "%' ";
            $sql .= "OR related_tags LIKE '%" . $keyword . " %' ";
        }
        $sql .= ") ORDER BY keyword_match_priority DESC, created_at DESC";

        $res = DB::select($sql);

        $itemData = $this->limitArray($res, $limit, ($page - 1) * $limit);

        if ($itemData != null) {
            foreach ($itemData as $item) {
                $catRow = Category::find($item->category_id);
                $item_rows[] = HelperController::getItemData($this->uid, $catRow, $item, json_decode($item->thumb_array), false);
            }
        }

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Loaded", [
            'isLastPage' => count($item_rows) < $limit,
            'datas' => $item_rows
        ]));
    }

    function searchElements(Request $request): array|string
    {

        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $elementType = $request->get('element_type');
        $cat_id = $request->get('id');
        $keywords = $request->get('keywords');
        $page = $request->get('page');
        $hasShowAll = $request->has('showAll');

        $status_condition = "=";
        $status = "1";
        if ($hasShowAll) {
            $status_condition = "!=";
            $status = "-1";
        }

        if ($elementType == null) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Parameters missing!"));
        }

        $item_rows = array();

        if ($keywords == null) {
            return $item_rows;
        }
        $limit = 50;

        $description = str_replace(',', ' ', $keywords);
        $desc_array = explode(' ', $description);

        if ($elementType == 1) {
            $tableName = "bg_items";
            $catColumnId = "bg_cat_id";
            $catColumnName = "bg_name";
        } else if ($elementType == 2) {
            $tableName = "sticker_items";
            $catColumnId = "stk_cat_id";
            $catColumnName = "sticker_name";
        } else {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Incorrect Type!"));

        }
        if ($cat_id != null && $cat_id > 0) {

            $totalCount = DB::table($tableName)
                ->where("status", $status_condition, $status)
                ->where($catColumnId, $cat_id)
                ->where(function ($query) use ($catColumnName, $desc_array, $keywords) {
                    $query->orWhere($catColumnName, 'like', '%' . $keywords . '%');
                    foreach ($desc_array as $condition) {
                        $query->orWhere($catColumnName, 'like', '%' . $condition . '%');
                    }
                })
                ->count();

            $total_pages = ceil($totalCount / $limit);

            $res = DB::table($tableName)
                ->where("status", $status_condition, $status)
                ->where($catColumnId, $cat_id)
                ->where(function ($query) use ($catColumnName, $desc_array, $keywords) {
                    $query->orWhere($catColumnName, 'like', '%' . $keywords . '%');
                    foreach ($desc_array as $condition) {
                        $query->orWhere($catColumnName, 'like', '%' . $condition . '%');
                    }
                })
                ->orderBy(
                    DB::raw(
                        "CASE
                                                            WHEN $catColumnName LIKE '%$keywords%' THEN 1
                                                            WHEN $catColumnName LIKE '$keywords%' THEN 2
                                                            WHEN $catColumnName LIKE '%$keywords' THEN 4
                                                            ELSE 3
                                                        END"
                    )
                )->get();

        } else {

            $totalCount = DB::table($tableName)
                ->where("status", $status_condition, $status)
                ->where(function ($query) use ($catColumnName, $desc_array, $keywords) {
                    $query->orWhere($catColumnName, 'like', '%' . $keywords . '%');
                    foreach ($desc_array as $condition) {
                        $query->orWhere($catColumnName, 'like', '%' . $condition . '%');
                    }
                })
                ->count();

            $total_pages = ceil($totalCount / $limit);

            $res = DB::table($tableName)
                ->where("status", $status_condition, $status)
                ->where(function ($query) use ($catColumnName, $desc_array, $keywords) {
                    $query->orWhere($catColumnName, 'like', '%' . $keywords . '%');
                    foreach ($desc_array as $condition) {
                        $query->orWhere($catColumnName, 'like', '%' . $condition . '%');
                    }
                })
                ->orderBy(
                    DB::raw(
                        "CASE
                                                            WHEN $catColumnName LIKE '%$keywords%' THEN 1
                                                            WHEN $catColumnName LIKE '$keywords%' THEN 2
                                                            WHEN $catColumnName LIKE '%$keywords' THEN 4
                                                            ELSE 3
                                                        END"
                    )
                )->get();
        }
        $itemData = $this->limitArray(json_decode($res), $limit, ($page - 1) * $limit);
        if ($itemData != null) {
            foreach ($itemData as $item) {
                if ($elementType == 1) {
                    $item_rows[] = array(
                        'category_id' => $item->bg_cat_id,
                        'id' => $item->id,
                        'name' => $item->bg_name,
                        'thumb' => HelperController::$mediaUrl . $item->bg_thumb,
                        'file' => HelperController::$mediaUrl . $item->bg_image,
                        'type' => $item->bg_type,
                        'width' => $item->width,
                        'height' => $item->height,
                        'latest' => 0,
                        'is_premium' => $item->is_premium,
                    );
                } else {
                    $item_rows[] = array(
                        'category_id' => $item->stk_cat_id,
                        'id' => $item->id,
                        'name' => $item->sticker_name,
                        'thumb' => HelperController::$mediaUrl . $item->sticker_thumb,
                        'file' => HelperController::$mediaUrl . $item->sticker_image,
                        'type' => $item->sticker_type,
                        'width' => $item->width,
                        'height' => $item->height,
                        'latest' => 0,
                        'is_premium' => $item->is_premium,
                    );
                }
            }
        }

        return ResponseHandler::sendResponse($request, new ResponseInterface(
            200,
            true,
            "",
            [
                'current_page' => $page,
                'isLastPage' => $page >= $total_pages,
                'datas' => $item_rows,
            ]
        ));

    }

    function limitArray($array, $limit, $offset = 0): array
    {
        $return = array();
        $end = ($limit + $offset);
        $count = 0;
        foreach ($array as $key => $val) {
            if ($count++ >= $offset) {
                $return[$key] = $val;
            }
            if ($count == $end)
                break;
        }
        return $return;
    }
}
