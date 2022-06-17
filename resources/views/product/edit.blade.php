@extends('layouts.app')

@section('content')
	<a href="/{{ $storeHash }}/products" class="block ml-3 mb-3" style="color: #86848c">
		<i class="fa-solid fa-arrow-left mr-3"></i> Products
	</a>

	<h1 class="text-3xl">Update product</h1>

	<div class="bg-white shadow-md p-5 mt-8">
		<form method="post" id="form" action="/api/{{ $storeHash }}/products/{{ $product['id'] }}" x-data="{
				formSubmit: false
			}" @submit="formSubmit = true">
			@csrf

			<div class="mt-5">
				<div class="mb-3">
					<label class="block font-bold mb-1">Name</label>
					<input type="text" class="block border p-2 w-1/2" name="name" value="{{ $product['name'] }}" />
				</div>

				<div class="mb-3">
					<label class="block font-bold mb-1">SKU</label>
					<input type="text" class="block border p-2 w-1/2" name="sku" value="{{ $product['sku'] }}" />
				</div>

				<div class="mb-3">
					<label class="block font-bold mb-1">Price</label>
					<input type="text" class="block border p-2 w-1/2" name="price" value="{{ $product['price'] }}" />
				</div>

				<div class="mb-3 text-right">
					<button type="submit" class="bg-[#4b71fc] text-white border border-[#4b71fc] px-3 py-1 rounded" x-bind:disabled="formSubmit" x-bind:class="formSubmit ? 'opacity-50' : ''">
						<i class="fas fa-circle-notch fa-spin" x-show="formSubmit"></i>
						Save
					</button>
				</div>
			</div>
		</form>
	</div>
@endsection