<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\facade\View;

/**
 *  模板管理
 */
class Template extends Backend
{

    //构造方法
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 查看
     * @throws \Exception
     */
    public function index()
    {
        return '';
    }

}
