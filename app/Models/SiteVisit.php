<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteVisit extends Model
{
    protected $fillable = ['visitor_hash', 'visited_on', 'path'];

    protected function casts(): array
    {
        return ['visited_on' => 'date'];
    }
}
