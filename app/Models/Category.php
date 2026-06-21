<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Category extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'icon',
        'color',
        'type',
        'budget_limit',
        'parent_id',
        'is_default',
        'sort_order',
    ];

    protected $casts = [
        'budget_limit' => 'double',
        'is_default' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
