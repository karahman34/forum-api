<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Notification extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'user_id',
        'from_id',
        'post_id',
        'message',
        'context',
        'sub_context',
        'read_at',
        'new_created_at',
        'prev_created_at',
    ];

    /**
     * Get user model of who's the notifications belongs to.
     *
     * @return  HasMany
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * Model User who's emit the event.
     *
     * @return  HasMany
     */
    public function from()
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * Get the attached post.
     *
     * @return  BelongsTo
     */
    public function post()
    {
        return $this->belongsTo('App\Models\Post');
    }
}
