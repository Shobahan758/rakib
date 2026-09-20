<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'parent_order_id',
        'incomplete_token',
        'name',
        'phone',
        'email',
        'address',
        'burger_type',
        'product_id',
        'quantity',
        'unit_price',
        'delivery_area',
        'delivery_charge',
        'total',
        'status',
        'risk_score',
        'risk_reasons',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'integer',
            'delivery_charge' => 'integer',
            'total' => 'integer',
            'risk_score' => 'integer',
            'risk_reasons' => 'array',
            'fake_marked_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Order $order) {
            if ($order->status === 'fake' && ! $order->fake_marked_at) {
                $order->fake_marked_at = now();
            }
        });
        static::saved(function (Order $order) {
            $deliveryFields = ['name', 'phone', 'email', 'address', 'delivery_area', 'status'];
            if ($order->parent_order_id === null && $order->wasChanged($deliveryFields)) {
                $updates = array_intersect_key($order->getAttributes(), array_flip($deliveryFields));
                if ($order->status === 'fake') $updates['fake_marked_at'] = now();
                $order->deliveryAddons()->update($updates);
            }
        });
    }

    public static function riskLabels(): array
    {
        return [
            'repeated_phone' => 'একই ফোনে repeated order',
            'repeated_ip_30_minutes' => 'একই IP থেকে ৩০ মিনিটে একাধিক order',
            'rapid_repeat_2_minutes' => 'একই ফোনে ২ মিনিটের মধ্যে repeated order',
            'changed_customer_details' => 'একই ফোনে নাম/ঠিকানা পরিবর্তন',
            'previously_fake_phone' => 'আগে Fake হওয়া ফোন',
        ];
    }

    public function deliveryAddons(): HasMany
    {
        return $this->hasMany(self::class, 'parent_order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
