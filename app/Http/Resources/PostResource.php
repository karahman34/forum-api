<?php

namespace App\Http\Resources;

use App\Models\Screenshot;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'screenshots' => $this->screenshots->map(function (Screenshot $screenshot) {
                return [
                    'original' => $screenshot->image,
                    'url' => $screenshot->getScreenshotUrl(),
                ];
            }),
            'author' => new UserResource($this->author),
        ];
    }
}
