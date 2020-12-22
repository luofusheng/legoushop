<?php

namespace app\home\logic;

use app\home\model\GoodsCart;

class OrderLogic
{
    // 获取选中的购物记录以及商品信息，统计总数量和价格
    public static function getCartDataWithGoods()
    {
        $userId = session('user_info.id');
        $cartData = GoodsCart::with(['goodsSpu', 'goodsSku'])
            ->where([
                ['is_selected', '=', 1],
                ['user_id', '=', $userId]
            ])->select();
        if ($cartData->isEmpty()) {
            $cartData = [];
        } else {
            $cartData = $cartData->toArray();
        }
        $totalPrice = 0;
        $totalNumber = 0;
        foreach ($cartData as &$v) {
            // 使用sku的价格，覆盖spu的价格
            if (isset($v['sku_price']) && $v['sku_price']>0) {
                $v['spu_price'] = $v['sku_price'];
            }
            // 库存处理
            if (isset($v['sku_stock']) && $v['sku_stock']>0) {
                $v['spu_stock'] = $v['sku_stock'];
            }
            if (isset($v['sku_frozen_stock']) && $v['sku_frozen_stock']>0) {
                $v['spu_frozen_stock'] = $v['sku_frozen_stock'];
            }
            // 累加总数量和总价格
            $totalNumber += $v['number'];
            $totalPrice += $v['number'] * $v['spu_price'];
        }
        unset($v);

        return [
            'cartData' => $cartData,
            'totalNumber' => $totalNumber,
            'totalPrice' => $totalPrice
        ];
    }
}