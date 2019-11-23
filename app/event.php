<?php
// 事件定义文件
return [
    'bind'      => [
        'adminLoginAfter' => 'app\listener\adminLoginAfter',
    ],

    'listen'    => [
        'AppInit'  => [],
        'HttpRun'  => [],
        'HttpEnd'  => [],
        'LogLevel' => [],
        'LogWrite' => [],
        'admin_login_after' => ['app\listener\adminLoginAfter'],
    ],

    'subscribe' => [
    ],
];
