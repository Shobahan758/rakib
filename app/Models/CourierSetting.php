<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourierSetting extends Model
{
    protected $fillable = ['provider', 'credentials'];

    protected $hidden = ['credentials'];

    public const FIELDS = [
        'steadfast' => ['api_key' => 'API Key', 'secret_key' => 'Secret Key'],
        'pathao' => ['client_id' => 'Client ID', 'client_secret' => 'Client Secret', 'username' => 'Merchant email', 'password' => 'Merchant password', 'store_id' => 'Pickup Store ID'],
        'redx' => ['access_token' => 'API Access Token', 'store_id' => 'Pickup Store ID'],
    ];

    protected function casts(): array
    {
        return ['credentials' => 'encrypted:array'];
    }

    public function configured(): bool
    {
        foreach (array_keys(self::FIELDS[$this->provider]) as $field) {
            if (blank($this->credentials[$field] ?? null)) {
                return false;
            }
        }

        return true;
    }
}
