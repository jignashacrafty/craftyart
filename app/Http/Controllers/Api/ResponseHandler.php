<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ResponseHandler extends Controller
{

    public static function sendResponse(Request $request, ResponseInterface $response): array|string
    {
        if ($request->has("showDecoded")) {
            return $response->toArray();
        }
        // return $response->toArray();
        return json_encode(CryptoJsAes::encrypt(json_encode($response->toArray())));
    }

    public static function sendRealResponse(ResponseInterface $response): array
    {
        return $response->toArray();
    }

    public static function sendEncryptedResponse(Request $request, $array): array|string
    {
        if ($request->has("showDecoded")) {
            return $array;
        }
        return json_encode(CryptoJsAes::encrypt(json_encode($array)));
    }
}