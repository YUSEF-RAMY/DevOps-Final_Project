@extends('themes.pure-rose.layouts.app')
@section('title', 'My Occasions')
@section('content')
<div class="container py-5">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="glass-panel p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="font-display text-cream mb-0">My Occasions</h2>
                    <a href="{{ route('occasions.create') }}" class="btn btn-gold-solid btn-sm">Add Occasion</a>
                </div>
                @forelse($occasions as $occasion)
                    <div class="p-3 mb-3 rounded" style="background:rgba(255,255,255,.04);border:1px solid rgba(197,160,89,.15);">
                        <div class="d-flex justify-content-between flex-wrap gap-2">
                            <div>
                                <h6 class="text-cream mb-1">{{ $occasion->recipient_name }} — {{ $occasion->occasion_name }}</h6>
                                <small class="text-white-50">{{ $occasion->relation_type }} · {{ $occasion->nextOccurrence()->format('F j, Y') }} · {{ $occasion->daysUntil() }} days away</small>
                                @if($occasion->preferredProduct)
                                    <div class="small text-gold mt-1">Preferred: {{ $occasion->preferredProduct->name }}</div>
                                @endif
                            </div>
                            <div class="d-flex gap-2 align-items-center">
                                <a href="{{ route('occasions.edit', $occasion) }}" class="btn" style="border: 1px solid #c5a059; color: #c5a059; background: transparent; font-size: 0.8rem; font-weight: 500; padding: 5px 14px; border-radius: 4px; min-width: 75px; text-align: center; text-transform: uppercase; letter-spacing: 0.05em; transition: all 0.2s;">Edit</a>
                                <form action="{{ route('occasions.destroy', $occasion) }}" method="POST" onsubmit="return confirm('Remove this occasion?')" class="m-0">
                                    @csrf @method('DELETE')
                                    <button class="btn" style="border: 1px solid rgba(255, 255, 255, 0.3); color: rgba(255, 255, 255, 0.6); background: transparent; font-size: 0.8rem; font-weight: 500; padding: 5px 14px; border-radius: 4px; min-width: 75px; text-align: center; text-transform: uppercase; letter-spacing: 0.05em; transition: all 0.2s;" onmouseover="this.style.borderColor='#e63946'; this.style.color='#e63946';" onmouseout="this.style.borderColor='rgba(255, 255, 255, 0.3)'; this.style.color='rgba(255, 255, 255, 0.6)';">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-white-50">No occasions yet. Add birthdays, graduations, and anniversaries to receive personalized reminders.</p>
                @endforelse
            </div>
        </div>
        <div class="col-lg-4">@include('themes.pure-rose.partials.sidebar-account')</div>
    </div>
</div>
@endsection