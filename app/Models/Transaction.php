<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'account_id',
        'bill_id',
        'amount',
        'type',
        'category',
        'subcategory',
        'description',
        'date',
        'is_recurring',
        'recurring_id',
        'tags',
        'notes',
    ];

    protected $casts = [
        'amount' => 'double',
        'is_recurring' => 'boolean',
        'tags' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }
}
