<?php

namespace app\controller\v1;

use app\controller\BaseController;
use app\lib\exception\ThemeException;
use app\model\Theme;
use app\validate\IDCollection;

class ThemeController extends BaseController
{
    public function getSimpleList($ids = '')
    {
        IDCollection::new()->goCheck();
        $list = Theme::with(['topicImg', 'headImg'])->select(explode(',', $ids));
        if ($list->isEmpty()) {
            throw new ThemeException();
        }
        return json($list);
    }
}