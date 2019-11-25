<?php
// 事件定义文件
return [
    'bind'      => [
        'AdminLoginAfter' => 'app\listener\AdminLoginAfter',
        'AdminLoginErrorAfter' => 'app\listener\AdminLoginErrorAfter',
        'AdminLogoutAfter' => 'app\listener\AdminLogoutAfter',
    ],

    'listen'    => [
        'AppInit'  => [],
        'HttpRun'  => [],
        'HttpEnd'  => [],
        'LogLevel' => [],
        'LogWrite' => [],
        'AdminLoginAfter' => ['app\listener\AdminLoginAfter'],
        'AdminLoginErrorAfter' => ['app\listener\AdminLoginErrorAfter'],
        'AdminLogoutAfter' => ['app\listener\AdminLogoutAfter'],
    ],

    'subscribe' => [
    ],
];
