<?php
declare (strict_types = 1);

namespace app\admin\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class GoodsSpu extends Model
{
    // 关联商品分类
    public function goodsCategory()
    {
        return $this->belongsTo(GoodsCategory::class, 'goods_category_id')
            ->bind([
                'category_name' => 'name',
                'category_pid_pathname' => 'pid_path_name'
            ]);
    }
}
