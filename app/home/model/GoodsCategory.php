<?php
declare (strict_types = 1);

namespace app\home\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class GoodsCategory extends Model
{
    // 关闭自动时间戳
    protected $autoWriteTimestamp = false;
}
