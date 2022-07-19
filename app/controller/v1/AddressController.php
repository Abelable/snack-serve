<?php

namespace app\controller\v1;

use app\controller\BaseController;
use app\lib\exception\SuccessMessage;
use app\lib\exception\UserException;
use app\middleware\CheckPrimaryScope;
use app\model\UserAddress;
use app\validate\AddressNew;

class AddressController extends BaseController
{
    protected $middleware = [CheckPrimaryScope::class];

    public function getUserAddress()
    {
        $userAddress = UserAddress::where('user_id', $this->uid())->find();
        return json($userAddress);
    }

    public function createOrUpdateAddress()
    {
        $data = AddressNew::new()->goCheck();
        $userAddress = $this->user()->address;
        if ($userAddress) {
            $userAddress->save($data);
        } else {
            $this->user()->address()->save($data);
        }
        return json('ok', 201);
    }
}