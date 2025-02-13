<?php

namespace App\Http\Middleware;

use Closure;
use App\Model\User;
use Illuminate\Support\Facades\Request;

class ProtectLoginMiddleware
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
        //if (session('user') == null) {
        //    session()->flush();
        //    return redirect('/login');
        //}
        return $next($request);
    }
}
