<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    //黑名单 允许所有数据插入数据库
    protected $guarded = [];

    /**
     * 返回某个用户的历史消息
     * @author Kalvin
     * @date   2020-08-07
     *
     * @return [type]
     */
    public function user(){
    	return $this->belongsTo('App\User');
    }
}
