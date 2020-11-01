<?php
declare (strict_types = 1);

namespace app\admin\controller;

use think\facade\Filesystem;
use think\facade\Validate;

class GoodsCategory extends Base
{
    // 商品分类列表
    public function index()
    {
        return view();
    }

    // 商品分类列表数据接口
    public function list()
    {
        $categoryList = \app\admin\model\GoodsCategory::select()->toArray();
        $data = [
            'code' => 0,
            'msg' => '',
            'count' => '',
            'data' => $categoryList
        ];

        return json($data);
    }

    // 商品分类添加页
    public function add()
    {
        // 查询到所有分类
        $categoryList = \app\admin\model\GoodsCategory::field('id,pid,name,level')
            ->where('level', '<', 2)
            ->select()->toArray();
        $categoryList = get_cate_list($categoryList);

        return view('', [
            'categoryList' => $categoryList
        ]);
    }

    // 商品分类上传图片接口
    public function upload()
    {
        $file = request()->file('file');
        try {
            validate(['file' => ['fileSize:102400', 'fileExt:jpg,png,gif,jpeg']])->check(['file' => $file]);
        } catch (\think\exception\ValidateException $e) {
            return json([
                'code' => 1,
                'msg' => $e->getMessage(),
                'data' => [
                    'src' => ''
                ]
            ]);
        }
        $savename = Filesystem::disk('public')->putFile('image/temp', $file);

        return json([
            'code' => 0,
            'msg' => '',
            'data' => [
                'src' => '/uploads/'.$savename
            ]
        ]);
    }

    // 分类添加页面保存接口
    public function save()
    {
        $params['image'] = trim(input('post.image', '', 'strip_tags'));
        $params['name'] = trim(input('post.name', '', 'strip_tags'));
        $params['pid'] = (int)input('post.pid', '', 'strip_tags');
        $params['sort'] = (int)input('post.sort', '', 'strip_tags');

        $validate = Validate::rule([
            'name|名称' => 'require|max:20',
            'pid|上级分类' => 'require|integer|max:10',
            'sort|排序' => 'max:10'
        ]);
        if (!$validate->check($params)) {
            return json([
                'code' => 1,
                'msg' => $validate->getError()
            ]);
        }

        // 补充数据库表剩下的参数
        if ($params['pid'] == 0) {  // 如果添加的是顶级分类
            $params['pid_path'] = '0';
            $params['pid_path_name'] = '';
            $params['level'] = 0;
        } else {    // 如果添加的是非顶级分类
            // 先获取父级分类
            $pCategory = \app\admin\model\GoodsCategory::find($params['pid'])->toArray();
            $params['pid_path'] = $pCategory['pid_path'] . '_' . $params['pid'];
            if (empty($pCategory['pid_path_name'])) {
                $params['pid_path_name'] = $pCategory['name'];
            } else {
                $params['pid_path_name'] = $pCategory['pid_path_name'] . '_' . $pCategory['name'];
            }
            $params['level'] = $pCategory['level'] + 1;
        }

        // 图片处理
        if (!empty($params['image'])) {
            // 将图片从临时temp文件夹中迁移到goods_category文件夹中
            // 传过来的临时文件路径为：/uploads/image/temp/20201030/d885cc3b24b184a7c631408b5e0f670e.jpg
            // 要迁移的文件路径：/uploads/image/goods_category/20201030/d885cc3b24b184a7c631408b5e0f670e.jpg
            // 新建日期文件夹
            $tempArray = explode('/', $params['image']);
            $imageFloder = './uploads/image/goods_category/' . $tempArray[4];
            if (!is_dir($imageFloder)) {
                mkdir($imageFloder, 0777, true);
            }
            $tempImg = '.' . $params['image'];
            $newImg = str_replace('/temp/', '/goods_category/', $tempImg);
            // 转移图片
            rename($tempImg, $newImg);
            // 修改图片路径为新路径
            $params['image'] = ltrim($newImg, '.');
        }

        \app\admin\model\GoodsCategory::create($params);

        return json([
            'code' => 0,
            'msg' => '保存成功'
        ]);
    }

    // 商品分类修改页面
    public function edit()
    {
        // 当前要修改的分类信息
        $id = (int)input('get.id', '', 'strip_tags');
        $category = \app\admin\model\GoodsCategory::find($id)->toArray();

        // 查询到所有分类
        $categoryList = \app\admin\model\GoodsCategory::field('id,pid,name,level')
            ->where('level', '<', 2)
            ->select()->toArray();
        $categoryList = get_cate_list($categoryList);

        return view('', [
            'category' => $category,
            'categoryList' => $categoryList
        ]);
    }

    // 商品分类修改页面保存接口
    public function update()
    {
        $params['id'] = (int)trim(input('post.id', '', 'strip_tags'));
        $params['image'] = trim(input('post.image', '', 'strip_tags'));
        $params['name'] = trim(input('post.name', '', 'strip_tags'));
        $params['pid'] = (int)input('post.pid', '', 'strip_tags');
        $params['sort'] = (int)input('post.sort', '', 'strip_tags');

        $validate = Validate::rule([
            'name|名称' => 'require|max:20',
            'pid|上级分类' => 'require|integer|max:10',
            'sort|排序' => 'max:10'
        ]);
        if (!$validate->check($params)) {
            return json([
                'code' => 1,
                'msg' => $validate->getError()
            ]);
        }

        // 补充数据库表剩下的参数
        if ($params['pid'] == 0) {  // 如果修改后是顶级分类
            $params['pid_path'] = '0';
            $params['pid_path_name'] = '';
            $params['level'] = 0;
        } else {    // 如果修改后不是顶级分类
            // 先获取父级分类
            $pCategory = \app\admin\model\GoodsCategory::find($params['pid'])->toArray();
            $params['pid_path'] = $pCategory['pid_path'] . '_' . $params['pid'];
            if (empty($pCategory['pid_path_name'])) {
                $params['pid_path_name'] = $pCategory['name'];
            } else {
                $params['pid_path_name'] = $pCategory['pid_path_name'] . '_' . $pCategory['name'];
            }
            $params['level'] = $pCategory['level'] + 1;
        }

        // 判断分类图片有没有被改动
        $category = \app\admin\model\GoodsCategory::find($params['id'])->toArray();
        if ($category['image'] != $params['image']) {   // 如果分类图片被改动了
            if (!empty($params['image'])) {
                // 将图片从临时temp文件夹中迁移到goods_category文件夹中
                // 传过来的临时文件路径为：/uploads/image/temp/20201030/d885cc3b24b184a7c631408b5e0f670e.jpg
                // 要迁移的文件路径：/uploads/image/goods_category/20201030/d885cc3b24b184a7c631408b5e0f670e.jpg
                // 新建日期文件夹
                $tempArray = explode('/', $params['image']);
                $imageFloder = './uploads/image/goods_category/' . $tempArray[4];
                if (!is_dir($imageFloder)) {
                    mkdir($imageFloder, 0777, true);
                }
                $tempImg = '.' . $params['image'];
                $newImg = str_replace('/temp/', '/goods_category/', $tempImg);
                // 转移图片
                rename($tempImg, $newImg);
                // 修改图片路径为新路径
                $params['image'] = ltrim($newImg, '.');
            }
        }

        \app\admin\model\GoodsCategory::update($params);

        return json([
            'code' => 0,
            'msg' => '保存成功'
        ]);
    }

    // 商品分类列表删除接口
    public function delete()
    {
        $id = (int)trim(input('post.id', '', 'strip_tags'));
        // 删除对应的分类图片
        $category = \app\admin\model\GoodsCategory::find($id);
        unlink('./' . $category->image);
        \app\admin\model\GoodsCategory::destroy($id);
        return json([
            'code' => 0,
            'msg' => '删除成功'
        ]);
    }
}
