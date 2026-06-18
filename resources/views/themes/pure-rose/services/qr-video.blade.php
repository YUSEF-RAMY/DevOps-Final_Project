@extends('themes.pure-rose.layouts.app')
@section('title', 'QR Video Messages')
@section('content')
<div class="container py-5">
    <div class="glass-panel p-5 text-center">
        <i class="bi bi-qr-code text-gold display-4 mb-3"></i>
        <h1 class="font-display text-cream">QR Video Messages</h1>
        <p class="text-white-50 mx-auto" style="max-width:560px;">Attach a personal video message to your bouquet. Recipients scan a gold-embossed QR card to relive your moment forever.</p>
        <img src="https://images.unsplash.com/photo-1519378058454-4c06c7f565c0?q=80&w=800" class="img-fluid rounded mt-4 mb-4" style="max-height:320px;object-fit:cover;width:100%;" alt="QR video bouquet">
        <a href="{{ route('products.index') }}" class="btn btn-gold-solid">Choose a Bouquet</a>
    </div>
</div>
@endsection
