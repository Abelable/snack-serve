<?php

namespace app\controller\v1;

use app\controller\BaseController;
use app\lib\exception\MissException;
use app\model\Banner;
use app\validate\IDMustBePositiveInt;

class BannerController extends BaseController
{
    public function getBanner($id)
    {
        IDMustBePositiveInt::new()->goCheck();
        $banner = Banner::getBannerById($id);
        if (!$banner) {
            throw new MissException([
                'msg' => '请求banner不存在',
                'errorCode' => 40000
            ]);
        }
        return json($banner);
    }
}