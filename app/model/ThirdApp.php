<?php

namespace app\model;

class ThirdApp extends BaseModel
{
    public static function check($appId, $appSecret)
    {
        return self::where('app_id', $appId)->where('app_secret', $appSecret)->find();
    }
}