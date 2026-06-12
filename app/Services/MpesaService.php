<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MpesaService
{
    private string $consumerKey;
    private string $consumerSecret;
    private string $shortcode;
    private string $passkey;
    private string $env;
    private string $baseUrl;

    public function __construct()
    {
        $this->consumerKey    = config('services.mpesa.consumer_key');
        $this->consumerSecret = config('services.mpesa.consumer_secret');
        $this->shortcode      = config('services.mpesa.shortcode');
        $this->passkey        = config('services.mpesa.passkey');
        $this->env            = config('services.mpesa.env', 'sandbox');

        $this->baseUrl = $this->env === 'production'
            ? 'https://api.safaricom.co.ke'
            : 'https://sandbox.safaricom.co.ke';
    }

    /**
     * Get a cached OAuth access token (valid ~1hr, we cache for 55min).
     */
    public function getAccessToken(): ?string
    {
        return Cache::remember('mpesa_access_token', now()->addMinutes(55), function () {
            $response = Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
                ->get($this->baseUrl . '/oauth/v1/generate', [
                    'grant_type' => 'client_credentials',
                ]);

            if ($response->failed()) {
                Log::error('M-Pesa: failed to get access token', ['response' => $response->body()]);
                return null;
            }

            return $response->json('access_token');
        });
    }

    /**
     * Initiate an STK Push (Lipa Na M-Pesa Online).
     *
     * @param string $phone        Format: 2547XXXXXXXX or 2541XXXXXXXX
     * @param float  $amount        Amount in KES (whole number)
     * @param string $accountRef    Shows on the STK prompt as "Account Reference" (max 12 chars)
     * @param string $description   Transaction description (max 13 chars recommended)
     * @param string $callbackUrl   Full URL Safaricom will POST the result to
     *
     * @return array ['success' => bool, 'checkout_request_id' => ?string, 'merchant_request_id' => ?string, 'error' => ?string]
     */
    public function stkPush(
        string $phone,
        float $amount,
        string $accountRef,
        string $description,
        string $callbackUrl
    ): array {
        $token = $this->getAccessToken();

        if (!$token) {
            return ['success' => false, 'error' => 'Could not authenticate with M-Pesa. Please try again.'];
        }

        $timestamp = now()->format('YmdHis');
        $password  = base64_encode($this->shortcode . $this->passkey . $timestamp);

        $payload = [
            'BusinessShortCode' => $this->shortcode,
            'Password'          => $password,
            'Timestamp'         => $timestamp,
            'TransactionType'   => 'CustomerPayBillOnline',
            'Amount'            => (int) round($amount),
            'PartyA'            => $phone,
            'PartyB'            => $this->shortcode,
            'PhoneNumber'       => $phone,
            'CallBackURL'       => $callbackUrl,
            'AccountReference'  => substr($accountRef, 0, 12),
            'TransactionDesc'   => substr($description, 0, 13),
        ];

        $response = Http::withToken($token)
            ->post($this->baseUrl . '/mpesa/stkpush/v1/processrequest', $payload);

        $data = $response->json();

        if ($response->failed() || ($data['ResponseCode'] ?? null) !== '0') {
            Log::error('M-Pesa STK push failed', ['response' => $data]);
            return [
                'success' => false,
                'error'   => $data['errorMessage'] ?? $data['ResponseDescription'] ?? 'STK push could not be initiated. Please try again.',
            ];
        }

        return [
            'success'             => true,
            'checkout_request_id' => $data['CheckoutRequestID'] ?? null,
            'merchant_request_id' => $data['MerchantRequestID'] ?? null,
        ];
    }

    /**
     * Query the status of an STK push (fallback if callback is delayed).
     */
    public function stkQuery(string $checkoutRequestId): array
    {
        $token = $this->getAccessToken();

        if (!$token) {
            return ['success' => false, 'error' => 'Could not authenticate with M-Pesa.'];
        }

        $timestamp = now()->format('YmdHis');
        $password  = base64_encode($this->shortcode . $this->passkey . $timestamp);

        $response = Http::withToken($token)
            ->post($this->baseUrl . '/mpesa/stkpushquery/v1/query', [
                'BusinessShortCode' => $this->shortcode,
                'Password'          => $password,
                'Timestamp'         => $timestamp,
                'CheckoutRequestID' => $checkoutRequestId,
            ]);

        $data = $response->json();

        return [
            'success'      => true,
            'result_code'  => $data['ResultCode'] ?? null,
            'result_desc'  => $data['ResultDesc'] ?? null,
            'raw'          => $data,
        ];
    }

    /**
     * Normalize a Kenyan phone number to 2547XXXXXXXX / 2541XXXXXXXX format
     * required by M-Pesa API (no leading +, no leading 0).
     */
    public function formatPhone(string $phone): string
    {
        $phone = preg_replace('/\s+/', '', trim($phone));

        if (str_starts_with($phone, '+254')) {
            return substr($phone, 1);
        }

        if (str_starts_with($phone, '0')) {
            return '254' . substr($phone, 1);
        }

        if (str_starts_with($phone, '254')) {
            return $phone;
        }

        // 7XXXXXXXX or 1XXXXXXXX (9 digits, no prefix)
        if (preg_match('/^[71][0-9]{8}$/', $phone)) {
            return '254' . $phone;
        }

        return $phone;
    }
}