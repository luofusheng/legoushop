<?php
declare (strict_types = 1);

namespace app\home\controller;

use app\BaseController;
use app\home\logic\CartLogic;
use app\home\model\GoodsCategory;
use think\facade\View;

class Base extends BaseController
{
    // 初始化方法
    public function initialize()
    {
        // 从数据库中查出商品分类数据
        $goodsCategory = GoodsCategory::select();
        if (!$goodsCategory->isEmpty()) {
            $goodsCategory = get_tree_list($goodsCategory->toArray());
        }

        // 计算购物车商品数量
        $cart = CartLogic::getAllCart();
        $cartNumber = 0;
        foreach ($cart as $v) {
            $cartNumber += $v['number'];
        }

        // 变量赋值
        View::assign('goodsCategory', $goodsCategory);
        View::assign('cartNumber', $cartNumber);
    }
}
