<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\model\GoodsSpu;

class GoodsList extends Base
{
    // 商品列表页
    public function index()
    {
        return view();
    }

    // 商品列表数据接口
    public function list()
    {
        $page = request()->param('page');
        $limit = request()->param('limit');
        $name = request()->param('name');

        $goodsList = GoodsSpu::field('id,name,goods_category_id,is_on_sale,price,stock')
            ->where([
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
        $godsListData = $goodsList->toArray()['data'];

        $data = [
            'code' => 0,
            'msg' => '',
            'count' => $goodsList->total(),
            'data' => $godsListData
        ];

        return json($data);
    }
}
