<?php
// 事件定义文件
return [
    'bind'      => [
        'AdminLoginAfter' => 'app\listener\AdminLoginAfter',
        'AdminNoLoginAfter' => 'app\listener\AdminNoLoginAfter',
    ],

    'listen'    => [
        'AppInit'  => [],
        'HttpRun'  => [],
        'HttpEnd'  => [],
        'LogLevel' => [],
        'LogWrite' => [],
        'AdminLoginAfter' => ['app\listener\AdminLoginAfter'],
        'AdminNoLoginAfter' => ['app\listener\AdminNoLoginAfter'],
    ],

    'subscribe' => [
    ],
];
