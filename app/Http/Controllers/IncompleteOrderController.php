<?php

namespace App\Http\Controllers;

use App\Models\IncompleteOrder;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class IncompleteOrderController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $banglaDigits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
        $englishDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $phone = str_replace($banglaDigits, $englishDigits, (string) $request->input('phone'));
        $phone = preg_replace('/[^0-9]/', '', $phone) ?? '';
        if (str_starts_with($phone, '88') && strlen($phone) === 13) {
            $phone = substr($phone, 2);
        } elseif (strlen($phone) === 10 && str_starts_with($phone, '1')) {
            $phone = '0'.$phone;
        }
        $request->merge([
            'name' => trim((string) $request->input('name')),
            'phone' => $phone,
            'email' => strtolower(trim((string) $request->input('email'))) ?: null,
        ]);

        $data = $request->validate([
            'token' => ['required', 'uuid'],
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'phone' => ['required', 'regex:/^01[3-9][0-9]{8}$/'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:9999'],
        ]);

        return Cache::lock('checkout:'.$data['token'], 30)->block(10, function () use ($data, $request) {
            if (Order::where('incomplete_token', $data['token'])->exists()) {
                return response()->json(['saved' => false, 'completed' => true]);
            }
            IncompleteOrder::updateOrCreate(
                ['token' => $data['token']],
                [
                    'name' => trim($data['name']),
                    'phone' => $data['phone'],
                    'email' => filled($data['email'] ?? null) ? strtolower(trim($data['email'])) : null,
                    'address' => filled($data['address'] ?? null) ? trim($data['address']) : null,
                    'quantity' => $data['quantity'] ?? 1,
                    'ip_address' => $request->ip(),
                    'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
                ]
            );

            return response()->json(['saved' => true]);
        });
    }
}
