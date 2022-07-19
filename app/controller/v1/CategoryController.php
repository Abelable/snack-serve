<?php

namespace app\controller\v1;

use app\controller\BaseController;
use app\lib\exception\MissException;
use app\model\Category;

class CategoryController extends BaseController
{
    public function getAllCategories()
    {
        $categories = Category::with('img')->select();
        if ($categories->isEmpty()) {
            throw new MissException([
                'msg' => '还没有任何类目',
                'errorCode' => 50000
            ]);
        }
        return json($categories);
    }
}