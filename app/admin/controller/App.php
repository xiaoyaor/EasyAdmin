<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\facade\Config;
use think\facade\Request;
use think\facade\Session;
use xiaoyaor\think\Jump;
use think\facade\View;

/**
 * 后台首页
 * @internal
 */
class App extends Backend
{

    //构造方法
    public function __construct()
    {
        parent::__construct();
        View::engine()->layout(false);
    }

    /**
     * 后台首页
     * @throws \Exception
     */
    public function index()
    {
        $app=$this->request->param('app');
        if ($app=='easyadmin'){
            Session::delete('app');
            $this->redirect(DIRECTORY_SEPARATOR.get_modulename(Config::get('app.app_map')),  302);
        }else{
            $app=get_addon_info($app);
            $app=array_intersect_key($app, array_flip(['name', 'title', 'intro', 'author', 'website', 'version', 'website', 'first_menu']));
            Session::set('app',$app);
            $this->redirect($app['first_menu'].'.html?ref=addtabs',  302);
        }
    }
}
