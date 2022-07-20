<?php

namespace app\model;

class Product extends BaseModel
{
    protected $hidden = ['main_img_id', 'pivot', 'from', 'category_id', 'create_time', 'update_time', 'delete_time'];

    public static function getMostRecent($count)
    {
        return self::limit($count)->order('create_time desc')->select();
    }

    public static function getProductDetail($id)
    {
        return self::with([
            'imgs.img' => function($query) {
                $query->order('order', 'asc');
            },
            'properties'
        ])->find($id);
    }

    public static function getProductsByCategoryId($categoryId, $paginate = true, $page = 1, $size = 30)
    {
        $query = self::where('category_id', $categoryId);
        return $paginate ? $query->paginate(['page' => $page,'list_rows' => $size], true) : $query->select();
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