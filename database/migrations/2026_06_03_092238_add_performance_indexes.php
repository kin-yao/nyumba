<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Helper to add index only if it doesn't already exist
        $addIndex = function (string $table, array $columns, string $name) {
            try {
                Schema::table($table, function (Blueprint $t) use ($columns, $name) {
                    $t->index($columns, $name);
                });
            } catch (\Exception $e) {
                // Index already exists — skip
            }
        };

        $addIndex('units',                ['property_id'],               'idx_units_property_id');
        $addIndex('units',                ['status'],                    'idx_units_status');
        $addIndex('units',                ['property_id', 'status'],     'idx_units_property_status');

        $addIndex('leases',               ['unit_id'],                   'idx_leases_unit_id');
        $addIndex('leases',               ['tenant_id'],                 'idx_leases_tenant_id');
        $addIndex('leases',               ['status'],                    'idx_leases_status');
        $addIndex('leases',               ['unit_id', 'status'],         'idx_leases_unit_status');

        $addIndex('invoices',             ['lease_id'],                  'idx_invoices_lease_id');
        $addIndex('invoices',             ['status'],                    'idx_invoices_status');
        $addIndex('invoices',             ['due_date'],                  'idx_invoices_due_date');
        $addIndex('invoices',             ['account_id'],                'idx_invoices_account_id');
        $addIndex('invoices',             ['period_month', 'period_year'], 'idx_invoices_period');
        $addIndex('invoices',             ['lease_id', 'period_month', 'period_year'], 'idx_invoices_lease_period');

        $addIndex('payments',             ['lease_id'],                  'idx_payments_lease_id');
        $addIndex('payments',             ['tenant_id'],                 'idx_payments_tenant_id');
        $addIndex('payments',             ['payment_date'],              'idx_payments_date');
        $addIndex('payments',             ['account_id'],                'idx_payments_account_id');
        $addIndex('payments',             ['is_allocated'],              'idx_payments_allocated');

        $addIndex('expenses',             ['property_id'],               'idx_expenses_property_id');
        $addIndex('expenses',             ['account_id'],                'idx_expenses_account_id');
        $addIndex('expenses',             ['expense_date'],              'idx_expenses_date');
        $addIndex('expenses',             ['category'],                  'idx_expenses_category');

        $addIndex('maintenance_requests', ['unit_id'],                   'idx_maintenance_unit_id');
        $addIndex('maintenance_requests', ['status'],                    'idx_maintenance_status');
        $addIndex('maintenance_requests', ['priority'],                  'idx_maintenance_priority');
        $addIndex('maintenance_requests', ['account_id'],                'idx_maintenance_account_id');
        $addIndex('maintenance_requests', ['status', 'priority'],        'idx_maintenance_status_priority');

        $addIndex('utility_readings',     ['unit_id'],                   'idx_utility_unit_id');
        $addIndex('utility_readings',     ['reading_month', 'reading_year'], 'idx_utility_period');
        $addIndex('utility_readings',     ['unit_id', 'reading_month', 'reading_year'], 'idx_utility_unit_period');

        $addIndex('tenants',              ['account_id'],                'idx_tenants_account_id');

        $addIndex('audit_logs',           ['account_id'],                'idx_audit_account_id');
        $addIndex('audit_logs',           ['created_at'],                'idx_audit_created_at');
        $addIndex('audit_logs',           ['event'],                     'idx_audit_event');
    }

    public function down(): void
    {
        $indexes = [
            'units'                => ['idx_units_property_id','idx_units_status','idx_units_property_status'],
            'leases'               => ['idx_leases_unit_id','idx_leases_tenant_id','idx_leases_status','idx_leases_unit_status'],
            'invoices'             => ['idx_invoices_lease_id','idx_invoices_status','idx_invoices_due_date','idx_invoices_account_id','idx_invoices_period','idx_invoices_lease_period'],
            'payments'             => ['idx_payments_lease_id','idx_payments_tenant_id','idx_payments_date','idx_payments_account_id','idx_payments_allocated'],
            'expenses'             => ['idx_expenses_property_id','idx_expenses_account_id','idx_expenses_date','idx_expenses_category'],
            'maintenance_requests' => ['idx_maintenance_unit_id','idx_maintenance_status','idx_maintenance_priority','idx_maintenance_account_id','idx_maintenance_status_priority'],
            'utility_readings'     => ['idx_utility_unit_id','idx_utility_period','idx_utility_unit_period'],
            'tenants'              => ['idx_tenants_account_id'],
            'audit_logs'           => ['idx_audit_account_id','idx_audit_created_at','idx_audit_event'],
        ];

        foreach ($indexes as $table => $names) {
            foreach ($names as $name) {
                try {
                    Schema::table($table, fn(Blueprint $t) => $t->dropIndex($name));
                } catch (\Exception $e) {}
            }
        }
    }
};