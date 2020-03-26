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
class Index extends Backend
{
    use Jump;
    protected $noNeedLogin = ['login','captcha'];
    protected $noNeedRight = ['index', 'logout'];

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
        //菜单标识
        Config::get('site.menu_flag')?$flag=['dashboard' => 'hot','addon' => ['new', 'red', 'badge'],'auth/rule' => __('Menu'),'general' => ['new', 'purple']]:$flag=[];
        //左侧菜单,有无权限
        if (!$this->auth){
            list($menulist, $navlist, $fixedmenu, $referermenu) = getSidebar($flag, Config::get('site.fixedpage'));
        }else{
            list($menulist, $navlist, $fixedmenu, $referermenu) = $this->auth->getSidebar($flag, Config::get('site.fixedpage'));
        }
        $action = Request::request('action');
        if (Request::isPost()) {
            if ($action == 'refreshmenu') {
                $this->success('', null, ['menulist' => $menulist, 'navlist' => $navlist]);
            }
        }
        $app_list=get_app_list(true);
        View::assign('app_list',$app_list);
        View::assign('app_list_count', count($app_list));
        View::assign('menulist', $menulist);
        View::assign('navlist', $navlist);
        View::assign('fixedmenu', $fixedmenu);
        View::assign('referermenu', $referermenu);
        View::assign('title', __('Home'));
        //应用
        View::assign('app',Session::get('app') );
        return View::fetch();
    }

    /*
     * 切换皮肤
     *
     */
    function changeSkin()
    {
        if (request()->isPost()) {
            $skin = request()->post("skin");
            if ($skin != Config::get('site.skin') && $skin!=''){
                change_site('skin',$skin);
                $this->success('切换皮肤成功');
            }
            else{
                $this->success('');
            }
        }
    }

    /*
     * 切换应用
     *
     */
    function changeApp()
    {
        $app=$this->request->param('app');
        if ($app=='easyadmin'){
            Session::delete('app');
            $this->redirect(DIRECTORY_SEPARATOR.get_modulename(Config::get('app.app_map')),  302);
        }else{
            $app=get_addon_info($app);
            $app=array_intersect_key($app, array_flip(['name', 'title', 'intro', 'author', 'website', 'version', 'website', 'first_menu']));
            Session::set('app',$app);
            $this->redirect(DIRECTORY_SEPARATOR.get_modulename(Config::get('app.app_map')).DIRECTORY_SEPARATOR.$app['first_menu'].'.html?ref=addtabs',  302);
        }
    }

}
