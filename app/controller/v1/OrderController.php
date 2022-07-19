<?php

namespace app\controller\v1;

use app\controller\BaseController;
use app\middleware\CheckExclusiveScope;
use app\middleware\CheckPrimaryScope;
use app\middleware\CheckSuperScope;
use app\model\Order;
use app\service\OrderService;
use app\validate\OrderPlace;
use app\validate\PagingParameter;

class OrderController extends BaseController
{
    protected $middleware = [
        CheckExclusiveScope::class => ['only' => ['placeOrder']],
        CheckPrimaryScope::class => ['only' => ['getDetail', 'getSummaryByUser']],
        CheckSuperScope::class => ['only' => ['delivery', 'getSummary']]
    ];

    public function getSummaryByUser($page = 1, $size = 15)
    {
        PagingParameter::new()->goCheck();
        $orderPagenite = Order::getSummaryByUser($this->uid(), $page, $size)->hidden(['snap_items', 'snap_address']);
        return json($orderPagenite);
    }

    public function placeOrder()
    {
        $data = OrderPlace::new()->goCheck();
        $status = OrderService::getInstance()->place($this->uid(), $data['products']);
        return json($status);
    }
}