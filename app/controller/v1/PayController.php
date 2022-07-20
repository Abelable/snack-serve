<?php

namespace app\controller\v1;

use app\controller\BaseController;
use app\middleware\CheckExclusiveScope;
use app\service\PayService;
use app\service\WxNotifyService;
use app\validate\IDMustBePositiveInt;
use WxPay\WxPayConfig;

class PayController extends BaseController
{
    protected $middleware = [
      CheckExclusiveScope::class => ['only' => 'getPreOrder']
    ];

    public function getPreOrder($id)
    {
        IDMustBePositiveInt::new()->goCheck();
        $pay = PayService::getInstance()->pay($id);
        return json($pay);
    }

    public function receiveNotify()
    {
        $notify = new WxNotifyService();
        $config = new \WxPayConfig();
        $notify->handle($config);
    }
}