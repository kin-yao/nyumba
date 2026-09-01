<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AuditLog;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\InvoiceLineItem;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\Message;
use App\Models\MessageTemplate;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use App\Models\UtilityRate;
use App\Models\UtilityReading;
use App\Services\AuditService;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // ── Dashboard ──────────────────────────────────────────────────────────
    public function dashboard()
    {
        $allAccounts = Account::all();

        $totalAccounts   = $allAccounts->count();
        $activeAccounts  = $allAccounts->filter(fn($a) => $a->isActive() && !$a->isOnTrial())->count();
        $trialAccounts   = $allAccounts->filter(fn($a) => $a->isOnTrial())->count();
        $expiredAccounts = $allAccounts->filter(fn($a) => $a->isExpired())->count();
        $graceAccounts   = $allAccounts->filter(fn($a) => $a->isInGracePeriod())->count();

        $totalUnits      = Unit::withoutGlobalScopes()->count();
        $totalProperties = Property::withoutGlobalScopes()->count();
        $totalTenants    = Tenant::withoutGlobalScopes()->count();
        $totalSmsCredits = (int) $allAccounts->sum('sms_credits');

        $planPrices = ['starter' => 2300, 'growth' => 4600, 'pro' => 7500, 'enterprise' => 0];
        $totalMrr   = $allAccounts
            ->filter(fn($a) => $a->isActive() && !$a->isOnTrial() && isset($planPrices[$a->plan]))
            ->sum(fn($a) => $planPrices[$a->plan]);

        $revenueByPlan = collect(['starter', 'growth', 'pro', 'enterprise'])
            ->mapWithKeys(fn($plan) => [
                $plan => [
                    'count'   => $allAccounts->where('plan', $plan)->filter(fn($a) => $a->isActive() && !$a->isOnTrial())->count(),
                    'revenue' => $allAccounts->where('plan', $plan)->filter(fn($a) => $a->isActive() && !$a->isOnTrial())->count()
                               * ($planPrices[$plan] ?? 0),
                ]
            ])->toArray();

        $byPlan = $allAccounts->groupBy('plan')
            ->map(fn($g) => $g->count())
            ->toArray();

        $approachingLimit = $allAccounts
            ->filter(function ($account) {
                if ($account->unit_limit <= 0) return false;
                $count = $account->currentUnitCount();
                return $account->unit_limit > 0 && ($count / $account->unit_limit) >= 0.8;
            })
            ->map(fn($a) => [
                'account'    => $a,
                'used'       => $a->currentUnitCount(),
                'limit'      => $a->unit_limit,
                'percentage' => round(($a->currentUnitCount() / $a->unit_limit) * 100),
            ])
            ->sortByDesc('percentage')
            ->take(5)
            ->values();

        $recentAccounts = Account::with('users')->latest()->take(8)->get();
        $totalUsers     = User::where('is_admin', false)->count();

        // Reconciliation monitoring, platform-wide, last 24h — how much is
        // coming through each channel, how often signatures fail (a spike
        // is a security signal), how often payments actually reconcile
        // automatically vs need manual attention.
        $since24h = now()->subDay();

        $eventsByProvider = \App\Models\PaymentEvent::withoutGlobalScopes()
            ->where('received_at', '>=', $since24h)
            ->selectRaw('provider, count(*) as total')
            ->groupBy('provider')
            ->pluck('total', 'provider');

        $totalEvents24h = $eventsByProvider->sum();

        $signatureFailures24h = \App\Models\PaymentEvent::withoutGlobalScopes()
            ->where('received_at', '>=', $since24h)
            ->where('signature_valid', false)
            ->count();

        $signatureFailureRate = $totalEvents24h > 0
            ? round($signatureFailures24h / $totalEvents24h * 100, 1)
            : null;

        $reconciledCount24h = \App\Models\PaymentEvent::withoutGlobalScopes()
            ->where('received_at', '>=', $since24h)
            ->where('status', 'RECONCILED')
            ->count();

        $unmatchedCount24h = \App\Models\PaymentEvent::withoutGlobalScopes()
            ->where('received_at', '>=', $since24h)
            ->whereIn('status', ['UNMATCHED', 'REQUIRES_REVIEW'])
            ->count();

        $reconciledRate24h = $totalEvents24h > 0
            ? round($reconciledCount24h / $totalEvents24h * 100, 1)
            : null;

        return view('admin.dashboard', compact(
            'totalAccounts', 'activeAccounts', 'trialAccounts',
            'expiredAccounts', 'graceAccounts', 'totalUsers',
            'totalUnits', 'totalProperties', 'totalTenants',
            'totalSmsCredits', 'totalMrr', 'revenueByPlan', 'byPlan',
            'recentAccounts', 'approachingLimit',
            'eventsByProvider', 'totalEvents24h', 'signatureFailureRate',
            'reconciledRate24h', 'unmatchedCount24h'
        ));
    }

    // ── Accounts list ──────────────────────────────────────────────────────
    public function accounts(Request $request)
    {
        $query = Account::addSelect([
            'units_count' => Unit::withoutGlobalScopes()->selectRaw('COUNT(*)')
                ->join('properties', 'units.property_id', '=', 'properties.id')
                ->whereColumn('properties.account_id', 'accounts.id'),
        ])
        ->with('users')
        ->latest();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('plan')) {
            $query->where('plan', $request->plan);
        }

        $accounts = $query->get();

        if ($request->filled('status')) {
            $accounts = $accounts->filter(function ($account) use ($request) {
                return match($request->status) {
                    'active'  => $account->isActive() && !$account->isOnTrial(),
                    'trial'   => $account->isOnTrial(),
                    'expired' => $account->isExpired(),
                    'grace'   => $account->isInGracePeriod(),
                    default   => true,
                };
            })->values();
        }

        return view('admin.accounts', compact('accounts'));
    }

    // ── Create account ─────────────────────────────────────────────────────
    public function createAccount()
    {
        return view('admin.account-create');
    }

    public function storeAccount(Request $request)
    {
        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'phone'           => ['required', 'string', 'max:20'],
            'email'           => ['nullable', 'email', 'max:255'],
            'county'          => ['nullable', 'string', 'max:100'],
            'currency'        => ['required', 'in:KES,TZS,UGX,USD'],
            'plan'            => ['required', 'in:explore,starter,growth,pro,enterprise'],
            'plan_expires_at' => ['nullable', 'date'],
            'owner_name'      => ['required', 'string', 'max:255'],
            'owner_email'     => ['required', 'email', 'unique:users,email'],
            'owner_phone'     => ['required', 'string', 'max:20'],
            'owner_password'  => ['required', 'string', 'min:6'],
        ]);

        DB::transaction(function () use ($validated) {
            $planConfig = Account::PLANS[$validated['plan']] ?? Account::PLANS['explore'];

            $account = Account::create([
                'name'                => $validated['name'],
                'phone'               => $validated['phone'],
                'email'               => $validated['email'] ?? null,
                'county'              => $validated['county'] ?? null,
                'currency'            => $validated['currency'],
                'plan'                => $validated['plan'],
                'unit_limit'          => $planConfig['unit_limit'],
                'sms_credits'         => $planConfig['sms_credits_monthly'],
                'sms_credits_monthly' => $planConfig['sms_credits_monthly'],
                'plan_expires_at'     => $validated['plan_expires_at'] ?? null,
            ]);

            User::create([
                'account_id' => $account->id,
                'name'       => $validated['owner_name'],
                'email'      => $validated['owner_email'],
                'phone'      => $validated['owner_phone'],
                'password'   => Hash::make($validated['owner_password']),
                'role'       => 'owner',
            ]);
        });

        return redirect()->route('admin.accounts')
            ->with('success', 'Account "' . $validated['name'] . '" created successfully.');
    }

    // ── Account detail ─────────────────────────────────────────────────────
    public function showAccount(Account $account)
    {
        // withoutGlobalScopes() bypasses BelongsToAccount trait which would
        // otherwise filter all queries by the logged-in admin's own account_id
        $account->load([
            'users',
            'properties'       => fn($q) => $q->withoutGlobalScopes(),
            'properties.units' => fn($q) => $q->withoutGlobalScopes(),
        ]);

        $totalInvoiced = Invoice::withoutGlobalScopes()
            ->where('account_id', $account->id)
            ->sum('total_amount');

        $totalPaid = Payment::withoutGlobalScopes()
            ->where('account_id', $account->id)
            ->where('payment_type', '!=', 'deposit')
            ->sum('amount');

        $unitCount = $account->properties->sum(fn($p) => $p->units->count());

        // Reconciliation health — automated channels only (mpesa/bank), a
        // manually-entered cash payment is always "matched" by definition
        // and isn't a signal of whether matching is actually working.
        $since = now()->subDays(30);

        $autoMatchedRecent = Payment::withoutGlobalScopes()
            ->where('account_id', $account->id)
            ->whereIn('method', ['mpesa', 'bank'])
            ->whereNotNull('tenant_id')
            ->where('payment_date', '>=', $since)
            ->count();

        $autoUnmatchedRecent = Payment::withoutGlobalScopes()
            ->where('account_id', $account->id)
            ->whereIn('method', ['mpesa', 'bank'])
            ->whereNull('tenant_id')
            ->where('payment_date', '>=', $since)
            ->count();

        $autoTotal = $autoMatchedRecent + $autoUnmatchedRecent;
        $matchRate = $autoTotal > 0 ? round($autoMatchedRecent / $autoTotal * 100) : null;

        // All currently-unmatched payments, not just the last 30 days — an
        // old one sitting unresolved is exactly what needs surfacing.
        $unmatchedPayments = Payment::withoutGlobalScopes()
            ->where('account_id', $account->id)
            ->whereNull('tenant_id')
            ->latest('payment_date')
            ->take(10)
            ->get();

        $unmatchedCount = Payment::withoutGlobalScopes()
            ->where('account_id', $account->id)
            ->whereNull('tenant_id')
            ->count();

        $propertiesWithChannel = $account->properties->filter(fn($p) =>
            $p->hasMpesaCredentials() || !empty($p->bank_account_number) || $p->hasIpslCredentials()
        )->count();

        return view('admin.account-detail', compact(
            'account', 'totalInvoiced', 'totalPaid', 'unitCount',
            'matchRate', 'unmatchedPayments', 'unmatchedCount', 'propertiesWithChannel'
        ));
    }

    /**
     * Saves KCB account number and IPSL (Pesalink) password for a property.
     * Unlike M-Pesa, neither has a programmatic registration API — the
     * actual URL/credential exchange with the bank happens out of band
     * (email, partner request letter). This just stores what comes back
     * from that process.
     */
    public function updatePropertyBankConfig(Request $request, Account $account, Property $property)
    {
        abort_unless($property->account_id === $account->id, 404);

        $validated = $request->validate([
            'bank_code'           => ['nullable', 'in:kcb,pesalink_central'], // widen this list only as each bank actually gets built
            'bank_account_number' => ['nullable', 'string', 'max:30'],
            'ipsl_password'       => ['nullable', 'string', 'max:255'],
        ]);

        $newBankCode = $validated['bank_code'] ?? null;

        // Opting a property INTO central collection requires every one of
        // its units' references to be free platform-wide first — otherwise
        // a payment could silently land on the wrong landlord's tenant.
        if ($newBankCode === 'pesalink_central' && $property->bank_code !== 'pesalink_central') {
            foreach ($property->units as $unit) {
                $reference = $unit->payment_reference ?: $unit->name;
                $collision = \App\Services\UnitMatcher::findCollisionInCentralCollection($reference, excludePropertyId: $property->id);

                if ($collision) {
                    return redirect()->back()->with('error',
                        'Cannot enable central collection: unit "' . $unit->name . '" has the same payment reference as "'
                        . $collision->name . '" at "' . $collision->property->name . '", already on central collection. '
                        . 'Change one of the references first, then try again.'
                    );
                }
            }
        }

        $property->update([
            'bank_code'           => $validated['bank_code'] ?? null,
            'bank_account_number' => $validated['bank_account_number'] ?? null,
            'ipsl_password'       => ($validated['ipsl_password'] ?? '') ?: $property->ipsl_password,
        ]);

        AuditService::log(
            'property.bank_config_updated',
            'Bank integration config updated for "' . $property->name . '"',
            $property
        );

        return redirect()->back()->with('success', 'Bank integration details saved.');
    }

    // ── Update subscription ────────────────────────────────────────────────
    public function updateAccount(Request $request, Account $account)
    {
        $validated = $request->validate([
            'plan'                 => ['required', 'in:explore,starter,growth,pro,enterprise'],
            'plan_expires_at'      => ['nullable', 'date'],
            'grace_period_ends_at' => ['nullable', 'date'],
            'unit_limit'           => ['required', 'integer', 'min:1'],
            'sms_credits'          => ['required', 'integer', 'min:0'],
            'sms_credits_monthly'  => ['required', 'integer', 'min:0'],
        ]);

        $account->update([
            'plan'                 => $validated['plan'],
            'plan_expires_at'      => $validated['plan_expires_at'],
            'grace_period_ends_at' => $validated['grace_period_ends_at'],
            'unit_limit'           => $validated['unit_limit'],
            'sms_credits'          => $validated['sms_credits'],
            'sms_credits_monthly'  => $validated['sms_credits_monthly'],
        ]);

        Notification::create([
            'account_id' => $account->id,
            'type'       => 'subscription_updated',
            'title'      => 'Subscription updated',
            'body'       => 'Your ' . ucfirst($validated['plan']) . ' plan has been activated.'
                . ($validated['plan_expires_at']
                    ? ' Expires: ' . \Carbon\Carbon::parse($validated['plan_expires_at'])->format('d M Y') . '.'
                    : ''),
        ]);

        return redirect()->route('admin.account', $account)
            ->with('success', 'Account updated successfully.');
    }

    // ── Manual SMS top-up ──────────────────────────────────────────────────
    public function topUpSms(Request $request, Account $account)
    {
        $validated = $request->validate([
            'credits' => ['required', 'integer', 'min:1', 'max:10000'],
            'note'    => ['nullable', 'string', 'max:255'],
        ]);

        $account->increment('sms_credits', $validated['credits']);
        $newBalance = $account->fresh()->sms_credits;

        Notification::create([
            'account_id' => $account->id,
            'type'       => 'sms_credits_topped_up',
            'title'      => 'SMS credits added',
            'body'       => $validated['credits'] . ' SMS credits have been added to your account.'
                . ($validated['note'] ? ' Note: ' . $validated['note'] . '.' : '')
                . ' New balance: ' . $newBalance . ' credits.',
        ]);

        return redirect()->route('admin.account', $account)
            ->with('success', $validated['credits'] . ' SMS credits added. New balance: ' . $newBalance . '.');
    }

    // ── Impersonation ──────────────────────────────────────────────────────
    public function impersonate(Account $account)
    {
        $user = $account->users()->where('role', 'owner')->first()
             ?? $account->users()->first();

        if (!$user) {
            return back()->with('error', 'This account has no users to impersonate.');
        }

        session([
            'impersonating_account_id'   => $account->id,
            'impersonating_account_name' => $account->name,
            'impersonating_admin_id'     => auth()->id(),
        ]);

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    public function stopImpersonating()
    {
        $adminId = session('impersonating_admin_id');

        session()->forget([
            'impersonating_account_id',
            'impersonating_account_name',
            'impersonating_admin_id',
        ]);

        if ($adminId) {
            $admin = User::find($adminId);
            if ($admin) Auth::login($admin);
        }

        return redirect()->route('admin.dashboard');
    }

    // ── Broadcast SMS ──────────────────────────────────────────────────────
    public function broadcast()
    {
        $planCounts = Account::all()
            ->filter(fn($a) => $a->isActive() && $a->phone)
            ->groupBy(fn($a) => $a->isOnTrial() ? 'trial' : $a->plan)
            ->map(fn($g) => $g->count())
            ->toArray();

        $planCounts['all'] = array_sum($planCounts);

        return view('admin.broadcast', compact('planCounts'));
    }

    public function sendBroadcast(Request $request)
    {
        $validated = $request->validate([
            'message'    => ['required', 'string', 'max:320'],
            'target'     => ['required', 'in:all,explore,starter,growth,pro,enterprise,trial'],
            'test_phone' => ['nullable', 'string', 'max:20'],
        ]);

        if ($request->filled('test_phone')) {
            $testAccount = Account::first();
            if ($testAccount) {
                $sms = new SmsService($testAccount);
                $sms->send($validated['test_phone'], '[TEST] ' . $validated['message']);
            }
            return back()->with('success', 'Test SMS sent to ' . $validated['test_phone']);
        }

        $accounts = Account::all()->filter(function ($account) use ($validated) {
            if (!$account->isActive()) return false;
            if (!$account->phone) return false;
            return match($validated['target']) {
                'all'   => true,
                'trial' => $account->isOnTrial(),
                default => $account->plan === $validated['target'],
            };
        });

        $sent = $failed = 0;

        foreach ($accounts as $account) {
            try {
                $sms    = new SmsService($account);
                $result = $sms->send($account->phone, $validated['message']);
                $result['success'] ? $sent++ : $failed++;
            } catch (\Exception $e) {
                $failed++;
                \Log::error('Broadcast failed for account ' . $account->id . ': ' . $e->getMessage());
            }
        }

        AuditService::log(
            'admin.broadcast_sms',
            'Admin broadcast to ' . $sent . ' accounts (target: ' . $validated['target'] . ')',
            null,
            ['sent' => $sent, 'failed' => $failed, 'target' => $validated['target']]
        );

        return back()->with('success',
            'Broadcast complete. Sent: ' . $sent . '. Failed: ' . $failed . '.'
        );
    }

    // ── Delete account (full cascade) ──────────────────────────────────────
    public function deleteAccount(Account $account)
    {
        if ($account->isActive() && $account->plan !== 'explore') {
            return back()->with('error', 'Cannot delete an account with an active paid subscription.');
        }

        $name      = $account->name;
        $accountId = $account->id;

        \Log::warning('ACCOUNT DELETED BY ADMIN', [
            'account_id'   => $accountId,
            'account_name' => $name,
            'deleted_by'   => auth()->user()->name,
            'timestamp'    => now()->toDateTimeString(),
        ]);

        DB::transaction(function () use ($accountId, $account) {
            $propertyIds = Property::withoutGlobalScopes()->where('account_id', $accountId)->pluck('id');
            $unitIds     = Unit::withoutGlobalScopes()->whereIn('property_id', $propertyIds)->pluck('id');
            $leaseIds    = Lease::withoutGlobalScopes()->whereIn('unit_id', $unitIds)->pluck('id');
            $invoiceIds  = Invoice::withoutGlobalScopes()->whereIn('lease_id', $leaseIds)->pluck('id');
            $paymentIds  = Payment::withoutGlobalScopes()->where('account_id', $accountId)->pluck('id');

            PaymentAllocation::whereIn('invoice_id', $invoiceIds)
                ->orWhereIn('payment_id', $paymentIds)->delete();
            InvoiceLineItem::whereIn('invoice_id', $invoiceIds)->delete();
            Invoice::withoutGlobalScopes()->whereIn('id', $invoiceIds)->delete();
            Payment::withoutGlobalScopes()->where('account_id', $accountId)->delete();
            UtilityReading::withoutGlobalScopes()->where('account_id', $accountId)->delete();
            MaintenanceRequest::withoutGlobalScopes()->where('account_id', $accountId)->delete();
            Lease::withoutGlobalScopes()->whereIn('unit_id', $unitIds)->delete();
            Tenant::withoutGlobalScopes()->withTrashed()->where('account_id', $accountId)->forceDelete();
            Unit::withoutGlobalScopes()->whereIn('property_id', $propertyIds)->delete();
            UtilityRate::withoutGlobalScopes()->whereIn('property_id', $propertyIds)->delete();
            Expense::withoutGlobalScopes()->where('account_id', $accountId)->delete();
            Property::withoutGlobalScopes()->where('account_id', $accountId)->delete();
            Message::withoutGlobalScopes()->where('account_id', $accountId)->delete();
            MessageTemplate::withoutGlobalScopes()->where('account_id', $accountId)->delete();
            Notification::where('account_id', $accountId)->delete();
            AuditLog::where('account_id', $accountId)->delete();
            User::where('account_id', $accountId)->delete();
            $account->delete();
        });

        return redirect()->route('admin.accounts')
            ->with('success', 'Account "' . $name . '" and all data permanently deleted.');
    }
}