<?php

namespace App\Http\Middleware;

use App\Helpers\Transformer;
use Closure;

class Guest
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
        if ($request->user()) {
            return Transformer::fail('This route is only for unauthenticated users.', null, 403);
        }

        return $next($request);
    }
}
