<?php
// +----------------------------------------------------------------------
// | 应用设置
// +----------------------------------------------------------------------

use think\facade\Env;

return [
    // 应用地址
    'app_host'         => Env::get('app.host', ''),
    // 应用的命名空间
    'app_namespace'    => '',
    // 是否启用路由
    'with_route'       => true,
    // 是否启用事件
    'with_event'       => true,
    // 是否启用事件
    'fixedpage'       => 'dashboard',
    // 默认应用
    'default_app'      => 'index',
    // 默认时区
    'default_timezone' => 'Asia/Shanghai',
    // 默认输出类型
    'default_return_type'    => 'html',
    // 默认AJAX 数据返回格式,可选json xml ...
    'default_ajax_return'    => 'json',
    // 视图输出字符串内容替换,留空则会自动进行计算
    'view_replace_str'       => [
        '__PUBLIC__' => '',
        '__ROOT__'   => '',
        '__CDN__'    => '',
    ],
    // 应用映射（自动多应用模式有效）
    'app_map'          => [],
    // 域名绑定（自动多应用模式有效）
    'domain_bind' => [
//        'm.030.project.com'         =>  'wap',    //  blog子域名绑定到blog应用
//        'admin.030.project.com'     =>  'admin',  //  完整域名绑定
//        '*'                         =>  'index',  // 二级泛域名绑定到home应用
    ],
    // 禁止URL访问的应用列表（自动多应用模式有效）
    'deny_app_list'    => ['common'],

    // 异常页面的模板文件
    'exception_tmpl'   => app()->getThinkPath() . 'tpl/think_exception.tpl',

    // 错误显示信息,非调试模式有效
    'error_message'    => '页面错误！请稍后再试～',
    // 显示错误信息
    'show_error_msg'   => false,

    /*
     * 以下为EasyAdmin自定义配置
     */

    //EasyAdmin配置
    'EasyAdmin'              => [
        // 框架名称
        'frame_name'   => 'EasyAdmin',
        // 框架名称
        'frame_url'   => 'https://www.EasyAdmin.vip',
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
        //layuimini
        'layuimini'    => "/static/extend/layuimini/",
        //是否启用多级菜单导航
        'multiplenav'         => false,
        //自动检测更新
        'checkupdate'         => false,
        //版本号
        'version'             => '1.0.0.20190705_beta',
        //API接口地址
        'api_url'             => 'https://api.easyadmin.vip',
    ],
];
