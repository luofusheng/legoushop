<?php
declare (strict_types = 1);

namespace app\home\controller;

use app\home\model\Address;
use app\home\model\User;
use think\facade\Filesystem;
use think\facade\Validate;

class PersonalCenter extends Base
{
    // 个人中心
    public function personalCenter()
    {
        // 登录检测
        if (!session('?user_info')) {
            // 没有登录，跳转到登录页
            // 设置登录成功后的跳转地址
            session('back_url', '/home/personal_center');
            return redirect('/home/login');
        }

        $userInfo = session('user_info');
        // 查询地址数据
        $address = Address::where('user_id', $userInfo['id'])->select();
        if ($address->isEmpty()) {
            $address = [];
        } else {
            $address = $address->toArray();
        }

        return view('', [
            'userInfo' => $userInfo,
            'address' => $address
        ]);
    }

    // 个人信息头像图片上传接口
    public function uploadImg()
    {
        $file = request()->file('file');
        try {
            validate(['file' => ['fileSize:102400', 'fileExt:jpg,png,gif,jpeg']])->check(['file' => $file]);
        } catch (\think\exception\ValidateException $e) {
            return json([
                'code' => 1,
                'msg' => $e->getMessage(),
                'data' => [
                    'src' => ''
                ]
            ]);
        }
        $savename = Filesystem::disk('public')->putFile('image/temp', $file);

        return json([
            'code' => 0,
            'msg' => '',
            'data' => [
                'src' => '/uploads/'.$savename
            ]
        ]);
    }

    // 个人信息保存接口
    public function updateInfo()
    {
        // 接收参数
        $params['figure'] = trim(input('post.figure', '', 'strip_tags'));
        $params['username'] = trim(input('post.username', '', 'strip_tags'));
        $params['nickname'] = trim(input('post.nickname', '', 'strip_tags'));
        $params['phone'] = trim(input('post.phone', '', 'strip_tags'));
        $params['password'] = trim(input('post.password', '', 'strip_tags'));
        $params['email'] = trim(input('post.email', '', 'strip_tags'));

        // 参数检测
        $validate = Validate::rule([
            'username' => 'require|length:0,30',
            'nickname|昵称' => 'length:0,30',
            'password|密码' => 'length:6,30',
            'email|邮箱' => 'email'
        ]);
        if (!$validate->check($params)) {
            return json([
                'code' => 1,
                'msg' => $validate->getError()
            ]);
        }

        // 密码处理
        if (empty($params['password'])) {
            unset($params['password']);
        } else {
            $params['password'] = encrypt_password($params['password']);
        }

        // 头像图片处理
        // 判断头像图片有没有被改动
        if (session('user_info.figure') != $params['figure']) { // 如果头像图片被改动了
            if (!empty($params['figure'])) {
                // 先删除原来的图片
                if (!empty(session('user_info.figure'))) {
                    unlink('.' . session('user_info.figure'));
                }

                // 将图片从临时temp文件夹中迁移到figure文件夹中
                // 传过来的临时文件路径为：/uploads/image/temp/20201030/d885cc3b24b184a7c631408b5e0f670e.jpg
                // 要迁移的文件路径：/uploads/image/figure/20201030/d885cc3b24b184a7c631408b5e0f670e.jpg
                // 新建日期文件夹
                $tempArray = explode('/', $params['figure']);
                $imageFloder = './uploads/image/figure/' . $tempArray[4];
                if (!is_dir($imageFloder)) {
                    mkdir($imageFloder, 0777, true);
                }
                $tempImg = '.' . $params['figure'];
                $newImg = str_replace('/temp/', '/figure/', $tempImg);
                // 转移图片
                rename($tempImg, $newImg);
                // 修改图片路径为新路径
                $params['figure'] = ltrim($newImg, '.');
            }

        }

        // 更新数据
        $newUserInfo = User::update($params, ['id' => session('user_info.id')]);

        // 更新session
        session('user_info', $newUserInfo);

        return json([
            'code' => 0,
            'msg' => '修改成功'
        ]);
    }

    // 新增收货地址保存接口
    public function saveAddress()
    {
        // 接收参数
        $params['consignee'] = trim(input('post.consignee', '', 'strip_tags'));
        $params['province'] = trim(input('post.province', '', 'strip_tags'));
        $params['city'] = trim(input('post.city', '', 'strip_tags'));
        $params['county'] = trim(input('post.county', '', 'strip_tags'));
        $params['address'] = trim(input('post.address', '', 'strip_tags'));
        $params['phone'] = trim(input('post.phone', '', 'strip_tags'));

        // 参数检测
        // 参数检测
        $validate = Validate::rule([
            'consignee|收货人' => 'require|length:0,30',
            'province|省份' => 'require|length:1,30',
            'city|城市' => 'require|length:1,30',
            'county|区县' => 'require|length:1,30',
            'address|详细地址' => 'require|length:1,100',
            'phone|手机号' => 'require|mobile',
        ]);
        if (!$validate->check($params)) {
            return json([
                'code' => 1,
                'msg' => $validate->getError()
            ]);
        }

        // 存入数据
        $params['user_id'] = session('user_info.id');
        Address::create($params);

        return json([
            'code' => 0,
            'msg' => '保存成功'
        ]);
    }

    // 修改收货地址页面接口
    public function edit()
    {
        $id = input('get.id', '', 'strip_tags');

        // 查询对应收货地址数据
        $address = Address::find($id)->toArray();

        return view('', [
            'address' => $address
        ]);
    }

    // 修改收货地址保存接口
    public function updateAddress()
    {
        // 接收参数
        $params['id'] = trim(input('post.id', '', 'strip_tags'));
        $params['consignee'] = trim(input('post.consignee', '', 'strip_tags'));
        $params['province'] = trim(input('post.province', '', 'strip_tags'));
        $params['city'] = trim(input('post.city', '', 'strip_tags'));
        $params['county'] = trim(input('post.county', '', 'strip_tags'));
        $params['address'] = trim(input('post.address', '', 'strip_tags'));
        $params['phone'] = trim(input('post.phone', '', 'strip_tags'));

        // 参数检测
        // 参数检测
        $validate = Validate::rule([
            'id' => 'require',
            'consignee|收货人' => 'require|length:0,30',
            'province|省份' => 'require|length:1,30',
            'city|城市' => 'require|length:1,30',
            'county|区县' => 'require|length:1,30',
            'address|详细地址' => 'require|length:1,100',
            'phone|手机号' => 'require|mobile',
        ]);
        if (!$validate->check($params)) {
            return json([
                'code' => 1,
                'msg' => $validate->getError()
            ]);
        }

        $params['user_id'] = session('user_info.id');
        Address::update($params);

        return json([
            'code' => 0,
            'msg' => '修改成功'
        ]);
    }

    // 设置默认收货地址
    public function setDefaultAddress()
    {
        $id = trim(input('post.id', '', 'strip_tags'));

        // 查询所有的收货地址
        $allAddress = Address::where('user_id', session('user_info.id'))->select();
        // 更新
        foreach ($allAddress as $v) {
            if ($v->id == $id) {
                $v->is_default = 1;
            } else {
                $v->is_default = 0;
            }
            $v->save();
        }

        return json([
            'code' => 0,
            'msg' => '修改成功'
        ]);
    }

    // 删除收货地址
    public function delAddress()
    {
        $id = trim(input('post.id', '', 'strip_tags'));

        Address::destroy($id);

        return json([
            'code' => 0,
            'msg' => '删除成功'
        ]);
    }
}
