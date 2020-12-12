<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\model\GoodsAttr;
use app\admin\model\GoodsSpecName;
use app\admin\model\GoodsSpecValue;
use app\admin\model\GoodsSpu;
use think\facade\Db;

class GoodsType extends Base
{
    // 模型列表列表页
    public function index()
    {
        return view();
    }

    // 商品模型列表数据接口
    public function list()
    {
        $page = request()->param('page');
        $limit = request()->param('limit');
        $name = request()->param('name');

        $brandList = \app\admin\model\GoodsType::where([
                ['name', 'like', '%'.$name.'%']
            ])
            ->paginate([
                'list_rows' => $limit,
                'var_page' => 'page',
                'page' => $page,
                'query' => [
                    'name' => $name
                ]
            ]);
        $brandListData = $brandList->toArray()['data'];

        $data = [
            'code' => 0,
            'msg' => '',
            'count' => $brandList->total(),
            'data' => $brandListData
        ];

        return json($data);
    }

    // 商品模型添加页
    public function add()
    {
        return view();
    }

    // 商品模型添加保存
    public function save()
    {
        // 接收表单数据
        // 接收到的格式为：
        /**
         * array(3) {
            ["name"]=>string(6) "电脑"
            ["spec"]=>array(2) {
                [0]=>array(3) {
                    ["spec_name"]=>string(6) "颜色"
                    ["spec_sort"]=>string(1) "0"
                    ["spec_value"]=>array(3) {
                        [0]=>string(6) "红色"
                        [1]=>string(6) "白色"
                        [2]=>string(6) "蓝色"
                    }
                }
                [1]=>array(3) {
                    ["spec_name"]=>string(6) "内存"
                    ["spec_sort"]=>string(1) "0"
                    ["spec_value"]=>array(3) {
                        [0]=>string(2) "8G"
                        [1]=>string(3) "16G"
                        [2]=>string(3) "32G"
                    }
                }
            }
            ["attr"]=>array(2) {
                [0]=>array(3) {
                    ["attr_name"]=>string(6) "产地"
                    ["attr_sort"]=>string(1) "0"
                    ["attr_value"]=>array(3) {
                        [0]=>string(2) "中国大陆"
                        [1]=>string(3) "台湾"
                        [2]=>string(3) "香港"
                    }
                }
                [1]=>array(3) {
                    ["attr_name"]=>string(6) "重量"
                    ["attr_sort"]=>string(1) "0"
                    ["attr_value"]=>array(3) {
                        [0]=>string(2) "1.2kg"
                        [1]=>string(3) "2kg"
                        [2]=>string(3) "2.5kg"
                    }
                }
            }
        }
         *
         */

        $typeData = input('post.type_data', '', 'strip_tags');

        Db::startTrans();
        try {
            // 添加商品模型
            $goodsType = \app\admin\model\GoodsType::create([
                'name' => $typeData['name']
            ]);
            // 批量添加商品模型中对应的商品规格名
            $specNameList = [];
            foreach ($typeData['spec'] as $k=>$v) {
                $specNameList[] = [
                    'name' => $v['spec_name'],
                    'sort' => $v['spec_sort'],
                    'goods_type_id' => $goodsType->id
                ];
            }
            $goodsSpecName = new GoodsSpecName();
            $specNameData = $goodsSpecName->saveAll($specNameList);
            // 批量添加商品模型中对应的商品规格值
            $specValueList = [];
            foreach ($specNameData as $k=>$v) {
                foreach ($typeData['spec'][$k]['spec_value'] as $kk) {
                    $specValueList[] = [
                        'goods_spec_name_id' => $v['id'],
                        'value' => $kk,
                        'goods_type_id' => $goodsType->id
                    ];
                }
            }
            $goodsSpecValue = new GoodsSpecValue();
            $goodsSpecValue->saveAll($specValueList);
            // 批量添加商品模型中对应的商品属性
            $attrList = [];
            foreach ($typeData['attr'] as $k=>$v) {
                // 用来存放属性值的字符串
                $attrValues = '';
                foreach ($v['attr_value'] as $vv) {
                    $attrValues = $attrValues . $vv . ',';
                }
                $attrValues = rtrim($attrValues, ',');
                $attrList[] = [
                    'name' => $v['attr_name'],
                    'sort' => $v['attr_sort'],
                    'values' => $attrValues,
                    'goods_type_id' => $goodsType->id
                ];
            }
            $goodsAttr = new GoodsAttr();
            $goodsAttr->saveAll($attrList);

            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            // 返回操作失败信息
            return json([
                'code' => 1,
                'msg' => '保存失败'
            ]);
        }

        return json([
            'code' => 0,
            'msg' => '保存成功'
        ]);

    }

    // 商品模型修改页面
    public function edit()
    {
        $id = (int)input('get.id', '', 'strip_tags');

        // 查询得到商品模型所有数据
        $goodsType = \app\admin\model\GoodsType::with(['goodsSpecName.goodsSpecValue', 'goodsAttr'])->find($id)->toArray();

        return view('', [
            'goodsTypeData' => $goodsType
        ]);
    }

    // 商品模型修改页面保存接口
    public function update()
    {
        // 接收参数，其中，$typeData参数结构和save方法中的一样
        $typeData = input('post.type_data', '', 'strip_tags');
        $id = input('post.id', '', 'strip_tags');

        Db::startTrans();
        try {
            // 修改模型名称
            \app\admin\model\GoodsType::update(['name' => $typeData['name']], ['id' => $id]);
            // 先删除掉该模型的所有规格和属性
            // 删除模型规格名
            GoodsSpecName::where('goods_type_id', '=', $id)->delete();
            // 删除模型规格值
            GoodsSpecValue::where('goods_type_id', '=', $id)->delete();
            // 删除模型属性
            GoodsAttr::where('goods_type_id', '=', $id)->delete();

            // 然后重新添加规格和属性
            // 批量添加商品模型中对应的商品规格名
            $specNameList = [];
            foreach ($typeData['spec'] as $k=>$v) {
                $specNameList[] = [
                    'name' => $v['spec_name'],
                    'sort' => $v['spec_sort'],
                    'goods_type_id' => $id
                ];
            }
            $goodsSpecName = new GoodsSpecName();
            $specNameData = $goodsSpecName->saveAll($specNameList);
            // 批量添加商品模型中对应的商品规格值
            $specValueList = [];
            foreach ($specNameData as $k=>$v) {
                foreach ($typeData['spec'][$k]['spec_value'] as $kk) {
                    $specValueList[] = [
                        'goods_spec_name_id' => $v['id'],
                        'value' => $kk,
                        'goods_type_id' => $id
                    ];
                }
            }
            $goodsSpecValue = new GoodsSpecValue();
            $goodsSpecValue->saveAll($specValueList);
            // 批量添加商品模型中对应的商品属性
            $attrList = [];
            foreach ($typeData['attr'] as $k=>$v) {
                // 用来存放属性值的字符串
                $attrValues = '';
                foreach ($v['attr_value'] as $vv) {
                    $attrValues = $attrValues . $vv . ',';
                }
                $attrValues = rtrim($attrValues, ',');
                $attrList[] = [
                    'name' => $v['attr_name'],
                    'sort' => $v['attr_sort'],
                    'values' => $attrValues,
                    'goods_type_id' => $id
                ];
            }
            $goodsAttr = new GoodsAttr();
            $goodsAttr->saveAll($attrList);

            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            // 返回操作失败信息
            return json([
                'code' => 1,
                'msg' => '保存失败'
            ]);
        }

        return json([
            'code' => 0,
            'msg' => '保存成功'
        ]);
    }

    // 商品模型列表删除接口
    public function delete()
    {
        $id = (int)trim(input('post.id', '', 'strip_tags'));

        // 先查询有没有商品使用该模型
        $goodsSpu = GoodsSpu::where('goods_type_id', $id)->find();
        if (!empty($goodsSpu)) {    // 如果有商品使用该模型
            return json([
                'code' => 2,
                'msg' => $goodsSpu->name . '在使用该模型，无法删除'
            ]);
        }

        Db::startTrans();
        try {
            \app\admin\model\GoodsType::destroy($id);
            GoodsSpecName::where('goods_type_id', '=', $id)->delete();
            GoodsSpecValue::where('goods_type_id', '=', $id)->delete();
            GoodsAttr::where('goods_type_id', '=', $id)->delete();

            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            // 返回操作失败信息
            return json([
                'code' => 1,
                'msg' => '删除失败'
            ]);
        }

        return json([
            'code' => 0,
            'msg' => '删除成功'
        ]);
    }

    // 商品模型列表多选删除接口
    public function deleteMulti()
    {
        $ids = input('post.ids', '', 'strip_tags');

        // 先查询有没有商品使用该模型
        $goodsSpu = GoodsSpu::where('goods_type_id', 'in', $ids)->select();
        if (!$goodsSpu->isEmpty()) {    // 如果有商品使用该模型
            $msg = '';
            foreach ($goodsSpu as $v) {
                $msg .= $v->name . ',';
            }
            return json([
                'code' => 2,
                'msg' => $msg . '在使用该模型，无法删除'
            ]);
        }

        Db::startTrans();
        try {
            \app\admin\model\GoodsType::destroy($ids);
            GoodsSpecName::where('goods_type_id', 'in', $ids)->delete();
            GoodsSpecValue::where('goods_type_id', 'in', $ids)->delete();
            GoodsAttr::where('goods_type_id', 'in', $ids)->delete();

            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            // 返回操作失败信息
            return json([
                'code' => 1,
                'msg' => '删除失败'
            ]);
        }

        return json([
            'code' => 0,
            'msg' => '删除成功'
        ]);
    }
}
