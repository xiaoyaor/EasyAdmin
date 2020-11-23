<?php
// +----------------------------------------------------------------------
// | 多语言设置
// +----------------------------------------------------------------------

use think\facade\Config;
use think\facade\Env;

return [
    // 扩展语言包
    'extend_list'     => [
        Env::get('lang.default_lang', 'zh-cn')=>root_path() .'app1'. DIRECTORY_SEPARATOR.'admin'. DIRECTORY_SEPARATOR.'lang'. DIRECTORY_SEPARATOR . Env::get('lang.default_lang', 'zh-cn') . '.php'
    ],
];
