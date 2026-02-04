<?php

namespace App\Http\Controllers\Api;

use App\Models\PageSlugHistory;

class PageSlugHistoryController extends ApiController
{
    public static function get($type)
    {
        return PageSlugHistory::select('old_slug', 'new_slug')->where('type', $type)->get();
    }

    public static function findOne()
    {

    }

}
