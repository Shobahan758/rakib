<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrackingSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public const DEMO_META_PIXEL_ID = '000000000000000';

    public const PROVIDER_FIELDS = [
        'meta' => ['meta_pixel_id'],
        'google' => ['google_analytics_id', 'google_tag_manager_id', 'google_ads_id', 'google_ads_conversion_label'],
        'tiktok' => ['tiktok_pixel_id'],
        'other' => ['clarity_id', 'linkedin_partner_id', 'pinterest_tag_id', 'snapchat_pixel_id', 'custom_head_script', 'custom_body_script'],
    ];

    public static function activeValues(): array
    {
        $settings = static::values();
        foreach (self::PROVIDER_FIELDS as $provider => $fields) {
            if (($settings[$provider.'_enabled'] ?? '1') === '0') {
                foreach ($fields as $field) unset($settings[$field]);
            }
        }
        if (($settings['meta_pixel_id'] ?? null) === self::DEMO_META_PIXEL_ID) {
            unset($settings['meta_pixel_id']);
        }
        return $settings;
    }

    public static function values(): array
    {
        return static::query()->pluck('value', 'key')->all();
    }
}
