<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\MessageTemplate;
use App\Models\Tenant;
use App\Models\Property;
use App\Models\Unit;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CommunicationController extends Controller
{
    public function index()
    {
        $propertyIds = $this->filteredPropertyIds();
        $unitIds     = $this->filteredUnitIds();

        $templates = MessageTemplate::latest()->get();

        if ($templates->isEmpty()) {
            $accountId = auth()->user()->account_id;
            $now       = now();

            $rows = array_map(function ($template) use ($accountId, $now) {
                return $template + [
                    'account_id' => $accountId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, MessageTemplate::defaults());

            MessageTemplate::insert($rows);

            $templates = MessageTemplate::latest()->get();
        }

        $tenants = Tenant::with('activeLease.unit.property')
            ->whereHas('activeLease', fn($q) => $q->whereIn('unit_id', $unitIds))
            ->get();

        $properties = Property::whereIn('id', $propertyIds)
            ->with(['units.activeLease.tenant'])
            ->get();

        $recentMessages = Message::with('tenant')
            ->latest()
            ->take(20)
            ->get();

        $totalSent   = Message::where('status', 'sent')->count()
                     + Message::where('status', 'delivered')->count();
        $totalFailed = Message::where('status', 'failed')->count();

        return view('communications.index', compact(
            'templates', 'tenants', 'properties',
            'recentMessages', 'totalSent', 'totalFailed'
        ));
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'recipient_type' => ['required', 'in:individual,property,all,overdue'],
            'tenant_id'      => ['nullable', 'exists:tenants,id'],
            'property_id'    => ['nullable', 'exists:properties,id'],
            'message'        => ['required', 'string', 'max:320'],
        ]);

        $account = auth()->user()->account;
        $sms     = new \App\Services\SmsService($account);

        // Redirect back to wherever the request came from (dashboard, communications, etc.)
        $redirectBack = url()->previous() ? redirect()->back() : redirect()->route('communications.index');

        if (!$sms->hasCredits()) {
            return $redirectBack
                ->with('error', 'Reminder not sent — no SMS credits remaining.');
        }

        $unitIds = $this->filteredUnitIds();
        $tenants = collect();

        switch ($validated['recipient_type']) {
            case 'individual':
                if (!empty($validated['tenant_id'])) {
                    $tenants = Tenant::where('id', $validated['tenant_id'])->get();
                }
                break;

            case 'property':
                if (!empty($validated['property_id'])) {
                    $property = Property::with('units.activeLease.tenant')
                        ->find($validated['property_id']);
                    if ($property) {
                        foreach ($property->units as $unit) {
                            if ($unit->activeLease?->tenant) {
                                $tenants->push($unit->activeLease->tenant);
                            }
                        }
                    }
                }
                break;

            case 'all':
                $tenants = Tenant::whereHas('activeLease', fn($q) =>
                    $q->whereIn('unit_id', $unitIds)
                )->get();
                break;

            case 'overdue':
                $tenants = Tenant::whereHas('activeLease', function ($q) use ($unitIds) {
                    $q->whereIn('unit_id', $unitIds)
                      ->whereHas('invoices', function ($q2) {
                          $q2->whereIn('status', ['overdue', 'sent', 'partial'])
                             ->where('due_date', '<', now());
                      });
                })->get();
                break;
        }

        if ($tenants->isEmpty()) {
            return $redirectBack
                ->with('error', 'Reminder not sent — no recipients found.');
        }

        if (!$sms->hasCredits($tenants->count())) {
            return $redirectBack
                ->with('error', 'Reminder not sent — insufficient SMS credits (' . $sms->remainingCredits() . ' remaining, ' . $tenants->count() . ' needed).');
        }

        $sent   = 0;
        $failed = 0;

        foreach ($tenants as $tenant) {
            if (!$tenant->phone) continue;

            $body   = $this->replacePlaceholders($validated['message'], $tenant);
            $result = $sms->send($tenant->phone, $body, $tenant->id);

            $result['success'] ? $sent++ : $failed++;
        }

        $recipientLabel = match($validated['recipient_type']) {
            'all'        => 'all tenants',
            'overdue'    => 'tenants with overdue invoices',
            'property'   => 'tenants in property #' . ($validated['property_id'] ?? ''),
            'individual' => 'individual tenant',
            default      => $validated['recipient_type'],
        };

        try {
            AuditService::log(
                'sms.sent',
                $sent . ' SMS ' . Str::plural('message', $sent) . ' sent to ' . $recipientLabel
                    . ($failed > 0 ? ' (' . $failed . ' failed)' : ''),
                null,
                [
                    'sent'           => $sent,
                    'failed'         => $failed,
                    'recipient_type' => $validated['recipient_type'],
                    'credits_left'   => $sms->remainingCredits(),
                ],
                !empty($validated['property_id']) ? (int) $validated['property_id'] : null
            );
        } catch (\Exception $e) {
            \Log::warning('Audit log failed: ' . $e->getMessage());
        }

        if ($sent === 0 && $failed > 0) {
            return $redirectBack->with('error', 'Reminder not sent — all ' . $failed . ' attempts failed.');
        }

        $message = 'Reminder sent successfully.';
        if ($failed > 0) {
            $message = $sent . ' sent, ' . $failed . ' failed.';
        }
        $message .= ' ' . $sms->remainingCredits() . ' credits remaining.';

        return $redirectBack->with('success', $message);
    }

    public function storeTemplate(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'channel'  => ['required', 'in:sms,whatsapp,both'],
            'body'     => ['required', 'string'],
        ]);

        $validated['account_id'] = auth()->user()->account_id;

        MessageTemplate::create($validated);

        AuditService::log(
            'sms.template_created',
            'SMS template "' . $validated['name'] . '" created',
            null,
            ['name' => $validated['name'], 'channel' => $validated['channel']]
        );

        return redirect()->route('communications.index')
            ->with('success', 'Template saved.');
    }

    public function updateTemplate(Request $request, MessageTemplate $messageTemplate)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'channel'  => ['required', 'in:sms,whatsapp,both'],
            'body'     => ['required', 'string'],
        ]);

        $messageTemplate->update($validated);

        AuditService::log(
            'sms.template_updated',
            'SMS template "' . $validated['name'] . '" updated',
            $messageTemplate,
            ['name' => $validated['name'], 'channel' => $validated['channel']]
        );

        return redirect()->route('communications.index')
            ->with('success', 'Template updated.')
            ->with('_panel', 'templates');
    }

    public function destroyTemplate(MessageTemplate $messageTemplate)
    {
        $name = $messageTemplate->name;

        AuditService::log(
            'sms.template_deleted',
            'SMS template "' . $name . '" deleted',
            null,
            ['name' => $name]
        );

        $messageTemplate->delete();

        return redirect()->route('communications.index')
            ->with('success', 'Template deleted.');
    }

    private function replacePlaceholders(string $message, Tenant $tenant): string
    {
        $lease    = $tenant->activeLease;
        $unit     = $lease?->unit;
        $property = $unit?->property;

        $balance = 0;
        if ($lease) {
            $lease->loadMissing(['invoices', 'payments']);
            $balance = $lease->invoices->sum('total_amount')
                     - $lease->payments->sum('amount');
        }

        return str_replace(
            ['{first_name}', '{last_name}', '{full_name}', '{balance}',
             '{unit_number}', '{property_name}', '{phone}'],
            [$tenant->first_name, $tenant->last_name, $tenant->full_name,
             number_format($balance), $unit?->name ?? '',
             $property?->name ?? '', $tenant->phone],
            $message
        );
    }

    private function formatPhone(string $phone): string
    {
        $phone = preg_replace('/\s+/', '', $phone);

        if (str_starts_with($phone, '0')) {
            return '+254' . substr($phone, 1);
        }

        if (str_starts_with($phone, '254') && !str_starts_with($phone, '+')) {
            return '+' . $phone;
        }

        return $phone;
    }
}