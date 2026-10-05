<?php

namespace Modules\BidModule\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\ServiceManagement\Entities\Service;

class PostService extends Model
{
    protected $fillable = ['post_id', 'service_id'];

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }
}
