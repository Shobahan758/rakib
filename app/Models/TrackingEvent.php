<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrackingEvent extends Model
{
    public const TYPES = [
        'page_view',
        'checkout_view',
        'order_button_click',
        'form_start',
        'order_completed',
        'whatsapp_click',
    ];

    protected $fillable = ['event_name', 'visitor_hash', 'path', 'occurred_on'];

    protected function casts(): array
    {
        return ['occurred_on' => 'date'];
    }
}
