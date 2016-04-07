<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->unsignedInteger('id'); // 用户ID
            // $table->increments('id');
            // $table->string('name')->unique(); // 用户登录名
            $table->bigInteger('tel')->nullable()->unique(); // 用户手机号码
            $table->string('email')->nullable()->unique(); // 用户邮箱
            $table->string('password'); // 用户密码
            $table->rememberToken();
            $table->timestamps();
            $table->primary('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users');
    }
}
