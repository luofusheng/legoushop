<?php
declare (strict_types = 1);

namespace app\middleware;

// 登录检测
class IsLogin
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
        $adminInfo = session('adminInfo');
        if (empty($adminInfo)) {
            header('Location: /admin/login');
            exit;
        }

        return $next($request);
    }
}
