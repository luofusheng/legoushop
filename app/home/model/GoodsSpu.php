<?php
declare (strict_types = 1);

namespace app\home\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class GoodsSpu extends Model
{
    // 模型关联
    // 关联商品分类
    public function goodsCategory()
    {
        return $this->belongsTo(GoodsCategory::class, 'goods_category_id')
            ->bind([
                'category_name' => 'name',
                'category_pid_pathname' => 'pid_path_name'
            ]);
    }

    // 关联商品详情
    public function goodsDetail()
    {
        return $this->hasOne(GoodsDetail::class);
    }
    // 关联相册图片
    public function goodsImages()
    {
        return $this->hasMany(GoodsImages::class);
    }
    // 关联SKU
    public function goodsSku()
    {
        return $this->hasMany(GoodsSku::class);
    }
}
