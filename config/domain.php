<?php
// +----------------------------------------------------------------------
// | 域名设置
// +----------------------------------------------------------------------

use think\facade\Env;

return [
    // 域名绑定（自动多应用模式有效)(与app里的合并)
    'domain_bind' => [
        //'admin' => 'admin' //注释：绑定'admin'子域名到{app/controller/<admin>||addons/<xxx>/app/controller/<admin>}
    ],
    // 域名绑定 (绑定到addons文件夹下插件)
    'addons_bind' => [
        //'demo' => 'xxx' //注释：绑定'demo'子域名到{addons/<xxx>/app/controller}
    ],
    // 域名绑定（绑定到addons文件夹下插件里的模块(非应用)）
    'addons_domain_bind' => [
        //'demo' => ['addon' => 'xxx','module' => 'admin'] //注释：绑定'demo'子域名到{addons/<xxx>/app/controller/<admin>}
    ],
    // 域名绑定（绑定到addons文件夹下应用里的插件里的模块）
    'modules_bind' => [
        //'demo' => ['app' => 'xx','addon' => 'xxxx'] //注释：绑定'demo'子域名到{addons/<xx>/addons/<xxxx>/app/controller}
    ],
    // 域名绑定（绑定到addons文件夹下应用里的插件里的模块）
    'app_modules_bind' => [
        //'demo' => ['app' => 'xx','addon' => 'xxxx','module' => 'admin'] //注释：绑定'demo'子域名到{addons/<xx>/addons/<xxxx>/app/controller/<admin>}
    ],
];
