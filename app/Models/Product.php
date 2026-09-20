<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    protected $fillable = ['name', 'price', 'regular_price', 'badge', 'image_path', 'fallback_image', 'is_active', 'is_modal_product', 'sort_order'];

    protected function casts(): array
    {
        return ['price' => 'integer', 'regular_price' => 'integer', 'is_active' => 'boolean', 'is_modal_product' => 'boolean', 'sort_order' => 'integer'];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function imageUrl(): string
    {
        return $this->image_path && Storage::disk('public')->exists($this->image_path)
            ? route('media.show', ['path' => $this->image_path])
            : asset($this->fallback_image ?: 'asset/images/furniture-polish-combo.webp');
    }

    public function displayRegularPrice(): ?int
    {
        if ($this->regular_price && $this->regular_price > $this->price) {
            return $this->regular_price;
        }

        if ($this->is_modal_product) {
            return null;
        }

        return [990 => 1350, 1250 => 1650, 1450 => 2000, 850 => 1080][$this->price] ?? null;
    }
}
