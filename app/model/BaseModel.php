<?php

namespace app\model;

use think\facade\Config;
use think\Model;
use think\model\concern\SoftDelete;

class BaseModel extends Model
{
    use SoftDelete;

    protected $autoWriteTimestamp = true;
    protected $hidden = ['create_time', 'update_time', 'delete_time'];

    public static function new()
    {
        return new static();
    }

    protected function prefixImgUrl($value, $data)
    {
        if ($data['from'] == 1) {
            return Config::get('setting.img_prefix') . $value;
        }
        return $value;
    }
}