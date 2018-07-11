<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\ResetPassword;
use Auth;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    //获取头像
    public function gravatar($size = 100)
    {
        $hash = md5(strtolower(trim($this->attributes['email'])));
        return "http://www.gravatar.com/avatar/$hash?s=$size";
    }

    public static function boot()
    {
        parent::boot();
        //监听用户创建时前，加入token
        static::creating(function($user){
            $user->activation_token = str_random(30);
        });
    }

    //发送重置密码邮件
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    //一个用户对应多个微博
    public function statuses()
    {
        return $this->hasMany(Status::class);
    }

    public function feed()
    {
        $user_ids = Auth::user()->followings->pluck('id')->toArray();
        array_push($user_ids, Auth::user()->id);

        return Status::whereIn('user_id', $user_ids)
                               ->with('user')
                               ->orderBy('created_at', 'desc');

    }

    //获取粉丝列表
    public function followers()
    {
        return $this->belongsToMany(User::class, 'followers', 'user_id', 'follower_id');
    }

    //获取关注列表
    public function followings()
    {
        return $this->belongsToMany(User::class, 'followers', 'follower_id', 'user_id');
    }

    //关注
    public function follow($user_ids)
    {
        if (!is_array($user_ids)) {
            $user_ids = compact('user_ids');
        }

        $this->followings()->sync($user_ids, false);
    }

    //取消关注
    public function unfollow($user_ids)
    {
        if(!is_array($user_ids)) {
            $user_ids = compact('user_ids');
        }

        $this->followings()->detach($user_ids);
    }

    //是否已经关注
    public function isFollowing($user_id)
    {
        return $this->followings->contains($user_id);
    }
}
