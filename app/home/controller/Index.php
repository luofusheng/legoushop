<?php
declare (strict_types = 1);

namespace app\home\controller;

use app\home\model\GoodsCategory;
use app\home\model\GoodsSpu;

class Index extends Base
{
    // 前台首页
    public function index()
    {
        // 精品推荐
        $recommend = GoodsSpu::limit(5)->select()->toArray();

        // 查询出所有手机分类
        $cate = GoodsCategory::where('name', '手机')->find();
        if (empty($cate)) {
            $mobile['goods'] = [];
            $mobile['category'] = [];
            $mobile['first_cate_id'] = 0;
        } else {
            $mobile['first_cate_id'] = $cate->id;
            $cate = GoodsCategory::where('pid', $cate->id)->select();
            if ($cate->isEmpty()) {
                $mobile['goods'] = [];
                $mobile['category'] = [];
            } else {
                $mobile['category'] = $cate->toArray();
                $ids = array_column($cate->toArray(), 'id');
                $mobile['goods'] = GoodsSpu::where('goods_category_id', 'in', $ids)
                    ->limit(10)->select();
                if ($mobile['goods']->isEmpty()) {
                    $mobile['goods'] = [];
                } else {
                    $mobile['goods'] = $mobile['goods']->toArray();
                }
            }
        }

        // 查询出所有笔记本分类
        $cate = GoodsCategory::where('name', '笔记本')->find();
        if (empty($cate)) {
            $laptop['goods'] = [];
            $laptop['category'] = [];
            $laptop['first_cate_id'] = 0;
        } else {
            $laptop['first_cate_id'] = $cate->id;
            $cate = GoodsCategory::where('pid', $cate->id)->select();
            if ($cate->isEmpty()) {
                $laptop['goods'] = [];
                $laptop['category'] = [];
            } else {
                $laptop['category'] = $cate->toArray();
                $ids = array_column($cate->toArray(), 'id');
                $laptop['goods'] = GoodsSpu::where('goods_category_id', 'in', $ids)
                    ->limit(10)->select();
                if ($laptop['goods']->isEmpty()) {
                    $laptop['goods'] = [];
                } else {
                    $laptop['goods'] = $laptop['goods']->toArray();
                }
            }
        }

        // 查询出所有平板分类
        $cate = GoodsCategory::where('name', '平板')->find();
        if (empty($cate)) {
            $pingban['goods'] = [];
            $pingban['category'] = [];
            $pingban['first_cate_id'] = 0;
        } else {
            $pingban['first_cate_id'] = $cate->id;
            $cate = GoodsCategory::where('pid', $cate->id)->select();
            if ($cate->isEmpty()) {
                $pingban['goods'] = [];
                $pingban['category'] = [];
            } else {
                $pingban['category'] = $cate->toArray();
                $ids = array_column($cate->toArray(), 'id');
                $pingban['goods'] = GoodsSpu::where('goods_category_id', 'in', $ids)
                    ->limit(10)->select();
                if ($pingban['goods']->isEmpty()) {
                    $pingban['goods'] = [];
                } else {
                    $pingban['goods'] = $pingban['goods']->toArray();
                }
            }
        }

        // 查询出所有智能穿戴分类
        $cate = GoodsCategory::where('name', '智能穿戴')->find();
        if (empty($cate)) {
            $chuandai['goods'] = [];
            $chuandai['category'] = [];
            $chuandai['first_cate_id'] = 0;
        } else {
            $chuandai['first_cate_id'] = $cate->id;
            $cate = GoodsCategory::where('pid', $cate->id)->select();
            if ($cate->isEmpty()) {
                $chuandai['goods'] = [];
                $chuandai['category'] = [];
            } else {
                $chuandai['category'] = $cate->toArray();
                $ids = array_column($cate->toArray(), 'id');
                $chuandai['goods'] = GoodsSpu::where('goods_category_id', 'in', $ids)
                    ->limit(10)->select();
                if ($chuandai['goods']->isEmpty()) {
                    $chuandai['goods'] = [];
                } else {
                    $chuandai['goods'] = $chuandai['goods']->toArray();
                }
            }
        }

        // 查询出所有智能家具分类
        $cate = GoodsCategory::where('name', '智能家具')->find();
        if (empty($cate)) {
            $furniture['goods'] = [];
            $furniture['category'] = [];
            $furniture['first_cate_id'] = 0;
        } else {
            $furniture['first_cate_id'] = $cate->id;
            $cate = GoodsCategory::where('pid', $cate->id)->select();
            if ($cate->isEmpty()) {
                $furniture['goods'] = [];
                $furniture['category'] = [];
            } else {
                $furniture['category'] = $cate->toArray();
                $ids = array_column($cate->toArray(), 'id');
                $furniture['goods'] = GoodsSpu::where('goods_category_id', 'in', $ids)
                    ->limit(10)->select();
                if ($furniture['goods']->isEmpty()) {
                    $furniture['goods'] = [];
                } else {
                    $furniture['goods'] = $furniture['goods']->toArray();
                }
            }
        }

        // 查询出所有电视分类
        $cate = GoodsCategory::where('name', '电视')->find();
        if (empty($cate)) {
            $tv['goods'] = [];
            $tv['category'] = [];
            $tv['first_cate_id'] = 0;
        } else {
            $tv['first_cate_id'] = $cate->id;
            $cate = GoodsCategory::where('pid', $cate->id)->select();
            if ($cate->isEmpty()) {
                $tv['goods'] = [];
                $tv['category'] = [];
            } else {
                $tv['category'] = $cate->toArray();
                $ids = array_column($cate->toArray(), 'id');
                $tv['goods'] = GoodsSpu::where('goods_category_id', 'in', $ids)
                    ->limit(10)->select();
                if ($tv['goods']->isEmpty()) {
                    $tv['goods'] = [];
                } else {
                    $tv['goods'] = $tv['goods']->toArray();
                }
            }
        }

        return view('', [
            'recommend' => $recommend,
            'mobile' => $mobile,
            'laptop' => $laptop,
            'pingban' => $pingban,
            'chuandai' => $chuandai,
            'furniture' => $furniture,
            'tv' => $tv
        ]);
    }
}
