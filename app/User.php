<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

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

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    
    /**
     * 生成用户头像
     * @Author Kalvin
     * @Date   2020-08-05
     *
     * @return string
     */
    public function avatar(){
        //A Gravatar is a Globally Recognized Avatar. 
        //You upload it and create your profile just once, and then when you participate in any Gravatar-enabled
        // site, your Gravatar image will automatically follow you there.
        //引入随机生成头像
        return "https://www.gravatar.com/avatar/" . md5( strtolower( trim( $this->email ) ) ) . "?d=retro" . "&s=64";

    }


    /**
     * 返回历史消息
     * @author Kalvin
     * @date   2020-08-07
     *
     * @return array
     */
    public function messages(){
        return $this->hasMany('App\Message');
    }
}
