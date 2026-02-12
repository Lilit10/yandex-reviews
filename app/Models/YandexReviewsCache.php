<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class YandexReviewsCache extends Model
{
    protected $table = 'yandex_reviews_cache';

    protected $fillable = [
        'user_id',
        'org_id',
        'rating',
        'reviews_count',
        'company_name',
        'reviews',
        'cached_at',
    ];

    protected function casts(): array
    {
        return [
            'reviews' => 'array',
            'rating' => 'decimal:2',
            'cached_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
