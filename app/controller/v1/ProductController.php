<?php

namespace app\controller\v1;

use app\controller\BaseController;
use app\lib\exception\ProductException;
use app\model\Product;
use app\validate\Count;
use app\validate\IDMustBePositiveInt;

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

    public function getOne($id)
    {
        IDMustBePositiveInt::new()->goCheck();
        $product = Product::getProductDetail($id);
        if (!$product) {
            throw new ProductException();
        }
        return json($product);
    }
}