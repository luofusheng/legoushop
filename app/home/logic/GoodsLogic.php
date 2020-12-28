<?php


namespace app\home\logic;


class GoodsLogic
{
    public static function search()
    {
        //实例化ES工具类
        $es = new \tools\es\MyElasticsearch();
        //计算分页条件
        $keywords = input('keywords');
        $page = input('page', 1);
        $page = $page < 1 ? 1 : $page;
        $size = 10;
        $from = ($page - 1) * $size;
        //组装搜索参数体
        $body = [
            'query' => [
                'bool' => [
                    'should' => [
                        ['match' => ['name' => [
                            'query' => $keywords,
                            'boost' => 5, // 权重大
                        ]]],
                        ['match' => ['category_name' => [
                            'query' => $keywords,
                            'boost' => 4,
                        ]]],
                        ['match' => ['category_pid_pathname' => [
                            'query' => $keywords,
                            'boost' => 3,
                        ]]],
                        ['match' => ['desc' => [
                            'query' => $keywords,
                            'boost' => 2, // 权重小
                        ]]],
                    ],
                ],
            ],
            'sort' => ['id' => ['order' => 'desc']],
            'from' => $from,
            'size' => $size
        ];
        //进行搜索
        $results = $es->searchDoc('goods_index', 'goods_type', $body);
        //获取数据
        $data = array_column($results['hits']['hits'], '_source');
        $total = $results['hits']['total']['value'];
        //分页处理
        $list = \tools\es\EsPage::paginate($data, $size, $total);
        return $list;
    }
}