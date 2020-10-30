<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\model\Admin;
use app\BaseController;
use think\facade\Validate;

class Login extends BaseController
{
    /**
     * 渲染登录页面
     */
    public function Login()
    {
        return view();
    }

    /**
     * 登录验证
     */
    public function loginCheck()
    {
        // 接收表单数据
        $params = request()->param();
        // 验证表单数据
        $validate = Validate::rule([
            'username|用户名' => 'require|length:1,20',
            'password|密码' => 'require|length:1,20',
            'captcha|验证码' => 'require|captcha'
        ]);
        if (!$validate->check($params)) {
            return json([
                'code' => 1,
                'msg' => $validate->getError()
            ]);
        }
        // 查询数据库中是否有该用户
        $admin = Admin::where('username', $params['username'])->find();
        if (empty($admin)) {
            return json([
                'code' => 2,
                'msg' => '该用户不存在'
            ]);
        }
        // 验证密码是否正确
        $res = verify_password($params['password'], $admin['password']);
        if (empty($res)) {
            return json([
                'code' => 3,
                'msg' => '密码错误'
            ]);
        }
        // 将用户信息存入session
        session('adminInfo', $admin->toArray());

        return json([
            'code' => 0,
            'msg' => '登录成功'
        ]);
    }

    /**
     * 退出登录
     */
    public function logout()
    {
        // 清除session
        session('adminInfo', null);

        return json([
            'code' => 0,
            'msg' => '退出成功'
        ]);
    }
}
