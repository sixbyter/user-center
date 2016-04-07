<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard(); // 使模型无防备, 包括字段的 `filladble` 一定为 `true`
        $this->call(UsersTableSeeder::class);
        Model::reguard();
    }
}


class UsersTableSeeder extends Seeder
{
    public  function run(){
        App\User::truncate(); // 去除数据库中的所有记录
        factory(App\User::class, 2)->create();

    }
}