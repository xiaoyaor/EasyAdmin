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
    }

    public function index()
    {
        if (!file_exists(root_path().'.env')){
           $this->redirect('install.php');
        }
        return View::fetch();
    }

    public function hello($name = 'ThinkPHP6')
    {
        return 'hello,' . $name;
    }

}
