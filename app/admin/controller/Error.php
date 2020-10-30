<?php
declare (strict_types = 1);

namespace app\admin\controller;

class Error
{
    /**
     * 输出错误页面
     * @param string $msg 错误信息
     * @return \think\response\View
     */
    public function error($msg='')
    {
        return view('', ['msg' => $msg]);
    }
}
