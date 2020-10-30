<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\model\AdminMenu;
use app\admin\model\AdminRole;
use app\BaseController;
use think\facade\View;
use think\response\Redirect;

/**
 * 后台基础控制器
 * 所有后台控制器，都应该作为它的子类存在
 * Class Base
 * @package app\admin\controller
 */
class Base extends BaseController
{
    // 当前登录用户的信息
    protected array $adminInfo;

    public function initialize()
    {
        // 登录检验
        $adminInfo = session('adminInfo');
        if (empty($adminInfo)) {
            header('Location: /admin/login');
            exit;
        }
        // 获取用户访问的控制器名称、方法名称
        $controller = request()->controller();
        $action = request()->action();
        // 判断当前控制器和方法所属的菜单是否存在
        $menu = AdminMenu::where([
            'controller' => $controller,
            'action' => $action
        ])->find();
        if (empty($menu)) {
            $this->outputErrorMsg('菜单不存在');
        }

        // 非admin超级管理员需要判断角色和权限
        if ($adminInfo['id'] != 1) {
            // 判断该用户是否被分配角色，分配的角色是否存在
            $role = AdminRole::where('id', $adminInfo['admin_role_id'])->find();
            if (empty($role)) {
                $this->outputErrorMsg('该角色不存在');
            }
            // 判断当前用户所属角色是否具有当前菜单的访问权限
            if (empty($role['menu_ids'])) {
                $this->outputErrorMsg('该角色没有被授权');
            }
            $menuIds = explode(',', $role['menu_ids']);
            if (!in_array($menu['id'], $menuIds)) {
                $this->outputErrorMsg('没有权限');
            }
        }

        // 将用户数据传递给对应的模板
        $this->adminInfo = $adminInfo;
        View::assign('adminInfo', $adminInfo);
    }

    /**
     * 输出错误信息
     * @param string $msg 错误信息
     */
    private function outputErrorMsg($msg)
    {
        //重定向到错误页面
        if (request()->isAjax()) {
            exit(json_encode(['code'=>302, 'msg' => $msg]));
        } else {
            header('Location: /admin/error/' . $msg);
            exit;
        }
    }
}
