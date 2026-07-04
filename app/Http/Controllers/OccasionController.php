<?php

namespace App\Http\Controllers;

use App\Models\Occasion;
use App\Models\Product;
use App\Services\OccasionReminderService;
use App\Support\ThemeHelper;
use Illuminate\Http\Request;

class OccasionController extends Controller
{
    public function __construct(private OccasionReminderService $reminders)
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $occasions = auth()->user()->occasions()
            ->with('preferredProduct')
            ->orderBy('occasion_date')
            ->get()
            ->sortBy(fn (Occasion $o) => $o->nextOccurrence())
            ->values();

        $upcoming = $this->reminders->upcomingForUser(auth()->user());

        return view(ThemeHelper::view('occasions.index'), compact('occasions', 'upcoming'));
    }

    public function create()
    {
        $products = Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'price']);

        return view(ThemeHelper::view('occasions.create'), compact('products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'recipient_name' => 'required|string|max:120',
            'relation_type' => 'required|string|max:80',
            'occasion_name' => 'required|string|max:120',
            'occasion_date' => 'required|date',
            'reminder_status' => 'boolean',
            'reminder_days_before' => 'nullable|integer|min:1|max:60',
            'preferred_product_id' => 'nullable|exists:products,id',
            'preferred_bouquet_notes' => 'nullable|string|max:1000',
        ]);

        $data['reminder_status'] = $request->boolean('reminder_status', true);
        $data['reminder_days_before'] = $data['reminder_days_before'] ?? 7;

        auth()->user()->occasions()->create($data);

        return redirect()->route('occasions.index')->with('success', 'Occasion saved to your calendar.');
    }

    public function edit(Occasion $occasion)
    {
        $this->authorizeOccasion($occasion);
        $products = Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'price']);

        return view(ThemeHelper::view('occasions.edit'), compact('occasion', 'products'));
    }

    public function update(Request $request, Occasion $occasion)
    {
        $this->authorizeOccasion($occasion);

        $data = $request->validate([
            'recipient_name' => 'required|string|max:120',
            'relation_type' => 'required|string|max:80',
            'occasion_name' => 'required|string|max:120',
            'occasion_date' => 'required|date',
            'reminder_status' => 'boolean',
            'reminder_days_before' => 'nullable|integer|min:1|max:60',
            'preferred_product_id' => 'nullable|exists:products,id',
            'preferred_bouquet_notes' => 'nullable|string|max:1000',
        ]);

        $data['reminder_status'] = $request->boolean('reminder_status', true);

        $occasion->update($data);

        return redirect()->route('occasions.index')->with('success', 'Occasion updated.');
    }

    public function destroy(Occasion $occasion)
    {
        $this->authorizeOccasion($occasion);
        $occasion->delete();

        return redirect()->route('occasions.index')->with('success', 'Occasion removed.');
    }

    private function authorizeOccasion(Occasion $occasion): void
    {
        abort_unless($occasion->user_id === auth()->id(), 403);
    }
}
