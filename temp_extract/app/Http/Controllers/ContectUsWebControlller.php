<?php

namespace App\Http\Controllers;

use App\Models\ContactUsWeb;
use Illuminate\Http\Request;

class ContectUsWebControlller extends AppBaseController
{
    public function index(Request $request)
    {
        $searchableFields = [
            ["id" => 'id', "value" => 'id'],
            ["id" => 'name', "value" => 'name'],
            ["id" => 'email', "value" => 'Email'],
            ["id" => 'message', "value" => 'Message'],
            // ["id" => 'ip_address', "value" => 'Ip Address'],
            // ["id" => 'user_agent', "value" => 'User Agent'],
        ];
        $ContactUses = $this->applyFiltersAndPagination($request, ContactUsWeb::query(), $searchableFields);
        return view("contact_us_web.index", compact('ContactUses', 'searchableFields'));
    }

}
