<nav class="navbar navbar-expand-lg navbar-dark navbar-pure-rose sticky-top py-3">
    <div class="container">
        <a class="navbar-brand brand-pure-rose d-flex align-items-center gap-2" href="{{ route('home') }}">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M12 21c-4-3-8-7-8-11a4 4 0 0 1 8 0 4 4 0 0 1 8 0c0 4-4 8-8 11z" fill="#C5A059" opacity=".9"/>
                <path d="M12 14c-2-1-4-3-4-5.5S10 5 12 5s4 1.5 4 3.5S14 13 12 14z" fill="#FDF5E6"/>
            </svg>
            {{ config('app.name') }}
        </a>

        <button class="navbar-toggler border-gold" type="button" data-bs-toggle="collapse" data-bs-target="#pureRoseNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="pureRoseNav">
            <ul class="navbar-nav mx-auto gap-lg-2">
                <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('products.index') }}">Boutique</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Premium Services</a>
                    <ul class="dropdown-menu dropdown-menu-dark-gold">
                        <li><a class="dropdown-item" href="{{ route('services.qr-video') }}"><i class="bi bi-qr-code me-2"></i>QR Video Messages</a></li>
                        <li><a class="dropdown-item" href="{{ route('services.ar-preview') }}"><i class="bi bi-badge-vr me-2"></i>AR Bouquet Preview</a></li>
                        <li><a class="dropdown-item" href="{{ route('services.floral-assistant') }}"><i class="bi bi-chat-heart me-2"></i>Floral Assistant</a></li>
                        <li><a class="dropdown-item" href="{{ route('services.corporate-events') }}"><i class="bi bi-building me-2"></i>Corporate Events</a></li>
                    </ul>
                </li>
            </ul>

            <ul class="navbar-nav align-items-center gap-2">
                <li class="nav-item position-relative">
                    <a class="nav-link px-2" href="#" id="notificationBell" title="Notifications">
                        <i class="bi bi-bell fs-5"></i>
                        @php $badgeCount = max(($unreadNotificationCount ?? 0), 6); @endphp
                        <span class="notification-badge">{{ $badgeCount }}</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-dark-gold dropdown-menu-end p-3" style="min-width:320px; display:none;" id="notificationDropdown">
                        <h6 class="text-gold mb-2">Notifications</h6>
                        @forelse(($userNotifications ?? collect()) as $n)
                            <div class="small mb-2 pb-2 border-bottom border-secondary">
                                <strong>{{ $n->title }}</strong><br>
                                <span class="text-white-50">{{ $n->message }}</span>
                            </div>
                        @empty
                            <div class="small text-white-50 mb-2">Order updates & occasion reminders appear here.</div>
                            <div class="small mb-2"><strong>Occasion Reminder</strong><br>Hey {{ auth()->user()->name ?? 'Amira' }}, a loved one's special day is approaching!</div>
                        @endforelse
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link px-2" href="{{ route('cart.index') }}"><i class="bi bi-bag fs-5"></i></a>
                </li>

                @auth
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" data-bs-toggle="dropdown">
                        <span class="rounded-circle bg-gold d-inline-flex align-items-center justify-content-center" style="width:34px;height:34px;background:var(--pr-gold);color:var(--pr-burgundy);font-weight:700;">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </span>
                        <span class="d-none d-md-inline">Hi, {{ auth()->user()->name }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark-gold dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('profile') }}">Personal Info</a></li>
                        <li><a class="dropdown-item" href="{{ route('orders.index') }}">Order History</a></li>
                        <li><a class="dropdown-item" href="{{ route('subscriptions.index') }}">My Subscriptions</a></li>
                        <li><a class="dropdown-item" href="{{ route('occasions.index') }}">My Occasions</a></li>
                        <li><hr class="dropdown-divider border-secondary"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">@csrf
                                <button class="dropdown-item">Logout</button>
                            </form>
                        </li>
                    </ul>
                </li>
                @else
                <li class="nav-item"><a class="btn btn-gold-outline btn-sm" href="{{ route('login') }}">Sign In</a></li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

@push('scripts')
<script>
document.getElementById('notificationBell')?.addEventListener('click', function(e) {
    e.preventDefault();
    const dd = document.getElementById('notificationDropdown');
    dd.style.display = dd.style.display === 'block' ? 'none' : 'block';
});
</script>
@endpush
