<?php
declare (strict_types = 1);

namespace app\home\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class GoodsCart extends Model
{
    // 设置购物车-商品SPU关联 一条购物车记录 属于 一个商品
    public function goodsSpu()
    {
        return $this->belongsTo('GoodsSpu')
            ->bind(['goods_logo',
                'spu_name'=>'name',
                'spu_price'=>'price',
                'spu_stock'=>'stock',
                'spu_frozen_stock'=>'frozen_stock'
                ]);
    }

    // 设置购物车-商品SKU关联 一条购物车记录 有 一个商品SKU
    public function goodsSku()
    {
        return $this->belongsTo('GoodsSku')
            ->bind(['spec_value_ids', 'spec_name_values',
                'sku_price'=>'price',
                'sku_stock'=>'stock',
                'sku_frozen_stock'=>'frozen_stock'
                ]);
    }
}
