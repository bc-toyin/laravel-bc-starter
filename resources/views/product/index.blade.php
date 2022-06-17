@extends('layouts.app')

@section('content')
    <a href="/{{ $storeHash }}" class="block ml-3 mb-3" style="color: #86848c">
        <i class="fa-solid fa-arrow-left mr-3"></i> Overview
    </a>

    <h1 class="text-3xl">Products</h1>

    <div class="bg-white shadow-md p-5 mt-8">
        <div class="mt-5 flex">
            <div class="mr-5"><a href="#" class="rounded bg-blue-50 py-2 px-4">All</a></div>
            <div><a href="#" class="rounded hover:bg-blue-50 py-2 px-4">Visible</a></div>

        </div>

        <div class="mt-5 flex justify-between items-center relative">
            <div class="absolute left-2">
                <i class="fa-solid fa-magnifying-glass" style="color: #86848c"></i>
            </div>

            <input
            type="search"
            class="border px-3 py-2 rounded w-full mr-3 pl-8"
            placeholder="Search"
            />
            <a href="#" class="btn-outline border px-3 py-1 rounded">Search</a>
        </div>

        <div class="my-5">
            <table class="table w-full">
                <thead>
                    <tr class="border-t border-b">
                        <th class="text-center py-3">Name</th>
                        <th class="text-center py-3 ">SKU</th>
                        <th class="text-center py-3">Price</th>
                        <th class="text-center py-3">Actions</th>
                    </tr>
                    <tbody>
                        @foreach ($products['data'] as $product)
                            <tr class="border-t border-b" x-data="{showDropdown: false}">
                                <td class="py-3 text-center">
                                    <a href="/{{ $storeHash }}/products/{{ $product['id'] }}">{{ $product['name'] }}</a>
                                </td>
                                <td class="py-3 text-sm text-gray-600 text-center">{{ $product['sku'] }}</td>
                                <td class="py-3 text-sm text-gray-600 text-center">${{ $product['price'] }}</td>
                                <td class="py-3 text-center">
                                    <div class="relative inline">
                                        <a href="javascript:;" @click="showDropdown = !showDropdown"><i class="fa-solid fa-ellipsis-vertical"></i></a>

                                        <div class="z-40 absolute right-0 bg-white shadow-md py-1 px-3 w-32" x-show="showDropdown">
                                            <div class="hover:bg-blue-50 py-2 px-3">
                                                <a href="/{{ $storeHash }}/products/{{ $product['id'] }}"><i class="fa fa-pencil pr-2" aria-hidden="true"></i>Edit</a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </thead>
            </table>
        </div>
    </div>
@endsection