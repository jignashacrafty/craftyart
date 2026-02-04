<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use function PHPUnit\Framework\isEmpty;

class PaginationController extends Controller
{

    public static function getPagination($data, array $filter = []): array
    {
        $data->withPath('');
        $currentPage = $data->currentPage();
        $keyMappings = [
            'is_premium' => 'license',
            'tags' => 'query',
        ];
        foreach ($keyMappings as $oldKey => $newKey) {
            if (array_key_exists($oldKey, $filter)) {
                $filter[$newKey] = $filter[$oldKey];
                unset($filter[$oldKey]);
            }
        }

        $filter = collect($filter)->except('page')->toArray();

        $pagination['next_url'] = $data->hasMorePages() ?  self::buildQueryUri($filter, $currentPage + 1) : null;
        $pagination['prev_url'] = $currentPage > 1 ? self::buildQueryUri($filter, $currentPage - 1) : null;

        $pagination['seo_next_url'] = $data->hasMorePages() && empty($filter) ? $pagination['next_url'] : null;
        $pagination['seo_prev_url'] = $currentPage > 1 && empty($filter) ? $pagination['prev_url'] : null;
        $pagination['links'] = PaginationController::paginate($currentPage, $data->lastPage(), 1, $filter);

        return $pagination;
    }

    public static function paginate($currentPage, $totalPages, $visiblePagesAround = 1, array $filter = []): array
    {
        if (is_array($visiblePagesAround)) {
            $queryParams = $visiblePagesAround;
            $visiblePagesAround = 1; // default value
        }

        $pages = [];
//        $baseQuery = http_build_query(collect($queryParams)->except('page')->toArray());

//        $buildUrl = function ($page) use ($baseQuery) {
//            $queryString = $baseQuery ? $baseQuery . '&page=' . $page : 'page=' . $page;
//            return '?' . $queryString;
//        };

        $pages[] = [
            "label" => 1,
            "url" => self::buildQueryUri($filter),
            "active" => $currentPage == 1
        ];

        $startPage = max(2, $currentPage - $visiblePagesAround);
        $endPage = min($totalPages - 1, $currentPage + $visiblePagesAround);

        for ($i = $startPage; $i <= $endPage; $i++) {
            $pages[] = [
                "label" => $i,
                "url" => self::buildQueryUri($filter, $i),
                "active" => $currentPage == $i
            ];
        }

        if ($totalPages > 1) {
            $pages[] = [
                "label" => $totalPages,
                "url" => self::buildQueryUri($filter, $totalPages),
                "active" => $currentPage == $totalPages
            ];
        }

        return $pages;
    }


    static function buildQueryUri(array $input, int $page = 1): string
    {
        $query = [];

        foreach ($input as $key => $value) {
            // Handle array-like strings: "['val1','val2']"
            if (preg_match("/^\[.*\]$/", $value)) {
                $items = json_decode(str_replace("'", '"', $value), true);
                if (is_array($items)) {
                    foreach ($items as $item) {
                        $query[] = urlencode($key) . '=' . urlencode($item);
                    }
                }
            } else {
                // Simple value
                $query[] = urlencode($key) . '=' . urlencode($value);
            }
        }
        if($page != 1) {
            $query[] = 'page=' . $page;
        }

        if(!empty($query)) {
            return '?' . implode('&', $query);
        }
        return "";
    }

}