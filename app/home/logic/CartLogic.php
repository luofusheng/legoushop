<?php


namespace app\home\logic;

// 购物车处理逻辑代码
use app\home\model\GoodsCart;

class CartLogic
{

    /**
     * 加入购物车
     * @param $goods_spu_id
     * @param $goods_sku_id
     * @param $number 商品购买数量
     * @param int $is_selected
     */
    public static function addCart($goods_spu_id, $goods_sku_id, $number, $is_selected = 1)
    {
        // 判断登录状态：已登录，添加到数据表；未登录，添加到cookie
        if (session('?user_info')) { // 已登录，添加到数据表
            $user_id = session('user_info.id');
            // 是否存在相同购物记录（用户id相同，商品id相同，sku的id也相同）
            $where = [
                'user_id' => $user_id,
                'goods_spu_id' => $goods_spu_id,
                'goods_sku_id' => $goods_sku_id
            ];
            $info = GoodsCart::where($where)->find();
            if ($info) {    // 存在相同记录，累加数量 修改操作
                $info->number += $number;
                $info->is_selected = $is_selected;
                $info->save();
            } else {    // 不存在相同记录，添加新数据
                $where['number'] = $number;
                $where['is_selected'] = $is_selected;
                GoodsCart::create($where);
            }
        } else { // 未登录，添加到cookie
            // 先取出cookie中的数据
            $data = cookie('cart') ? json_decode(stripslashes(html_entity_decode(cookie('cart'))), true) : [];
            // 添加或修改数据数组，判断是否存在相同的记录（商品id相同，sku的id相同）
            $key = $goods_spu_id . '_' . $goods_sku_id;
            if (isset($data[$key])) {  // 存在相同记录，则累加数量
                $data[$key]['number'] += $number;
                $data[$key]['is_selected'] = $is_selected;
            } else {    // 不存在相同记录，则添加新的数据（键值对）
                $data[$key] = [
                    'id' => $key,
                    'user_id' => '',
                    'goods_spu_id' => $goods_spu_id,
                    'goods_sku_id' => $goods_sku_id,
                    'number' => $number,
                    'is_selected' => $is_selected
                ];
            }
            // 重新保存新数组到cookie，保存7天
            $data = json_encode($data);
            cookie('cart', $data, 86400*7);
        }
    }

    // 获取所有的购物记录
    public static function getAllCart()
    {
        // 判断登录状态：已登录，查询数据表；未登录，取cookie
        if (session('?user_info')) {    // 已登录，查询数据表
            $user_id = session('user_info.id');
            $data = GoodsCart::field('id,user_id,goods_spu_id,number,goods_sku_id,is_selected')
                ->where('user_id', $user_id)
                ->select()->toArray();
        } else {    // 未登录，取cookie
            $data = cookie('cart') ? json_decode(stripslashes(html_entity_decode(cookie('cart'))), true): [];
            // 转化为标准的二维数组（统一格式） 去掉字符串下标
            $data = array_values($data);
        }
        return $data;
    }

    /**
     * 修改购买数量
     * @param $id int 修改条件 已登录为主键id；未登录为20_30这样的下标
     * @param $number int 修改数量
     */
    public static function changeNum($id, $number)
    {
        // 判断登录状态 已登录修改数据表；未登录修改cookie
        if (session('?user_info')) {    // 已登录修改数据表
            $user_id = session('user_info.id');
            // 只能修改当前用户自己的记录
            GoodsCart::update(['number' => $number], ['id' => $id, 'user_id' => $user_id]);
        } else {    // 未登录修改cookie
            // 先从cookie中取出所有的记录
            $data = cookie('cart') ? json_decode(stripslashes(html_entity_decode(cookie('cart'))), true) : [];
            // 修改数量
            $data[$id]['number'] = $number;
            // 重新保存到cookie
            $data = json_encode($data);
            cookie('cart', $data, 86400*7);
        }
    }

    /**
     * 删除购物车记录
     * @param $id int  删除条件 主键id或者下标20_30
     */
    public static function delCart($id)
    {
        // 判断登录状态 已登录，删除数据表；未登录，删除cookie
        if (session('?user_info')) {
            // 已登录，删除数据表
            $user_id = session('user_info.id');
            GoodsCart::where([
                'id' => $id,
                'user_id' => $user_id
            ])->delete();
        } else {
            // 未登录删除cookie
            // 从cookie中取出所有
            $data = cookie('cart') ? json_decode(stripslashes(html_entity_decode(cookie('cart'))), true) : [];
            // $id就是一个下标
            unset($data[$id]);
            // 重新保存到cookie
            $data = json_encode($data);
            cookie('cart', $data, 86400*7);
        }
    }

    /**
     * 修改选中状态
     * @param $id int 修改条件，all表示全选
     * @param $isSelected int 选中状态，1表示选中，0表示未选中
     */
    public static function changeStatus($id, $isSelected)
    {
        // 判断登录状态 登录，修改数据表;未登录，修改cookie
        if (session('?user_info')) {
            // 登录，修改数据表
            $user_id = session('user_info.id');
            $where['user_id'] = $user_id;
            if ($id != 'all') {
                $where['id'] = $id;
            }
            GoodsCart::where($where)->update(['is_selected' => $isSelected]);
        } else {
            // 未登录，修改cookie
            // 取出所有cookie购物车数据
            $data = cookie('cart') ? json_decode(stripslashes(html_entity_decode(cookie('cart'))), true) : [];
            // $id就是一个下标
            if ($id == 'all') {
                // 全部修改
                foreach ($data as &$v) {
                    $v['is_selected'] = $isSelected;
                }
                unset($v);
            } else {
                // 修改一个
                $data[$id]['is_selected'] = $isSelected;
            }
            // 重新保存到cookie
            $data = json_encode($data);
            cookie('cart', $data, 86400*7);
        }
    }

    // 登录后将cookie购物车数据迁移到数据表
    public static function cookieToDb()
    {
        // 从cookie中取出所有数据
        $data = cookie('cart') ? json_decode(stripslashes(html_entity_decode(cookie('cart'))), true) : [];
        // 将数据添加/修改到数据表
        foreach ($data as $v) {
            self::addCart($v['goods_spu_id'], $v['goods_sku_id'], $v['number'], $v['is_selected']);
        }
        // 删除cookie购物车数据
        cookie('cart', null);
    }
}