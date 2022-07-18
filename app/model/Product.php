<?php

namespace app\model;

class Product extends BaseModel
{
    protected $autoWriteTimestamp = 'datetime';
    protected $hidden = ['main_img_id', 'pivot', 'from', 'category_id', 'create_time', 'update_time', 'delete_time'];

    public static function getMostRecent($count)
    {
        return self::limit($count)->order('create_time desc')->select();
    }

    public function getMainImgUrlAttr($value, $data)
    {
        return $this->prefixImgUrl($value, $data);
    }

    public function imgs()
    {
        return $this->hasMany('ProductImage', 'product_id', 'id');
    }

    public function properties()
    {
        return $this->hasMany('ProductProperty', 'product_id', 'id');
    }
}