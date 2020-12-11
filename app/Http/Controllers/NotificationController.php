<?php

namespace App\Http\Controllers;

use App\Helpers\Transformer;
use App\Http\Resources\NotificationsCollection;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get notifications collection.
     *
     * @param   Request  $request
     *
     * @return  JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $auth = Auth::user();
            $notifications = $auth
                                ->notifications()
                                ->with(['from', 'post:id,user_id,title'])
                                ->orderByDesc('new_created_at')
                                ->paginate($request->get('limit', 8));

            return (new NotificationsCollection($notifications))
                        ->additional(array_merge(
                            Transformer::meta(true, 'Success to get notifications data.'),
                            ['new_notifications' => $auth->new_notifications],
                        ));
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to get notifications data.');
        }
    }

    public function notificationOpen()
    {
        try {
            $auth = Auth::user();
            $auth->update([
                'new_notifications' => 0
            ]);

            return Transformer::ok('Success to update notifications data.');
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to update notifications data.');
        }
    }

    /**
     * Mark notification readed.
     *
     * @param   string  $id
     *
     * @return  JsonResponse
     */
    public function markRead($id)
    {
        try {
            $notification = Notification::select('id', 'user_id')->whereId($id)->firstOrFail();

            $this->authorize('update', $notification);
            
            $notification->update([
                'read_at' => Carbon::now()
            ]);

            return Transformer::ok('Success to mark notification.');
        } catch (ModelNotFoundException $th) {
            return Transformer::modelNotFound('Notification');
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to mark notification.');
        }
    }

    /**
     * Delete Notification Object.
     *
     * @param   string  $id
     *
     * @return  JsonResponse
     */
    public function destroy($id)
    {
        try {
            $notification = Notification::select('id', 'user_id')->whereId($id)->firstOrFail();
            
            $this->authorize('delete', $notification);
            
            $notification->delete();

            return Transformer::ok('Success to delete notification.');
        } catch (ModelNotFoundException $th) {
            return Transformer::modelNotFound('Notification');
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to delete notification.');
        }
    }
}
