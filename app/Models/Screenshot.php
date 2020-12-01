<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Screenshot extends Model
{
    protected $fillable = [
        'post_id',
        'image'
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

    public function getScreenshotUrl()
    {
        return env('APP_URL') . '/' . $this->image;
    }
}
