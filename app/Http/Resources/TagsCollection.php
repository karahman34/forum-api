<?php

namespace App\Http\Resources;

use App\Models\PostTag;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TagsCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->resource->map(function (PostTag $postTag) {
            return $postTag->name;
        });
    }
}
