<?php

namespace App\Console\Commands;

use App\Models\MoveOutRequest;
use App\Services\MoveOutProcessor;
use Illuminate\Console\Command;

class ProcessMoveOuts extends Command
{
    protected $signature   = 'move-outs:process';
    protected $description = 'Automatically move out tenants whose accepted move-out date has arrived';

    public function handle()
    {
        $due = MoveOutRequest::with(['lease', 'tenant', 'unit'])
            ->where('status', 'accepted')
            ->whereDate('requested_move_out_date', '<=', now()->toDateString())
            ->get();

        if ($due->isEmpty()) {
            $this->info('No move-outs due today.');
            return;
        }

        foreach ($due as $request) {
            MoveOutProcessor::process($request);
            $this->info('Processed move-out for ' . ($request->tenant->full_name ?? 'tenant #' . $request->tenant_id) . ' — Unit ' . ($request->unit->name ?? $request->unit_id));
        }

        $this->info($due->count() . ' move-out(s) processed.');
    }
}