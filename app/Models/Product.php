<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = ['name', 'price', 'badge', 'image_path', 'fallback_image', 'is_active', 'is_modal_product', 'sort_order'];

    protected function casts(): array
    {
        return ['price' => 'integer', 'is_active' => 'boolean', 'is_modal_product' => 'boolean', 'sort_order' => 'integer'];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function imageUrl(): string
    {
        return $this->image_path
            ? route('media.show', ['path' => $this->image_path])
            : asset($this->fallback_image ?: 'asset/images/furniture-polish-combo.png');
    }
}
