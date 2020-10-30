<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\model\AdminMenu;
use app\admin\model\AdminRole;
use think\facade\Validate;

class Role extends Base
{
    // 角色列表页
    public function index()
    {
        return view();
    }

    // 角色列表数据接口
    public function list()
    {
        $page = request()->param('page');
        $limit = request()->param('limit');
        $name = request()->param('name');

        $roleList = AdminRole::field('id,name,desc,menu_ids')
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
        $roleListData = $roleList->toArray()['data'];
        $data = [
            'code' => 0,
            'msg' => '',
            'count' => $roleList->total(),
            'data' => $roleListData
        ];

        return json($data);
    }

    // 角色添加页
    public function add()
    {
        // 获取所有菜单数据
        $menuList = AdminMenu::field('id,pid,title')->select()->toArray();
        $menuList = get_tree_list($menuList);

        return view('', [
            'menuList' => json_encode($menuList)
        ]);
    }

    // 角色添加页面保存接口
    public function save()
    {
        $params['name'] = trim(input('post.name', '', 'strip_tags'));
        $params['desc'] = trim(input('post.desc', '', 'strip_tags'));
        $params['menu_ids'] = input('post.menus', '', 'strip_tags');
        if (!empty($params['menu_ids'])) {
            $params['menu_ids'] = implode(',', $params['menu_ids']);
        }

        $validate = Validate::rule([
            'name|角色名称' => 'require|max:20',
            'desc|备注' => 'max:255',
        ]);
        if (!$validate->check($params)) {
            return json([
                'code' => 1,
                'msg' => $validate->getError()
            ]);
        }

        AdminRole::create($params);

        return json([
            'code' => 0,
            'msg' => '保存成功'
        ]);
    }

    // 角色修改页面
    public function edit()
    {
        // 获取角色数据
        $id = (int)input('get.id', '', 'strip_tags');
        $role = AdminRole::field('id,name,desc,menu_ids')
            ->find($id)->toArray();
        // 角色拥有的有权限的菜单的id集合
        if (empty($role['menu_ids'])) {
            $role['menu_ids'] = '';
        }
        $menuIds = explode(',', $role['menu_ids']);

        // 获取所有菜单数据
        $menuList = AdminMenu::field('id,pid,title')->select()->toArray();
        // 转换成菜单树
        $menuList = get_tree_list($menuList);
        // 设置菜单树中被选中的节点
        set_checked_menus($menuList, $menuIds);

        return view('', [
            'role' => $role,
            'menuList' => json_encode($menuList)
        ]);
    }

    // 角色编辑页面保存接口
    public function update()
    {
        $params['id'] = (int)trim(input('post.id', '', 'strip_tags'));
        $params['name'] = trim(input('post.name', '', 'strip_tags'));
        $params['desc'] = trim(input('post.desc', '', 'strip_tags'));
        $params['menu_ids'] = input('post.menus', '', 'strip_tags');
        if (!empty($params['menu_ids'])) {
            $params['menu_ids'] = implode(',', $params['menu_ids']);
        }

        $validate = Validate::rule([
            'id' => 'require|integer',
            'name|角色名称' => 'require|max:20',
            'desc|备注' => 'max:255',
        ]);
        if (!$validate->check($params)) {
            return json([
                'code' => 1,
                'msg' => $validate->getError()
            ]);
        }

        AdminRole::update($params);

        return json([
            'code' => 0,
            'msg' => '保存成功'
        ]);
    }

    // 角色列表删除接口
    public function delete()
    {
        $id = (int)trim(input('post.id', '', 'strip_tags'));
        // 判断该角色是否被使用
        $admin = \app\admin\model\Admin::where('admin_role_id', $id)->select();
        if (!$admin->isEmpty()) {
            return json([
                'code' => 1,
                'msg' => '该角色已被使用，无法删除'
            ]);
        }

        AdminRole::destroy($id);
        return json([
            'code' => 0,
            'msg' => '删除成功'
        ]);
    }

    // 角色列表多选删除接口
    public function deleteMulti()
    {
        $ids = input('post.ids', '', 'strip_tags');
        // 判断多选的角色中是否有角色被使用了
        $admin = \app\admin\model\Admin::where('admin_role_id', 'in', $ids)->select();
        if (!$admin->isEmpty()) {
            return json([
                'code' => 1,
                'msg' => '该角色已被使用，无法删除'
            ]);
        }

        AdminRole::destroy($ids);
        return json([
            'code' => 0,
            'msg' => '删除成功'
        ]);
    }
}
