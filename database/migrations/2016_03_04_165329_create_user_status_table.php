<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUserStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_status', function (Blueprint $table) {
            $table->unsignedInteger('user_id'); // 主键
            $table->boolean('is_blocked');
            $table->boolean('is_tel_confirmed');
            $table->boolean('is_email_confirmed');
            $table->boolean('is_openid'); // 是否拥有第三方的openid
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            // on update 在更新user_id 以外的字段会触发吗?!
            $table->primary('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('user_status');
    }
}
