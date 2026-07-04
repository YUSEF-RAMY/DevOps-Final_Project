@extends('themes.pure-rose.layouts.app')
@section('title', 'Boutique')
@section('content')
<div class="container py-5">
    <div class="row g-4">
        <div class="col-lg-9">
            <h1 class="font-display text-cream mb-4">The Boutique</h1>
            <div class="row g-4">
                @foreach($products as $product)
                    <div class="col-md-4 col-sm-6">@include('themes.pure-rose.products._card', ['product' => $product])</div>
                @endforeach
            </div>
            <div class="mt-4">{{ $products->links() }}</div>
        </div>
        <div class="col-lg-3">@include('themes.pure-rose.partials.sidebar-account')</div>
    </div>
</div>
@endsection
