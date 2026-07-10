<?php

namespace App\Services;

use App\Models\MoveOutRequest;

class MoveOutProcessor
{
    /**
     * Actually move the tenant out: end the lease, free (or reserve) the
     * unit, and mark the request completed. Mirrors the manual move-out
     * flow in TenantController@moveOut.
     */
    public static function process(MoveOutRequest $moveOutRequest): void
    {
        $lease = $moveOutRequest->lease;

        if (!$lease || $lease->status !== 'active') {
            // Lease already ended some other way — just close out the request.
            $moveOutRequest->update(['status' => 'completed']);
            return;
        }

        $tenant = $moveOutRequest->tenant;
        $unit   = $lease->unit;

        $lease->update([
            'move_out_date' => $moveOutRequest->requested_move_out_date,
            'status'        => 'ended',
        ]);

        $unit->update([
            'status' => $moveOutRequest->referral_status === 'accepted' ? 'reserved' : 'vacant',
        ]);

        $moveOutRequest->update(['status' => 'completed']);

        AuditService::system(
            $moveOutRequest->account_id,
            'tenant.moved_out',
            $tenant->full_name . ' moved out of Unit ' . $unit->name . ' automatically (accepted move-out date reached)'
                . ($moveOutRequest->referral_status === 'accepted' ? ' — held as reserved for ' . $moveOutRequest->referral_name : ''),
            $tenant,
            ['move_out_date' => $moveOutRequest->requested_move_out_date->toDateString(), 'automatic' => true]
        );
    }
}