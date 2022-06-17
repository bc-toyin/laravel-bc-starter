@extends('layouts.app')

@section('content')
    <div class="bg-white shadow-md p-5 mt-8">
        <div class="border p-5 flex items-center justify-between mt-4">
            <div class="font-bold">Products</div>

            <a href="/{{ $storeHash }}/products" class="btn-outline border border-[#4b71fc] px-3 py-1 rounded text-[#4b71fc]">View</a>
        </div>
    </div>
@endsection