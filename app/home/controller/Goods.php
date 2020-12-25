<?php
declare (strict_types = 1);

namespace app\home\controller;

use app\home\logic\GoodsLogic;
use app\home\model\GoodsCategory;
use app\home\model\GoodsSpecValue;
use app\home\model\GoodsSpu;

class Goods extends Base
{
    // 商品列表页
    public function index()
    {
        $id = (int)input('get.id', '', 'strip_tags');
        $keywords = input('get.keywords', '', 'strip_tags');
        $limit = input('get.limit', '10', 'strip_tags');
        $page = input('get.page', '1', 'strip_tags');

        if (empty($keywords)) {
            // 获取指定分类下的商品列表

            // 查询出该分类
            $goodsCategory = GoodsCategory::find($id);
            if ($goodsCategory->pid == 0) { // 一级分类
                $crumb = $goodsCategory->name;

                // 查询商品列表
                $categoryIds = GoodsCategory::field('id')->where('pid', $id)->select();
                if ($categoryIds->isEmpty()) {  // 如果该一级分类下没有二级分类
                    $goodsList = [];
                    $count = 0;
                } else {    // 如果该一级分类下有二级分类
                    // 查询出该一级分类下的所有二级分类的商品
                    $goodsSpu = GoodsSpu::field('id,name,goods_logo,price')
                        ->where('goods_category_id', 'in', array_column($categoryIds->toArray(), 'id'))
                        ->paginate([
                            'list_rows' => $limit,
                            'var_page' => 'page',
                            'page' => $page
                        ]);
                    if ($goodsSpu->isEmpty()) { // 如果该分类下没有商品
                        $goodsList = [];
                        $count = 0;
                    } else {
                        $goodsList = $goodsSpu->toArray()['data'];
                        $count = $goodsSpu->total();
                    }
                }

            } else {    // 二级分类
                $crumb = [$goodsCategory->pid_path_name, $goodsCategory->name];

                // 查询出该分类下的商品
                $goodsSpu = GoodsSpu::field('id,name,goods_logo,price')->where('goods_category_id', $id)->paginate([
                    'list_rows' => $limit,
                    'var_page' => 'page',
                    'page' => $page
                ]);
                if ($goodsSpu->isEmpty()) { // 如果该分类下没有商品
                    $goodsList = [];
                    $count = 0;
                } else {
                    $goodsList = $goodsSpu->toArray()['data'];
                    $count = $goodsSpu->total();
                }
            }
        } else {
            // 从elasticsearch中搜索
            try {
                $crumb = $keywords;
                $list = GoodsLogic::search()->toArray();
                $goodsList = $list['data'];
                $count = $list['total'];
            } catch (\Exception $e) {
                return '服务器异常';
            }
        }

        return view('', [
            'crumb' => $crumb,
            'goodsList' => $goodsList,
            'count' => $count,
            'id' => $id,
            'keywords' => $keywords,
            'page' => $page
        ]);
    }

    // 商品详情页
    public function detail()
    {
        $id = (int)input('get.id', '', 'strip_tags');

        // 查询该商品数据
        $goodsInfo = GoodsSpu::with(['goodsDetail', 'goodsImages', 'goodsSku', 'goodsCategory'])
            ->find($id)->toArray();

        // 转化商品属性json格式为数组
        $goodsInfo['goods_attr'] = json_decode($goodsInfo['goods_attr'], true);

        // 将商品的第一个SKU的价格库存信息，替换到商品的价格库存中
        if (!empty($goodsInfo['goodsSku'])) {
            if ($goodsInfo['goodsSku'][0]['price'] > 0) {
                $goodsInfo['price'] = $goodsInfo['goodsSku'][0]['price'];
            }
            if ($goodsInfo['goodsSku'][0]['stock'] > 0) {
                $goodsInfo['stock'] = $goodsInfo['goodsSku'][0]['stock'];
            } else {
                $goodsInfo['stock'] = 0;
            }
        }

        // 查询商品的规格数据
        // 查找出SKU表中所有的规格值的id，组成一个数组，并去重
        $value_ids = array_unique(explode('_', implode('_', array_column($goodsInfo['goodsSku'], 'spec_value_ids'))));
        // 查找出SKU表中对应的规格值数据
        $specValues = GoodsSpecValue::with(['goodsSpecName'])->where('id', 'in', $value_ids)->select()->toArray();
        // 重新整理数据格式
        // 重新整理之前的数据格式：
        /*
        $specValues = [
            ['id' => 77, 'goods_spec_name_id' => 29, 'value' => '亮黑色', 'goods_type_id' => 7, 'goods_spec_name' => '颜色'],
            ['id' => 78, 'goods_spec_name_id' => 29, 'value' => '釉白色', 'goods_type_id' => 7, 'goods_spec_name' => '颜色'],
            ['id' => 79, 'goods_spec_name_id' => 29, 'value' => '秘银色', 'goods_type_id' => 7, 'goods_spec_name' => '颜色']
        ];
        */
        $res = [];
        foreach ($specValues as $v) {
            $res[$v['goods_spec_name_id']] = [
                'spec_name_id' => $v['goods_spec_name_id'],
                'spec_name' => $v['goods_spec_name'],
                'spec_values' => []
            ];
        }
        // 到这里，格式为：
        /*
        $res = [
            29 => ['spec_name_id'=>29, 'spec_name'=>'颜色', 'spec_values'=>[]]
        ];
        */
        foreach ($specValues as $v) {
            $res[$v['goods_spec_name_id']]['spec_values'][] = $v;
        }
        // 到这里，格式变为：
        /*
        $res = [
            29 => [
                'spec_name_id'=>29,
                'spec_name'=>'颜色',
                'spec_values'=>[
                    ['id' => 77, 'goods_spec_name_id' => 29, 'value' => '亮黑色', 'goods_type_id' => 7, 'goods_spec_name' => '颜色'],
                    ['id' => 78, 'goods_spec_name_id' => 29, 'value' => '釉白色', 'goods_type_id' => 7, 'goods_spec_name' => '颜色'],
                    ['id' => 79, 'goods_spec_name_id' => 29, 'value' => '秘银色', 'goods_type_id' => 7, 'goods_spec_name' => '颜色']
                ]
            ]
        ];
        */
        // 规格值ids组合--商品规格SKU的映射关系 页面需要使用
        /*
        $goodsInfo['goodsSku'] = [
            ['id'=>47, 'spec_value_ids'=>'77_82', 'price'=>'6999.00'],
            ['id'=>48, 'spec_value_ids'=>'77_83', 'price'=>'7999.00'],
            ['id'=>49, 'spec_value_ids'=>'77_84', 'price'=>'6499.00'],
            ['id'=>50, 'spec_value_ids'=>'78_82', 'price'=>'6999.00'],
        ];
        */
        /*
        $valueIdsMap = [
            '规格值ids组合' => 'SKU的id和price'
            '77_82' => ['id'=>47, 'price'=>'6999.00'],
            '77_83' => ['id'=>48, 'price'=>'7999.00'],
            '77_84' => ['id'=>49, 'price'=>'6499.00'],
            '78_82' => ['id'=>50, 'price'=>'6999.00'],
        ];
        */
        $valueIdsMap = [];
        foreach ($goodsInfo['goodsSku'] as $v) {
            $row = [
                'id' => $v['id'],
                'price' => $v['price']
            ];
            $valueIdsMap[$v['spec_value_ids']] = $row;
        }
        // 数据最终在js中使用，需要转化为json格式，用于输出到js中
        $valueIdsMap = json_encode($valueIdsMap);

        return view('', [
            'goodsInfo' => $goodsInfo,
            'specs' => $res,
            'valueIdsMap' => $valueIdsMap
        ]);
    }
}
