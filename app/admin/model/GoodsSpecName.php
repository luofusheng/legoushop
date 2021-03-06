<?php
declare (strict_types = 1);

namespace app\admin\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class GoodsSpecName extends Model
{
    // 关闭自动时间戳
    protected $autoWriteTimestamp = false;

    // 模型关联
    // 关联规格值
    public function goodsSpecValue()
    {
        return $this->hasMany(GoodsSpecValue::class);
    }
}
