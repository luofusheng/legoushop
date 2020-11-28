<?php
declare (strict_types = 1);

namespace app\admin\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class GoodsType extends Model
{
    // 关闭自动时间戳
    protected $autoWriteTimestamp = false;

    // 模型关联
    // 关联规格名
    public function goodsSpecName()
    {
        return $this->hasMany(GoodsSpecName::class);
    }
    // 关联属性
    public function goodsAttr()
    {
        return $this->hasMany(GoodsAttr::class);
    }
}
