<?php

namespace app\service;

use app\lib\exception\OrderException;
use app\lib\exception\UserException;
use app\model\User;

class DeliveryMsgService extends WxMsgService
{
    const DELIVERY_MSG_ID = 'your wx template ID';// 小程序模板消息ID号

    public function sendDeliveryMessage($order, $tplJumpPage = '')
    {
        if (!$order) {
            throw new OrderException();
        }
        $this->tplID = self::DELIVERY_MSG_ID;
        $this->formID = $order->prepay_id;
        $this->page = $tplJumpPage;
        $this->prepareMessageData($order);
        $this->emphasisKeyWord='keyword2.DATA';
        return parent::sendMessage($this->getUserOpenID($order->user_id));
    }

    private function prepareMessageData($order)
    {
        $dt = new \DateTime();
        $data = [
            'keyword1' => [
                'value' => '顺风速运',
            ],
            'keyword2' => [
                'value' => $order->snap_name,
                'color' => '#27408B'
            ],
            'keyword3' => [
                'value' => $order->order_no
            ],
            'keyword4' => [
                'value' => $dt->format("Y-m-d H:i")
            ]
        ];
        $this->data = $data;
    }

    private function getUserOpenID($uid)
    {
        $user = User::find($uid);
        if (!$user) {
            throw new UserException();
        }
        return $user->openid;
    }
}