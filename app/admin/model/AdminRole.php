<?php
declare (strict_types = 1);

namespace app\admin\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class AdminRole extends Model
{
    // 模型关联
    public function admin()
    {
        return $this->hasMany(Admin::class);
    }
}
