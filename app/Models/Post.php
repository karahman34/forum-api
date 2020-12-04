<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Ramsey\Uuid\Uuid;

class Post extends Model
{
    use HasFactory;

    public $incrementing = false;
    public static $screenshots_folder = 'screenshots';

    protected $fillable = [
        'id',
        'user_id',
        'title',
        'body',
        'views',
        'solved',
    ];

    /**
     * Get the author object.
     *
     * @return  BelongsTo
     */
    public function author()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    /**
     * Get Post's screenshots.
     *
     * @return  HasMany
     */
    public function screenshots()
    {
        return $this->hasMany('App\Models\Screenshot');
    }

    /**
     * Get the Post's tags.
     *
     * @return  HasMany
     */
    public function tags()
    {
        return $this->hasMany('App\Models\PostTag');
    }

    /**
     * Get the Post's comments.
     *
     * @return  HasMany
     */
    public function comments()
    {
        return $this->hasMany('App\Models\Comment');
    }

    /**
     * Generate UUID.
     *
     * @return  string
     */
    public static function generateUuid()
    {
        $uuid1 = Uuid::uuid1();

        return $uuid1->toString();
    }
}
