<?php
declare (strict_types = 1);

namespace app\home\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class GoodsSpecValue extends Model
{
    // 关闭自动时间戳
    protected $autoWriteTimestamp = false;

    // 模型关联
    // 关联商品规格名
    public function goodsSpecName()
    {
        return $this->belongsTo(GoodsSpecName::class, 'goods_spec_name_id')
            ->bind([
                'goods_spec_name' => 'name'
            ]);
    }
}
