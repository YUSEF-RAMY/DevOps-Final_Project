@php
    $img = $product->images->first();
    $imgUrl = $img ? (str_starts_with($img->path, 'http') ? $img->path : asset('storage/' . $img->path)) : 'https://images.unsplash.com/photo-1561181286-d3fee7d55364?q=80&w=600';
@endphp
<div class="product-card-pure h-100 d-flex flex-column">
    <a href="{{ route('products.show', $product->slug) }}">
        <img src="{{ $imgUrl }}" alt="{{ $product->name }}" class="w-100" style="height:220px;object-fit:cover;">
    </a>
    <div class="p-3 d-flex flex-column flex-grow-1">
        <h6 class="font-display text-cream">{{ $product->name }}</h6>
        <div class="text-gold small mb-1">5.0 ⭐</div>
        <div class="text-gold fw-bold mb-3">AED {{ number_format($product->price, 0) }}</div>
        <div class="d-grid gap-2 mt-auto">
            <a href="{{ route('products.show', $product->slug) }}" class="btn btn-gold-outline btn-sm">View Details</a>
            <form action="{{ route('cart.add') }}" method="POST">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <button class="btn btn-gold-solid btn-sm w-100" {{ $product->stock <= 0 ? 'disabled' : '' }}>Add to Cart</button>
            </form>
        </div>
    </div>
</div>
