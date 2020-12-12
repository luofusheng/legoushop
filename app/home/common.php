<?php
// 这是系统自动生成的公共文件
if (!function_exists('get_tree_list')) {
    /**
     * 引用方式实现 父子级树状结构 该版本最后一级有空children元素
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