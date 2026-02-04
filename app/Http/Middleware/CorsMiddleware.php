<?php
namespace App\Http\Middleware;

use Closure;

class CorsMiddleware
{
    public function handle($request, Closure $next)
    {
        $allowedOrigins = [
            'http://192.168.29.121','http://192.168.29.18'
        ];

        $origin = $request->headers->get('Origin');

        if (in_array($origin, $allowedOrigins)) {
            return $next($request)
                ->header('Access-Control-Allow-Origin', $origin)
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN')
                ->header('Access-Control-Allow-Credentials', 'true');
        }

        return $next($request);
    }
}
//
//namespace App\Http\Middleware;
//
//use Closure;
//use Illuminate\Http\Request;
//
//class CorsMiddleware
//{
//    /**
//     * Handle an incoming request.
//     *
//     * @param  \Illuminate\Http\Request  $request
//     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
//     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
//     */
//    public function handle(Request $request, Closure $next)
//    {
//        $headers = [
//            'Access-Control-Allow-Origin'      => '*',
//            'Access-Control-Allow-Methods'     => 'GET, POST, PUT, DELETE, OPTIONS',
//            'Access-Control-Allow-Credentials' => 'true',
//            'Access-Control-Allow-Headers'     => 'Authorization, Content-Type, X-Requested-With'
//        ];
//
//        if ($request->isMethod('OPTIONS'))
//        {
//            return response()->json('', 200, $headers);
//        }
//
//        $response = $next($request);
//        foreach($headers as $key => $value)
//        {
//            $response->header($key, $value);
//        }
//
//        return $response;
//    }
//}
