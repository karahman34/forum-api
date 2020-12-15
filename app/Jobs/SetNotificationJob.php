<?php

namespace App\Jobs;

use App\Events\RefreshNotificationEvent;
use App\Models\Notification;
use App\Models\Post;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;

class SetNotificationJob extends Job implements ShouldQueue
{
    public $auth;
    public $from;
    public $post;
    public $context;
    public $sub_context;
    public $new_created_at;
    public $prev_created_at;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $auth, User $from, Post $post, string $context, string $sub_context, Carbon $new_created_at = null, Carbon $prev_created_at = null)
    {
        $this->auth = $auth;
        $this->from = $from;
        $this->post = $post;
        $this->context = $context;
        $this->sub_context = $sub_context;
        $this->new_created_at = $new_created_at;
        $this->prev_created_at = $prev_created_at;
    }

    /**
     * Get notification's message.
     *
     * @return  string
     */
    private function getMessage()
    {
        $message = null;
        if ($this->context === 'post') {
            if ($this->sub_context === 'comment') {
                $comments = $this->post->comments()->with('author')
                                                    ->orderByDesc('created_at')
                                                    ->limit(3)
                                                    ->groupBy('comments.user_id', 'comments.id')
                                                    ->get();
                $last_three_usernames = $comments->pluck('author.username')->join(',');

                $message = $last_three_usernames . ' commented on your post: ' . $this->post->title;
            }
        }

        if ($this->context === 'comment') {
            //
        }

        return $message;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $message = $this->getMessage();
        $notification = Notification::where('user_id', $this->auth->id)
                                        ->where('post_id', $this->post->id)
                                        ->where('context', $this->context)
                                        ->where('sub_context', $this->sub_context)
                                        ->first();

        if (!$notification) {
            Notification::create([
                'user_id' => $this->auth->id,
                'post_id' => $this->post->id,
                'from_id' => $this->from->id,
                'context' => $this->context,
                'sub_context' => $this->sub_context,
                'message' => $message,
                'new_created_at' => Carbon::now()
            ]);
        } else {
            $new_created_at = Carbon::now();
            $prev_created_at = $notification->new_created_at;

            if (!is_null($this->new_created_at)) {
                $new_created_at = $this->new_created_at;
            }
            
            if (!is_null($this->prev_created_at)) {
                $prev_created_at = $this->prev_created_at;
            }

            $notification->update([
                'from_id' => $this->from->id,
                'message' => $message,
                'read_at' => null,
                'new_created_at' => $new_created_at,
                'prev_created_at' => $prev_created_at
            ]);
        }

        // Increase new notif.
        $this->auth->increment('new_notifications', 1);

        event(new RefreshNotificationEvent($this->auth));
    }
}
