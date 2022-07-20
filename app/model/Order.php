<?php

namespace app\model;

class Order extends BaseModel
{
    protected $hidden = ['user_id', 'update_time', 'delete_time'];

    public static function getSummaryByUser($uid, $page = 1, $size = 15)
    {
        return self::where('user_id', $uid)
            ->order('create_time desc')
            ->paginate(['page' => $page, 'list_rows' => $size], true);
    }

    public static function getSummaryByPage($page = 1, $size = 15)
    {
        return self::order('create_time desc')
            ->paginate(['page' => $page, 'list_rows' => $size], true);
    }

    public function products()
    {
        return $this->belongsToMany('Product', 'order_product', 'product_id', 'order_id');
    }

    public function getSnapItemsAttr($value)
    {
        return $value ? json_decode($value) : null;
    }

    public function getSnapAddressAttr($value){
        return $value ? json_decode($value) : null;
    }
}