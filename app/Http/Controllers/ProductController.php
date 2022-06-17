<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\StoreInfo;

class ProductController extends Controller
{
    public function index(Request $request, $storeHash) {
        $page = $request->has('page') ? $request->get('page') : 1;
        $store = StoreInfo::where('store_hash', 'stores/' . $storeHash)->first();
        $products = $this->products($store, $page);
        $storeHash = 'stores/' . $storeHash;

        return view('product.index', compact('products', 'storeHash'));
    }

    public function edit(Request $request, $storeHash, $id) {
        $store = StoreInfo::where('store_hash', 'stores/' . $storeHash)->first();

        if (!$store) {
            abort(404);
        }

        try {
            $response = Http::withHeaders([
                            'Accept' => 'application/json',
                            'Content-Type' => 'application/json',
                            'X-Auth-Token' => $store->access_token
                        ])->get('https://api.bigcommerce.com/'. $store->store_hash .'/v3/catalog/products/' . $id);

            if ($response->ok()) {
                $json = $response->json();
                $product = $json['data'];
                $storeHash = 'stores/' . $storeHash;

                return view('product.edit', compact('product', 'storeHash'));
            } else {
                abort(404);
            }
        } catch (\Exception $e) {
            abort(404);
        }
    }

    public function update(Request $request, $storeHash, $id) {
        $store = StoreInfo::where('store_hash', 'stores/' . $storeHash)->first();

        if (!$store) {
            abort(404);
        }

        try {
            $response = Http::withHeaders([
                            'Accept' => 'application/json',
                            'Content-Type' => 'application/json',
                            'X-Auth-Token' => $store->access_token
                        ])->put('https://api.bigcommerce.com/'. $store->store_hash .'/v3/catalog/products/' . $id, [
                            'name' => $request->get('name'),
                            'sku' => $request->get('sku'),
                            'price' => $request->get('price')
                        ]);

            if ($response->ok()) {
                return back()->with('success', 'Successfully updated.');
            } else {
                abort(404);
            }
        } catch (\Exception $e) {
            abort(404);
        }
    }

    protected function products($store, $page) {
        try {
            $response = Http::withHeaders([
                            'Accept' => 'application/json',
                            'Content-Type' => 'application/json',
                            'X-Auth-Token' => $store->access_token
                        ])->get('https://api.bigcommerce.com/'. $store->store_hash .'/v3/catalog/products?page=' . $page . '&limit=20');

            if ($response->ok()) {
                $json = $response->json();

                return $json;
            } else {
                return [
                    'data' => [],
                    'meta' => [
                        'pagination' => []
                    ]
                ];
            }
        } catch (\Exception $e) {
            return [
                'data' => [],
                'meta' => [
                    'pagination' => []
                ]
            ];
        }
    }
}
