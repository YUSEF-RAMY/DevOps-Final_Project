@extends('themes.pure-rose.layouts.app')

@section('title', 'Home')

@section('content')
<section class="position-relative py-5" style="min-height: 70vh; display:flex; align-items:center;">
    <div class="container text-center py-5">
        <p class="text-gold text-uppercase letter-spacing mb-3" style="letter-spacing:.25em;font-size:.8rem;">Luxury Floral Atelier</p>
        <h1 class="display-3 font-display text-cream hero-headline mb-4">Crafting Moments,<br>One Pure Petal at a Time.</h1>
        <p class="lead text-white-50 mx-auto mb-4" style="max-width:600px;">Bespoke bouquets, intelligent occasion reminders, and white-glove delivery across the Emirates.</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="{{ route('products.index') }}" class="btn btn-gold-solid">Explore Boutique</a>
            <a href="{{ route('services.floral-assistant') }}" class="btn btn-gold-outline">Meet Your Florist</a>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="d-flex justify-content-between align-items-end mb-4">
                    <div>
                        <h2 class="font-display text-cream">Signature Collection</h2>
                        <p class="text-white-50 mb-0">Curated masterpieces — rated 5.0 by our patrons</p>
                    </div>
                    <a href="{{ route('products.index') }}" class="btn btn-gold-outline btn-sm">View All</a>
                </div>
                <div class="row g-4">
                    @forelse($featuredProducts->take(3) as $product)
                        <div class="col-md-4">@include('themes.pure-rose.products._card', ['product' => $product])</div>
                    @empty
                        @foreach([
                            ['name'=>'The Aurora Bouquet — Mixed Pastels','price'=>450,'img'=>'https://images.unsplash.com/photo-1561181286-d3fee7d55364?q=80&w=600','slug'=>'aurora-bouquet-mixed-pastels'],
                            ['name'=>'The Lavender Symphony — Purple Roses','price'=>520,'img'=>'https://images.unsplash.com/photo-1596436889106-be35e843f974?q=80&w=600','slug'=>'lavender-symphony-purple-roses'],
                            ['name'=>'The Royal Crimson — Dark Red Roses','price'=>480,'img'=>'https://images.unsplash.com/photo-1526047932273-341f2a7631f9?q=80&w=600','slug'=>'royal-crimson-dark-red-roses'],
                        ] as $bouquet)
                        <div class="col-md-4">
                            <div class="product-card-pure h-100">
                                <img src="{{ $bouquet['img'] }}" class="w-100" style="height:220px;object-fit:cover;" alt="{{ $bouquet['name'] }}">
                                <div class="p-3">
                                    <h6 class="font-display text-cream">{{ $bouquet['name'] }}</h6>
                                    <div class="text-gold small mb-2">5.0 ⭐</div>
                                    <div class="text-gold fw-bold mb-3">AED {{ $bouquet['price'] }}</div>
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('products.index') }}" class="btn btn-gold-outline btn-sm">View Details</a>
                                        <a href="{{ route('products.index') }}" class="btn btn-gold-solid btn-sm">Add to Cart</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @endforelse
                </div>
            </div>

            <div class="col-lg-4">
                @include('themes.pure-rose.partials.sidebar-account')

                <div class="sidebar-widget mt-4">
                    <h6 class="font-display text-gold mb-3"><i class="bi bi-stars me-2"></i>World of Flowers</h6>
                    <div class="row g-2">
                        @foreach([
                            'https://images.unsplash.com/photo-1490750967868-88ea4486c946?q=80&w=300',
                            'https://images.unsplash.com/photo-1455659817273-f96807779a8a?q=80&w=300',
                            'https://images.unsplash.com/photo-1462275646964-a0e3386b89eb?q=80&w=300',
                            'https://images.unsplash.com/photo-1508610048655-a06b669e3321?q=80&w=300',
                        ] as $img)
                        <div class="col-6">
                            <img src="{{ $img }}" class="w-100 rounded" style="height:80px;object-fit:cover;" alt="Floral gallery">
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
