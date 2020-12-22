<?php
declare (strict_types = 1);

namespace app\home\controller;

use app\home\logic\CartLogic;
use app\home\model\User;
use think\facade\Validate;

class Login
{
    // 注册页面
    public function register()
    {
        return view();
    }

    // 发送验证码
    public function sendCaptcha()
    {
        // 接收参数
        $params['phone'] = input('post.phone','','strip_tags');

        // 参数检测
        $validate = Validate::rule([
            'phone' => 'require|unique:user,phone|regex:1[3-9]\d{9}'
        ]);
        if (!$validate->check($params)) {
            // 验证失败
            $res = [
                'code' => 400,
                'msg' => '参数错误'
            ];
            return json($res);
        }

        // 同一个手机号，一分钟只能发一次
        $lastTime = cache('register_time_' . $params['phone']);
        if ((time() - $lastTime) < 60) {
            $res = [
                'code' => 200,
                'msg' => '发送太频繁',
            ];
            return json($res);
        }

        // 发送验证码（生成验证码、生成短信内容、发短信）
        $code = mt_rand(1000, 9999);
        $content = "【创信】你的验证码是：{$code}，3分钟内有效！";
        //$result = send_msg($params['phone'], $content);
        // 开发测试时，不用真正发短信
        $result = true;
        // 返回结果
        if ($result === true) {
            // 发送成功，将验证码存储到缓存，用于后续校验
            cache('register_code_' . $params['phone'], $code, 180);
            cache('register_time_' . $params['phone'], time(), 180);
            $res = [
                'code' => 200,
                'msg' => '短信发送成功',
                // 调试时打开，正式上线关闭
                'data' => $code
            ];
            return json($res);
        } else {
            $res = [
                'code' => 200,
                'msg' => $result
            ];
            return json($res);
        }
    }

    // 注册页面提交
    public function doRegister()
    {
        // 接收数据
        $params['phone'] = input('post.phone','','strip_tags');
        $params['captcha'] = input('post.captcha','','strip_tags');
        $params['password'] = input('post.password','','strip_tags');
        $params['repassword'] = input('post.repassword','','strip_tags');

        // 参数检测
        $validate = Validate::rule([
            'phone|手机号' => 'require|mobile|unique:user,phone',
            'captcha|验证码' => 'require|length:4',
            'password|密码' => 'require|length:6,20|confirm:repassword'
        ]);
        if (!$validate->check($params)) {
            return json([
                'code' => 1,
                'msg' => $validate->getError()
            ]);
        }

        // 验证码校验
        $code = cache('register_code_' . $params['phone']);
        if ($code != $params['captcha']) {
            return json([
                'code' => 2,
                'msg' => '验证码错误'
            ]);
        }
        // 验证码成功一次后失效
        cache('register_code_' . $params['phone'], null);

        // 注册用户（添加操作）
        // 密码加密
        $params['password'] = encrypt_password($params['password']);
        $params['username'] = $params['phone'];
        $params['nickname'] = encrypt_phone($params['phone']);
        // 存入数据库
        User::create($params);

        return json([
            'code' => 0,
            'msg' => '注册成功'
        ]);
    }

    // 登录页面
    public function login()
    {
        return view();
    }

    // 登录页面提交
    public function doLogin()
    {
        // 接收数据
        $params['name'] = input('post.name','','strip_tags');
        $params['password'] = input('post.password','','strip_tags');
        $params['captcha'] = input('post.captcha','','strip_tags');

        // 参数检测
        $validate = Validate::rule([
            'name' => 'require|length:0,30',
            'captcha|验证码' => 'require|captcha',
            'password|密码' => 'require|length:0,30'
        ]);
        if (!$validate->check($params)) {
            return json([
                'code' => 1,
                'msg' => $validate->getError()
            ]);
        }

        // 查询用户表
        $password = encrypt_password($params['password']);
        // 用户名和密码一起查询，同时查询手机号和邮箱两个字段
        $info = User::where(function ($query) use ($params) {
            $query->where('phone', $params['name'])
                ->whereOr('email', $params['name']);
        })->where('password', $password)->find();
        if ($info) {    // 如果查询到
            // 设置登录标识
            session('user_info', $info->toArray());
            // 迁移cookie购物车数据到数据表
            CartLogic::cookieToDb();
            // 页面跳转
            // 从session中取跳转地址
            $backUrl = session('back_url') ?: '/home/';
            return json([
                'code' => 0,
                'msg' => '登录成功',
                'url' => $backUrl
            ]);
        } else {
            return json([
                'code' => 1,
                'msg' => '用户名或密码错误'
            ]);
        }
    }

    // 退出登录
    public function logout()
    {
        // 清空session
        session(null);
        // 页面跳转
        return redirect('/home/login');
    }

}
