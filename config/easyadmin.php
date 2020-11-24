<?php
// +----------------------------------------------------------------------
// | 应用设置
// +----------------------------------------------------------------------

use think\facade\Env;

return [
    // 框架名称
    'name'   => 'EasyAdmin',
    //是否开启前台会员中心
    'usercenter'          => true,
    //登录失败超过10次则1天后重试
    'login_failure_retry' => true,
    //是否同一账号同一时间只能在一个地方登录
    'login_unique'        => false,
    //自动检测更新
    'checkupdate'         => false,
    // 是否开启多语言
    'lang_switch_on'      => true,
    // 插件网址前缀
    'addons_url_prefix'   => 'addon',
    // 插件网址前缀
    'app_url_prefix'      => 'web',
    // 权限系统路径
    'auth_path'           => '\addons\auth\app\admin\library\Auth',
    //版本号
    'version'             => '1.0.0.20201124_dev',
    //框架名称
    'url'                 => 'https://www.easyadmin.vip',
    //演示
    'demo_url'             => 'https://demo.easyadmin.vip',
    //官方文档
    'doc_url'             => 'https://doc.easyadmin.vip',
    //交流社区
    'ask_url'            => 'https://ask.easyadmin.vip',
    //Gitee开源
    'gitee_url'          => 'https://gitee.com/gitshenyin/EasyAdmin',
    //Github开源
    'github_url'         => 'https://github.com/xiaoyaor/EasyAdmin',
    //插件市场
    'app_url'          => 'https://cloud.easyadmin.vip',
    //API接口地址
    'api_url'            => 'https://api.easyadmin.vip',
    //QQ交流群
    'QQqun'              => 'https://shang.qq.com/wpa/qunwpa?idkey=ce12bc3cbc9a2ccbca97d287609f61dffc0347a62a204780271be3ef12f70129',
];