<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\model\GoodsBrand;
use app\admin\model\GoodsDetail;
use app\admin\model\GoodsSpu;
use think\facade\Db;
use think\facade\Filesystem;
use think\facade\Validate;

class GoodsList extends Base
{
    // 商品列表页
    public function index()
    {
        return view();
    }

    // 商品列表数据接口
    public function list()
    {
        $page = request()->param('page');
        $limit = request()->param('limit');
        $name = request()->param('name');

        $goodsList = GoodsSpu::with(['goodsCategory'])
            ->field('id,name,goods_category_id,is_on_sale,price,stock')
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
        $goodsListData = $goodsList->toArray()['data'];
        // 添加分类完整路径数据
        foreach ($goodsListData as &$v) {
            $v['category'] = $v['category_pid_pathname'] .'/' . $v['category_name'];
        }
        unset($v);

        $data = [
            'code' => 0,
            'msg' => '',
            'count' => $goodsList->total(),
            'data' => $goodsListData
        ];

        return json($data);
    }

    // 商品列表添加页面
    public function add()
    {
        // 查询所有分类
        $goodsCategory = \app\admin\model\GoodsCategory::field('id,pid,name')->select()->toArray();
        $goodsCategory = get_tree_list2($goodsCategory);

        // 查询所有模型
        $goodsType = \app\admin\model\GoodsType::select()->toArray();

        return view('', [
            'goodsCategory' => json_encode($goodsCategory),
            'goodsType' => $goodsType
        ]);
    }

    // 商品列表添加页面获取品牌数据接口
    public function getBrand()
    {
        // 接收参数
        $page = request()->param('page');
        $limit = request()->param('limit');
        $name = request()->param('keyword');

        $goodsBrand = GoodsBrand::field('id,name')
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
        $data = [
            'code' => 0,
            'msg' => '',
            'count' => $goodsBrand->total(),
            'data' => $goodsBrand->toArray()['data']
        ];

        return json($data);
    }

    // 商品logo图片上传接口
    public function uploadLogo()
    {
        $file = request()->file('file');
        try {
            validate(['file' => ['fileSize:1024000', 'fileExt:jpg,png,gif,jpeg']])->check(['file' => $file]);
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

    // 详情描述富文本编辑器图片上传接口
    // 多图片上传
    public function uploadImg()
    {
        $files = request()->file();
        $savename = [];
        try {
            validate(['image' => ['fileSize:1024000', 'fileExt:jpg,png,gif,jpeg']])
                ->check($files);

            foreach($files as $file) {
                $savename[] = '/uploads/' . Filesystem::disk('public')->putFile('image/temp', $file);
            }
        } catch (\think\exception\ValidateException $e) {
            echo $e->getMessage();
        }

        return json([
            'errno' => 0,
            'data' => $savename
        ]);
    }

    // 商品列表添加页面通用信息保存接口
    public function save()
    {
        // 接收表单数据
        $params['logo'] = trim(input('post.logo', '', 'strip_tags'));
        $params['name'] = trim(input('post.name', '', 'strip_tags'));
        $params['keywords'] = trim(input('post.keywords', '', 'strip_tags'));
        $params['desc'] = trim(input('post.desc', '', 'strip_tags'));
        $params['brand'] = trim(input('post.brand', '', 'strip_tags'));
        $params['is_on_sale'] = trim(input('post.is_on_sale', '', 'strip_tags'));
        $params['is_free_shipping'] = trim(input('post.is_free_shipping', '', 'strip_tags'));
        $params['price'] = trim(input('post.price', '', 'strip_tags'));
        $params['stock'] = trim(input('post.stock', '', 'strip_tags'));
        $params['category'] = trim(input('post.category', '', 'strip_tags'));
        $params['detail'] = trim(input('post.detail'));
        $params['gallery'] = trim(input('post.goods_gallery', '', 'strip_tags'));

        // 表单数据验证
        $validate = Validate::rule([
            'name' => 'require|max:50',
            'keywords' => 'max:255',
            'desc' => 'max:200',
            'brand' => 'require',
            'price' => ['require','regex'=>'/(^[1-9]\d*(\.\d{1,2})?$)|(^0(\.\d{1,2})?$)/'],
            'stock' => 'number',
            'category' => 'require'
        ]);
        if (!$validate->check($params)) {
            return json([
                'code' => 1,
                'msg' => $validate->getError()
            ]);
        }

        // logo图片位置迁移
        if (!empty($params['logo'])) {
            // 将图片从临时temp文件夹中迁移到goods_list/logo文件夹中
            // 传过来的临时文件路径为：/uploads/image/temp/20201030/d885cc3b24b184a7c631408b5e0f670e.jpg
            // 要迁移的文件路径：/uploads/image/goods_list/logo/20201030/d885cc3b24b184a7c631408b5e0f670e.jpg
            // 新建日期文件夹
            $tempArray = explode('/', $params['logo']);
            $imageFloder = './uploads/image/goods_list/logo/' . $tempArray[4];
            if (!is_dir($imageFloder)) {
                mkdir($imageFloder, 0777, true);
            }
            $tempImg = '.' . $params['logo'];
            $newImg = str_replace('/temp/', '/goods_list/logo/', $tempImg);
            // 转移图片
            rename($tempImg, $newImg);
            // 修改图片路径为新路径
            $params['logo'] = ltrim($newImg, '.');
        }

        // 处理富文本编辑器传来的文本内容
        $newContent = $this->processContent($params['detail']);

        // 将数据存入数据库
        Db::startTrans();
        try {
            // 存入goods_spu表
            $goodsSpu = GoodsSpu::create([
                'name' => $params['name'],
                'keywords' => $params['keywords'],
                'desc' => $params['desc'],
                'goods_logo' => $params['logo'],
                'goods_brand_id' => $params['brand'],
                'goods_category_id' => $params['category'],
                'is_on_sale' => empty($params['is_on_sale']) ? '下架' : '上架',
                'is_free_shipping' => empty($params['is_free_shipping']) ? '不包邮' : '包邮',
                'price' => $params['price'],
                'stock' => $params['stock']
            ]);
            // 存入goods_detail表
            GoodsDetail::create([
                'goods_spu_id' => $goodsSpu->id,
                'content' => htmlspecialchars($newContent)
            ]);
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            // 返回操作失败信息
            return json([
                'code' => 1,
                'msg' => '保存失败'
            ]);
        }

        return json([
            'code' => 0,
            'msg' => '保存成功'
        ]);


        // $params = trim(input('post.goods_gallery', '', 'strip_tags'));
        // $gallery = explode(',', $params);

    }

    // 获取选中的模型规格属性数据接口
    public function getSpecAttr()
    {
        $type_id = (int)input('post.type_id', '', 'strip_tags');

        $type = \app\admin\model\GoodsType::with(['goodsSpecName.goodsSpecValue', 'goodsAttr'])->find($type_id);
        $data['goodsAttr'] = $type['goodsAttr'];
        $data['goodsSpecName'] = $type['goodsSpecName'];
        return json([
            'code' => 0,
            'msg' => '操作成功',
            'data' => $data
        ]);
    }

    // 商品列表相册图片上传接口
    public function uploadGallery()
    {
        $file = request()->file('file');
        try {
            validate(['file' => ['fileSize:1024000', 'fileExt:jpg,png,gif,jpeg']])->check(['file' => $file]);
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

    // 富文本编辑器文本内容处理方法
    private function processContent($content)
    {
        // 转移图片
        if (!empty($content)) {
            // 已经存在的图片
            $imgArray = [];
            // 完成处理的content
            $newContent = null;

            // 正则表达式匹配查找图片路径
            $pattern = '/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.jpeg|\.png]))[\'|\"].*?[\/]?>/i';
            preg_match_all($pattern, $content, $matches);
            $count = count($matches[1]);
            for ($i=0; $i < $count; $i++) {
                $editorImg = $matches[1][$i];
                // 判断是否是新上传的图片
                $position = stripos($editorImg, "/temp/");
                if ($position > 0) {    // 新上传的图片走这里
                    // 将图片从临时temp文件夹中迁移到goods_list/detail_img文件夹中
                    // 传过来的临时文件路径为：/uploads/image/temp/20201030/d885cc3b24b184a7c631408b5e0f670e.jpg
                    // 要迁移的文件路径：/uploads/image/goods_list/detail_img/20201030/d885cc3b24b184a7c631408b5e0f670e.jpg
                    // 新建日期文件夹
                    $tempArray = explode('/', $editorImg);
                    $imageFloder = './uploads/image/goods_list/detail_img/' . $tempArray[4];
                    if (!is_dir($imageFloder)) {
                        mkdir($imageFloder, 0777, true);
                    }
                    $tempImg = '.' . $editorImg;
                    $newImg = str_replace('/temp/', '/goods_list/detail_img/', $tempImg);
                    // 转移图片
                    rename($tempImg, $newImg);
                } else {
                    // 已经存在的图片走这里
                    $imgArray[] = $editorImg;
                }
            }

            // 重新整理content中的图片路径
            $newContent = str_replace('/temp/', '/goods_list/detail_img/', $content);
            return $newContent;
        }

        return false;
    }
}
