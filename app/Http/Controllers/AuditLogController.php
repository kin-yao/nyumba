<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $accountId = auth()->user()->account_id;

        $query = AuditLog::with(['user', 'property'])
            ->where('account_id', $accountId)
            ->latest();

        if ($request->filled('event')) {
            $query->where('event', 'like', $request->event . '%');
        }

        if ($request->filled('user_id')) {
            if ($request->user_id === 'system') {
                $query->whereNull('user_id');
            } else {
                $query->where('user_id', $request->user_id);
            }
        }

        if ($request->filled('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $logs       = $query->paginate(50)->withQueryString();
        $users      = User::where('account_id', $accountId)->get();
        $properties = Property::where('account_id', $accountId)->orderBy('name')->get();

        $eventGroups = [
            'tenant'      => 'Tenants',
            'invoice'     => 'Invoices',
            'payment'     => 'Payments',
            'expense'     => 'Expenses',
            'maintenance' => 'Maintenance',
            'utility'     => 'Utilities',
            'unit'        => 'Units',
            'property'    => 'Properties',
            'sms'         => 'SMS',
            'settings'    => 'Settings',
            'user'        => 'Users',
        ];

        return view('audit.index', compact('logs', 'users', 'eventGroups', 'properties'));
    }
}