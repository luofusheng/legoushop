<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\model\AdminMenu;
use app\admin\model\AdminRole;
use think\facade\Validate;

class Menu extends Base
{
    // 菜单列表
    public function index()
    {
        return view();
    }

    // 菜单列表数据接口
    public function list()
    {
        $menuList = AdminMenu::select()->toArray();
        $data = [
            'code' => 0,
            'msg' => '',
            'count' => '15',
            'data' => $menuList
        ];

        return json($data);
    }

    // 菜单添加页
    public function add()
    {
        // 查询到所有菜单
        $menuList = AdminMenu::field('id,title')->select()->toArray();

        return view('', [
            'menuList' => $menuList
        ]);
    }

    // 菜单添加页面保存接口
    public function save()
    {
        $params['title'] = trim(input('post.title', '', 'strip_tags'));
        $params['pid'] = (int)input('post.pid', '', 'strip_tags');
        $params['icon'] = 'fa ' . trim(input('post.icon', '', 'strip_tags'));
        $params['href'] = trim(input('post.href', '', 'strip_tags'));
        $params['target'] = trim(input('post.target', '', 'strip_tags'));
        $params['sort'] = (int)input('post.sort', '', 'strip_tags');
        $params['controller'] = trim(input('post.controller', '', 'strip_tags'));
        $params['action'] = trim(input('post.action', '', 'strip_tags'));
        $params['is_nav'] = empty(input('post.is_nav', '', 'strip_tags')) ? 0 : 1;
        $params['status'] = empty(input('post.status', '', 'strip_tags')) ? 1 : 0;
        $params['remark'] = trim(input('post.remark', '', 'strip_tags'));

        $validate = Validate::rule([
            'title|名称' => 'require|max:100',
            'pid|上级菜单' => 'require|integer|max:10',
            'href' => 'max:100',
            'target' => 'max:20',
            'sort' => 'max:10',
            'controller' => 'max:30',
            'action' => 'max:30',
            'remark' => 'max:255'
        ]);
        if (!$validate->check($params)) {
            return json([
                'code' => 1,
                'msg' => $validate->getError()
            ]);
        }

        AdminMenu::create($params);

        return json([
            'code' => 0,
            'msg' => '保存成功'
        ]);
    }

    // 菜单修改页面
    public function edit()
    {
        // 当前要修改的菜单信息
        $id = (int)input('get.id', '', 'strip_tags');
        $menu = AdminMenu::field('id,pid,title,icon,href,target,sort,status,controller,action,is_nav,remark')
            ->find($id)->toArray();

        // 查询到所有菜单
        $menuList = AdminMenu::field('id,title')->select()->toArray();

        return view('', [
            'menu' => $menu,
            'menuList' => $menuList
        ]);
    }

    // 菜单修改页面保存接口
    public function update()
    {
        $params['id'] = (int)trim(input('post.id', '', 'strip_tags'));
        $params['title'] = trim(input('post.title', '', 'strip_tags'));
        $params['pid'] = (int)input('post.pid', '', 'strip_tags');
        $params['icon'] = 'fa ' . trim(input('post.icon', '', 'strip_tags'));
        $params['href'] = trim(input('post.href', '', 'strip_tags'));
        $params['target'] = trim(input('post.target', '', 'strip_tags'));
        $params['sort'] = (int)input('post.sort', '', 'strip_tags');
        $params['controller'] = trim(input('post.controller', '', 'strip_tags'));
        $params['action'] = trim(input('post.action', '', 'strip_tags'));
        $params['is_nav'] = (int)trim(input('post.is_nav', '', 'strip_tags'));
        $params['status'] = (int)trim(input('post.status', '', 'strip_tags'))==1?0:1;
        $params['remark'] = trim(input('post.remark', '', 'strip_tags'));

        $validate = Validate::rule([
            'id' => 'require',
            'title|名称' => 'require|max:100',
            'pid|上级菜单' => 'require|integer|max:10',
            'href' => 'max:100',
            'target' => 'max:20',
            'sort' => 'max:10',
            'controller' => 'max:30',
            'action' => 'max:30',
            'remark' => 'max:255'
        ]);
        if (!$validate->check($params)) {
            return json([
                'code' => 1,
                'msg' => $validate->getError()
            ]);
        }

        AdminMenu::update($params);

        return json([
            'code' => 0,
            'msg' => '修改成功'
        ]);
    }

    // 菜单列表删除接口
    public function delete()
    {
        $id = (int)trim(input('post.id', '', 'strip_tags'));

        // 判断该菜单下是否有角色，如果有角色，则不能删除
        $menuIdsList = AdminRole::column('menu_ids');
        $menuIds = implode(',', $menuIdsList);
        $menuIds = explode(',', $menuIds);
        $menuIds = array_unique($menuIds);
        if (in_array($id, $menuIds)) {
            return json([
                'code' => 1,
                'msg' => '已被角色使用，无法删除'
            ]);
        }

        AdminMenu::destroy($id);

        return json([
            'code' => 0,
            'msg' => '删除成功'
        ]);
    }
}
