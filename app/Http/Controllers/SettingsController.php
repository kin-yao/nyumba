<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class SettingsController extends Controller
{
    public function index()
    {
        $account = auth()->user()->account;
        $users   = User::where('account_id', $account->id)->get();

        return view('settings.index', compact('account', 'users'));
    }

    public function updateAccount(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'phone'    => ['required', 'string', 'max:20'],
            'email'    => ['nullable', 'email', 'max:255'],
            'county'   => ['nullable', 'string', 'max:100'],
            'currency' => ['required', 'in:KES,TZS,UGX,USD'],
            'logo'     => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $account = auth()->user()->account;

        if ($request->hasFile('logo')) {
            if ($account->logo_path && \Storage::disk('public')->exists($account->logo_path)) {
                \Storage::disk('public')->delete($account->logo_path);
            }
            $path = $request->file('logo')->store('logos', 'public');
            $validated['logo_path'] = $path;
        }

        unset($validated['logo']);
        $account->update($validated);

        AuditService::log(
            'settings.account_updated',
            'Business details updated — name: ' . $validated['name'] . ', currency: ' . $validated['currency'],
            null,
            ['name' => $validated['name'], 'currency' => $validated['currency']]
        );

        return redirect()->route('settings.index')
            ->with('success', 'Account details updated.');
    }

    public function updateMpesa(Request $request)
    {
        $validated = $request->validate([
            'payment_type'    => ['required', 'in:paybill,till'],
            'business_number' => ['nullable', 'string', 'max:20'],
            'account_format'  => ['nullable', 'string', 'max:100'],
            'till_number'     => ['nullable', 'string', 'max:20'],
        ]);

        $account = auth()->user()->account;
        $config  = json_decode($account->notes ?? '{}', true);

        $config['mpesa'] = [
            'payment_type'    => $validated['payment_type'],
            'business_number' => $validated['business_number'] ?? null,
            'account_format'  => $validated['account_format'] ?? null,
            'till_number'     => $validated['till_number'] ?? null,
        ];

        $account->update(['notes' => json_encode($config)]);

        AuditService::log(
            'settings.mpesa_updated',
            'M-Pesa settings updated — type: ' . $validated['payment_type'],
            null,
            ['payment_type' => $validated['payment_type']]
        );

        return redirect()->route('settings.index')
            ->with('success', 'M-Pesa settings saved.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required'],
            'password'         => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if (!Hash::check($validated['current_password'], auth()->user()->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        auth()->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        AuditService::log(
            'settings.password_changed',
            'Password changed by ' . auth()->user()->name,
            null
        );

        return redirect()->route('settings.index')
            ->with('success', 'Password updated successfully.');
    }

    public function updateInvoiceSettings(Request $request)
    {
        $validated = $request->validate([
            'invoice_send_day'     => ['required', 'integer', 'min:1', 'max:28'],
            'auto_invoice_enabled' => ['nullable', 'boolean'],
        ]);

        $enabled = $request->boolean('auto_invoice_enabled');

        auth()->user()->account->update([
            'invoice_send_day'     => $validated['invoice_send_day'],
            'auto_invoice_enabled' => $enabled,
        ]);

        AuditService::log(
            'settings.invoice_schedule_updated',
            'Invoice schedule updated — day: ' . $validated['invoice_send_day'] . ', auto: ' . ($enabled ? 'yes' : 'no'),
            null,
            ['send_day' => $validated['invoice_send_day'], 'auto_enabled' => $enabled]
        );

        return redirect()->route('settings.index')
            ->with('success', 'Invoice schedule saved.');
    }

    public function updateReportAlerts(Request $request)
    {
        $validated = $request->validate([
            'weekly_report_enabled'  => ['nullable', 'boolean'],
            'weekly_report_day'      => ['required', 'integer', 'min:0', 'max:6'],
            'weekly_report_time'     => ['required', 'string', 'regex:/^\d{2}:\d{2}$/'],
            'monthly_report_enabled' => ['nullable', 'boolean'],
            'monthly_report_day'     => ['required', 'integer', 'min:1', 'max:28'],
            'monthly_report_time'    => ['required', 'string', 'regex:/^\d{2}:\d{2}$/'],
            'yearly_report_enabled'  => ['nullable', 'boolean'],
            'yearly_report_month'    => ['required', 'integer', 'min:1', 'max:12'],
            'yearly_report_day'      => ['required', 'integer', 'min:1', 'max:28'],
            'yearly_report_time'     => ['required', 'string', 'regex:/^\d{2}:\d{2}$/'],
        ]);

        auth()->user()->account->update([
            'weekly_report_enabled'  => $request->boolean('weekly_report_enabled'),
            'weekly_report_day'      => $validated['weekly_report_day'],
            'weekly_report_time'     => $validated['weekly_report_time'],
            'monthly_report_enabled' => $request->boolean('monthly_report_enabled'),
            'monthly_report_day'     => $validated['monthly_report_day'],
            'monthly_report_time'    => $validated['monthly_report_time'],
            'yearly_report_enabled'  => $request->boolean('yearly_report_enabled'),
            'yearly_report_month'    => $validated['yearly_report_month'],
            'yearly_report_day'      => $validated['yearly_report_day'],
            'yearly_report_time'     => $validated['yearly_report_time'],
        ]);

        AuditService::log(
            'settings.report_alerts_updated',
            'Report alert settings updated',
            null
        );

        return redirect()->route('settings.index')
            ->with('success', 'Report alert settings saved.');
    }

    public function inviteUser(Request $request)
    {
        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'role'  => ['required', 'in:owner,read_only'],
        ]);

        $user = User::create([
            'account_id' => auth()->user()->account_id,
            'name'       => $validated['name'],
            'email'      => $validated['email'],
            'phone'      => $validated['phone'],
            'role'       => $validated['role'],
            'password'   => Hash::make('password123'),
        ]);

        AuditService::log(
            'user.invited',
            'User ' . $validated['name'] . ' (' . $validated['email'] . ') added with role: ' . $validated['role'],
            $user,
            ['email' => $validated['email'], 'role' => $validated['role']]
        );

        return redirect()->route('settings.index')
            ->with('success', $validated['name'] . ' has been added. Temporary password: password123');
    }

    public function removeUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot remove yourself.');
        }

        if ($user->account_id !== auth()->user()->account_id) {
            abort(403);
        }

        $name  = $user->name;
        $email = $user->email;

        AuditService::log(
            'user.removed',
            'User ' . $name . ' (' . $email . ') was removed from the account',
            null,
            ['name' => $name, 'email' => $email]
        );

        $user->delete();

        return redirect()->route('settings.index')
            ->with('success', 'User removed.');
    }

    public function resetAccount(Request $request)
    {
        $request->validate([
            'confirmation' => ['required', 'in:RESET'],
        ], [
            'confirmation.in' => 'You must type RESET exactly to confirm.',
        ]);

        $user    = auth()->user();
        $account = $user->account;

        // Owner only
        if ($user->role !== 'owner') {
            return back()->with('error', 'Only the account owner can reset the account.');
        }

        // Log permanently to system log before wiping
        \Log::warning('ACCOUNT RESET', [
            'account_id'   => $account->id,
            'account_name' => $account->name,
            'reset_by'     => $user->name,
            'email'        => $user->email,
            'ip'           => $request->ip(),
            'timestamp'    => now()->toDateTimeString(),
        ]);

        try {
            \DB::transaction(function () use ($account) {
                $accountId = $account->id;

                // Get scoped IDs first
                $propertyIds = \App\Models\Property::where('account_id', $accountId)->pluck('id');
                $unitIds     = \App\Models\Unit::whereIn('property_id', $propertyIds)->pluck('id');
                $leaseIds    = \App\Models\Lease::whereIn('unit_id', $unitIds)->pluck('id');
                $invoiceIds  = \App\Models\Invoice::whereIn('lease_id', $leaseIds)->pluck('id');
                $paymentIds  = \App\Models\Payment::where('account_id', $accountId)->pluck('id');

                // Delete in dependency order
                \App\Models\PaymentAllocation::whereIn('invoice_id', $invoiceIds)
                    ->orWhereIn('payment_id', $paymentIds)
                    ->delete();

                \App\Models\InvoiceLineItem::whereIn('invoice_id', $invoiceIds)->delete();
                \App\Models\Invoice::whereIn('id', $invoiceIds)->delete();
                \App\Models\Payment::where('account_id', $accountId)->delete();
                \App\Models\UtilityReading::where('account_id', $accountId)->delete();
                \App\Models\MaintenanceRequest::where('account_id', $accountId)->delete();
                \App\Models\Lease::whereIn('unit_id', $unitIds)->delete();

                // Force delete tenants including soft-deleted
                \App\Models\Tenant::withTrashed()
                    ->where('account_id', $accountId)
                    ->forceDelete();

                \App\Models\Unit::whereIn('property_id', $propertyIds)->delete();
                \App\Models\UtilityRate::whereIn('property_id', $propertyIds)->delete();
                \App\Models\Expense::where('account_id', $accountId)->delete();
                \App\Models\Property::where('account_id', $accountId)->delete();

                // Communications
                \App\Models\Message::where('account_id', $accountId)->delete();
                \App\Models\MessageTemplate::where('account_id', $accountId)->delete();

                // Housekeeping
                \App\Models\Notification::where('account_id', $accountId)->delete();
                \App\Models\AuditLog::where('account_id', $accountId)->delete();
            });
        } catch (\Exception $e) {
            \Log::error('Account reset failed: ' . $e->getMessage());
            return back()->with('error', 'Reset failed. No data was deleted. Error: ' . $e->getMessage());
        }

        return redirect()->route('dashboard')
            ->with('success', 'Account has been reset. All data has been cleared. You can now start fresh.');
    }
}