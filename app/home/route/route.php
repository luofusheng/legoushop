<?php

use think\facade\Route;

// 商品列表页
Route::get('list', 'Goods/index');
Route::get('goods_detail', 'Goods/detail');

Route::post('add_cart', 'Cart/addCart');