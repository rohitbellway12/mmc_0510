<?php

namespace Modules\BidModule\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\ProviderManagement\Entities\Provider;

class PostProvider extends Model
{
    protected $table = 'post_providers';

    protected $fillable = [
        'post_id',
        'provider_id',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }
}
