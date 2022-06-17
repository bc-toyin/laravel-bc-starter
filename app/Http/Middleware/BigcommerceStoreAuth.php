<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\StoreInfo;

class BigcommerceStoreAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $storeHash = 'stores/' . $request->route()->parameter('storeHash');
        $store = StoreInfo::where('store_hash', $storeHash)->first();

        if (!$store) {
            abort(404);
        }

        return $next($request);
    }
}
