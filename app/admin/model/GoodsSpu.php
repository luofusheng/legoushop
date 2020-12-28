<?php
declare (strict_types = 1);

namespace app\admin\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class GoodsSpu extends Model
{
    // elasticsearch商品文档维护
    // 模型事件
    public static function onAfterInsert(Model $model)
    {
        // 实例化Es工具类
        $es = new \tools\es\MyElasticsearch();
        // 添加文档
        $doc = $model->goodsCategory;
        $doc = $model->visible(['id', 'name', 'desc', 'price', 'goods_logo', 'category_name', 'category_pid_pathname'])
            ->toArray();
        unset($doc['goodsCategory']);
        $es->addDoc($model->id, $doc, 'goods_index', 'goods_type');
    }

    public static function onAfterUpdate(Model $model)
    {
        // 实例化Es工具类
        $es = new \tools\es\MyElasticsearch();
        // 修改文档
        $doc = $model->goodsCategory;
        $doc = $model->visible(['id', 'name', 'desc', 'price', 'goods_logo', 'category_name', 'category_pid_pathname'])
            ->toArray();
        unset($doc['goodsCategory']);
        $body = ['doc' => $doc];
        $es->updateDoc($model->id, 'goods_index', 'goods_type', $body);
    }

    public static function onAfterDelete(Model $model)
    {
        // 实例化Es工具类
        $es = new \tools\es\MyElasticsearch();
        // 删除文档
        $es->deleteDoc($model->id, 'goods_index', 'goods_type');
    }

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
    // 关联商品模型 相对关联
    public function goodsType()
    {
        return $this->belongsTo(GoodsType::class);
    }
}
