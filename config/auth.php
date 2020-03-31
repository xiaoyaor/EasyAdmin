<?php
return[
    'auth_on'           => 1, // 权限开关
    'auth_type'         => 1, // 认证方式，1为实时认证；2为登录认证。
    //后端
    'admin'             => 'admin', // 管理员数据表
    'auth_group'        => 'auth_group', // 用户组数据表名
    'auth_group_access' => 'auth_group_access', // 用户-用户组关系表
    'auth_rule'         => 'auth_rule', // 权限规则表
    //后端log
    'admin_log'         => 'admin_log', // 管理员操作日志表
    //前端
    'user'              => 'user', // 用户信息表
    'user_group'        => 'user_group', // 用户组表
    'user_rule'         => 'user_rule', // 前端权限规则表
    'user_token'        => 'user_token', // token信息表
    //前端log记录
    'user_log'          => 'user_log', // 用户日志表
    'user_score_log'    => 'user_score_log', // 用户积分表
    'user_money_log'    => 'user_money_log', // 用户金钱表
];
