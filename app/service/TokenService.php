<?php

namespace app\service;

use app\lib\enum\ScopeEnum;
use app\lib\exception\ForbiddenException;
use app\lib\exception\ParameterException;
use app\lib\exception\TokenException;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Request;

class TokenService extends BaseService
{
    public static function generateToken()
    {
        $randChar = getRandChar(32);
        $timestamp = $_SERVER['REQUEST_TIME_FLOAT'];
        $tokenSalt = Config::get('secure.token_salt');
        return md5($randChar . $timestamp . $tokenSalt);
    }

    public static function verifyToken($token)
    {
        $exist = Cache::get($token);
        return boolval($exist);
    }

    public static function isValidOperate($checkedUid)
    {
        if (!$checkedUid) {
            throw new ParameterException([
                'msg' => '检查UID时必须传入一个被检查的UID'
            ]);
        }
        $currentUid = self::getCurrentUid();
        return $checkedUid == $currentUid;
    }

    public static function needPrimaryScope()
    {
        $scope = self::getCurrentTokenVar('scope');
        if ($scope < ScopeEnum::User) {
            throw new ForbiddenException();
        }
        return true;
    }

    // 用户专有权限
    public static function needExclusiveScope()
    {
        $scope = self::getCurrentTokenVar('scope');
        if ($scope != ScopeEnum::User) {
            throw new ForbiddenException();
        }
        return true;
    }

    public static function needSuperScope()
    {
        $scope = self::getCurrentTokenVar('scope');
        if ($scope != ScopeEnum::Super) {
            throw new ForbiddenException();
        }
        return true;
    }

    public static function getCurrentUid()
    {
        $uid = self::getCurrentTokenVar('uid');
        $scope = self::getCurrentTokenVar('scope');
        if ($scope == ScopeEnum::Super) {
            $userId = input('get.uid'); // 只有Super权限才可以自己传入uid, 且必须在get参数中，post不接受任何uid字段
            if (!$userId) {
                throw new ParameterException([
                    'msg' => '没有指定需要操作的用户对象'
                ]);
            }
            return $userId;
        }
        return $uid;
    }

    public static function getCurrentTokenVar($key)
    {
        $identity = self::getCurrentIdentities([$key]);
        $var = $identity[$key];
        if (!$var) {
            throw new TokenException([
                'msg' => '尝试获取的Token变量并不存在'
            ]);
        }
        return $var;
    }

    public static function getCurrentIdentities($keys)
    {
        $token = Request::header('token');
        $identities = Cache::get($token);
        if (!$identities) {
            throw new TokenException();
        }
        $identities = json_decode($identities, true);
        $result = [];
        foreach ($keys as $key) {
            if (array_key_exists($key, $identities)) {
                $result[$key] = $identities[$key];
            }
        }
        return $result;
    }
}