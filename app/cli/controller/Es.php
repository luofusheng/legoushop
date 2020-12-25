<?php
declare (strict_types = 1);

namespace app\cli\controller;

use app\BaseController;
use app\home\model\GoodsSpu;

class Es extends BaseController
{
    /**
     * 创建商品索引并导入全部商品文档
     * cd public
     * php index.php /cli/Es/createAllGoodsDocs
     * 如果命令行无法访问，则直接在浏览器访问
     */
    public function createAllGoodsDocs()
    {
        try {
            // 实例化ES工具类
            $es = new \tools\es\MyElasticsearch();
            // 创建索引
            if ($es->existsIndex('goods_index')) $es->deleteIndex('goods_index');
            $es->createIndex('goods_index');

            $i = 0;
            while (true) {
                // 查询商品数据 每次处理1000条
                $goods = GoodsSpu::with(['goodsCategory'])
                    ->field('id,name,desc,price,goods_logo,goods_category_id')
                    ->limit($i, 1000)
                    ->select()->toArray();
                if (empty($goods)) {
                    // 查询结果为空，则停止
                    break;
                }
                // 添加文档
                foreach ($goods as $v) {
                    unset($v['goods_category_id']);
                    $es->addDoc($v['id'], $v, 'goods_index', 'goods_type');
                }
                $i += 1000;
            }
            die('success');
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            die($msg);
        }

    }
}
