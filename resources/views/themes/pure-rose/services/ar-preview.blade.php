@extends('themes.pure-rose.layouts.app')
@section('title', 'AR Bouquet Preview')
@section('content')
<div class="container py-5">
    <div class="glass-panel p-5">
        <h1 class="font-display text-cream text-center mb-3">AR Bouquet Preview</h1>
        <p class="text-white-50 text-center mx-auto mb-4" style="max-width:560px;">Visualize your arrangement in your space before you order. Our augmented reality studio places lifelike stems on your table in real time.</p>
        <div class="row g-4 align-items-center">
            <div class="col-md-6">
                <img src="https://images.unsplash.com/photo-1487074763055-281eb345b1cc?q=80&w=800" class="w-100 rounded" alt="AR preview">
            </div>
            <div class="col-md-6">
                <ul class="text-white-50">
                    <li class="mb-2">True-to-scale bouquet rendering</li>
                    <li class="mb-2">Swap palettes instantly</li>
                    <li class="mb-2">Share previews with loved ones</li>
                </ul>
                <a href="{{ route('products.index') }}" class="btn btn-gold-solid mt-2">Preview Collection</a>
            </div>
        </div>
    </div>
</div>
@endsection
