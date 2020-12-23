<?php

use think\facade\Route;

// 首页
Route::get('/', 'Index/index');
// 注册页面
Route::get('register', 'Login/register');
// 注册页面发送短信验证码
Route::post('send_captcha', 'Login/sendCaptcha');
// 注册页面提交
Route::post('do_register', 'Login/doRegister');
// 登录页面
Route::get('login', 'Login/login');
// 验证码路由
Route::get('captcha/[:config]','\\think\\captcha\\CaptchaController@index');
// 登录页面提交
Route::post('do_login', 'Login/doLogin');
// 退出登录
Route::get('logout', 'Login/logout');

// 商品列表页
Route::get('list', 'Goods/index');
// 商品详情页
Route::get('goods_detail', 'Goods/detail');

// 添加购物车
Route::post('add_cart', 'Cart/addCart');
// 展示购物车列表
Route::get('cart_list', 'Cart/cartList');
// 修改商品数量
Route::post('cart/change_num', 'Cart/changeNum');
// 删除一行记录
Route::post('cart/delete', 'Cart/delete');
// 修改状态
Route::post('cart/change_status', 'Cart/changeStatus');

// 订单
// 创建订单
Route::get('order/create', 'Order/create');
// 保存订单
Route::post('order/save', 'Order/save');
// 去支付
Route::post('order/pay', 'Order/pay');
// 支付宝同步通知地址callback
Route::get('order/callback', 'Order/callback');
// 支付宝异步通知地址notify
Route::post('order/notify', 'Order/notify');
// 清除无效订单，由服务器计划任务访问
Route::get('order/clear', 'Order/clear');
// 我的订单页面
Route::get('order/my_order', 'Order/myOrder');
// 删除待付款订单接口
Route::post('order/del_order', 'Order/delOrder');
// 我的订单页面去支付接口
Route::get('order/to_pay', 'Order/toPay');

// 个人中心
Route::get('personal_center', 'PersonalCenter/personalCenter');
// 个人信息头像图片上传接口
Route::post('personal_info/upload_img', 'PersonalCenter/uploadImg');
// 个人信息保存接口
Route::post('personal_info/update', 'PersonalCenter/updateInfo');
// 新增收货地址保存接口
Route::post('address/save', 'PersonalCenter/saveAddress');
// 修改收货地址页面接口
Route::get('address/edit', 'PersonalCenter/edit');
// 修改收货地址保存接口
Route::post('address/update', 'PersonalCenter/updateAddress');
// 设置默认收货地址
Route::post('address/set_default_address', 'PersonalCenter/setDefaultAddress');
// 删除收货地址
Route::post('address/del_address', 'PersonalCenter/delAddress');