<?php
declare (strict_types = 1);

namespace app\home\controller;

use app\home\logic\OrderLogic;
use app\home\model\Address;
use app\home\model\GoodsCart;
use app\home\model\GoodsSku;
use app\home\model\GoodsSpu;
use app\home\model\OrderGoods;
use app\home\model\PayLog;
use think\Exception;
use think\facade\Config;
use think\facade\Db;
use think\facade\Validate;

class Order extends Base
{
    // 显示结算页面
    public function create()
    {
        // 登录检测
        if (!session('?user_info')) {
            // 没有登录，跳转到登录页
            // 设置登录成功后的跳转地址
            session('back_url', '/home/cart_list');
            return redirect('/home/login');
        }

        // 获取收货地址信息
        // 获取用户id
        $userId = session('user_info.id');
        $address = Address::where('user_id', $userId)->select();
        if ($address->isEmpty()) {
            $address = [];
        } else {
            $address = $address->toArray();
        }

        // 查询选中的购物记录以及对应的商品SPU和SKU信息
        $res = OrderLogic::getCartDataWithGoods();
        $cartData = $res['cartData'];
        $totalNumber = $res['totalNumber'];
        $totalPrice = $res['totalPrice'];

        // dump($address);
        // dump($cartData);

        return view('', [
            'address' => $address,
            'cartData' => $cartData,
            'totalNumber' => $totalNumber,
            'totalPrice' => $totalPrice
        ]);
    }

    // 保存订单
    public function save()
    {
        // 接收参数
        $params['address_id'] = input('post.address_id', '', 'strip_tags');
        // 参数检测
        $validate = Validate::rule([
            'address_id' => 'require|integer|gt:0'
        ]);
        if (!$validate->check($params)) {
            return $validate->getError();
        }

        // 组装订单数据
        // 查询收货地址
        $address = Address::find($params['address_id']);
        if (!$address) {
            return '请重新选择收货地址';
        }
        $userId = session('user_info.id');
        // 查询结算的商品（选中的购物车记录以及商品SPU和SKU信息）
        $res = OrderLogic::getCartDataWithGoods();
        if (empty($res['cartData'])) {   // 如果购物车被清空，说明是在重新刷新页面，此时不需要创建订单
            // 查询出最新的订单
            $latestOrderId = \app\home\model\Order::where('user_id', $userId)->max('id');
            $latestOrder = \app\home\model\Order::find($latestOrderId);

            // 渲染支付页面
            $payType = Config::get('shop.pay_type');
            return view('pay', [
                'orderSn' => $latestOrder->order_sn,
                'payType' => $payType,
                'totalPrice' => $latestOrder->total_price
            ]);

        } else {    // 购物车没被清空，说明是第一次请求该页面，需要创建订单
            // 订单编号
            $orderSn = time() . mt_rand(100000, 999999);

            $orderData = [
                'user_id' => $userId,
                'order_sn' => $orderSn,
                'consignee' => $address['consignee'],
                'address' => $address['province'] . $address['city'] . $address['county'] . $address['address'],
                'phone' => $address['phone'],
                'total_price' => $res['totalPrice'],
            ];
            // 开启事务
            Db::startTrans();
            try {
                // 创建订单前，进行库存检测
                foreach ($res['cartData'] as $v) {
                    if ($v['number'] > $v['spu_stock']) {
                        // 抛出异常 直接进入catch语法结构
                        throw new Exception('订单中包含库存不足的商品');
                    }
                }
                $order = \app\home\model\Order::create($orderData);
                // 向订单商品表添加多条数据
                $orderGoodsData = [];
                foreach ($res['cartData'] as $v) {
                    $row = [
                        'order_id' => $order->id,
                        'goods_spu_id' => $v['goods_spu_id'],
                        'goods_sku_id' => $v['goods_sku_id'],
                        'number' => $v['number'],
                        'goods_name' => $v['spu_name'],
                        'goods_logo' => $v['goods_logo'],
                        'goods_price' => $v['spu_price'],
                        'spec_name_values' => $v['spec_name_values']
                    ];
                    $orderGoodsData[] = $row;
                }
                // 批量添加
                $orderGoodsModel = new OrderGoods();
                $orderGoodsModel->saveAll($orderGoodsData);
                // 从购物车表删除对应数据
                GoodsCart::where([
                    'user_id' => $userId,
                    'is_selected' => 1
                ])->delete();
                // 库存预扣减（冻结库存）
                $goodsSku = [];
                $goodsSpu = [];
                foreach ($res['cartData'] as $v) {
                    // 判断是否有sku，如果有则修改sku表，无则修改spu表
                    if ($v['goods_sku_id']) {
                        // 修改sku表，购买数量$v['number']，库存$v['spu_stock']，冻结库存$v['spu_frozen_stock']
                        $row = [
                            'id' => $v['goods_sku_id'],
                            'stock' => $v['spu_stock'] - $v['number'],
                            'frozen_stock' => $v['spu_frozen_stock'] + $v['number']
                        ];
                        $goodsSku[] = $row;
                    } else {
                        // 修改spu表，购买数量$v['number']，库存$v['spu_stock']，冻结库存$v['spu_frozen_stock']
                        $row = [
                            'id' => $v['goods_spu_id'],
                            'stock' => $v['spu_stock'] - $v['number'],
                            'frozen_stock' => $v['spu_frozen_stock'] + $v['number']
                        ];
                        $goodsSpu[] = $row;
                    }
                }
                // 批量修改库存
                $skuModel = new GoodsSku();
                $skuModel->saveAll($goodsSku);
                $spuModel = new GoodsSpu();
                $spuModel->saveAll($goodsSpu);
                // 提交事务
                Db::commit();

                // 渲染支付页面
                $payType = Config::get('shop.pay_type');
                return view('pay', [
                    'orderSn' => $orderSn,
                    'payType' => $payType,
                    'totalPrice' => $res['totalPrice']
                ]);

            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                return '创建订单失败，请重试';
            }
        }

    }

    // 去支付
    public function pay()
    {
        // 接收参数
        $params = request()->param();
        // 检测参数
        $validate = Validate::rule([
            'order_sn' => 'require',
            'pay_code|支付方式' => 'require'
        ]);
        if (!$validate->check($params)) {
            return $validate->getError();
        }
        // 查询订单
        $userId = session('user_info.id');
        $order = \app\home\model\Order::where('order_sn', $params['order_sn'])
            ->where('user_id', $userId)
            ->find();
        if (!$order) {
            return '订单不存在';
        }
        // 将选择的支付方式，修改到订单表
        $order->pay_code = $params['pay_code'];
        $order->pay_name = Config::get('shop.pay_type.' . $params['pay_code'] . '.pay_name');
        $order->save();
        // 支付（根据支付方式进行处理）
        switch ($params['pay_code']) {
            case 'wechat':
                // 微信支付
                break;
            case 'union':
                // 银联支付
                break;
            case 'alipay':
                // 支付宝
            default:
                // 默认 支付宝
                echo "<form id='alipayment' action='/plugins/alipay/pagepay/pagepay.php' method='post' style='display: none'>
    <input id='WIDout_trade_no' name='WIDout_trade_no' value='{$order['order_sn']}' />
    <input id='WIDsubject' name='WIDsubject' value='品优购订单' />
    <input id='WIDtotal_amount' name='WIDtotal_amount' value='{$order['total_price']}' />
    <input id='WIDbody' name='WIDbody' value='品优购订单，测试订单，付款了也不发货' />
</form><script>document.getElementById('alipayment').submit()</script>";
                break;
        }
    }

    // 页面跳转 同步通知地址 get请求
    public function callback()
    {
        // 参考/plugins/alipay/return_url.php
        // 接收参数
        $params = request()->param();
        // 参数检测（签名验证）接收到的参数和支付宝传递的参数 是否发生改变
        require_once("./plugins/alipay/config.php");
        require_once './plugins/alipay/pagepay/service/AlipayTradeService.php';
        $alipaySevice = new \AlipayTradeService($config);
        $result = $alipaySevice->check($params);
        if ($result) {
            // 验签成功
            $order_sn = $params['out_trade_no'];
            $order = \app\home\model\Order::where('order_sn', $order_sn)->find()->toArray();
            // 展示结果
            return view('paysuccess', [
                'pay_name' => '支付宝',
                'order_amount' => $params['total_amount'],
                'order' => $order
            ]);
        } else {
            // 验签失败
            // 展示结果
            return view('payfail', [
                'msg' => '支付失败'
            ]);
        }
    }

    // 支付宝异步通知地址，这里面执行订单状态修改等逻辑 post请求
    // 这个方法需要服务器能够被支付宝请求到，因此服务器需要公网ip才能访问
    public function notify()
    {
        // 参考/plugins/alipay/notify_url.php
        // 接收参数
        $params = request()->param();
        // 记录日志
        trace('支付宝异步通知-home/order/notify:'.json_encode($params), 'debug');
        // 参数检测（签名验证）接收到的参数和支付宝传递的参数 是否发生改变
        require_once("./plugins/alipay/config.php");
        require_once './plugins/alipay/pagepay/service/AlipayTradeService.php';
        $alipaySevice = new \AlipayTradeService($config);
        $result = $alipaySevice->check($params);
        if (!$result) {
            // 验证签名失败
            // 记录日志
            trace('支付宝异步通知-home/order/notify:验签失败', 'error');
            echo 'fail';
            return;
        }
        // 验签成功
        $order_sn = $params['out_trade_no'];
        $trade_status = $params['trade_status'];
        if ($trade_status == 'TRADE_FINISHED') {
            // 交易已经处理过
            echo 'success';
            return;
        }
        // 交易尚未处理
        $order = \app\home\model\Order::where('order_sn', $order_sn)->find();
        if (!$order) {
            // 订单不存在
            // 记录日志
            trace('支付宝异步通知-home/order/notify:订单不存在', 'error');
            echo 'fail';
            return;
        }
        if ($order['total_price'] != $params['total_amount']) {
            // 支付金额不对
            // 记录日志
            trace('支付宝异步通知-home/order/notify:支付金额不对', 'error');
            echo 'fali';
            return;
        }
        // 修改订单状态
        if ($order['status'] == 0) {
            $order->status = 1;
            $order->pay_time = time();
            $order->save();
            // 记录支付信息 核心字段 支付宝订单号
            $json = json_encode($params);
            // 添加数据到 pay_log表 用于后续向支付宝发起交易查询
            PayLog::create([
                'order_sn' => $order_sn,
                'json' => $json
            ]);
            echo 'success';
            return;
        }
    }
}
