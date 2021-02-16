<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration
{
    /*
    user_id INT(255) NOT NULL,
    category_id INT(255) NOT NULL,

    CONSTRAINT pk_post PRIMARY KEY (id_post),
    CONSTRAINT fk_post_user FOREIGN KEY (user_id) REFERENCES users(id_user),
    CONSTRAINT fk_post_category FOREIGN KEY (category_id) REFERENCES categories(id_category)
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->text('content');
            $table->string('image');
            $table->timestamps();

            $table->unsignedInteger('user_id');
            $table->unsignedInteger('category_id');

            $table->foreign('user_id')
                ->references('id')
                ->on('users');

            $table->foreign('category_id')
                ->references('id')
                ->on('categories');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posts');
    }
}
