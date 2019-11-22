<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use think\facade\Config;
use think\facade\View;

class Index extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';

    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
        Config::set(['layout_on'=>'','layout_name'=>''],'view');
    }

    public function index()
    {
        return View::fetch();
    }

    public function news()
    {
        $newslist = [];
        return jsonp(['newslist' => $newslist, 'new' => count($newslist), 'url' => 'https://www.easyadmin.vip?ref=news']);
    }

}
