<?php
declare (strict_types = 1);

namespace app\index\controller;

use think\facade\View;

class Index
{
    public function index()
    {
        hook('testhook', ['id'=>1]);
        //return View::fetch();
    }
}
