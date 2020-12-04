<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create users & posts factory
        User::factory(15)
                ->has(
                    Post::factory()->count(rand(0, 20))
                )->create();

        $this->call(PostTagTableSeeder::class);
        $this->call(CommentTableSeeder::class);
    }
}
