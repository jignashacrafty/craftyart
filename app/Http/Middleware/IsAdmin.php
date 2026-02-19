<?php
 
namespace App\Http\Middleware;
 
use Closure;
 
class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Check if user is authenticated
        if (!$request->user()) {
            return redirect()->route('login')->with('error', 'Please login to access this page.');
        }
        
        // Check if user is admin
        if ($request->user()->user_type == 1) {
            return $next($request);   
        }
        
        return abort(404);
    }
}