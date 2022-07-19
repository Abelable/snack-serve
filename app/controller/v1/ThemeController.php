<?php

namespace app\controller\v1;

use app\controller\BaseController;
use app\lib\exception\ThemeException;
use app\model\Theme;
use app\validate\IDCollection;
use app\validate\IDMustBePositiveInt;

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

    public function getComplexOne($id)
    {
        IDMustBePositiveInt::new()->goCheck();
        $theme = Theme::getThemeWithProducts($id)->hidden(['products.summary']);
        if (!$theme) {
            throw new ThemeException();
        }
        return json($theme);
    }
}