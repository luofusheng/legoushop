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

if (!function_exists('encrypt_password')) {

    /**
     * 密码加密函数
     * @param $password 密码
     * @return string
     */
    function encrypt_password($password)
    {
        $salt = 'dsagsgsddgd';
        return md5($salt . md5($password));
    }
}

/**
 * 手机号加密
 */
if (!function_exists('encrypt_phone')) {
    function encrypt_phone($phone)
    {
        return substr($phone, 0, 3) . '****' . substr($phone, 7);
    }
}

/**
 * 使用curl函数库发送请求
 */
if(!function_exists('curl_request'))
{
    //使用curl函数库发送请求
    function curl_request($url, $post=true, $params=[], $https=true)
    {
        //初始化请求
        $ch = curl_init($url);
        //默认是get请求。如果是post请求 设置请求方式和请求参数
        if($post){
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }
        //如果是https协议，禁止从服务器验证本地证书
        if($https){
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }
        //发送请求，获取返回结果
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);
        dump($res);die;
        /*if(!$res){
            $msg = curl_error($ch);
            dump($msg);die;
        }*/
        //关闭请求
        curl_close($ch);
        return $res;
    }
}

/**
 * 使用curl_request函数调用短信接口发送短信
 */
if (!function_exists('send_msg')) {
    function send_msg($phone, $content)
    {
        // 从配置中取出请求地址、appkey
        $gateway = \think\facade\Config::get('shop.msg.gateway');
        $appkey = \think\facade\Config::get('shop.msg.appkey');

        $url = $gateway . '?appkey=' . $appkey;
        // get请求
        /*$url .= '&mobile=' . $phone . '&content=' . $content;
        $res = curl_request($url, false, [], true);*/
        // post请求
        $params = [
            'mobile' => $phone,
            'content' => $content
        ];
        $res = curl_request($url, true, $params, true);
        // 处理结果
        if (!$res) {
            return '请求发送失败';
        }
        // 解析结果
        $arr = json_decode($res, true);
        if (isset($arr['code']) && $arr['code'] == 10000) {
            // 短信接口调用成功
            return true;
        } else {
            // if (isset($arr['msg'])) {
            //     return $arr['msg'];
            // }

            return '短信发送失败';
        }
    }
}