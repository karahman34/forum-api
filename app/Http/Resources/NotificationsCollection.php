<?php

namespace App\Http\Resources;

use App\Models\Notification;
use Illuminate\Http\Resources\Json\ResourceCollection;

class NotificationsCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->resource->map(function (Notification $notification) {
            return [
                'id' => $notification->id,
                'message' => $notification->message,
                'context' => $notification->context,
                'sub_context' => $notification->sub_context,
                'read_at' => $notification->read_at,
                'new_created_at' => $notification->new_created_at,
                'prev_created_at' => $notification->prev_created_at,
                'from' => new UserResource($notification->from),
                'post' => [
                    'id' => $notification->post->id,
                    'user_id' => $notification->post->user_id,
                    'title' => $notification->post->title,
                ],
            ];
        });
    }
}
