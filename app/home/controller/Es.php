<?php
declare (strict_types = 1);

namespace app\home\controller;

class Es
{
    // 创建索引
    public function index()
    {
        $es = \Elasticsearch\ClientBuilder::create()->setHosts(['127.0.0.1:9200'])->build();
        $params = [
            'index' => 'test_index'
        ];
        $r = $es->indices()->create($params);
        dump($r);die;
    }

    // 索引文档|添加文档
    public function create()
    {
        $es = \Elasticsearch\ClientBuilder::create()->setHosts(['127.0.0.1:9200'])->build();
        $params = [
            'index' => 'test_index',
            'type' => 'test_type',
            'id' => 100,
            'body' => ['id'=>100, 'title'=>'PHP从入门到精通', 'author' => '张三']
        ];

        $r = $es->index($params);
        dump($r);die;
    }

    // 修改文档
    public function update()
    {
        $es = \Elasticsearch\ClientBuilder::create()->setHosts(['127.0.0.1:9200'])->build();
        $params = [
            'index' => 'test_index',
            'type' => 'test_type',
            'id' => 100,
            'body' => ['id'=>100, 'title'=>'PHP从入门到精通', 'author' => '李四']
        ];

        $r = $es->index($params);
        dump($r);die;
    }

    // 删除文档
    public function delete()
    {
        $es = \Elasticsearch\ClientBuilder::create()->setHosts(['127.0.0.1:9200'])->build();
        $params = [
            'index' => 'test_index',
            'type' => 'test_type',
            'id' => 100,
        ];

        $r = $es->delete($params);
        dump($r);die;
    }
}
