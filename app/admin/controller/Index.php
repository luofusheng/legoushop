<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\model\AdminMenu;
use app\admin\model\AdminRole;
use app\BaseController;
use think\facade\Db;

class Index extends BaseController
{
    // 控制器中间件
    protected array $middleware = ['app\middleware\IsLogin'];

    // 首页框架
    public function index()
    {
        $adminInfo = session('adminInfo');

        return view('', [
            'adminInfo' => $adminInfo
        ]);
    }

    // 首页页面
    public function home()
    {
        return view();
    }

    // 基本资料
    public function basicInfo()
    {
        $id = (int)input('get.id', '', 'strip_tags');
        $basicInfo = \app\admin\model\Admin::find($id)->toArray();

        $roleList = AdminRole::field('id,name')->select()->toArray();

        return view('', [
            'basicInfo' => $basicInfo,
            'roleList' => $roleList
        ]);
    }

    // 获取初始化数据
    public function getSystemInit()
    {
        $homeInfo = [
            'title' => '首页',
            'href'  => '/admin/home/',
        ];
        $logoInfo = [
            'title' => '品优购后台',
            'image' => '/static/admin/images/logo.png',
            'href' => '/admin/'
        ];
        $menuInfo = $this->getMenuList();
        $systemInit = [
            'homeInfo' => $homeInfo,
            'logoInfo' => $logoInfo,
            'menuInfo' => $menuInfo,
        ];
        return json($systemInit);
    }

    // 缓存清理接口
    public function clearCache()
    {
        // 删除临时图片
        delete_dir('./uploads/image/temp');
    }

    // 获取菜单列表
    private function getMenuList(){
        // 获取该用户拥有权限的菜单
        $adminInfo = session('adminInfo');
        if ($adminInfo['id'] == 1) {
            // 如果是超级管理员
            $menuList = Db::name('admin_menu')
                ->field('id,pid,title,icon,href,target')
                ->where([
                    ['status', '=', 1],
                    ['is_nav', '=', 1]
                ])
                ->order('sort', 'desc')
                ->select();
        } else {
            // 如果是其他管理员
            $role = AdminRole::where('id', $adminInfo['admin_role_id'])->find();
            if (empty($role)) {
                exit(json_encode(['code'=> 1, 'msg' => '该用户没有被分配角色']));
            }
            $menuList = Db::name('admin_menu')
                ->field('id,pid,title,icon,href,target')
                ->where([
                    ['status', '=', 1],
                    ['is_nav', '=', 1],
                    ['id', 'in', $role['menu_ids']]
                ])
                ->order('sort', 'desc')
                ->select();
        }
        $menuList = $this->buildMenuChild(0, $menuList);
        return $menuList;
    }

    //递归获取子菜单
    private function buildMenuChild($pid, $menuList){
        $treeList = [];
        foreach ($menuList as $v) {
            if ($pid == $v['pid']) {
                $node = $v;
                $child = $this->buildMenuChild($v['id'], $menuList);
                if (!empty($child)) {
                    $node['child'] = $child;
                }
                // todo 后续此处加上用户的权限判断
                $treeList[] = $node;
            }
        }
        return $treeList;
    }
}
