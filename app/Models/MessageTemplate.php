<?php

namespace App\Models;

use App\Models\Traits\BelongsToAccount;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageTemplate extends Model
{
    use HasFactory, BelongsToAccount;

    protected $fillable = ['account_id', 'name', 'category', 'channel', 'body'];

    public function messages()
    {
        return $this->hasMany(Message::class, 'template_id');
    }

    /**
     * Starter library seeded into every account's Templates tab the first
     * time they visit Communications with none of their own yet. All bodies
     * are written to comfortably fit a single/double SMS segment.
     */
    public static function defaults(): array
    {
        return [
            // ── Rent & Payments ──────────────────────────────────────────
            [
                'name'     => 'Rent due reminder',
                'category' => 'Rent & Payments',
                'channel'  => 'sms',
                'body'     => 'Dear {first_name}, this is a reminder that rent for Unit {unit_number}, {property_name} is due soon. Balance: KES {balance}. Kindly make payment at your earliest convenience. Thank you.',
            ],
            [
                'name'     => 'Overdue — first notice',
                'category' => 'Rent & Payments',
                'channel'  => 'sms',
                'body'     => 'Dear {first_name}, your rent for Unit {unit_number} is now overdue. Balance: KES {balance}. Please clear this as soon as possible. Thank you.',
            ],
            [
                'name'     => 'Overdue — final notice',
                'category' => 'Rent & Payments',
                'channel'  => 'sms',
                'body'     => 'Dear {first_name}, despite previous reminders, your balance of KES {balance} for Unit {unit_number} remains unpaid. Please settle immediately to avoid further action.',
            ],
            [
                'name'     => 'Payment received',
                'category' => 'Rent & Payments',
                'channel'  => 'sms',
                'body'     => "Dear {first_name}, we've received your payment for Unit {unit_number}. Thank you. Your current balance is KES {balance}.",
            ],
            [
                'name'     => 'Partial payment received',
                'category' => 'Rent & Payments',
                'channel'  => 'sms',
                'body'     => "Dear {first_name}, we've received a partial payment for Unit {unit_number}. Remaining balance: KES {balance}. Kindly clear this soon.",
            ],
            [
                'name'     => 'Rent increase notice',
                'category' => 'Rent & Payments',
                'channel'  => 'sms',
                'body'     => 'Dear {first_name}, please note that rent for Unit {unit_number} will be adjusted from the next billing cycle. Contact us for details. Thank you.',
            ],

            // ── Lease & Tenancy ──────────────────────────────────────────
            [
                'name'     => 'Welcome / move-in',
                'category' => 'Lease & Tenancy',
                'channel'  => 'sms',
                'body'     => "Dear {first_name}, welcome to {property_name}! We're glad to have you in Unit {unit_number}. Reach out anytime you need assistance. Wishing you a comfortable stay.",
            ],
            [
                'name'     => 'Lease renewal reminder',
                'category' => 'Lease & Tenancy',
                'channel'  => 'sms',
                'body'     => 'Dear {first_name}, your lease for Unit {unit_number} is approaching renewal. Kindly contact us to discuss terms at your earliest convenience.',
            ],
            [
                'name'     => 'Lease expiry notice',
                'category' => 'Lease & Tenancy',
                'channel'  => 'sms',
                'body'     => 'Dear {first_name}, your lease for Unit {unit_number} is set to expire soon. Please contact us to discuss renewal or move-out plans.',
            ],
            [
                'name'     => 'Move-out confirmation',
                'category' => 'Lease & Tenancy',
                'channel'  => 'sms',
                'body'     => 'Dear {first_name}, we acknowledge your move-out notice for Unit {unit_number}. Kindly leave the unit in good condition for inspection. Contact us with any questions.',
            ],
            [
                'name'     => 'Deposit received',
                'category' => 'Lease & Tenancy',
                'channel'  => 'sms',
                'body'     => 'Dear {first_name}, we confirm receipt of your security deposit for Unit {unit_number}, {property_name}. Welcome aboard!',
            ],
            [
                'name'     => 'Deposit refund in progress',
                'category' => 'Lease & Tenancy',
                'channel'  => 'sms',
                'body'     => "Dear {first_name}, following your move-out from Unit {unit_number}, your deposit refund is being processed. We'll notify you once complete.",
            ],

            // ── Maintenance ──────────────────────────────────────────────
            [
                'name'     => 'Request received',
                'category' => 'Maintenance',
                'channel'  => 'sms',
                'body'     => "Dear {first_name}, we've received your maintenance request for Unit {unit_number}. Our team will attend to it shortly. Thank you for reporting it.",
            ],
            [
                'name'     => 'Maintenance completed',
                'category' => 'Maintenance',
                'channel'  => 'sms',
                'body'     => 'Dear {first_name}, the issue you reported for Unit {unit_number} has been resolved. Let us know if you experience any further problems.',
            ],
            [
                'name'     => 'Scheduled access notice',
                'category' => 'Maintenance',
                'channel'  => 'sms',
                'body'     => 'Dear {first_name}, our maintenance team will need access to Unit {unit_number} on the scheduled date for repairs. We appreciate your cooperation.',
            ],
            [
                'name'     => 'Routine inspection notice',
                'category' => 'Maintenance',
                'channel'  => 'sms',
                'body'     => "Dear {first_name}, a routine inspection of Unit {unit_number} has been scheduled. We'll coordinate a suitable time with you in advance.",
            ],

            // ── Announcements ────────────────────────────────────────────
            [
                'name'     => 'Utility bill notice',
                'category' => 'Announcements',
                'channel'  => 'sms',
                'body'     => 'Dear {first_name}, utility charges for Unit {unit_number} have been added to your account. Current balance: KES {balance}. Thank you.',
            ],
            [
                'name'     => 'Water/power interruption',
                'category' => 'Announcements',
                'channel'  => 'sms',
                'body'     => 'Dear {first_name}, please note there will be a temporary interruption of water/power supply at {property_name}. We apologize for the inconvenience.',
            ],
            [
                'name'     => 'General announcement',
                'category' => 'Announcements',
                'channel'  => 'sms',
                'body'     => 'Dear {first_name}, this is a notice regarding {property_name}. Please contact the property office for more details. Thank you.',
            ],
            [
                'name'     => 'Urgent notice',
                'category' => 'Announcements',
                'channel'  => 'sms',
                'body'     => 'Dear {first_name}, this is an urgent notice regarding {property_name}. Please contact the property management office immediately.',
            ],
        ];
    }
}