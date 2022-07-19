<?php

namespace app\controller\v1;

use app\controller\BaseController;
use app\service\UserTokenService;
use app\validate\TokenGet;

class TokenController extends BaseController
{
    public function getToken($code = '')
    {
        TokenGet::new()->goCheck();
        $token = UserTokenService::getInstance()->get($code);
        return json(['token' => $token]);
    }
}