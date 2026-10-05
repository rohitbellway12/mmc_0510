<?php

namespace Modules\ProviderManagement\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasUuid;

class ProviderQuestion extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'provider_id',
        'category_id',
        'question_text',
        'options',
        'question_type',
        'is_required',
        'is_active',
        'display_order'
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * Get the provider that owns the question
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }

    /**
     * Get the category this question belongs to
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(\Modules\CategoryManagement\Entities\Category::class, 'category_id');
    }

    /**
     * Get all answers for this question
     */
    public function answers(): HasMany
    {
        return $this->hasMany(\Modules\BookingModule\Entities\BookingQuestionAnswer::class, 'provider_question_id');
    }

    /**
     * Scope to get only active questions
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    /**
     * Scope to get only required questions
     */
    public function scopeRequired($query)
    {
        return $query->where('is_required', 1);
    }

    /**
     * Scope to get questions for a specific provider
     */
    public function scopeOfProvider($query, string $providerId)
    {
        return $query->where('provider_id', $providerId);
    }

    /**
     * Scope to get questions for a specific category (including general questions)
     */
    public function scopeForCategory($query, string $categoryId)
    {
        return $query->where(function($q) use ($categoryId) {
            $q->where('category_id', $categoryId)
              ->orWhereNull('category_id');
        });
    }

    /**
     * Scope to order by display order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order', 'asc');
    }
}
