<div class="sidebar-widget mb-4">
    <h6 class="font-display text-gold mb-3"><i class="bi bi-person-circle me-2"></i>My Account</h6>
    <ul class="list-unstyled small mb-0">
        <li class="mb-2"><a href="{{ route('profile') }}" class="text-white-50"><i class="bi bi-chevron-right text-gold me-1"></i> Personal Info</a></li>
        <li class="mb-2"><a href="{{ route('orders.index') }}" class="text-white-50"><i class="bi bi-chevron-right text-gold me-1"></i> Order History</a></li>
        <li class="mb-2"><a href="{{ route('subscriptions.index') }}" class="text-white-50"><i class="bi bi-chevron-right text-gold me-1"></i> Subscriptions</a></li>
        <li><a href="{{ route('occasions.index') }}" class="text-white-50"><i class="bi bi-chevron-right text-gold me-1"></i> Gift Cards</a></li>
    </ul>
</div>

<div class="sidebar-widget">
    <h6 class="font-display text-gold mb-3"><i class="bi bi-calendar-heart me-2"></i>My Occasions Calendar</h6>
    @forelse(($upcomingOccasions ?? collect()) as $occasion)
        <div class="mb-3 pb-3 border-bottom" style="border-color: rgba(197,160,89,.15) !important;">
            <div class="small text-cream fw-semibold">{{ $occasion->displayLabel() }}</div>
            <div class="text-white-50" style="font-size:.75rem;">{{ $occasion->relation_type }} · {{ $occasion->daysUntil() }} days away</div>
            <a href="{{ route('products.index') }}?occasion={{ $occasion->id }}" class="btn btn-gold-outline btn-sm mt-2 w-100">Pre-order Bouquet</a>
        </div>
    @empty
        <p class="small text-white-50">Register birthdays & celebrations to receive elegant reminders.</p>
        @auth
            <a href="{{ route('occasions.create') }}" class="btn btn-gold-solid btn-sm w-100">Add Occasion</a>
        @else
            <a href="{{ route('login') }}" class="btn btn-gold-outline btn-sm w-100">Sign in to save dates</a>
        @endauth
    @endforelse
</div>
