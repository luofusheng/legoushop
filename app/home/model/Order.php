<?php
declare (strict_types = 1);

namespace app\home\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class Order extends Model
{
    // 模型关联
    // 关联订单商品表
    public function orderGoods()
    {
        return $this->hasMany(OrderGoods::class);
    }
}
