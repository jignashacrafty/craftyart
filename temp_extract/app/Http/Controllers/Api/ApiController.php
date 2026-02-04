<?php

namespace App\Http\Controllers\Api;

use App\Helpers\JwtHelper;
use App\Http\Controllers\Controller;
use App\Models\UserData;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public array|null|string $authKey;
    public array|null|string $uid = "48vNvOa7qYPZwRTnS4sqkEdUTGv1";
    public string $testingUid = "YTC1UOvR05hSKSkJSXFnb6LUFAi1";
    public string $aesPassword = 'E@7r1K7!6v#KZx^m';
    public bool $isTester = false;
    public Request $request;

    public function __construct(Request $request)
    {
       $this->request = $request;
    }

    public function isFakeRequest(Request $request): bool
    {
        return false;
    }

//    public function isFakeRequestAndUser(Request $request): bool
//    {
//        return false;
//    }

    public function isFakeRequestAndUser(Request $request): bool
    {
        $token = $request->get('vgs');
        if (!$token) return true;

        try {
            $decoded = JwtHelper::decode($token);

            // 🔁 Auto regenerate if expiring in 1 day
            if ($decoded->exp - time() < 86400) {
                $newToken = JwtHelper::generate((array) $decoded, 30);
                cookie()->queue(cookie('jwt_token', $newToken, 60 * 24 * 30));
            }

            $user = UserData::whereUid($decoded->uid)->first();

            if(!$user) return true;

            $this->uid = $user->uid;
            return false;
        } catch (\Exception $e) {
            return true;
        }
    }

    public function isFakeRequestAndCreator(Request $request): bool
    {
        return false;
    }

    public function successed(string $msg = "Loaded!!", array $datas = [], bool $showDecoded = false): array|string
    {
        return ResponseHandler::sendResponse($this->request, new ResponseInterface(200, true, $msg, $datas), $showDecoded);
    }

    public function failed(int $statusCode = 401, string $msg = "Something went wrong", array $datas = [], bool $showDecoded = false): array|string
    {
        return ResponseHandler::sendResponse($this->request, new ResponseInterface($statusCode, false, $msg, $datas), $showDecoded);
    }

    public static function findIp(Request $request): string
    {
        // Check for IP in common proxy headers
        $ip = $request->header('CF-Connecting-IP') // Cloudflare
            ?? $request->header('X-Forwarded-For') // Proxy
            ?? $request->header('X-Real-IP')
            ?? $request->ip(); // Fallback to Laravel's default

        // X-Forwarded-For may contain multiple IPs — use the first valid one
        if (strpos($ip, ',') !== false) {
            $ip = explode(',', $ip)[0];
        }

        return trim($ip);
    }

}