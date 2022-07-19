<?php

namespace app\controller\v1;

use app\controller\BaseController;
use app\lib\exception\ParameterException;
use app\service\TokenService;
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

    public function verifyToken($token = '')
    {
        if (!$token) {
            throw new ParameterException(['msg' => 'token不允许为空']);
        }
        $isValid = TokenService::verifyToken($token);
        return json(['isValid' => $isValid]);
    }
}