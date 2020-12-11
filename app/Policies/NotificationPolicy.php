<?php

namespace App\Policies;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class NotificationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update the notification.
     *
     * @param  App\User  $user
     * @param  App\Notification  $notification
     * @return mixed
     */
    public function update(User $user, Notification $notification)
    {
        return $user->id === $notification->user_id;
    }

    /**
     * Determine whether the user can delete the notification.
     *
     * @param  App\User  $user
     * @param  App\Notification  $notification
     * @return mixed
     */
    public function delete(User $user, Notification $notification)
    {
        return $user->id === $notification->user_id;
    }
}
