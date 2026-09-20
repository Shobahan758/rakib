<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncompleteOrder extends Model
{
    protected $fillable = [
        'token', 'name', 'phone', 'email', 'address', 'quantity',
        'ip_address', 'user_agent',
    ];

    protected function casts(): array
    {
        return ['quantity' => 'integer'];
    }
}
