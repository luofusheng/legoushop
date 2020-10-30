<?php
declare (strict_types = 1);

namespace app\admin\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class AdminMenu extends Model
{
    // 获取器
    public function getStatusAttr($value)
    {
        $status = [
            0 => '禁用',
            1 => '启用'
        ];
        return $status[$value];
    }

    // 获取器
    public function getIsNavAttr($value)
    {
        $status = [
            0 => '否',
            1 => '是'
        ];
        return $status[$value];
    }
}
