<?php

namespace App\Http\Middleware;

use App\Helpers\Transformer;
use Closure;

class Verified
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
        if (is_null($request->user()->verified_at)) {
            return Transformer::fail('This route is only for verified users.', null, 403);
        }

        return $next($request);
    }
}
