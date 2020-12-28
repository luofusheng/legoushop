<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\model\GoodsBrand;
use app\admin\model\GoodsDetail;
use app\admin\model\GoodsImages;
use app\admin\model\GoodsSku;
use app\admin\model\GoodsSpecValue;
use app\admin\model\GoodsSpu;
use think\facade\Db;
use think\facade\Filesystem;
use think\facade\Validate;
use think\Image;

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
        // 接收通用信息数据
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
        $params['goods_type'] = trim(input('post.goods_type', '', 'strip_tags'));
        // 接收商品相册图片
        $params['gallery'] = trim(input('post.goods_gallery', '', 'strip_tags'));
        // 接收商品模型数据，格式为：
        /*
         * 规格值，其中的55-58表示规格id的组合
        item[55_58][price]: 4999
        item[55_58][value_names]: 颜色:红色 内存:4+64
        item[55_58][value_ids]: 55_58
        item[55_58][store_count]: 999
        item[55_59][price]: 4999
        item[55_59][value_names]: 颜色:红色 内存:4+128
        item[55_59][value_ids]: 55_59
        item[55_59][store_count]: 999

        属性值：
        attr[21][attr_name]: 重量
        attr[21][id]: 21
        attr[21][attr_value]: 200g
        attr[22][attr_name]: 版本
        attr[22][id]: 22
        attr[22][attr_value]: 国行
        */
        $params['sku'] = input('post.item', '', 'strip_tags');
        $params['attr'] = input('post.attr', '', 'strip_tags');

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

        // logo图片位置迁移，然后生成缩略图
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

            // 生成缩略图
            // 给缩略图重新取名
            $goodsLogo = dirname($params['logo']) . DIRECTORY_SEPARATOR . 'thumb_' . basename($params['logo']);
            // 生成缩略图
            Image::open('.' . $params['logo'])->thumb(210, 240)->save('.' . $goodsLogo);
            // 删除掉原图片
            unlink('.' . $params['logo']);
            // 将生成的缩略图存入
            $params['logo'] = $goodsLogo;
        }

        // 处理富文本编辑器传来的文本内容
        $newContent = $this->processContent($params['detail']);

        // 商品相册处理
        $goodsImages = [];
        if (!empty($params['gallery'])) {
            // 先将字符串转换成数组
            $params['gallery'] = rtrim($params['gallery'], ',');
            $params['gallery'] = explode(',', $params['gallery']);
            foreach ($params['gallery'] as $image) {
                // 先迁移图片位置
                // 将图片从临时temp文件夹中迁移到goods_list/gallery_img文件夹中
                // 传过来的临时文件路径为：/uploads/image/temp/20201030/d885cc3b24b184a7c631408b5e0f670e.jpg
                // 要迁移的文件路径：/uploads/image/goods_list/gallery_img/20201030/d885cc3b24b184a7c631408b5e0f670e.jpg
                // 新建日期文件夹
                $tempArray = explode('/', $image);
                $imageFloder = './uploads/image/goods_list/gallery_img/' . $tempArray[4];
                if (!is_dir($imageFloder)) {
                    mkdir($imageFloder, 0777, true);
                }
                $tempImg = '.' . $image;
                $newImg = str_replace('/temp/', '/goods_list/gallery_img/', $tempImg);
                // 转移图片
                rename($tempImg, $newImg);
                // 修改图片路径为新路径
                $image = ltrim($newImg, '.');


                // 生成两张不同尺寸的缩略图 800*800 400*400 （都为最大值）
                if (is_file('.' . $image)) {
                    // 定义两张缩略图路径
                    $bigImg = dirname($image) . DIRECTORY_SEPARATOR . 'thumb_800_' . basename($image);
                    $smallImg = dirname($image) . DIRECTORY_SEPARATOR . 'thumb_400_' . basename($image);
                    Image::open('.' . $image)->thumb(800, 800)->save('.' . $bigImg);
                    Image::open('.' . $image)->thumb(400, 400)->save('.' . $smallImg);
                    // 组装一条数据
                    $row = [
                        'goods_spu_id' => '',
                        'image_big' => $bigImg,
                        'image_small' => $smallImg
                    ];
                    $goodsImages[] = $row;

                    // 删除掉原来的图片
                    unlink('.' . $image);
                }
            }
        }

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
                'goods_attr' => json_encode($params['attr'], JSON_UNESCAPED_UNICODE),
                'price' => $params['price'],
                'stock' => $params['stock'],
                'goods_type_id' => $params['goods_type']
            ]);
            // 存入goods_detail表
            GoodsDetail::create([
                'goods_spu_id' => $goodsSpu->id,
                'content' => htmlspecialchars($newContent)
            ]);
            // 存入goods_images表
            if (!empty($goodsImages)) {
                // 添加goods_id
                foreach ($goodsImages as &$v) {
                    $v['goods_spu_id'] = $goodsSpu->id;
                }
                unset($v);
                // 批量存入数据
                $goodsImagesModel = new GoodsImages();
                $goodsImagesModel->saveAll($goodsImages);
            }
            // 批量添加SKU
            if (!empty($params['sku'])) {
                $goodsSku = [];
                foreach ($params['sku'] as $v) {
                    $row = [
                        'goods_spu_id' => $goodsSpu->id,
                        'spec_value_ids' => $v['value_ids'],
                        'spec_name_values' => $v['value_names'],
                        'price' => $v['price'],
                        'stock' => $v['store_count']
                    ];
                    $goodsSku[] = $row;
                }
                $goodsSkuModel = new GoodsSku();
                $goodsSkuModel->saveAll($goodsSku);
            }

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

    }

    // 商品列表修改页面
    public function edit()
    {
        $id = (int)input('get.id', '', 'strip_tags');

        // 查询所有分类
        $goodsCategory = \app\admin\model\GoodsCategory::field('id,pid,name')->select()->toArray();
        $goodsCategory = get_tree_list2($goodsCategory);

        // 查询所有模型
        $goodsType = \app\admin\model\GoodsType::select()->toArray();
        // 查询得到商品数据
        $goodsSpu = GoodsSpu::with(['goodsDetail', 'goodsImages', 'goodsSku','goodsType'=>['goodsSpecName.goodsSpecValue','goodsAttr']])->find($id)->toArray();

        /* 获得该商品分类的分类家谱数组 */
        $categorySelected = \app\admin\model\GoodsCategory::field('pid_path')->find($goodsSpu['goods_category_id'])->toArray();
        $categorySelected = explode('_', $categorySelected['pid_path']);
        // 移除最左边的元素
        array_splice($categorySelected, 0, 1);
        $categorySelected[] = $goodsSpu['goods_category_id'];

        /* 获得该商品所属品牌的名称 */
        $brand = GoodsBrand::field('id,name')->find($goodsSpu['goods_brand_id'])->toArray();

        // 获得该商品所拥有的规格值，即goodsSku表里面的spec_value_ids字段
        $specValueIds = explode('_', implode('_', array_column($goodsSpu['goodsSku'], 'spec_value_ids')));
        $goodsSpu['spec_value_ids'] = array_unique($specValueIds);

        // 用商品的SKU表里面的规格值集合数组，到商品规格值表中查询SKU包含的规格值数据，并将结果放到SKU下
        // 即goodsSpu['goodsSku']['spec_values']
        foreach ($goodsSpu['goodsSku'] as $k=>&$v) {
            $v['spec_values'] = GoodsSpecValue::select(explode('_', $v['spec_value_ids']))->toArray();
        }
        unset($v);

        // 将商品数据里面的商品属性json字符串转换成数组
        $goodsSpu['goods_attr'] = json_decode($goodsSpu['goods_attr'], true);

        return view('', [
            'goodsCategory' => json_encode($goodsCategory),
            'goodsType' => $goodsType,
            'goodsSpu' => $goodsSpu,
            // 分类家谱数组
            'categoryPath' => json_encode($categorySelected),
            // 所属品牌名称
            'brand' => $brand
        ]);
    }

    // 商品修改页面保存接口
    public function update()
    {
        // 接收通用信息数据
        $params['goods_spu_id'] = trim(input('post.goods_spu_id', '', 'strip_tags'));
        $params['logo'] = trim(input('post.logo', '', 'strip_tags'));
        $params['old_logo'] = trim(input('post.old_logo', '', 'strip_tags'));
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
        $params['goods_type'] = trim(input('post.goods_type', '', 'strip_tags'));
        // 接收商品相册图片
        $params['gallery'] = trim(input('post.goods_gallery', '', 'strip_tags'));
        // 接收商品模型数据，格式为：
        /*
         * 规格值，其中的55-58表示规格id的组合
        item[55_58][price]: 4999
        item[55_58][value_names]: 颜色:红色 内存:4+64
        item[55_58][value_ids]: 55_58
        item[55_58][store_count]: 999
        item[55_59][price]: 4999
        item[55_59][value_names]: 颜色:红色 内存:4+128
        item[55_59][value_ids]: 55_59
        item[55_59][store_count]: 999

        属性值：
        attr[21][attr_name]: 重量
        attr[21][id]: 21
        attr[21][attr_value]: 200g
        attr[22][attr_name]: 版本
        attr[22][id]: 22
        attr[22][attr_value]: 国行
        */
        $params['sku'] = input('post.item', '', 'strip_tags');
        $params['attr'] = input('post.attr', '', 'strip_tags');

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

        // logo图片处理
        if (!empty($params['logo'])) {
            // 判断logo有没有改变
            if ($params['logo'] != $params['old_logo']) { // 新增logo图片，logo改变了
                // 删除掉原先的老图片
                unlink('.' . $params['old_logo']);

                // logo图片位置迁移，然后生成缩略图
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

                // 生成缩略图
                // 给缩略图重新取名
                $goodsLogo = dirname($params['logo']) . DIRECTORY_SEPARATOR . 'thumb_' . basename($params['logo']);
                // 生成缩略图
                Image::open('.' . $params['logo'])->thumb(210, 240)->save('.' . $goodsLogo);
                // 删除掉原图片
                unlink('.' . $params['logo']);
                // 将生成的缩略图存入
                $params['logo'] = $goodsLogo;
            }
        }

        // 处理富文本编辑器传来的文本内容
        $newContent = $this->processContent($params['detail']);

        // 找到原先content中存在但是富文本编辑器在本次编辑中删除的图片的url，然后删除该图片
        // 找到数据库中存储的content
        $oldContent = GoodsDetail::where('goods_spu_id', $params['goods_spu_id'])->find();
        $oldContent = htmlspecialchars_decode($oldContent['content']);
        // 正则表达式匹配查找图片路径
        $pattern = '/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.jpeg|\.png]))[\'|\"].*?[\/]?>/i';
        preg_match_all($pattern, $oldContent, $oldMatches);
        $oldImgs = $oldMatches[1];
        preg_match_all($pattern, $newContent, $newMatches);
        $newImgs = $newMatches[1];
        // 删除图片
        for ($i=0; $i < count($oldImgs); $i++) {
            if (!in_array($oldImgs[$i], $newImgs)) {
                unlink('.' . $oldImgs[$i]);
            }
        }

        // 商品相册处理
        $goodsImages = [];
        if (!empty($params['gallery'])) {
            // 先将字符串转换成数组
            $params['gallery'] = rtrim($params['gallery'], ',');
            $params['gallery'] = explode(',', $params['gallery']);
            foreach ($params['gallery'] as $image) {
                // 先迁移图片位置
                // 将图片从临时temp文件夹中迁移到goods_list/gallery_img文件夹中
                // 传过来的临时文件路径为：/uploads/image/temp/20201030/d885cc3b24b184a7c631408b5e0f670e.jpg
                // 要迁移的文件路径：/uploads/image/goods_list/gallery_img/20201030/d885cc3b24b184a7c631408b5e0f670e.jpg
                // 新建日期文件夹
                $tempArray = explode('/', $image);
                $imageFloder = './uploads/image/goods_list/gallery_img/' . $tempArray[4];
                if (!is_dir($imageFloder)) {
                    mkdir($imageFloder, 0777, true);
                }
                $tempImg = '.' . $image;
                $newImg = str_replace('/temp/', '/goods_list/gallery_img/', $tempImg);
                // 转移图片
                rename($tempImg, $newImg);
                // 修改图片路径为新路径
                $image = ltrim($newImg, '.');


                // 生成两张不同尺寸的缩略图 800*800 400*400 （都为最大值）
                if (is_file('.' . $image)) {
                    // 定义两张缩略图路径
                    $bigImg = dirname($image) . DIRECTORY_SEPARATOR . 'thumb_800_' . basename($image);
                    $smallImg = dirname($image) . DIRECTORY_SEPARATOR . 'thumb_400_' . basename($image);
                    Image::open('.' . $image)->thumb(800, 800)->save('.' . $bigImg);
                    Image::open('.' . $image)->thumb(400, 400)->save('.' . $smallImg);
                    // 组装一条数据
                    $row = [
                        'goods_spu_id' => '',
                        'image_big' => $bigImg,
                        'image_small' => $smallImg
                    ];
                    $goodsImages[] = $row;

                    // 删除掉原来的图片
                    unlink('.' . $image);
                }
            }
        }

        // 将数据存入数据库
        Db::startTrans();
        try {
            // 更新goods_spu表
            $goodsSpuModel = GoodsSpu::find($params['goods_spu_id']);
            $goodsSpuModel->name = $params['name'];
            $goodsSpuModel->keywords = $params['keywords'];
            $goodsSpuModel->desc = $params['desc'];
            $goodsSpuModel->goods_logo = $params['logo'];
            $goodsSpuModel->goods_brand_id = $params['brand'];
            $goodsSpuModel->goods_category_id = $params['category'];
            $goodsSpuModel->is_on_sale = empty($params['is_on_sale']) ? '下架' : '上架';
            $goodsSpuModel->is_free_shipping = empty($params['is_free_shipping']) ? '不包邮' : '包邮';
            $goodsSpuModel->goods_attr = json_encode($params['attr'], JSON_UNESCAPED_UNICODE);
            $goodsSpuModel->price = $params['price'];
            $goodsSpuModel->stock = $params['stock'];
            $goodsSpuModel->goods_type_id = $params['goods_type'];
            $goodsSpuModel->save();
            // 更新goods_detail表
            $goodsDetail = GoodsDetail::where('goods_spu_id', $params['goods_spu_id'])->find();
            $goodsDetail->content = htmlspecialchars($newContent);
            $goodsDetail->save();
            // 新增的相册图片存入goods_images表
            if (!empty($goodsImages)) {
                // 添加goods_id
                foreach ($goodsImages as &$v) {
                    $v['goods_spu_id'] = $params['goods_spu_id'];
                }
                unset($v);
                // 批量存入数据
                $goodsImagesModel = new GoodsImages();
                $goodsImagesModel->saveAll($goodsImages);
            }
            // 更新SKU
            if (!empty($params['sku'])) {
                // 首先删除原来的SKU
                GoodsSku::where('goods_spu_id', $params['goods_spu_id'])->delete();
                // 然后添加新的SKU
                $goodsSku = [];
                foreach ($params['sku'] as $v) {
                    $row = [
                        'goods_spu_id' => $params['goods_spu_id'],
                        'spec_value_ids' => $v['value_ids'],
                        'spec_name_values' => $v['value_names'],
                        'price' => $v['price'],
                        'stock' => $v['store_count']
                    ];
                    $goodsSku[] = $row;
                }
                $goodsSkuModel = new GoodsSku();
                $goodsSkuModel->saveAll($goodsSku);
            }

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
    }

    // 商品列表删除
    public function delete()
    {
        $id = (int)trim(input('post.id', '', 'strip_tags'));

        Db::startTrans();
        try {
            $goodsSpuModel = GoodsSpu::find($id);
            // 先删除logo图片
            unlink('.' . $goodsSpuModel->goods_logo);
            // 再删除goodsSpu表
            $goodsSpuModel->delete();

            $goodsDetailModel = GoodsDetail::where('goods_spu_id', $id)->find();
            // 删除content中的图片
            $content = $goodsDetailModel->content;
            // 正则表达式匹配查找图片路径
            $pattern = '/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.jpeg|\.png]))[\'|\"].*?[\/]?>/i';
            preg_match_all($pattern, $content, $matches);
            $images = $matches[1];
            // 删除图片
            for ($i=0; $i < count($images); $i++) {
                unlink('.' . $images[$i]);
            }
            // 删除goods_detail表
            $goodsDetailModel->delete();

            $goodsImagesModel = GoodsImages::where('goods_spu_id', $id)->select();
            // 删除相册图片
            if (!$goodsImagesModel->isEmpty()) { // 不为空
                foreach ($goodsImagesModel as $v) {
                    unlink('.' . $v->image_big);
                    unlink('.' . $v->image_small);
                }
            }
            // 删除goods_images表数据
            GoodsImages::where('goods_spu_id', $id)->delete();

            // 删除goods_sku表数据
            GoodsSku::where('goods_spu_id', $id)->delete();

            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            // 返回操作失败信息
            return json([
                'code' => 1,
                'msg' => '删除失败'
            ]);
        }

        return json([
            'code' => 0,
            'msg' => '删除成功'
        ]);
    }

    // 商品列表多选删除接口
    public function deleteMulti()
    {
        $ids = input('post.ids', '', 'strip_tags');

        Db::startTrans();
        try {
            $goodsSpuModel = GoodsSpu::select($ids);
            foreach ($goodsSpuModel as $v) {
                if (!empty($v->goods_logo)) {
                    // 先删除logo图片
                    unlink('.' . $v->goods_logo);
                }
            }
            // 再删除goodsSpu表
            GoodsSpu::destroy($ids);

            $goodsDetailModel = GoodsDetail::where('goods_spu_id', 'in', $ids)->select();
            if (!$goodsDetailModel->isEmpty()) {
                foreach ($goodsDetailModel as $v) {
                    $content = $v->content;
                    // 正则表达式匹配查找图片路径
                    $pattern = '/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.jpeg|\.png]))[\'|\"].*?[\/]?>/i';
                    preg_match_all($pattern, $content, $matches);
                    $images = $matches[1];
                    // 删除图片
                    for ($i=0; $i < count($images); $i++) {
                        unlink('.' . $images[$i]);
                    }
                }
            }
            // 删除goods_detail表
            GoodsDetail::where('goods_spu_id', 'in', $ids)->delete();

            $goodsImagesModel = GoodsImages::where('goods_spu_id', 'in', $ids)->select();
            // 删除相册图片
            if (!$goodsImagesModel->isEmpty()) { // 不为空
                foreach ($goodsImagesModel as $v) {
                    unlink('.' . $v->image_big);
                    unlink('.' . $v->image_small);
                }
            }
            // 删除goods_images表数据
            GoodsImages::where('goods_spu_id', 'in', $ids)->delete();

            // 删除goods_sku表数据
            GoodsSku::where('goods_spu_id', 'in', $ids)->delete();

            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            // 返回操作失败信息
            return json([
                'code' => 1,
                'msg' => '删除失败'
            ]);
        }

        return json([
            'code' => 0,
            'msg' => '删除成功'
        ]);
    }

    // 商品修改页面商品相册删除一张图片
    public function delImg()
    {
        $img = input('post.img', '', 'strip_tags');
        // 从数据库中删除该图片
        GoodsImages::where('image_small', $img)->delete();
        // 从文件系统中删除该图片
        // 删除400*400图片
        unlink('.' . $img);
        // 删除800*800图片
        // 800*800图片路径
        $bigImg = dirname($img) . '/' . 'thumb_800_' . substr(basename($img), 10);
        unlink('.' . $bigImg);

        return json([
            'code' => 0,
            'msg' => '删除成功'
        ]);
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
