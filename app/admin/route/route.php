<?php

use think\facade\Route;

Route::get('/', 'index/index');
// 获取初始化数据
Route::get('get-system-init', 'index/getSystemInit');
// 主页
Route::get('home', 'index/home');
// 登录
Route::get('login', 'login/login');
// 验证码路由
Route::get('captcha/[:config]','\\think\\captcha\\CaptchaController@index');
// 登录验证
Route::post('login-check', 'login/loginCheck');
// 退出登录
Route::get('logout', 'login/logout');
// 错误页面
Route::get('error/[:msg]', 'error/error');
// 基本资料
Route::get('basic-info', 'index/basicInfo');

// 管理员管理
Route::group('admin', function () {
    // 管理员管理列表页
    Route::get('/', 'admin/index');
    // 管理员列表数据接口
    Route::get('list', 'admin/list');
    // 管理员列表状态改变接口
    Route::post('state-change', 'admin/statusChange');
    // 管理员列表删除接口
    Route::post('delete', 'admin/delete');
    // 管理员列表多选删除接口
    Route::post('delete-multi', 'admin/deleteMulti');
    // 管理员添加页面
    Route::get('add', 'admin/add');
    // 管理员添加页面保存接口
    Route::post('save', 'admin/save')->token();
    // 管理员编辑页面
    Route::get('edit', 'admin/edit');
    // 管理员编辑页面保存接口
    Route::post('update', 'admin/update')->token();
});
// 角色管理
Route::group('role', function () {
    // 角色管理列表页
    Route::get('/', 'role/index');
    // 角色列表数据接口
    Route::get('list', 'role/list');
    // 角色添加页面
    Route::get('add', 'role/add');
    // 角色添加页面保存接口
    Route::post('save', 'role/save')->token();
    // 角色编辑页面
    Route::get('edit', 'role/edit');
    // 角色编辑页面保存接口
    Route::post('update', 'role/update')->token();
    // 角色列表删除接口
    Route::post('delete', 'role/delete');
    // 角色列表多选删除接口
    Route::post('delete-multi', 'role/deleteMulti');
});
// 菜单管理
Route::group('menu', function () {
    // 菜单管理列表页
    Route::get('/', 'menu/index');
    // 菜单列表数据接口
    Route::get('list', 'menu/list');
    // 菜单添加页面
    Route::get('add', 'menu/add');
    // 菜单添加页面保存接口
    Route::post('save', 'menu/save')->token();
    // 菜单修改页面
    Route::get('edit', 'menu/edit');
    // 菜单修改页面保存接口
    Route::post('update', 'menu/update')->token();
    // 菜单列表删除接口
    Route::post('delete', 'menu/delete');
});


// 商品品牌管理
Route::group('brand', function () {
    // 商品品牌管理列表
    Route::get('/', 'brand/index');
    // 商品品牌列表数据接口
    Route::get('list', 'brand/list');
    // 商品品牌添加页面
    Route::get('add', 'brand/add');
    // 商品品牌上传logo图片接口
    Route::post('upload', 'brand/upload');
    // 品牌添加页面保存接口
    Route::post('save', 'brand/save')->token();
    // 品牌修改页面
    Route::get('edit', 'brand/edit');
    // 品牌修改页面保存接口
    Route::post('update', 'brand/update')->token();
    // 商品品牌列表删除接口
    Route::post('delete', 'brand/delete');
    // 商品品牌列表多选删除接口
    Route::post('delete-multi', 'brand/deleteMulti');
});

// 商品分类管理
Route::group('goods_category', function () {
    // 商品分类管理列表
    Route::get('/', 'GoodsCategory/index');
    // 商品分类列表数据接口
    Route::get('list', 'GoodsCategory/list');
    // 商品分类添加页面
    Route::get('add', 'GoodsCategory/add');
    // 商品分类上传图片接口
    Route::post('upload', 'GoodsCategory/upload');
    // 商品分类添加页面保存接口
    Route::post('save', 'GoodsCategory/save')->token();
    // 商品分类修改页面
    Route::get('edit', 'GoodsCategory/edit');
    // 商品分类修改页面保存接口
    Route::post('update', 'GoodsCategory/update')->token();
    // 商品分类列表删除接口
    Route::post('delete', 'GoodsCategory/delete');
});

// 商品模型
Route::group('goods_type', function () {
    // 商品模型列表页
    Route::get('/', 'GoodsType/index');
    // 商品模型列表数据接口
    Route::get('list', 'GoodsType/list');
    // 商品模型添加页面
    Route::get('add', 'GoodsType/add');
    // 商品模型添加页面保存接口
    Route::post('save', 'GoodsType/save')->token();
    // 商品模型修改页面
    Route::get('edit', 'GoodsType/edit');
    // 商品模型修改页面保存接口
    Route::post('update', 'GoodsType/update')->token();
    // 商品模型列表删除接口
    Route::post('delete', 'GoodsType/delete');
    // 商品模型列表多选删除接口
    Route::post('delete-multi', 'GoodsType/deleteMulti');
});

// 商品列表
Route::group('goods_list', function () {
    // 商品列表展示页
    Route::get('/', 'GoodsList/index');
    // 商品列表数据接口
    Route::get('list', 'GoodsList/list');
    // 商品列表添加页面
    Route::get('add', 'GoodsList/add');
    // 商品列表添加页面获取品牌数据接口
    Route::get('get_brand', 'GoodsList/getBrand');
    // 商品logo图片上传接口
    Route::post('upload_logo', 'GoodsList/uploadLogo');
    // 详情描述富文本编辑器图片上传接口
    Route::post('upload_img', 'GoodsList/uploadImg');
    // 商品列表添加页面保存接口
    Route::post('save', 'GoodsList/save');
    // 商品列表相册图片上传接口
    Route::post('upload_gallery', 'GoodsList/uploadGallery');
    // 获取选中的模型规格属性数据接口
    Route::post('get_spec_attr', 'GoodsList/getSpecAttr');
});
