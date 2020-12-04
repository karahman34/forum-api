<?php

namespace Database\Seeders;

use App\Models\Post;
use Illuminate\Database\Seeder;

class PostTagTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $posts = Post::select('id')->get();
        $list_tags = ['dev', 'test', 'laravel', 'javascript', 'php', 'python', 'node', 'vue', 'react', 'django', 'livewire', 'codeigniter'];

        $posts->each(function (Post $post) use ($list_tags) {
            $tags = array();
            $available_tags = $list_tags;

            for ($i=0; $i < rand(1, count($available_tags) + 1); $i++) {
                $taken_tag_index = array_rand($available_tags);
                $tag = $available_tags[$taken_tag_index];

                array_splice($available_tags, $taken_tag_index, 1);

                array_push($tags, [
                    'name' => $tag
                ]);
            }

            // Create tags
            $post->tags()->createMany($tags);
        });
    }
}
