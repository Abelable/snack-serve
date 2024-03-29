<?php

namespace app\controller\v1;

use app\controller\BaseController;
use app\lib\exception\OrderException;
use app\middleware\CheckExclusiveScope;
use app\middleware\CheckPrimaryScope;
use app\middleware\CheckSuperScope;
use app\model\Order;
use app\service\OrderService;
use app\validate\IDMustBePositiveInt;
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
        $orderPagenite = Order::getSummaryByUser($this->uid(), $page, $size)
            ->hidden(['user_id', 'snap_items', 'snap_address', 'update_time', 'delete_time']);
        return json($orderPagenite);
    }

    public function placeOrder()
    {
        $data = OrderPlace::new()->goCheck();
        $status = OrderService::getInstance()->place($this->uid(), $data['products']);
        return json($status);
    }

    public function getDetail($id)
    {
        IDMustBePositiveInt::new()->goCheck();
        $orderDetail = Order::find($id)->hidden(['prepay_id']);
        if (!$orderDetail) {
            throw new OrderException();
        }
        return json($orderDetail);
    }

    public function getSummary($page = 1, $size = 20)
    {
        PagingParameter::new()->goCheck();
        $pagingOrders = Order::getSummaryByPage($page, $size)->hidden(['user_id', 'snap_items', 'snap_address', 'update_time', 'delete_time']);
        return json($pagingOrders);
    }

    public function delivery($id)
    {
        IDMustBePositiveInt::new()->goCheck();
        OrderService::getInstance()->delivery($id);
        return json('ok', 201);
    }
}