<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Models\TenantDevice;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        // Already verified this session
        $tenantId = session('portal_tenant_id');

        if ($tenantId) {
            $tenant = Tenant::find($tenantId);
            if ($tenant && $tenant->activeLease) {
                $request->attributes->set('portalTenant', $tenant);
                return $next($request);
            }
            session()->forget('portal_tenant_id');
        }

        // Try the remembered-device cookie (skips OTP for up to 180 days).
        // Every successful use slides the expiry forward, so a tenant who logs
        // in each month never falls off the end of the window and never costs
        // another OTP.
        $deviceToken = $request->cookie('nyumba_tenant_device');

        if ($deviceToken) {
            $devices = TenantDevice::where('expires_at', '>', now())->get();

            foreach ($devices as $device) {
                if (Hash::check($deviceToken, $device->token_hash)) {
                    $tenant = Tenant::find($device->tenant_id);

                    if ($tenant && $tenant->activeLease) {
                        session(['portal_tenant_id' => $tenant->id]);
                        $device->update([
                            'last_used_at' => now(),
                            'expires_at'   => now()->addDays(180),
                        ]);
                        $request->attributes->set('portalTenant', $tenant);
                        return $next($request);
                    }
                    break;
                }
            }
        }

        return redirect()->route('portal.login');
    }
}