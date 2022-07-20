<?php

namespace app\service;

use app\lib\exception\OrderException;
use app\lib\exception\TokenException;
use app\model\Order;
use think\Exception;
use think\facade\Config;

class PayService extends BaseService
{
    public function pay($orderId)
    {
        $orderNo = $this->checkOrderValid($orderId);
        $status = OrderService::getInstance()->checkOrderStock($orderId);
        if (!$status['pass']) {
            return $status;
        }
        return $this->makeWxPreOrder($orderId, $orderNo, $status['orderPrice']);
    }

    private function checkOrderValid($orderId)
    {
        $order = Order::find($orderId);
        if (!$order) {
            throw new OrderException();
        }
        if (!TokenService::isValidOperate($order->user_id)) {
            throw new TokenException([
                'msg' => '订单与用户不匹配',
                'errorCode' => 10003
            ]);
        }
        if ($order->status != 1) {
            throw new OrderException([
                'msg' => '订单已支付过啦',
                'errorCode' => 80003,
                'code' => 400
            ]);
        }
        return $order->order_no;
    }

    private function makeWxPreOrder($orderId, $orderNo, $totalPrice)
    {
        $openid = TokenService::getCurrentTokenVar('openid');
        if (!$openid) {
            throw new TokenException();
        }

        $wxOrderData = new \WxPayUnifiedOrder();
        $wxOrderData->SetOut_trade_no($orderNo);
        $wxOrderData->SetTrade_type('JSAPI');
        $wxOrderData->SetTotal_fee($totalPrice * 100);
        $wxOrderData->SetBody('零食商贩');
        $wxOrderData->SetOpenid($openid);
        $wxOrderData->SetNotify_url(Config::get('secure.pay_back_url'));

        return $this->getPaySignature($orderId, $wxOrderData);
    }

    private function getPaySignature($orderId, $wxOrderData)
    {
        $config = new \WxPayConfig();
        $wxOrder = \WxPayApi::unifiedOrder($config, $wxOrderData);
        if ($wxOrder['return_code'] != 'SUCCESS' || $wxOrder['result_code'] != 'SUCCESS') {
            throw new Exception('获取预支付订单失败' . $wxOrder);
        }
        OrderModel::where('id', $orderId)->update(['prepay_id' => $wxOrder['prepay_id']]);
        $signature = $this->sign($wxOrder);
        return $signature;
    }

    private function sign($wxOrder)
    {
        $config = new \WxPayConfig();
        $jsApiPayData = new \WxPayJsApiPay();
        $jsApiPayData->SetAppid(config('wx.app_id'));
        $jsApiPayData->SetTimeStamp((string)time());
        $rand = md5(time() . mt_rand(0, 1000));
        $jsApiPayData->SetNonceStr($rand);
        $jsApiPayData->SetPackage('prepay_id=' . $wxOrder['prepay_id']);
        $jsApiPayData->SetSignType('md5');
        $sign = $jsApiPayData->MakeSign($config);
        $rawValues = $jsApiPayData->GetValues();
        $rawValues['paySign'] = $sign;
        unset($rawValues['appId']);
        return $rawValues;
    }
}