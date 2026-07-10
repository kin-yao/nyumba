<?php

namespace App\Http\Controllers;

use App\Models\MoveOutRequest;
use App\Services\AuditService;
use Illuminate\Http\Request;

class MoveOutRequestController extends Controller
{
    public function index()
    {
        $unitIds = $this->filteredUnitIds();

        $requests = MoveOutRequest::with(['tenant', 'unit.property', 'lease'])
            ->whereIn('unit_id', $unitIds)
            ->latest()
            ->get();

        $pendingCount = $requests->where('status', 'pending')->count();

        return view('move-out-requests.index', compact('requests', 'pendingCount'));
    }

    public function show(MoveOutRequest $moveOutRequest)
    {
        abort_unless($moveOutRequest->account_id === auth()->user()->account_id, 403);

        $moveOutRequest->load(['tenant', 'unit.property', 'lease']);

        if (!$moveOutRequest->read_at) {
            $moveOutRequest->update(['read_at' => now()]);
        }

        if ($moveOutRequest->status === 'pending') {
            $moveOutRequest->update(['status' => 'acknowledged']);
        }

        return view('move-out-requests.show', compact('moveOutRequest'));
    }

    public function accept(MoveOutRequest $moveOutRequest)
    {
        abort_unless($moveOutRequest->account_id === auth()->user()->account_id, 403);

        if (in_array($moveOutRequest->status, ['completed', 'cancelled'])) {
            return back()->with('error', 'This request can no longer be accepted.');
        }

        $moveOutRequest->update(['status' => 'accepted']);

        AuditService::log(
            'move_out_request.accepted',
            'Move-out accepted for ' . $moveOutRequest->tenant->full_name . ' — Unit ' . $moveOutRequest->unit->name
                . ', date: ' . $moveOutRequest->requested_move_out_date->format('d M Y'),
            $moveOutRequest,
            ['move_out_date' => $moveOutRequest->requested_move_out_date->toDateString()]
        );

        return back()->with('success', 'Move-out approved. The tenant will be automatically moved out on ' . $moveOutRequest->requested_move_out_date->format('d M Y') . '.');
    }

    public function acceptBooking(MoveOutRequest $moveOutRequest)
    {
        abort_unless($moveOutRequest->account_id === auth()->user()->account_id, 403);

        if (!$moveOutRequest->hasReferral()) {
            return back()->with('error', 'This request has no referral booking to accept.');
        }

        $moveOutRequest->update(['referral_status' => 'accepted']);

        AuditService::log(
            'move_out_request.booking_accepted',
            'Referral booking accepted for Unit ' . $moveOutRequest->unit->name
                . ' — ' . $moveOutRequest->referral_name . ' (' . $moveOutRequest->referral_phone . ')',
            $moveOutRequest,
            ['referral_name' => $moveOutRequest->referral_name, 'referral_phone' => $moveOutRequest->referral_phone]
        );

        return back()->with('success', 'Booking accepted. The unit will be held for ' . $moveOutRequest->referral_name . ' once ' . $moveOutRequest->tenant->full_name . ' moves out.');
    }

    public function declineBooking(MoveOutRequest $moveOutRequest)
    {
        abort_unless($moveOutRequest->account_id === auth()->user()->account_id, 403);

        $moveOutRequest->update(['referral_status' => 'declined']);

        AuditService::log(
            'move_out_request.booking_declined',
            'Referral booking declined for Unit ' . $moveOutRequest->unit->name,
            $moveOutRequest
        );

        return back()->with('success', 'Booking declined.');
    }

    public function updateNotes(Request $request, MoveOutRequest $moveOutRequest)
    {
        abort_unless($moveOutRequest->account_id === auth()->user()->account_id, 403);

        $validated = $request->validate([
            'landlord_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $moveOutRequest->update($validated);

        return back()->with('success', 'Notes saved.');
    }
}