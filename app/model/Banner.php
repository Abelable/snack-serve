<?php

namespace app\model;

class Banner extends BaseModel
{
    public static function getBannerById($id)
    {
        return self::with('items.img')->find($id);
    }

    public function items()
    {
        return $this->hasMany('BannerItem', 'banner_id', 'id');
    }
}