<?php
declare (strict_types = 1);

namespace app\home\controller;

use app\home\logic\CartLogic;
use app\home\model\GoodsSpu;
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

        // 处理数据 调用封装好的方法
        CartLogic::addCart($params['goods_id'], $params['sku_id'], $params['number']);

        // 结果页面显示
        $goodsSpu = GoodsSpu::find($params['goods_id']);
        $goodsSku = $goodsSpu->goodsSku()->find($params['sku_id']);

        return view('', [
            'goodsSpu' => $goodsSpu,
            'goodsSku' => $goodsSku,
            'number' => $params['number']
        ]);
    }

    // 购物车列表
    public function cartList()
    {
        // 查询所有的购物车记录
        $list = CartLogic::getAllCart();
        // 对每一条购物记录，查询商品相关信息（商品信息和SKU信息）
        foreach ($list as &$v) {
            $v['goodsSpu'] = GoodsSpu::find($v['goods_spu_id']);
            $v['goodsSku'] = $v['goodsSpu']->goodsSku()->find($v['goods_sku_id']);
        }
        unset($v);

        return view('', [
            'list' => $list
        ]);
    }

    // ajax修改购物车购买数量
    public function changeNum()
    {
        // 接收参数
        $params = request()->param();
        // 参数检测
        $validate = Validate::rule([
            'id' => 'require',
            'number' => 'require|integer|gt:0'
        ]);
        if (!$validate->check($params)) {
            return json([
                'code' => 1,
                'msg' => '参数错误'
            ]);
        }
        // 处理数据
        CartLogic::changeNum($params['id'], $params['number']);
        // 返回数据
        return json([
            'code' => 0,
            'msg' => '修改成功'
        ]);
    }

    // ajax删除购物记录
    public function delete()
    {
        // 接收参数
        $params = request()->param();
        // 参数检测
        if (!isset($params['id']) || empty($params['id'])) {
            return json([
                'code' => 1,
                'msg' => '参数错误'
            ]);
        }
        // 处理数据
        CartLogic::delCart($params['id']);
        // 返回数据
        return json([
            'code' => 0,
            'msg' => '删除成功'
        ]);
    }

    // ajax修改选中状态
    public function changeStatus()
    {
        // 接收参数
        $params = request()->param();
        // 参数检测
        $validate = Validate::rule([
            'id' => 'require',
            'status' => 'require|in:0,1'
        ]);
        if (!$validate->check($params)) {
            return json([
                'code' => 1,
                'msg' =>  $validate->getError()
            ]);
        }
        // 处理数据
        CartLogic::changeStatus($params['id'], $params['status']);
        // 返回数据
        return json([
            'code' => 0,
            'msg' => '修改成功'
        ]);
    }
}
