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
        //弹窗提示
        $autotip = true;
        if (open_auth()){
            $autotip = false;
        }
        $this->assignconfig('autotip',$autotip);

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

}
