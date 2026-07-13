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
        $account   = auth()->user()->account;
        $users     = User::where('account_id', $account->id)->with('assignedProperties')->get();
        $properties = \App\Models\Property::where('account_id', $account->id)->orderBy('name')->get();

        return view('settings.index', compact('account', 'users', 'properties'));
    }

    public function showLogo()
    {
        $account = auth()->user()->account;

        if (!$account->logo_path || !\Storage::disk('r2')->exists($account->logo_path)) {
            abort(404);
        }

        $contents = \Storage::disk('r2')->get($account->logo_path);
        $mime     = \Storage::disk('r2')->mimeType($account->logo_path) ?? 'image/png';

        return response($contents, 200)
            ->header('Content-Type', $mime)
            ->header('Cache-Control', 'private, max-age=3600');
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
            if ($account->logo_path && \Storage::disk('r2')->exists($account->logo_path)) {
                \Storage::disk('r2')->delete($account->logo_path);
            }
            $path = $request->file('logo')->store('logos', 'r2');
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

        $user = auth()->user();

        if (!$user->firebase_uid) {
            return back()->withErrors(['current_password' => 'Password changes are not available for this account. Please contact support.']);
        }

        $firebase = app(\App\Services\FirebaseService::class);

        if (!$firebase->verifyPassword($user->email, $validated['current_password'])) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        if (!$firebase->changeUserPassword($user->firebase_uid, $validated['password'])) {
            return back()->withErrors(['current_password' => 'Could not update your password. Please try again.']);
        }

        // Keep the local hash in sync too, though Firebase is what actually
        // gates login.
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        AuditService::log(
            'settings.password_changed',
            'Password changed by ' . $user->name,
            null
        );

        return redirect()->route('settings.index')
            ->with('success', 'Password updated successfully.');
    }

    public function inviteUser(Request $request)
    {
        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', 'unique:users,email'],
            'phone'         => ['required', 'string', 'max:20'],
            'role'          => ['required', 'in:owner,manager,caretaker'],
            'property_ids'   => ['nullable', 'array'],
            'property_ids.*' => ['integer', 'exists:properties,id'],
        ]);

        $tempPassword = 'password123';

        // Create the Firebase account first — this app authenticates via Firebase,
        // so a User row without a matching firebase_uid cannot log in.
        $firebase    = app(\App\Services\FirebaseService::class);
        $firebaseUid = $firebase->createUser($validated['email'], $tempPassword, $validated['name']);

        if (!$firebaseUid) {
            return back()->withInput()->with('_panel', 'users')->withErrors([
                'email' => 'Could not create a login for this email. It may already be registered, or there was a connection issue. Please try again.',
            ]);
        }

        $user = User::create([
            'account_id'         => auth()->user()->account_id,
            'firebase_uid'       => $firebaseUid,
            'auth_provider'      => 'email',
            'name'               => $validated['name'],
            'email'              => $validated['email'],
            'phone'              => $validated['phone'],
            'role'               => $validated['role'],
            'password'           => Hash::make($tempPassword),
            'email_verified_at'  => now(),
        ]);

        $assignedProperties = [];
        if (in_array($validated['role'], ['manager', 'caretaker']) && !empty($validated['property_ids'])) {
            $properties = \App\Models\Property::where('account_id', auth()->user()->account_id)
                ->whereIn('id', $validated['property_ids'])
                ->pluck('id');
            $user->assignedProperties()->sync($properties);
            $assignedProperties = $properties->all();
        }

        AuditService::log(
            'user.invited',
            'User ' . $validated['name'] . ' (' . $validated['email'] . ') added with role: ' . $validated['role']
                . (!empty($assignedProperties) ? ', assigned to ' . count($assignedProperties) . ' ' . \Illuminate\Support\Str::plural('property', count($assignedProperties)) : ''),
            $user,
            ['email' => $validated['email'], 'role' => $validated['role'], 'property_ids' => $assignedProperties]
        );

        return redirect()->route('settings.index')
            ->with('success', $validated['name'] . ' has been added. They can sign in with email ' . $validated['email'] . ' and temporary password: ' . $tempPassword)
            ->with('_panel', 'users');
    }

    public function updateUserProperties(Request $request, User $user)
    {
        abort_unless($user->account_id === auth()->user()->account_id, 403);

        if ($user->isOwner()) {
            return back()->with('error', 'Owners always have access to every property.')->with('_panel', 'users');
        }

        $validated = $request->validate([
            'property_ids'   => ['nullable', 'array'],
            'property_ids.*' => ['integer', 'exists:properties,id'],
        ]);

        $properties = \App\Models\Property::where('account_id', auth()->user()->account_id)
            ->whereIn('id', $validated['property_ids'] ?? [])
            ->pluck('id');

        $user->assignedProperties()->sync($properties);

        AuditService::log(
            'user.properties_updated',
            'Property access updated for ' . $user->name . ' — now assigned to ' . $properties->count() . ' ' . \Illuminate\Support\Str::plural('property', $properties->count()),
            $user,
            ['property_ids' => $properties->all()]
        );

        return redirect()->route('settings.index')
            ->with('success', 'Property access updated for ' . $user->name . '.')
            ->with('_panel', 'users');
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

        if ($user->firebase_uid) {
            app(\App\Services\FirebaseService::class)->deleteUser($user->firebase_uid);
        }

        AuditService::log(
            'user.removed',
            'User ' . $name . ' (' . $email . ') was removed from the account',
            null,
            ['name' => $name, 'email' => $email]
        );

        $user->delete();

        return redirect()->route('settings.index')
            ->with('success', 'User removed.')
            ->with('_panel', 'users');
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

        if ($user->role !== 'owner') {
            return back()->with('error', 'Only the account owner can reset the account.');
        }

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

                $propertyIds = \App\Models\Property::where('account_id', $accountId)->pluck('id');
                $unitIds     = \App\Models\Unit::whereIn('property_id', $propertyIds)->pluck('id');
                $leaseIds    = \App\Models\Lease::whereIn('unit_id', $unitIds)->pluck('id');
                $invoiceIds  = \App\Models\Invoice::whereIn('lease_id', $leaseIds)->pluck('id');
                $paymentIds  = \App\Models\Payment::where('account_id', $accountId)->pluck('id');

                \App\Models\PaymentAllocation::whereIn('invoice_id', $invoiceIds)
                    ->orWhereIn('payment_id', $paymentIds)
                    ->delete();

                \App\Models\InvoiceLineItem::whereIn('invoice_id', $invoiceIds)->delete();
                \App\Models\Invoice::whereIn('id', $invoiceIds)->delete();
                \App\Models\Payment::where('account_id', $accountId)->delete();
                \App\Models\UtilityReading::where('account_id', $accountId)->delete();
                \App\Models\MaintenanceRequest::where('account_id', $accountId)->delete();
                \App\Models\Lease::whereIn('unit_id', $unitIds)->delete();

                \App\Models\Tenant::withTrashed()
                    ->where('account_id', $accountId)
                    ->forceDelete();

                \App\Models\Unit::whereIn('property_id', $propertyIds)->delete();
                \App\Models\UtilityRate::whereIn('property_id', $propertyIds)->delete();
                \App\Models\Expense::where('account_id', $accountId)->delete();
                \App\Models\Property::where('account_id', $accountId)->delete();

                \App\Models\Message::where('account_id', $accountId)->delete();
                \App\Models\MessageTemplate::where('account_id', $accountId)->delete();

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