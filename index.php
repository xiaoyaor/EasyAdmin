<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]
namespace think;

if(!file_exists(__DIR__ . '/.env')){
    //安装引导程序
    header("location:/install.php");
}else{
    //判断输出静态文件
    require __DIR__ . '/static.php';

    //加载第三方引用
    require __DIR__ . '/vendor/autoload.php';

    // 执行HTTP应用并响应
    $http = (new App())->http;

    $response = $http->run();

    $response->send();

    $http->end($response);
}