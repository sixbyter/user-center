<?php

// php artisan make:migration create_login_hashes_table --create=login_hashes

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLoginHashesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 这个应该交给redis处理会比较好吧.
        Schema::create('login_hashes', function (Blueprint $table) {
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('app_id');
            $table->string('hash', 100); // 唯一
            $table->timestamp('ttl_at');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            // on update 在更新user_id 以外的字段会触发吗?!
            // $table->unique(['user_id', 'app_id']);
            $table->primary('hash');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('login_hashes');
    }
}
