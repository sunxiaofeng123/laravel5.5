<?php

use Illuminate\Database\Seeder;
use App\Models\User;

class FollowersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $users = User::all();
        $user  = $users->first();
        $user_id = $user->id;

        //获取出去ID为1的所有用户ID组
        $followers = $users->slice(1);
        $follower_ids = $followers->pluck('id')->toArray();

        $user->follow($follower_ids);

        //除了一号用户以外的所有用户都来关注1号用户

        foreach ($followers as $follower) {
            $follower->follow($user_id);
        }

    }
}
