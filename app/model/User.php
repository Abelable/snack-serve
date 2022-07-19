<?php

namespace app\model;

class User extends BaseModel
{
    public static function getByOpenId($openid)
    {
        return self::where('openid', $openid)->find();
    }

    public function orders()
    {
        return $this->hasMany('Order', 'user_id', 'id');
    }

    public function address()
    {
        return $this->hasOne('UserAddress', 'user_id', 'id');
    }
}