<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUserInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_info', function (Blueprint $table) {
            $table->unsignedInteger('user_id'); // 主键
            $table->string('nickname', 50);
            $table->string('headimage', 255); // 全路径
            $table->tinyInteger('sex', false); // 1: 男, 2: 女, 3: 未知
            $table->string('birthday_at', 10); // 生日
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
        Schema::drop('user_info');
    }
}
