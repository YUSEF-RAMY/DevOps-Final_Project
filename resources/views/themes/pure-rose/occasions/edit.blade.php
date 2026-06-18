@extends('themes.pure-rose.layouts.app')
@section('title', 'Edit Occasion')
@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="glass-panel p-4">
                <h2 class="font-display text-cream mb-4">Edit Occasion</h2>
                <form action="{{ route('occasions.update', $occasion) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-gold small">Recipient Name</label>
                            <input type="text" name="recipient_name" class="form-control" value="{{ old('recipient_name', $occasion->recipient_name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-gold small">Relation</label>
                            <input type="text" name="relation_type" class="form-control" value="{{ old('relation_type', $occasion->relation_type) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-gold small">Occasion</label>
                            <input type="text" name="occasion_name" class="form-control" value="{{ old('occasion_name', $occasion->occasion_name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-gold small">Date</label>
                            <input type="date" name="occasion_date" class="form-control" value="{{ old('occasion_date', $occasion->occasion_date->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-gold small">Remind me (days before)</label>
                            <input type="number" name="reminder_days_before" class="form-control" value="{{ old('reminder_days_before', $occasion->reminder_days_before) }}" min="1" max="60">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-gold small">Preferred Bouquet</label>
                            <select name="preferred_product_id" class="form-select">
                                <option value="">— Optional —</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}" @selected(old('preferred_product_id', $occasion->preferred_product_id) == $p->id)>{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <textarea name="preferred_bouquet_notes" class="form-control" rows="3">{{ old('preferred_bouquet_notes', $occasion->preferred_bouquet_notes) }}</textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="reminder_status" value="1" @checked(old('reminder_status', $occasion->reminder_status))>
                                <label class="form-check-label text-white-50">Send reminders</label>
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-gold-solid mt-4">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
