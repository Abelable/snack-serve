<?php

namespace app\model;

class ProductImage extends BaseModel
{
    protected $hidden = ['img_id', 'product_id', 'create_time', 'update_time', 'delete_time'];

    public function img()
    {
        return $this->belongsTo('Image', 'img_id', 'id');
    }
}