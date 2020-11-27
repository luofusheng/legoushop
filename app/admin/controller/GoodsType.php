<?php
declare (strict_types = 1);

namespace app\admin\controller;

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
                    ["attr_value"]=>string(12) "中国大陆"
                }
                [1]=>array(3) {
                    ["attr_name"]=>string(6) "重量"
                    ["attr_sort"]=>string(1) "0"
                    ["attr_value"]=>string(5) "1.2kg"
                }
            }
        }
         *
         */

        $typeData = input('post.type_data', '', 'strip_tags');

    }
}
