<?php

namespace app\controller\v1;

use app\controller\BaseController;
use app\lib\exception\ProductException;
use app\model\Product;
use app\validate\Count;

class ProductController extends BaseController
{
    public function getRecent($count = 15)
    {
        Count::new()->goCheck();
        $products = Product::getMostRecent($count);
        if ($products->isEmpty()) {
            throw new ProductException();
        }
        return json($products);
    }
}