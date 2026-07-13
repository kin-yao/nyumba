<?php

use App\Http\Controllers\CommunicationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UtilityController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// ─── Health check — must be first, no middleware, keeps Railway container warm ─
Route::get('/health', function () {
    return response('ok', 200);
});

// ─── Tenant Portal — separate auth from the landlord/staff app ───────────────
Route::prefix('portal')->name('portal.')->group(function () {
    Route::get('/', fn() => redirect()->route('portal.login'));
    Route::get('/login', [App\Http\Controllers\Portal\AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [App\Http\Controllers\Portal\AuthController::class, 'sendOtp'])->name('login.send');
    Route::get('/verify', [App\Http\Controllers\Portal\AuthController::class, 'showVerify'])->name('verify');
    Route::post('/verify', [App\Http\Controllers\Portal\AuthController::class, 'verifyOtp'])->name('verify.submit');
    Route::post('/verify/resend', [App\Http\Controllers\Portal\AuthController::class, 'resendOtp'])->name('verify.resend');
    Route::get('/select-tenancy', [App\Http\Controllers\Portal\AuthController::class, 'showSelectTenancy'])->name('select-tenancy');
    Route::post('/select-tenancy', [App\Http\Controllers\Portal\AuthController::class, 'selectTenancy'])->name('select-tenancy.submit');
    Route::post('/logout', [App\Http\Controllers\Portal\AuthController::class, 'logout'])->name('logout');

    Route::middleware('tenant.auth')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Portal\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/payment', [App\Http\Controllers\Portal\PaymentController::class, 'index'])->name('payment');
        Route::get('/communications', [App\Http\Controllers\Portal\CommunicationController::class, 'index'])->name('communications');
        Route::post('/communications/maintenance', [App\Http\Controllers\Portal\CommunicationController::class, 'storeMaintenance'])->name('communications.maintenance');
        Route::post('/communications/move-out', [App\Http\Controllers\Portal\CommunicationController::class, 'storeMoveOut'])->name('communications.move-out');
        Route::post('/communications/move-out/{moveOutRequest}/cancel', [App\Http\Controllers\Portal\CommunicationController::class, 'cancelMoveOut'])->name('communications.move-out.cancel');
    });
});

// ─── Root ──────────────────────────────────────────────────────────────────────
Route::get('/', function () {
    if (auth()->check()) return redirect()->route('dashboard');
    return view('landing');
});

// ─── Public ────────────────────────────────────────────────────────────────────
Route::get('/terms', fn() => view('legal.terms'))->name('terms');
Route::get('/privacy', fn() => view('legal.privacy'))->name('privacy');

Route::get('/invoices/{invoice}/download', [InvoiceController::class, 'publicPdf'])
    ->name('invoices.pdf.public')
    ->middleware('signed');

// ─── M-Pesa STK callback — public, Safaricom calls this directly ──────────────
Route::post('/mpesa/stk/callback', [SubscriptionController::class, 'callback'])->name('mpesa.stk.callback');

// ─── M-Pesa C2B webhooks — public, Safaricom calls these directly ─────────
Route::post('/mpesa/c2b/{property}/confirmation', [App\Http\Controllers\MpesaC2BController::class, 'confirmation'])->name('mpesa.c2b.confirmation');
Route::post('/mpesa/c2b/{property}/validation', [App\Http\Controllers\MpesaC2BController::class, 'validation'])->name('mpesa.c2b.validation');

// ── Alias routes for Safaricom sandbox (rejects URLs containing 'mpesa') ──────
Route::post('/payments/c2b/{property}/confirmation', [App\Http\Controllers\MpesaC2BController::class, 'confirmation'])->name('payments.c2b.confirmation');
Route::post('/payments/c2b/{property}/validation', [App\Http\Controllers\MpesaC2BController::class, 'validation'])->name('payments.c2b.validation');
Route::post('/payments/pull/{property}/callback', [App\Http\Controllers\MpesaC2BController::class, 'pullCallback'])->name('payments.pull.callback');

// ─── Firebase auth endpoints ───────────────────────────────────────────────────
Route::post('/auth/verify', [App\Http\Controllers\Auth\FirebaseAuthController::class, 'verify'])
    ->name('auth.verify');
Route::post('/auth/mark-verified', [App\Http\Controllers\Auth\FirebaseAuthController::class, 'markEmailVerified'])
    ->name('auth.mark-verified');
Route::get('/auth/verified-callback', function () {
    return view('auth.verified-callback');
})->name('auth.verified-callback');

// ─── Forgot password ───────────────────────────────────────────────────────────
Route::get('/forgot-password', [App\Http\Controllers\Auth\FirebaseAuthController::class, 'showForgotPassword'])
    ->name('password.request');

// ─── Phone collection for Google signup (mid-registration) ────────────────────
Route::get('/register/phone', [App\Http\Controllers\Auth\FirebaseAuthController::class, 'showCollectPhone'])
    ->name('register.phone');
Route::post('/register/phone', [App\Http\Controllers\Auth\FirebaseAuthController::class, 'storePhone'])
    ->name('register.phone.post');

// ─── Registration steps (guests only) ─────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Volt::route('/login', 'pages.auth.login')->name('login');
    Route::get('/register/step1', [App\Http\Controllers\Auth\RegisterController::class, 'showStep1'])->name('register.step1');
    Route::get('/register/step2', [App\Http\Controllers\Auth\RegisterController::class, 'showStep2'])->name('register.step2');
});

// ─── Verify email + collect phone (auth required) ─────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/verify-email', function () {
        if (auth()->user()->email_verified_at) {
            return redirect()->route('dashboard');
        }
        return view('auth.verify-email');
    })->name('verify-email');

    Route::get('/collect-phone', [App\Http\Controllers\Auth\FirebaseAuthController::class, 'showCollectPhoneLoggedIn'])
        ->name('collect.phone');
    Route::post('/collect-phone', [App\Http\Controllers\Auth\FirebaseAuthController::class, 'storePhoneLoggedIn'])
        ->name('collect.phone.post');
});

// ─── Admin ─────────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/', [App\Http\Controllers\AdminController::class, 'dashboard'])->name('dashboard');

    // Accounts — create routes BEFORE {account} to avoid route conflicts
    Route::get('/accounts/create',                 [App\Http\Controllers\AdminController::class, 'createAccount'])->name('accounts.create');
    Route::post('/accounts',                       [App\Http\Controllers\AdminController::class, 'storeAccount'])->name('accounts.store');
    Route::get('/accounts',                        [App\Http\Controllers\AdminController::class, 'accounts'])->name('accounts');
    Route::get('/accounts/{account}',              [App\Http\Controllers\AdminController::class, 'showAccount'])->name('account');
    Route::post('/accounts/{account}/update',      [App\Http\Controllers\AdminController::class, 'updateAccount'])->name('account.update');
    Route::post('/accounts/{account}/top-up-sms',  [App\Http\Controllers\AdminController::class, 'topUpSms'])->name('account.top-up-sms');
    Route::post('/accounts/{account}/impersonate', [App\Http\Controllers\AdminController::class, 'impersonate'])->name('account.impersonate');
    Route::delete('/accounts/{account}',           [App\Http\Controllers\AdminController::class, 'deleteAccount'])->name('account.delete');
    Route::post('/accounts/{account}/properties/{property}/mpesa-register', [App\Http\Controllers\MpesaC2BController::class, 'register'])->name('account.property.mpesa-register');

    // Impersonation
    Route::post('/stop-impersonating', [App\Http\Controllers\AdminController::class, 'stopImpersonating'])->name('stop-impersonating');

    // Broadcast SMS
    Route::get('/broadcast',  [App\Http\Controllers\AdminController::class, 'broadcast'])->name('broadcast');
    Route::post('/broadcast', [App\Http\Controllers\AdminController::class, 'sendBroadcast'])->name('broadcast.send');
});

// ─── Authenticated app routes ──────────────────────────────────────────────────
Route::middleware(['auth', 'firebase.check'])->group(function () {

    Route::post('/filter/property', function (\Illuminate\Http\Request $request) {
        $propertyId = $request->input('property_id');
        if ($propertyId === 'all' || !$propertyId) {
            session()->forget('filter_property_id');
        } else {
            $accessible = auth()->user()->accessiblePropertyIds();
            if (in_array((int) $propertyId, $accessible, true)) {
                session(['filter_property_id' => $propertyId]);
            }
        }
        return redirect()->back();
    })->name('filter.property');

    Route::get('/audit', [App\Http\Controllers\AuditLogController::class, 'index'])->name('audit.index');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/subscription/expired', fn() => view('subscription.expired'))->name('subscription.expired');

    // Subscription upgrades — M-Pesa STK push
    Route::post('/subscription/upgrade', [SubscriptionController::class, 'initiate'])->name('subscription.upgrade');
    Route::get('/subscription/status/{checkoutRequestId}', [SubscriptionController::class, 'status'])->name('subscription.status');

    // Properties + Units + Import
    Route::resource('properties', PropertyController::class)->only(['index', 'store', 'show', 'destroy']);
    Route::put('/properties/{property}', [PropertyController::class, 'update'])->name('properties.update');
    Route::post('/properties/{property}/invoice-schedule', [PropertyController::class, 'updateInvoiceSchedule'])->name('properties.invoice-schedule');
    Route::post('/properties/{property}/units', [UnitController::class, 'store'])->name('units.store');
    Route::get('/properties/{property}/import/sample',   [App\Http\Controllers\ImportController::class, 'sampleCsv'])->name('properties.import.sample');
    Route::post('/properties/{property}/import/preview', [App\Http\Controllers\ImportController::class, 'preview'])->name('properties.import.preview');
    Route::post('/properties/{property}/import/store',   [App\Http\Controllers\ImportController::class, 'store'])->name('properties.import.store');

    // Tenants
    Route::resource('tenants', TenantController::class)->only(['index', 'show', 'create', 'store']);
    Route::put('/tenants/{tenant}', [TenantController::class, 'update'])->name('tenants.update');
    Route::post('/tenants/{tenant}/move-out', [TenantController::class, 'moveOut'])->name('tenants.move-out');
    Route::delete('/tenants/{tenant}', [TenantController::class, 'destroy'])->name('tenants.destroy');
    Route::post('/tenants/{id}/restore', [TenantController::class, 'restore'])->name('tenants.restore');
    Route::post('/tenants/{tenant}/transfer', [TenantController::class, 'transfer'])->name('tenants.transfer');

    // Invoices
    Route::get('/invoices/bulk', [InvoiceController::class, 'bulkCreate'])->name('invoices.bulk');
    Route::post('/invoices/bulk/preview', [InvoiceController::class, 'bulkPreview'])->name('invoices.bulk.preview');
    Route::get('/invoices/bulk/preview', [InvoiceController::class, 'bulkPreviewShow'])->name('invoices.bulk.preview.show');
    Route::post('/invoices/bulk', [InvoiceController::class, 'bulkStore'])->name('invoices.bulk.store');
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');
    Route::post('/invoices/send-all', [InvoiceController::class, 'sendAll'])->name('invoices.send-all');
    Route::resource('invoices', InvoiceController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
    Route::post('/invoices/{invoice}/send', [InvoiceController::class, 'send'])->name('invoices.send');

    // Payments
    Route::resource('payments', PaymentController::class)->only(['index', 'create', 'store']);
    Route::post('/payments/{payment}/assign', [PaymentController::class, 'assign'])->name('payments.assign');

    // Utilities
    Route::get('/utilities/charges', [UtilityController::class, 'chargesForLease'])->name('utilities.charges');
    Route::get('/utilities/rates', [UtilityController::class, 'rates'])->name('utilities.rates');
    Route::post('/utilities/rates', [UtilityController::class, 'storeRate'])->name('utilities.rates.store');
    Route::delete('/utilities/rates/{utilityRate}', [UtilityController::class, 'destroyRate'])->name('utilities.rates.destroy');
    Route::get('/utilities/readings/csv', [UtilityController::class, 'downloadReadingsCsv'])->name('utilities.readings.csv.download');
    Route::post('/utilities/readings/csv', [UtilityController::class, 'uploadReadingsCsv'])->name('utilities.readings.csv.upload');
    Route::get('/utilities', [UtilityController::class, 'index'])->name('utilities.index');
    Route::post('/utilities', [UtilityController::class, 'store'])->name('utilities.store');

    // Expenses
    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

    // Maintenance
    Route::get('/maintenance', [MaintenanceController::class, 'index'])->name('maintenance.index');
    Route::post('/maintenance', [MaintenanceController::class, 'store'])->name('maintenance.store');
    Route::patch('/maintenance/{maintenance}', [MaintenanceController::class, 'update'])->name('maintenance.update');
    Route::delete('/maintenance/{maintenance}', [MaintenanceController::class, 'destroy'])->name('maintenance.destroy');

    // Move-out requests (submitted by tenants via the portal)
    Route::get('/move-out-requests', [App\Http\Controllers\MoveOutRequestController::class, 'index'])->name('move-out-requests.index');
    Route::get('/move-out-requests/{moveOutRequest}', [App\Http\Controllers\MoveOutRequestController::class, 'show'])->name('move-out-requests.show');
    Route::post('/move-out-requests/{moveOutRequest}/accept', [App\Http\Controllers\MoveOutRequestController::class, 'accept'])->name('move-out-requests.accept');
    Route::post('/move-out-requests/{moveOutRequest}/accept-booking', [App\Http\Controllers\MoveOutRequestController::class, 'acceptBooking'])->name('move-out-requests.accept-booking');
    Route::post('/move-out-requests/{moveOutRequest}/decline-booking', [App\Http\Controllers\MoveOutRequestController::class, 'declineBooking'])->name('move-out-requests.decline-booking');
    Route::post('/move-out-requests/{moveOutRequest}/notes', [App\Http\Controllers\MoveOutRequestController::class, 'updateNotes'])->name('move-out-requests.notes');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/download', [ReportController::class, 'download'])->name('reports.download');
    Route::get('/reports/rent-roll', [ReportController::class, 'rentRoll'])->name('reports.rent-roll');
    Route::get('/reports/outstanding', [ReportController::class, 'outstanding'])->name('reports.outstanding');
    Route::get('/reports/collections', [ReportController::class, 'collections'])->name('reports.collections');
    Route::get('/reports/income-expenses', [ReportController::class, 'incomeExpenses'])->name('reports.income-expenses');
    Route::get('/reports/tenant-statement', [ReportController::class, 'tenantStatement'])->name('reports.tenant-statement');
    Route::get('/reports/occupancy', [ReportController::class, 'occupancy'])->name('reports.occupancy');
    Route::get('/reports/deposits', [ReportController::class, 'deposits'])->name('reports.deposits');
    Route::get('/reports/utilities', [ReportController::class, 'utilities'])->name('reports.utilities');

    // Communications
    Route::get('/communications', [CommunicationController::class, 'index'])->name('communications.index');
    Route::post('/communications/send', [CommunicationController::class, 'send'])->name('communications.send');
    Route::post('/communications/templates', [CommunicationController::class, 'storeTemplate'])->name('communications.templates.store');
    Route::put('/communications/templates/{messageTemplate}', [CommunicationController::class, 'updateTemplate'])->name('communications.templates.update');
    Route::delete('/communications/templates/{messageTemplate}', [CommunicationController::class, 'destroyTemplate'])->name('communications.templates.destroy');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/account', [SettingsController::class, 'updateAccount'])->name('settings.account');
    Route::post('/settings/mpesa', [SettingsController::class, 'updateMpesa'])->name('settings.mpesa');
    Route::post('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');
    Route::post('/settings/users', [SettingsController::class, 'inviteUser'])->name('settings.users.invite');
    Route::post('/settings/users/{user}/properties', [SettingsController::class, 'updateUserProperties'])->name('settings.users.properties');
    Route::delete('/settings/users/{user}', [SettingsController::class, 'removeUser'])->name('settings.users.remove');
    Route::post('/settings/reset-account', [SettingsController::class, 'resetAccount'])->name('settings.reset-account');

    // Notifications
    Route::get('/notifications', [DashboardController::class, 'notifications'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [DashboardController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [DashboardController::class, 'markAllRead'])->name('notifications.read-all');
});

require __DIR__.'/auth.php';