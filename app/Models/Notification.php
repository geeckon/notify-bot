<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Notification extends Model
{
    /**
     * The attributes that should be cast.
     *
     * @var array<string>
     */
    protected $casts = [
        'notify_at' => 'date',
    ];
}
