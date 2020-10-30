<?php
// 这是系统自动生成的公共文件
if (!function_exists('encrypt_password')) {

    /**
     * 密码加密函数
     * @param string $password 密码
     * @return bool|string
     */
    function encrypt_password($password)
    {
        $salt = 'dsagsgsddgd';
        return password_hash($password . $salt, PASSWORD_DEFAULT);
    }
}

if (!function_exists('verify_password')) {

    /**
     * 密码加密函数
     * @param string $password 密码
     * @param string $hash 数据库中存储的hash密码
     * @return boolean 如果匹配则返回true，否则返回false
     */
    function verify_password($password, $hash)
    {
        $salt = 'dsagsgsddgd';
        return password_verify($password . $salt, $hash);
    }
}

if (!function_exists('get_tree_list')) {
    /**
     * 引用方式实现 父子级树状结构
     *
     * @param array $list 数据库表结构的数组
     * @return array|mixed
     */
    function get_tree_list($list)
    {
        //将每条数据中的id值作为其下标
        $temp = [];
        foreach ($list as $v) {
            $v['children'] = [];
            $temp[$v['id']] = $v;
        }
        //获取分类树
        foreach ($temp as $k => $v) {
            $temp[$v['pid']]['children'][] = &$temp[$v['id']];
        }
        return isset($temp[0]['children']) ? $temp[0]['children'] : [];
    }
}

if (!function_exists('set_checked_menus')) {
    /**
     * 设置菜单树中被选中的节点
     *
     * @param array $menuList 菜单树
     * @param array $menuIds 被选中的许多节点的id
     */
    function set_checked_menus(&$menuList, $menuIds)
    {
        foreach ($menuList as &$menu) {
            if (empty($menu['children'])) { // 到达叶节点
                // 判断当前叶节点是否被选中
                if (in_array($menu['id'], $menuIds)) {
                    $menu['checked'] = true;
                }
            } else {
                set_checked_menus($menu['children'], $menuIds);
            }
        }
        unset($menu);
    }
}