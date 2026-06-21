<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Debt extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'type',
        'original_amount',
        'current_balance',
        'interest_rate',
        'minimum_payment',
        'due_day',
        'start_date',
        'currency',
        'lender',
        'notes',
        'is_paid_off',
        'exclude_from_balance',
    ];

    protected $casts = [
        'original_amount' => 'double',
        'current_balance' => 'double',
        'interest_rate' => 'double',
        'minimum_payment' => 'double',
        'due_day' => 'integer',
        'is_paid_off' => 'boolean',
        'exclude_from_balance' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
