<footer class="mt-5 py-5 border-top" style="border-color: rgba(197,160,89,.2) !important; background: rgba(0,0,0,.35);">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <h5 class="font-display text-gold">{{ config('app.name') }}</h5>
                <p class="text-white-50 small">Crafting Moments, One Pure Petal at a Time.</p>
            </div>
            <div class="col-md-4">
                <h6 class="text-gold">My Account</h6>
                <ul class="list-unstyled small">
                    <li><a href="{{ route('profile') }}" class="text-white-50">Personal Info</a></li>
                    <li><a href="{{ route('orders.index') }}" class="text-white-50">Order History</a></li>
                    <li><a href="{{ route('occasions.index') }}" class="text-white-50">My Occasions</a></li>
                    <li><a href="{{ route('subscriptions.index') }}" class="text-white-50">Subscriptions</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h6 class="text-gold">Premium Services</h6>
                <ul class="list-unstyled small">
                    <li><a href="{{ route('services.qr-video') }}" class="text-white-50">QR Video Messages</a></li>
                    <li><a href="{{ route('services.ar-preview') }}" class="text-white-50">AR Bouquet Preview</a></li>
                    <li><a href="{{ route('services.floral-assistant') }}" class="text-white-50">Floral Assistant</a></li>
                    <li><a href="{{ route('services.corporate-events') }}" class="text-white-50">Corporate Events</a></li>
                </ul>
            </div>
        </div>
        <div class="text-center text-white-50 small mt-4 pt-3 border-top" style="border-color: rgba(197,160,89,.15) !important;">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</footer>
