<?php

namespace app\service;

use app\lib\enum\OrderStatusEnum;
use app\model\Order;
use app\model\Product;
use think\Exception;
use think\facade\Db;
use think\facade\Log;

class WxNotifyService extends \WxPayNotify
{
    public function NotifyProcess($data, $config, &$msg)
    {
        if ($data['result_code'] == 'SUCCESS') {
            $orderNo = $data['out_trade_no'];
            Db::startTrans();
            try {
                $order = Order::where('order_no', '=', $orderNo)->lock(true)->find();
                if ($order->status == 1) {
                    $status = OrderService::getInstance()->checkOrderStock($order->id);
                    if ($status['pass']) {
                        $this->updateOrderStatus($order->id, true);
                        $this->reduceStock($status);
                    } else {
                        $this->updateOrderStatus($order->id, false);
                    }
                }
                Db::commit();
            } catch (Exception $e) {
                Db::rollback();
                Log::error($e);
                // 如果出现异常，向微信返回false，请求重新发送通知
                return false;
            }
        }
        return true;
    }

    private function reduceStock($status)
    {
        foreach ($status['pStatusArray'] as $singlePStatus) {
            Product::where('id', $singlePStatus['id'])->setDec('stock', $singlePStatus['count']);
        }
    }

    private function updateOrderStatus($orderID, $success)
    {
        $status = $success ? OrderStatusEnum::PAID : OrderStatusEnum::PAID_BUT_OUT_OF;
        Order::where('id', $orderID)->update(['status' => $status]);
    }
}