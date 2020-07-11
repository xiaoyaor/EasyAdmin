<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\facade\View;

/**
 *  会员管理
 */
class Member extends Backend
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
