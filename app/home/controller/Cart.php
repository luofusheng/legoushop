<?php
declare (strict_types = 1);

namespace app\home\controller;

use think\facade\Validate;

class Cart extends Base
{
    //加入购物车 表单提交
    public function addCart()
    {
        // 接收参数
        $params = request()->param();
        // 参数检测
        $validate = Validate::rule([
            'goods_id|商品SPU的id' => 'require|integer|gt:0',
            'sku_id' => 'integer|gt:0',
            'number|商品数量' => 'require|integer|gt:0'
        ]);
        if (!$validate->check($params)) {
            return $validate->getError();
        }


        return '成功';
    }
}
