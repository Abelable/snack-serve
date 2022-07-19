<?php
declare (strict_types = 1);

namespace app\middleware;

use app\service\TokenService;

class CheckSuperScope
{
    /**
     * 处理请求
     *
     * @param \think\Request $request
     * @param \Closure       $next
     * @return Response
     */
    public function handle($request, \Closure $next)
    {
        TokenService::needSuperScope();
        return $next($request);
    }
}
