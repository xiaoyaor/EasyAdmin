<?php
// 事件定义文件
return [
    'bind'      => [
        'admin_login_after' => 'app\listener\admin_login_after',
    ],

    'listen'    => [
        'AppInit'  => [],
        'HttpRun'  => [],
        'HttpEnd'  => [],
        'LogLevel' => [],
        'LogWrite' => [],
        'admin_login_after' => ['app\listener\admin_login_after'],
    ],

    'subscribe' => [
        //'app\listener\admin_login_after',
    ],
];
