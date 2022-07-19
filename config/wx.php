<?php

return [
    'app_id' => 'wx4b321ab1269e5a6e',
    'app_secret' => 'a3c0e18d9d0714e883810d0f33fba1ec',
    'login_url' => "https://api.weixin.qq.com/sns/jscode2session?appid=%s&secret=%s&js_code=%s&grant_type=authorization_code",
    'access_token_url' => "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s",
];