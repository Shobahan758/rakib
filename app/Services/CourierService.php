<?php

namespace App\Services;

use App\Models\CourierSetting;
use App\Models\Order;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class CourierService
{
    public function client(CourierSetting $setting): PendingRequest
    {
        if (! $setting->configured()) {
            throw new RuntimeException('Configure this courier in Courier Settings first.');
        }
        $credentials = $setting->credentials;
        $sandbox = ($credentials['environment'] ?? 'live') === 'sandbox';
        $client = Http::acceptJson()->asJson()->connectTimeout(10)->timeout(30);
        if ($setting->provider === 'steadfast') {
            return $client->baseUrl('https://portal.steadfast.com.bd/api/v1')->withHeaders([
                'Api-Key' => $credentials['api_key'], 'Secret-Key' => $credentials['secret_key'],
            ]);
        }
        if ($setting->provider === 'redx') {
            return $client->baseUrl($sandbox ? 'https://sandbox.redx.com.bd/v1.0.0-beta' : 'https://openapi.redx.com.bd/v1.0.0-beta')
                ->withToken($credentials['access_token']);
        }
        $client->baseUrl($sandbox ? 'https://courier-api-sandbox.pathao.com' : 'https://api-hermes.pathao.com');
        $response = $client->post('/aladdin/api/v1/issue-token', [
            'client_id' => $credentials['client_id'], 'client_secret' => $credentials['client_secret'],
            'username' => $credentials['username'], 'password' => $credentials['password'], 'grant_type' => 'password',
        ]);
        if (! $response->successful() || ! is_string($response->json('access_token'))) {
            throw new RuntimeException('Pathao authentication failed. Check your credentials and environment.');
        }

        return $client->withToken($response->json('access_token'));
    }

    public function check(CourierSetting $setting): bool
    {
        $client = $this->client($setting);
        $response = $client->get(match ($setting->provider) {
            'steadfast' => '/get_balance', 'pathao' => '/aladdin/api/v1/stores', 'redx' => '/pickup/stores',
        });

        return $response->successful() && is_array($response->json())
            && ! in_array((int) $response->json('status', 200), [400, 401, 403, 422, 500], true);
    }

    public function payload(CourierSetting $setting, Order $order, array $data, int $total): array
    {
        $phone = preg_replace('/\D/', '', $order->phone);
        if (str_starts_with($phone, '880')) {
            $phone = substr($phone, 2);
        }
        if (! preg_match('/^01[3-9][0-9]{8}$/', $phone)) {
            throw new RuntimeException('Edit the order and enter a valid Bangladesh mobile number.');
        }
        if (mb_strlen($order->name) > 100 || mb_strlen($order->address) < 10 || mb_strlen($order->address) > 250) {
            throw new RuntimeException('Customer name must be at most 100 characters and address must be 10–250 characters. Edit the order first.');
        }
        $quantity = $order->quantity + $order->deliveryAddons->sum('quantity');
        $invoice = 'FP-'.$order->id;

        return match ($setting->provider) {
            'steadfast' => ['invoice' => $invoice, 'recipient_name' => $order->name, 'recipient_phone' => $phone,
                'recipient_address' => $order->address, 'cod_amount' => $total, 'note' => $data['note'] ?? ''],
            'pathao' => ['store_id' => (int) $setting->credentials['store_id'], 'merchant_order_id' => $invoice,
                'recipient_name' => $order->name, 'recipient_phone' => $phone, 'recipient_address' => $order->address,
                'recipient_city' => (int) $data['city_id'], 'recipient_zone' => (int) $data['zone_id'],
                'recipient_area' => (int) ($data['area_id'] ?? 0), 'delivery_type' => 48, 'item_type' => 2,
                'item_quantity' => $quantity, 'item_weight' => (float) $data['weight'], 'amount_to_collect' => $total,
                'item_description' => mb_substr($order->burger_type ?? 'Furniture Polish', 0, 200), 'special_instruction' => $data['note'] ?? ''],
            'redx' => ['customer_name' => $order->name, 'customer_phone' => $phone, 'customer_address' => $order->address,
                'merchant_invoice_id' => $invoice, 'delivery_area' => $data['area_name'], 'delivery_area_id' => (int) $data['area_id'],
                'pickup_store_id' => (int) $setting->credentials['store_id'], 'cash_collection_amount' => $total,
                'parcel_weight' => (int) round((float) $data['weight'] * 1000), 'value' => max(0, $total - $order->delivery_charge),
                'instruction' => $data['note'] ?? ''],
        };
    }

    public function send(PendingRequest $client, string $provider, array $payload): Response
    {
        // Booking POSTs must never be retried automatically: the first request may have created a parcel.
        return $client->post(match ($provider) {
            'steadfast' => '/create_order', 'pathao' => '/aladdin/api/v1/orders', 'redx' => '/parcel',
        }, $payload);
    }
}
