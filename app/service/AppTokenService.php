<?php

namespace app\service;

use app\lib\exception\TokenException;
use app\model\ThirdApp;
use think\facade\Config;

class AppTokenService extends TokenService
{
    public function get($ac, $se)
    {
        $app = ThirdApp::check($ac, $se);
        if (!$app) {
            throw new TokenException([
                'msg' => '授权失败',
                'errorCode' => 10004
            ]);
        }

        $token = $this->saveToCache([
            'scope' => $app->scope,
            'uid' => $app->id
        ]);
        return $token;
    }

    private function saveToCache($values)
    {
        $key = self::generateToken();
        $expire_in = Config::get('setting.token_expire_in');
        $result = cache($key, json_encode($values), $expire_in);
        if (!$result) {
            throw new TokenException([
                'msg' => '服务器缓存异常',
                'errorCode' => 10005
            ]);
        }
        return $key;
    }
}