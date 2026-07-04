@extends('themes.pure-rose.layouts.app')
@section('title', 'Corporate Events')
@section('content')
<div class="container py-5">
    <div class="glass-panel p-5">
        <h1 class="font-display text-cream text-center mb-3">Corporate Events</h1>
        <p class="text-white-50 text-center mx-auto mb-5" style="max-width:600px;">From gala centerpieces to executive gifting programs — PURE ROSE designs immersive floral experiences for brands that demand excellence.</p>
        <div class="row g-3">
            @foreach([
                ['https://images.unsplash.com/photo-1464349153735-7db50ed83c84?q=80&w=400','Gala Installations'],
                ['https://images.unsplash.com/photo-1478146896981-b80fe463b330?q=80&w=400','Executive Gifting'],
                ['https://images.unsplash.com/photo-1527529482837-4698179dc6ce?q=80&w=400','Weekly Office Styling'],
            ] as [$img, $title])
            <div class="col-md-4 text-center">
                <img src="{{ $img }}" class="w-100 rounded mb-2" style="height:180px;object-fit:cover;" alt="{{ $title }}">
                <h6 class="text-gold">{{ $title }}</h6>
            </div>
            @endforeach
        </div>
        <div class="text-center mt-4">
            <a href="{{ route('page.show', 'contact-us') }}" class="btn btn-gold-solid">Request a Proposal</a>
        </div>
    </div>
</div>
@endsection
