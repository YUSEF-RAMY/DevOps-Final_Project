@extends('frontend.layouts.app')

@section('title', 'Your Cart')

@section('content')

<div class="w-100" style="background-color: #FAF6F0; min-h-screen: 100vh; color: #4a1525;">
<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="display-5 fw-bold mb-0" style="color: #4a1525;">
                    <i class="bi bi-cart me-2"></i>Your Cart
                </h1>
                <a href="{{ route('products.index') }}" class="btn btn-custom" style="border-color: #4a1525; color: #4a1525; background: transparent; font-weight: 500;">
                    <i class="bi bi-arrow-left me-2"></i>Continue Shopping
                </a>
            </div>
        </div>
    </div>

    @if(!empty($removedItems))
        <div class="alert alert-warning">
            Some items were removed because they are out of stock or unavailable:
            <ul class="mb-0">
                @foreach($removedItems as $ri)
                    <li>{{ $ri }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($cart->items->isEmpty())
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card border-0 shadow-sm text-center bg-white">
                    <div class="card-body py-5">
                        <i class="bi bi-cart-x display-1 text-muted mb-4"></i>
                        <h3 class="text-muted mb-3">Your cart is empty</h3>
                        <p class="text-muted mb-4">
                            Looks like you haven't added any items to your cart yet. 
                            Start shopping to fill it up!
                        </p>
                        <a href="{{ route('products.index') }}" class="btn btn-lg btn-custom" style="background-color: #4a1525; color: #ffffff; border: none;">
                            <i class="bi bi-grid me-2"></i>Start Shopping
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="card border-0 shadow-sm bg-white">
                   
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3" style="border-bottom: 1px solid rgba(74, 21, 37, 0.1);">
                        <h5 class="mb-0" style="color: #4a1525; font-weight: 600;">
                            <i class="bi bi-list-ul me-2"></i>Cart Items ({{ $cart->items->count() }})
                        </h5>
                        @if($cart->items->count() > 0)
                        <form id="cartClearForm" action="{{ route('cart.clear') }}" method="post" class="d-inline m-0">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Clear All</button>
                        </form>
                        @endif
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr style="background-color: #fcfbfa;">
                                        <th class="border-0" style="color: #4a1525; font-weight: 600;">Product</th>
                                        <th class="border-0" style="color: #4a1525; font-weight: 600;">Price</th>
                                        <th class="border-0" style="color: #4a1525; font-weight: 600;">Quantity</th>
                                        <th class="border-0" style="color: #4a1525; font-weight: 600;">Total</th>
                                        <th class="border-0" style="color: #4a1525; font-weight: 600;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(($availableItems ?? $cart->items) as $item)
                                        @php($oos = false)
                                        <tr data-item-id="{{ $item->id }}" data-stock="{{ (int) ($item->product->stock ?? 0) }}">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3">
                                                        @if($item->product->images->count() > 0)
                                                            <img src="{{ asset('storage/' . $item->product->images->first()->image_path) }}" 
                                                                 alt="{{ $item->product->name }}" 
                                                                 class="rounded" 
                                                                 style="width: 60px; height: 60px; object-fit: cover;">
                                                        @else
                                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                                 style="width: 60px; height: 60px;">
                                                                <i class="bi bi-image text-muted"></i>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-1">
                                                            <a href="{{ route('products.show', $item->product->slug) }}" 
                                                               class="text-decoration-none fw-semibold" style="color: #4a1525;">
                                                                 {{ $item->product->name }}
                                                            </a>
                                                        </h6>
                                                        <small class="text-muted">{{ $item->product->sku }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="fw-semibold" style="color: #4a1525;">@currency($item->unit_price)</span>
                                            </td>
                                            <td>
                                                <form action="{{ route('cart.items.update', $item->id) }}" method="post" class="d-flex align-items-center cart-update-form">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="input-group" style="width: 120px;">
                                                        <input type="number" name="quantity" min="1" max="{{ (int) ($item->product->stock ?? 0) }}" 
                                                               value="{{ $item->quantity }}" 
                                                               class="form-control form-control-sm text-center" style="background-color: #FAF6F0; border-color: rgba(74, 21, 37, 0.2);">
                                                        <button type="submit" class="btn btn-sm" style="background-color: #c5a059; color: #4a1525; border: 1px solid #c5a059;">
                                                            <i class="bi bi-check text-white"></i>
                                                        </button>
                                                    </div>
                                                </form>
                                            </td>
                                            <td>
                                                <span class="fw-bold item-line-total" style="color: #4a1525;">@currency($item->line_total)</span>
                                            </td>
                                            <td>
                                                <form action="{{ route('cart.items.remove', $item->id) }}" method="post" class="d-inline cart-remove-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                
                <div class="card border-0 shadow-sm sticky-top" style="top: 100px; background-color: #4a1525; color: #FAF6F0;">
                    <div class="card-header text-white" style="background-color: rgba(0,0,0,0.15) !important; border-bottom: 1px solid rgba(250, 246, 240, 0.1);">
                        <h5 class="mb-0 py-1">
                            <i class="bi bi-receipt me-2"></i>Order Summary
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <h6 class="fw-semibold mb-2" style="color: #FAF6F0;">
                                <i class="bi bi-ticket me-1"></i>Coupon Code
                            </h6>
                            @if($cart->coupon)
                                <div class="alert alert-success py-2 mb-2 bg-white/10 border-0 text-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $cart->coupon->code }}</strong>
                                            <br>
                                            <small style="color: rgba(250, 246, 240, 0.8);">{{ $cart->coupon->name }}</small>
                                        </div>
                                        <form action="{{ route('coupons.remove') }}" method="post" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-light btn-sm border-0">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @else
                                <form id="couponForm" class="d-flex gap-2">
                                    @csrf
                                    <input type="text" 
                                           id="couponCode" 
                                           class="form-control form-control-sm text-white" 
                                           style="background-color: rgba(250, 246, 240, 0.1); border-color: rgba(250, 246, 240, 0.2);"
                                           placeholder="Enter coupon code"
                                           maxlength="50">
                                    <button type="submit" class="btn btn-sm" style="background-color: #c5a059; color: #4a1525; border: none;">
                                        <i class="bi bi-check lg text-white"></i>
                                    </button>
                                </form>
                                <div id="couponMessage" class="mt-2"></div>
                            @endif
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span style="color: rgba(250, 246, 240, 0.8);">Subtotal</span>
                            <span id="cartSubtotal" class="fw-semibold">@currency($cart->subtotal)</span>
                        </div>
                        @if($cart->coupon_discount > 0)
                            <div class="d-flex justify-content-between mb-2" id="cartDiscountRow">
                                <span style="color: #c5a059;">
                                    <i class="bi bi-ticket me-1"></i>Discount ({{ $cart->coupon->code }})
                                </span>
                                <span class="fw-semibold" id="cartDiscount" style="color: #c5a059;">-@currency($cart->coupon_discount)</span>
                            </div>
                        @endif
                        <div class="d-flex justify-content-between mb-2">
                            <span style="color: rgba(250, 246, 240, 0.8);">Tax</span>
                            <span id="cartTax" class="fw-semibold">@currency($cart->tax_total)</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span style="color: rgba(250, 246, 240, 0.8);">Shipping</span>
                            <span class="small" style="color: #c5a059;">Calculated at checkout</span>
                        </div>
                        <hr style="border-color: rgba(250, 246, 240, 0.15);">
                        <div class="d-flex justify-content-between mb-4">
                            <span class="fw-bold fs-5" style="color: #FAF6F0;">Total</span>
                            <span class="fw-bold fs-5" style="color: #c5a059;" id="cartGrand">@currency($cart->grand_total)</span>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <a href="{{ route('checkout.show') }}" class="btn btn-lg btn-custom" style="background-color: #c5a059 !important; color: #4a1525 !important; border: none; font-weight: bold; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                                <i class="bi bi-credit-card me-2"></i>Proceed to Checkout
                            </a>
                            <a href="{{ route('products.index') }}" class="btn btn-custom" style="border-color: rgba(250, 246, 240, 0.3); color: #FAF6F0; background: transparent;">
                                <i class="bi bi-arrow-left me-2"></i>Continue Shopping
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        @if(isset($unavailableItems) && $unavailableItems->count())
        <div class="row mt-3">
            <div class="col-12">
                <div class="card border-0 shadow-sm bg-white">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 text-warning"><i class="bi bi-exclamation-triangle me-2"></i>Unavailable Items (won't be checked out)</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-warning">
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Total</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($unavailableItems as $item)
                                    <tr class="table-warning">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    @if($item->product && $item->product->images->count() > 0)
                                                        <img src="{{ asset('storage/' . $item->product->images->first()->image_path) }}" alt="{{ $item->product->name }}" class="rounded" style="width: 60px; height: 60px; object-fit: cover;">
                                                    @else
                                                        <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                                            <i class="bi bi-image text-muted"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div>
                                                    <h6 class="mb-1 text-muted">{{ $item->product->name ?? 'Unavailable product' }}</h6>
                                                    <small class="text-muted">{{ $item->product->sku ?? '' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="text-muted">@currency($item->unit_price)</span></td>
                                        <td>
                                            <div class="text-muted">{{ $item->quantity }}</div>
                                        </td>
                                        <td><span class="text-muted text-decoration-line-through">@currency($item->line_total)</span></td>
                                        <td>
                                            <form action="{{ route('cart.items.remove', $item->id) }}" method="post" class="d-inline cart-remove-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                            </form>
                                            <div class="mt-1"><span class="badge bg-warning text-dark">Out of stock</span></div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    @endif
</div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // AJAX quantity update
    document.querySelectorAll('.cart-update-form').forEach(function(form){
        form.addEventListener('submit', function(e){
            e.preventDefault();
            const row = form.closest('tr');
            const itemId = row.dataset.itemId;
            const qty = form.querySelector('input[name="quantity"]').value;
            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value
                },
                body: new URLSearchParams({ _method: 'PUT', quantity: qty })
            }).then(r=>r.json()).then(res=>{
                if(!res || !res.success) return;
                row.querySelector('.item-line-total').textContent = new Intl.NumberFormat(undefined, { style: 'currency', currency: '{{ $currentCurrency->code ?? 'USD' }}' }).format(res.item.line_total);
                document.getElementById('cartSubtotal').textContent = new Intl.NumberFormat(undefined, { style: 'currency', currency: '{{ $currentCurrency->code ?? 'USD' }}' }).format(res.cart.subtotal);
                document.getElementById('cartGrand').textContent = new Intl.NumberFormat(undefined, { style: 'currency', currency: '{{ $currentCurrency->code ?? 'USD' }}' }).format(res.cart.grand_total);
                if(typeof window.__updateCartCount === 'function'){
                    window.__updateCartCount(res.cart.count);
                }
            }).catch(()=>{});
        });
    });

    // AJAX remove item with SweetAlert2
    document.querySelectorAll('.cart-remove-form').forEach(function(form){
        form.addEventListener('submit', async function(e){
            e.preventDefault();
            try {
                const result = await Swal.fire({
                    title: 'Remove item?',
                    text: 'This will remove the item from your cart.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, remove',
                    cancelButtonText: 'Cancel'
                });
                if (!result.isConfirmed) return false;
            } catch(_) { /* fallback continues */ }

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value
                },
                body: new URLSearchParams({ _method: 'DELETE' })
            }).then(r=>r.json()).then(res=>{
                if(!res || !res.success) return;
                const row = form.closest('tr');
                if(row) row.remove();
                document.getElementById('cartSubtotal').textContent = new Intl.NumberFormat(undefined, { style: 'currency', currency: '{{ $currentCurrency->code ?? 'USD' }}' }).format(res.cart.subtotal);
                document.getElementById('cartGrand').textContent = new Intl.NumberFormat(undefined, { style: 'currency', currency: '{{ $currentCurrency->code ?? 'USD' }}' }).format(res.cart.grand_total);
                if(typeof window.__updateCartCount === 'function'){
                    window.__updateCartCount(res.cart.count);
                }
                if (window.Swal) {
                    Swal.fire({ icon: 'success', title: 'Removed', timer: 1200, showConfirmButton: false });
                }
            }).catch(()=>{});
        });
    });
    
    const couponForm = document.getElementById('couponForm');
    const couponCode = document.getElementById('couponCode');
    const couponMessage = document.getElementById('couponMessage');

    if (couponForm) {
        couponForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const code = couponCode.value.trim();
            if (!code) {
                showMessage('Please enter a coupon code.', 'danger');
                return;
            }

            // Show loading state
            const submitBtn = couponForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i>';
            submitBtn.disabled = true;

            // Get CSRF token
            let csrfToken = null;
            const metaToken = document.querySelector('meta[name="csrf-token"]');
            if (metaToken) {
                csrfToken = metaToken.getAttribute('content');
            }
            if (!csrfToken) {
                const formToken = document.querySelector('input[name="_token"]');
                if (formToken) {
                    csrfToken = formToken.value;
                }
            }
            if (!csrfToken) {
                showMessage('CSRF token not found. Please refresh the page and try again.', 'danger');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                return;
            }

            fetch('{{ route("coupons.apply") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ code: code }),
                credentials: 'same-origin'
            })
            .then(response => {
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    return response.json().then(data => {
                        return { ok: response.ok, status: response.status, data: data };
                    });
                } else {
                    return {
                        ok: false,
                        status: response.status,
                        data: {
                            message: response.status === 419 
                                ? 'Your session has expired. Please refresh the page and try again.' 
                                : 'An error occurred. Please try again.'
                        }
                    };
                }
            })
            .then(result => {
                if (result.status === 419) {
                    showMessage('Your session has expired. Refreshing page...', 'warning');
                    setTimeout(() => { window.location.reload(); }, 2000);
                    return;
                }
                
                if (result.ok && result.data.success) {
                    showMessage(result.data.message || 'Coupon applied successfully!', 'success');
                    setTimeout(() => { window.location.reload(); }, 1500);
                } else {
                    const errorMessage = result.data.message || result.data.error || 'An error occurred. Please try again.';
                    showMessage(errorMessage, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('An error occurred. Please try again.', 'danger');
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    }

    function showMessage(message, type) {
        couponMessage.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`;
    }
    
    // Clear all with SweetAlert2
    const clearForm = document.getElementById('cartClearForm');
    if (clearForm) {
        clearForm.addEventListener('submit', async function(e){
            e.preventDefault();
            try {
                const result = await Swal.fire({
                    title: 'Clear entire cart?',
                    text: 'This will remove all items from your cart.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, clear',
                    cancelButtonText: 'Cancel'
                });
                if (!result.isConfirmed) return false;
            } catch(_) {}
            fetch(clearForm.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': clearForm.querySelector('input[name="_token"]').value
                }
            }).then(r=>r.json()).then(res=>{
                if(!res || !res.success) { window.location.reload(); return; }
                document.querySelectorAll('table tbody tr').forEach(tr=>tr.remove());
                document.getElementById('cartSubtotal').textContent = new Intl.NumberFormat(undefined, { style: 'currency', currency: '{{ $currentCurrency->code ?? 'USD' }}' }).format(res.cart.subtotal);
                document.getElementById('cartGrand').textContent = new Intl.NumberFormat(undefined, { style: 'currency', currency: '{{ $currentCurrency->code ?? 'USD' }}' }).format(res.cart.grand_total);
                if(typeof window.__updateCartCount === 'function'){
                    window.__updateCartCount(0);
                }
                Swal.fire({ icon: 'success', title: 'Cart cleared', timer: 1200, showConfirmButton: false }).then(()=> window.location.reload());
            }).catch(()=>{ window.location.reload(); });
        });
    }
});
</script>
@endpush