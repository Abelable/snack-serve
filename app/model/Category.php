<?php

namespace app\model;

class Category extends BaseModel
{
    public static function getCategories($ids)
    {
        return self::with('products.img')->select($ids);
    }

    public static function getCategory($id)
    {
        return self::with('products.img')->find($id);
    }

    public function products()
    {
        return $this->hasMany('Product', 'category_id', 'id');
    }

    public function img()
    {
        return $this->belongsTo('Image', 'topic_img_id', 'id');
    }
}