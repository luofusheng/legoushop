<?php
declare (strict_types = 1);

namespace app\admin\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class Admin extends Model
{
    // 模型关联
    public function adminRoleName()
    {
        return $this->belongsTo(AdminRole::class)->bind(['role' => 'name']);
    }

    // 获取器
    public function getStatusAttr($value)
    {
        $status = [
            0 => '禁用',
            1 => '启用'
        ];
        return $status[$value];
    }
}
