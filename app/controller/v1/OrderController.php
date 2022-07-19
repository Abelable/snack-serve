<?php

namespace app\controller\v1;

use app\controller\BaseController;
use app\middleware\CheckExclusiveScope;
use app\middleware\CheckPrimaryScope;
use app\middleware\CheckSuperScope;
use app\model\Order;
use app\validate\PagingParameter;

class OrderController extends BaseController
{
    protected $middleware = [
        CheckExclusiveScope::class => ['only' => ['placeOrder']],
        CheckPrimaryScope::class => ['only' => ['getDetail', 'getSummaryByUser']],
        CheckSuperScope::class => ['only' => ['delivery', 'getSummary']]
    ];

    public function getSummaryByUser($page, $size)
    {
        PagingParameter::new()->goCheck();
        $orderPagenite = Order::getSummaryByUser($this->uid(), $page, $size)->hidden(['snap_items', 'snap_address']);
        return json($orderPagenite);
    }
}