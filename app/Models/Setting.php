<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    protected $fillable = [
        'user_id',
        'currency',
        'locale',
        'theme',
        'pin_hash',
        'has_completed_onboarding',
        'payday_freq',
        'payday_date',
        'payday_amount',
        'payday_override',
    ];

    protected $casts = [
        'has_completed_onboarding' => 'boolean',
        'payday_amount' => 'double',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
