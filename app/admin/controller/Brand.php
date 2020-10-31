<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\model\GoodsBrand;
use think\facade\Filesystem;
use think\facade\Validate;

class Brand extends Base
{
    // 品牌列表
    public function index()
    {
        return view();
    }

    // 商品品牌列表数据接口
    public function list()
    {
        $page = request()->param('page');
        $limit = request()->param('limit');
        $name = request()->param('name');

        $brandList = GoodsBrand::field('id,name,logo,desc')
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
        $brandListData = $brandList->toArray()['data'];

        $data = [
            'code' => 0,
            'msg' => '',
            'count' => $brandList->total(),
            'data' => $brandListData
        ];

        return json($data);
    }

    // 品牌添加页
    public function add()
    {
        return view();
    }

    // 商品品牌上传logo图片接口
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

    // 品牌添加页面保存接口
    public function save()
    {
        $params['logo'] = trim(input('post.logo', '', 'strip_tags'));
        $params['name'] = trim(input('post.name', '', 'strip_tags'));
        $params['desc'] = trim(input('post.desc', '', 'strip_tags'));

        $validate = Validate::rule([
            'name' => 'require|max:20'
        ]);
        if (!$validate->check($params)) {
            return json([
                'code' => 1,
                'msg' => $validate->getError()
            ]);
        }

        if (!empty($params['logo'])) {
            // 将图片从临时temp文件夹中迁移到brand文件夹中
            // 传过来的临时文件路径为：/uploads/image/temp/20201030/d885cc3b24b184a7c631408b5e0f670e.jpg
            // 要迁移的文件路径：/uploads/image/brand/20201030/d885cc3b24b184a7c631408b5e0f670e.jpg
            // 新建日期文件夹
            $tempArray = explode('/', $params['image']);
            $imageFloder = './uploads/image/brand/' . $tempArray[4];
            if (!is_dir($imageFloder)) {
                mkdir($imageFloder, 0777, true);
            }
            $tempImg = '.' . $params['image'];
            $newImg = str_replace('/temp/', '/brand/', $tempImg);
            // 转移图片
            rename($tempImg, $newImg);
            // 修改图片路径为新路径
            $params['logo'] = ltrim($newImg, '.');
        }

        GoodsBrand::create($params);

        return json([
            'code' => 0,
            'msg' => '保存成功'
        ]);
    }

    // 商品品牌修改页面
    public function edit()
    {
        // 当前要修改的品牌信息
        $id = (int)input('get.id', '', 'strip_tags');

        $brand = GoodsBrand::field('id,name,logo,desc')
            ->find($id)->toArray();

        return view('', [
            'brand' => $brand
        ]);
    }

    // 商品品牌修改页面保存接口
    public function update()
    {
        $params['id'] = (int)trim(input('post.id', '', 'strip_tags'));
        $params['logo'] = trim(input('post.logo', '', 'strip_tags'));
        $params['name'] = trim(input('post.name', '', 'strip_tags'));
        $params['desc'] = trim(input('post.desc', '', 'strip_tags'));

        $validate = Validate::rule([
            'id' => 'require',
            'name' => 'require|max:20'
        ]);
        if (!$validate->check($params)) {
            return json([
                'code' => 1,
                'msg' => $validate->getError()
            ]);
        }

        // 判断分类图片有没有被改动
        $brand = GoodsBrand::find($params['id'])->toArray();
        if ($brand['logo'] != $params['logo']) {   // 如果品牌图片被改动了
            if (!empty($params['logo'])) {
                // 将图片从临时temp文件夹中迁移到brand文件夹中
                // 传过来的临时文件路径为：/uploads/image/temp/20201030/d885cc3b24b184a7c631408b5e0f670e.jpg
                // 要迁移的文件路径：/uploads/image/goods_category/20201030/d885cc3b24b184a7c631408b5e0f670e.jpg
                // 新建日期文件夹
                $tempArray = explode('/', $params['logo']);
                $imageFloder = './uploads/image/brand/' . $tempArray[4];
                if (!is_dir($imageFloder)) {
                    mkdir($imageFloder, 0777, true);
                }
                $tempImg = '.' . $params['logo'];
                $newImg = str_replace('/temp/', '/brand/', $tempImg);
                // 转移图片
                rename($tempImg, $newImg);
                // 修改图片路径为新路径
                $params['logo'] = ltrim($newImg, '.');
            }
        }

        GoodsBrand::update($params);

        return json([
            'code' => 0,
            'msg' => '修改成功'
        ]);
    }

    // 品牌列表删除接口
    public function delete()
    {
        $id = (int)trim(input('post.id', '', 'strip_tags'));
        // 删除对应的logo图片
        $brand = GoodsBrand::find($id);
        unlink('./' . $brand->logo);
        GoodsBrand::destroy($id);
        return json([
            'code' => 0,
            'msg' => '删除成功'
        ]);
    }

    // 商品品牌列表多选删除接口
    public function deleteMulti()
    {
        $ids = input('post.ids', '', 'strip_tags');
        // 批量删除对应的logo图片
        $brandList = GoodsBrand::select($ids);
        foreach ($brandList as $value) {
            unlink('./' . $value->logo);
        }
        // 批量删除品牌
        GoodsBrand::destroy($ids);

        return json([
            'code' => 0,
            'msg' => '删除成功'
        ]);
    }

}
