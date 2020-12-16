<?php

namespace App\Jobs;

use App\Models\Post;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;

class SetLastCommentJob extends Job implements ShouldQueue
{
    public $auth;
    public $post;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $auth, Post $post)
    {
        $this->auth = $auth;
        $this->post = $post;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Decrease new notif
        $this->auth->decrement('new_notifications', 1);

        // Get last comment.
        $post_author = $this->post->author()->select('id', 'username')->first();
        $two_last_comment = $this->post->comments()
                                ->select('id', 'post_id', 'user_id', 'created_at')
                                ->with('author:id,username')
                                ->whereHas('author', function ($query) use ($post_author) {
                                    $query->where('username', '!=', $post_author->username);
                                })
                                ->orderByDesc('created_at')
                                ->limit(2)
                                ->groupBy('user_id')
                                ->get();

        if ($two_last_comment->count() === 0) {
            DB::table('notifications')
                    ->where('post_id', $this->post->id)
                    ->where('context', 'post')
                    ->where('sub_context', 'comment')
                    ->delete();
        } else {
            $last_comment = $two_last_comment[0];

            $prev_created_at = null;
            if (isset($two_last_comment[1])) {
                $prev_created_at = $two_last_comment[1]->created_at;
            }

            // Set notifications.
            dispatch(new SetNotificationJob(
                $this->post->author,
                $last_comment->author,
                $this->post,
                'post',
                'comment',
                $last_comment->created_at,
                $prev_created_at
            ));
        }
    }
}
