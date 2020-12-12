<?php
declare (strict_types = 1);

namespace app\home\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class GoodsDetail extends Model
{
    // 关闭自动时间戳
    protected $autoWriteTimestamp = false;
}
