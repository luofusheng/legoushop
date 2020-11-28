<?php
declare (strict_types = 1);

namespace app\admin\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class GoodsSpecValue extends Model
{
    // 关闭自动时间戳
    protected $autoWriteTimestamp = false;
}
