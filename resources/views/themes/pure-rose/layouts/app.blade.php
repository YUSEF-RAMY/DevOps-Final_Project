<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name')) — {{ $siteSettings->site_name ?? config('app.name') }}</title>
    @if(!empty($siteSettings->meta_description))
    <meta name="description" content="{{ $siteSettings->meta_description }}">
    @endif
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    @stack('schema')
    <style>
        :root {
            --pr-burgundy: #2D0101;
            --pr-burgundy-light: #4a0a0a;
            --pr-cream: #FDF5E6;
            --pr-gold: #C5A059;
            --pr-gold-hover: #d4b06a;
            --pr-glass: rgba(45, 1, 1, 0.75);
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--pr-burgundy);
            color: var(--pr-cream);
            min-height: 100vh;
        }
        h1, h2, h3, h4, h5, .font-display {
            font-family: 'Playfair Display', serif;
        }
        .pure-rose-bg {
            background: linear-gradient(rgba(45,1,1,.88), rgba(45,1,1,.92)),
                url('https://images.unsplash.com/photo-1518709268805-4e9042af9f23?q=80&w=2000') center/cover fixed;
        }
        .glass-panel {
            background: var(--pr-glass);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(197, 160, 89, 0.25);
            border-radius: 16px;
        }
        .navbar-pure-rose {
            background: rgba(45, 1, 1, 0.85) !important;
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(197, 160, 89, 0.2);
        }
        .navbar-pure-rose .nav-link {
            color: var(--pr-cream) !important;
            font-weight: 500;
            transition: color .25s;
        }
        .navbar-pure-rose .nav-link:hover,
        .navbar-pure-rose .nav-link:focus {
            color: var(--pr-gold) !important;
        }
        .brand-pure-rose {
            font-family: 'Playfair Display', serif;
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--pr-cream) !important;
            letter-spacing: .04em;
        }
        .btn-gold-outline {
            border: 2px solid var(--pr-gold);
            color: var(--pr-gold);
            background: transparent;
            border-radius: 0;
            font-weight: 600;
            letter-spacing: .06em;
            text-transform: uppercase;
            font-size: .75rem;
            padding: .6rem 1.2rem;
            transition: all .3s ease;
        }
        .btn-gold-outline:hover {
            background: var(--pr-gold);
            color: var(--pr-burgundy);
        }
        .btn-gold-solid {
            background: var(--pr-gold);
            color: var(--pr-burgundy);
            border: 2px solid var(--pr-gold);
            border-radius: 0;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            font-size: .75rem;
            padding: .6rem 1.2rem;
            transition: all .3s ease;
        }
        .btn-gold-solid:hover {
            background: var(--pr-gold-hover);
            border-color: var(--pr-gold-hover);
            color: var(--pr-burgundy);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(197,160,89,.35);
        }
        .product-card-pure {
            background: rgba(255,255,255,.04);
            border: 1px solid rgba(197,160,89,.2);
            border-radius: 12px;
            overflow: hidden;
            transition: transform .4s ease, box-shadow .4s ease;
        }
        .product-card-pure:hover {
            transform: scale(1.03);
            box-shadow: 0 20px 50px rgba(0,0,0,.45), 0 0 30px rgba(197,160,89,.15);
        }
        .notification-badge {
            position: absolute;
            top: -4px;
            right: -6px;
            background: #dc3545;
            color: #fff;
            font-size: .65rem;
            min-width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }
        .sidebar-widget {
            background: rgba(255,255,255,.03);
            border: 1px solid rgba(197,160,89,.18);
            border-radius: 12px;
            padding: 1.25rem;
        }
        .dropdown-menu-dark-gold {
            background: #1a0000;
            border: 1px solid rgba(197,160,89,.3);
        }
        .dropdown-menu-dark-gold .dropdown-item {
            color: var(--pr-cream);
        }
        .dropdown-menu-dark-gold .dropdown-item:hover {
            background: rgba(197,160,89,.15);
            color: var(--pr-gold);
        }
        #ai-chat-drawer {
            position: fixed;
            top: 0;
            right: -420px;
            width: 400px;
            max-width: 100vw;
            height: 100vh;
            background: #1a0000;
            border-left: 1px solid rgba(197,160,89,.3);
            z-index: 9999;
            transition: right .4s cubic-bezier(.4,0,.2,1);
            display: flex;
            flex-direction: column;
        }
        #ai-chat-drawer.open { right: 0; }
        .ai-bubble {
            background: rgba(197,160,89,.12);
            border: 1px solid rgba(197,160,89,.25);
            border-radius: 12px;
            padding: .85rem 1rem;
            margin-bottom: .75rem;
            animation: fadeUp .4s ease;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes heroGlow {
            0%, 100% { text-shadow: 0 0 40px rgba(197,160,89,.2); }
            50% { text-shadow: 0 0 60px rgba(197,160,89,.45); }
        }
        .hero-headline { animation: heroGlow 4s ease-in-out infinite; }
        .form-control, .form-select {
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(197,160,89,.25);
            color: var(--pr-cream);
        }
        .form-control:focus, .form-select:focus {
            background: rgba(255,255,255,.08);
            border-color: var(--pr-gold);
            color: var(--pr-cream);
            box-shadow: 0 0 0 .2rem rgba(197,160,89,.2);
        }
        a { color: var(--pr-gold); }
        .text-cream { color: var(--pr-cream); }
        .text-gold { color: var(--pr-gold); }
    </style>
    @stack('styles')
</head>
<body class="pure-rose-bg d-flex flex-column min-vh-100">
    @include('themes.pure-rose.partials.nav')

    <main class="flex-grow-1">
        @if(session('success'))
            <div class="container mt-3"><div class="alert alert-success glass-panel border-0">{{ session('success') }}</div></div>
        @endif
        @if(session('error'))
            <div class="container mt-3"><div class="alert alert-danger glass-panel border-0">{{ session('error') }}</div></div>
        @endif
        @yield('content')
    </main>

    @include('themes.pure-rose.partials.footer')
    @include('themes.pure-rose.partials.ai-chat')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
