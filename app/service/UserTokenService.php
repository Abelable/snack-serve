<?php

namespace app\service;

use app\lib\enum\ScopeEnum;
use app\lib\exception\TokenException;
use app\lib\exception\WeChatException;
use app\model\User;
use think\Exception;
use think\facade\Config;

class UserTokenService extends TokenService
{
    public function get($code)
    {
        $wxLoginUrl = sprintf(Config::get('wx.login_url'), Config::get('wx.app_id'), Config::get('wx.app_secret'), $code);
        $wxResult = curl_get($wxLoginUrl);
        $wxResult = json_decode($wxResult, true);
        if (empty($wxResult)) {
            throw new Exception('获取session_key及openID时异常，微信内部错误');
        }
        $loginFail = array_key_exists('errcode', $wxResult);
        if ($loginFail) {
            throw new WeChatException([
               'msg' => $wxResult['errmsg'],
               'errorCode' => $wxResult['errcode']
            ]);
        }
        return $this->grantToken($wxResult);
    }

    private function grantToken($wxResult)
    {
        $openid = $wxResult['openid'];
        $user = User::getByOpenId($openid);
        $uid = $user ? $user->id : $this->newUser($openid);
        $token = $this->saveToCache($wxResult, $uid);
        return $token;
    }

    private function newUser($openid)
    {
        $user = User::create(['openid' => $openid]);
        return $user->id;
    }

    private function saveToCache($wxResult, $uid)
    {
        $wxResult['uid'] = $uid;
        $wxResult['scope'] = ScopeEnum::User;
        $key = self::generateToken();
        $result = cache($key, json_encode($wxResult), Config::get('setting.token_expire_in'));
        if (!$result) {
            throw new TokenException([
                'msg' => '服务器缓存异常',
                'errorCode' => 10005
            ]);
        }
        return $key;
    }
}