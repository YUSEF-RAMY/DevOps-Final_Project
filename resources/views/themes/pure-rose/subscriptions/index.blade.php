@extends('themes.pure-rose.layouts.app')
@section('title', 'My Subscriptions')
@section('content')
<div class="container py-5">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="glass-panel p-4 mb-4">
                <h2 class="font-display text-cream mb-4">Flower Subscriptions</h2>
                @forelse($subscriptions as $sub)
                    <div class="p-3 mb-3 rounded" style="background:rgba(255,255,255,.04);">
                        <div class="d-flex justify-content-between flex-wrap gap-2">
                            <div>
                                <h6 class="text-cream text-capitalize">{{ $sub->frequency }} delivery · {{ ucfirst($sub->delivery_day) }}</h6>
                                <small class="text-white-50">Status: <span class="text-gold">{{ $sub->status }}</span> · AED {{ number_format($sub->amount, 0) }}</small>
                                @if($sub->preferred_color_palette)
                                    <div class="small text-white-50">Palette: {{ $sub->preferred_color_palette }}</div>
                                @endif
                            </div>
                            <div class="d-flex gap-2">
                                @if($sub->status === 'active')
                                    <form action="{{ route('subscriptions.pause', $sub) }}" method="POST">@csrf<button class="btn btn-gold-outline btn-sm">Pause</button></form>
                                @elseif($sub->status === 'paused')
                                    <form action="{{ route('subscriptions.resume', $sub) }}" method="POST">@csrf<button class="btn btn-gold-solid btn-sm">Resume</button></form>
                                @endif
                                @if($sub->status !== 'cancelled')
                                    <form action="{{ route('subscriptions.cancel', $sub) }}" method="POST" onsubmit="return confirm('Cancel subscription?')">@csrf<button class="btn btn-outline-danger btn-sm">Cancel</button></form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-white-50">No active subscriptions. Fresh blooms delivered on your schedule.</p>
                @endforelse
            </div>

            <div class="glass-panel p-4">
                <h5 class="font-display text-gold mb-3">Start a Subscription</h5>
                <form action="{{ route('subscriptions.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label text-gold small">Frequency</label>
                            <select name="frequency" class="form-select" required>
                                <option value="weekly">Weekly — AED {{ $weeklyPrice }}</option>
                                <option value="monthly" selected>Monthly — AED {{ $monthlyPrice }}</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-gold small">Delivery Day</label>
                            <select name="delivery_day" class="form-select" required>
                                @foreach(['monday','tuesday','wednesday','thursday','friday','saturday'] as $day)
                                    <option value="{{ $day }}">{{ ucfirst($day) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-gold small">Color Palette</label>
                            <input type="text" name="preferred_color_palette" class="form-control" placeholder="Blush & cream, Purple tones...">
                        </div>
                    </div>
                    <button class="btn btn-gold-solid mt-3">Activate Subscription</button>
                </form>
            </div>
        </div>
        <div class="col-lg-4">@include('themes.pure-rose.partials.sidebar-account')</div>
    </div>
</div>
@endsection
