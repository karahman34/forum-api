<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    protected $fillable = [
        'user_id',
        'post_id',
        'body',
        'solution',
    ];

    /**
     * Get the post object.
     *
     * @return  BelongsTo
     */
    public function post()
    {
        return $this->belongsTo('App\Models\Post');
    }

    /**
     * Get the author of the comment.
     *
     * @return  BelongsTo
     */
    public function author()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
}
