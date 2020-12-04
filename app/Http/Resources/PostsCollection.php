<?php

namespace App\Http\Resources;

use App\Models\Post;
use App\Models\Screenshot;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PostsCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->resource->map(function (Post $post) {
            return [
                'id' => $post->id,
                'title' => $post->title,
                'views' => $post->views,
                'solved' => $post->solved,
                'comments_count' => $post->comments_count,
                'created_at' => $post->created_at,
                'updated_at' => $post->updated_at,
                'tags' => $post->tags->map(function ($tag) {
                    return $tag->name;
                }),
                'screenshots' => $post->screenshots->map(function (Screenshot $screenshot) {
                    return [
                        'original' => $screenshot->image,
                        'url' => $screenshot->getScreenshotUrl(),
                    ];
                }),
                'author' => new UserResource($post->author),
            ];
        });
    }
}
