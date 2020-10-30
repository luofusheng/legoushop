<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\model\AdminRole;
use think\facade\Validate;

class Admin extends Base
{
    // 管理员列表页
    public function index()
    {
        return view();
    }

    // 管理员列表数据接口
    public function list()
    {
        $page = request()->param('page');
        $limit = request()->param('limit');
        $username = request()->param('username');
        $nickname = request()->param('nickname');

        $adminList = \app\admin\model\Admin::with(['adminRoleName'])
            ->field('id,username,nickname,status,admin_role_id,create_time')
            ->where([
                ['username', 'like', '%'.$username.'%'],
                ['nickname', 'like', '%'.$nickname.'%']
            ])
            ->paginate([
                'list_rows' => $limit,
                'var_page' => 'page',
                'page' => $page,
                'query' => [
                    'username' => $username,
                    'nickname' => $nickname
                ]
            ]);
        $adminListData = $adminList->toArray()['data'];
        // 设置超级管理员角色名
        foreach ($adminListData as &$v) {
            if ($v['id'] == 1) {
                $v['role'] = '超级管理员';
            }
        }
        unset($v);

        $data = [
            'code' => 0,
            'msg' => '',
            'count' => $adminList->total(),
            'data' => $adminListData
        ];

        return json($data);
    }

    // 管理员列表状态改变接口
    public function statusChange()
    {
        $status = trim(input('post.status', '', 'strip_tags'));
        if ($status == 'false') {
            $status = 0;
        } else {
            $status = 1;
        }
        $id = (int)trim(input('post.id', '', 'strip_tags'));
        if ($id == 1) {
            return json([
                'code' => 1,
                'msg' => '无法禁用admin超级管理员'
            ]);
        }

        \app\admin\model\Admin::update(['status' => $status], ['id' => $id]);
    }

    // 管理员列表删除接口
    public function delete()
    {
        $id = (int)trim(input('post.id', '', 'strip_tags'));
        if ($id == 1) {
            return json([
                'code' => 1,
                'msg' => '无法删除超级管理员'
            ]);
        }
        \app\admin\model\Admin::destroy($id);
        return json([
            'code' => 0,
            'msg' => '删除成功'
        ]);
    }

    // 管理员列表多选删除接口
    public function deleteMulti()
    {
        $ids = input('post.ids', '', 'strip_tags');
        \app\admin\model\Admin::destroy($ids);

        return json([
            'code' => 0,
            'msg' => '删除成功'
        ]);
    }

    // 管理员添加页
    public function add()
    {
        // 角色列表数组
        $roleList = AdminRole::field('id,name')->select()->toArray();

        return view('', ['roleList' => $roleList]);
    }

    // 管理员添加页面保存接口
    public function save()
    {
        $params['username'] = trim(input('post.username', '', 'strip_tags'));
        $params['password'] = trim(input('post.password', '', 'strip_tags'));
        $params['nickname'] = trim(input('post.nickname', '', 'strip_tags'));
        $params['admin_role_id'] = (int)trim(input('post.role', '', 'strip_tags'));

        $validate = Validate::rule([
            'username|用户名' => 'require|max:20|regex:^[A-Za-z]\w*[A-Za-z0-9]$|unique:admin',
            'password|密码' => 'length:6,20',
            'nickname|昵称' => 'max:20'
        ]);
        if (!$validate->check($params)) {
            return json([
                'code' => 1,
                'msg' => $validate->getError()
            ]);
        }
        if (empty($params['nickname'])) {
            $params['nickname'] = $params['username'];
        }
        $params['password'] = encrypt_password($params['password']);
        \app\admin\model\Admin::create($params);

        return json([
            'code' => 0,
            'msg' => '保存成功'
        ]);
    }

    // 管理员编辑页
    public function edit()
    {
        // 管理员信息
        $id = (int)input('get.id', '', 'strip_tags');
        $admin = \app\admin\model\Admin::field('id,username,nickname,admin_role_id')
            ->find($id)->toArray();

        // 角色列表数组，键为id值
        $roleList = AdminRole::field('id,name')->select()->toArray();

        return view('', [
            'admin' => $admin,
            'roleList' => $roleList
        ]);
    }

    // 管理员编辑页面保存接口
    public function update()
    {
        // 管理员信息
        $params['id'] = (int)trim(input('post.id', '', 'strip_tags'));
        $params['nickname'] =  trim(input('post.nickname', '', 'strip_tags'));
        $params['password'] = trim(input('post.password', '', 'strip_tags'));
        $params['admin_role_id'] = (int)trim(input('post.role', '', 'strip_tags'));

        $validate = Validate::rule([
            'id|id' => 'require|integer',
            'password|密码' => 'max:20',
            'nickname|昵称' => 'require|max:20'
        ]);
        if (!$validate->check($params)) {
            return json([
                'code' => 1,
                'msg' => $validate->getError()
            ]);
        }

        if (empty($params['password'])) {
            unset($params['password']);
        } else {
            $params['password'] = encrypt_password($params['password']);
        }

        \app\admin\model\Admin::update($params);

        return json([
            'code' => 0,
            'msg' => '修改成功'
        ]);
    }
}
