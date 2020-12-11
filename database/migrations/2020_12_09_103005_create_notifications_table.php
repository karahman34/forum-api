<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('from_id')->constrained('users')->onDelete('cascade');
            $table->char('post_id', 36);
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
            $table->string('message');
            $table->enum('context', ['post', 'comment']);
            $table->enum('sub_context', ['comment']);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('new_created_at');
            $table->timestamp('prev_created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notifications');
    }
}
