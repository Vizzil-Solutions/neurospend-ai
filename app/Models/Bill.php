<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bill extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'amount',
        'category',
        'frequency',
        'due_day',
        'due_month',
        'account_id',
        'is_auto_pay',
        'is_paid',
        'is_variable',
        'last_paid_date',
        'next_due_date',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'double',
        'due_day' => 'integer',
        'due_month' => 'integer',
        'is_auto_pay' => 'boolean',
        'is_paid' => 'boolean',
        'is_variable' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
