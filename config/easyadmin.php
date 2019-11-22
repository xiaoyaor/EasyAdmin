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
    //登录验证码
    'login_captcha'       => true,
    //登录失败超过10次则1天后重试
    'login_failure_retry' => true,
    //是否同一账号同一时间只能在一个地方登录
    'login_unique'        => false,
    //登录页默认背景图
    'login_background'    => "/assets/img/loginbg.jpg",
    //自动检测更新
    'checkupdate'         => false,
    //版本号
    'version'             => '0.0.6.20191122_dev',
    //框架名称
    'url'   => 'https://www.easyadmin.vip',
    //官方文档
    'doc_url'   => 'https://doc.easyadmin.vip',
    //交流社区
    'ask_url'   => 'https://ask.easyadmin.vip',
    //Gitee开源
    'gitee_url'   => 'https://gitee.easyadmin.vip',
    //Github开源
    'github_url'   => 'https://github.com/xiaoyaor/EasyAdmin',
    //插件市场
    'store_url'   => 'https://store.easyadmin.vip',
    //API接口地址
    'api_url'     => 'https://api.easyadmin.vip',
    //QQ交流群
    'QQqun'   => 'https://shang.qq.com/wpa/qunwpa?idkey=46c326e570d0f97cfae1f8257ae82322192ec8841c79b2136446df0b3b62028c',
];