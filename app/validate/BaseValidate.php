<?php

namespace app\validate;

use app\api\service\Token;
use app\lib\exception\ParameterException;
use think\facade\Request;
use think\Validate;

/**
 * Class BaseValidate
 * 验证类的基类
 */
class BaseValidate extends Validate
{
    public static function new()
    {
        return new static();
    }

    /**
     * 检测所有客户端发来的参数是否符合验证类规则
     * 基类定义了很多自定义验证方法
     * 这些自定义验证方法其实，也可以直接调用
     * @throws ParameterException
     * @return true
     */
    public function goCheck()
    {
        $params = Request::instance()->param();

        if (!$this->check($params)) {
            // $this->error有一个问题，并不是一定返回数组，需要判断
            throw new ParameterException([
                'msg' => is_array($this->error) ? implode(';', $this->error) : $this->error
            ]);
        }
        $newArray = [];
        foreach ($this->rule as $key => $value) {
            $newArray[$key] = $params[$key] ?? null;
        }
        return $newArray;
    }

    protected function isPositiveInteger($value, $rule='', $data=[], $field='')
    {
        if (is_numeric($value) && is_int($value + 0) && ($value + 0) > 0) {
            return true;
        }
        return $field . '必须是正整数';
    }

    protected function isNotEmpty($value, $rule='', $data='', $field='')
    {
        if (empty($value)) {
            return $field . '不允许为空';
        } else {
            return true;
        }
    }

    //没有使用TP的正则验证，集中在一处方便以后修改
    //不推荐使用正则，因为复用性太差
    //手机号的验证规则
    protected function isMobile($value)
    {
        $phone = '/^((13[0-9])|(14[5,7,9])|(15[^4])|(18[0-9])|(17[0,1,3,5,6,7,8]))[0-9]{8}$/';
        $ring = '/^(\(\d{3,4}\)|\d{3,4}-|\s)?\d{7,14}$/';
        return preg_match($phone, $value) || preg_match($ring, $value);
    }
}