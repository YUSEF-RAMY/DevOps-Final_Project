@extends('themes.pure-rose.layouts.app')
@section('title', 'Floral Assistant')
@section('content')
<div class="container py-5 text-center">
    <div class="glass-panel p-5">
        <h1 class="font-display text-cream mb-3">Floral Assistant</h1>
        <p class="text-white-50 mx-auto mb-4" style="max-width:520px;">Our AI concierge knows every stem in our catalog. Ask about occasions, palettes, or pairings — then add to cart in one tap.</p>
        <button class="btn btn-gold-solid btn-lg" onclick="document.getElementById('ai-chat-open').click()">Open Chat Drawer</button>
    </div>
</div>
@endsection
