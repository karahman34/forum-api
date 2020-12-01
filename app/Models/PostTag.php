<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostTag extends Model
{
    public $table = 'post_tags';

    protected $fillable = [
        'post_id',
        'name',
    ];
    
    /**
     * Get the post
     *
     * @return  BelongsTo
     */
    public function post()
    {
        return $this->belongsTo('App\Models\Post');
    }
}
