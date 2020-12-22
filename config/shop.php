<?php
// +----------------------------------------------------------------------
// | 品优购项目设置
// +----------------------------------------------------------------------

return [
    // 短信配置
    'msg' => [
        'gateway' => 'https://way.jd.com/chuangxin/dxjk',
        'appkey' => '896941fc23bdd5c21e7670dca6ca4aa9'
    ],

    // 支付方式
    'pay_type' => [
        'alipay' => ['pay_code' => 'alipay', 'pay_name' => '支付宝', 'logo' => '/static/home/img/pay/alipay.jpg']
    ],

    // ping++聚合支付
    'pingpp' => [
        'api_key' => 'sk_test_KOGmH8SSuzrDOmLiDCzX9eX9',//test_key 或 live_key
        'app_id' => 'app_nTG0uPa5OWH4mH0m',// 应用app_id
        'private_key_path' => './pingpp_rsa_private_key.pem' //商户私钥文件路径
    ]
];