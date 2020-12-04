<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $posts = Post::select('id')->get();
        $users = User::select('id')->get();
        $user_ids = $users->pluck('id');

        $posts->each(function (Post $post) use ($user_ids) {
            $comments = collect();
            for ($i=0; $i < rand(0, 24); $i++) {
                $index = $i + 1;

                $comments->push([
                    'user_id' => $user_ids->random(),
                    'body' => "Comment ke - {$index}"
                ]);
            }

            $post->comments()->createMany($comments);

            // Solution
            if (strtolower($post->solved) === 'y') {
                $comment_solution = $post->comments()->inRandomOrder()->first();
                $comment_solution->update([
                    'solution' => 'Y'
                ]);
            }
        });
    }
}
