<?php

namespace app\admin\controller;

use app\admin\model\AdminLog;
use app\common\controller\Backend;
use app\common\model\Config as ConfigModel;
use think\facade\Config;
use think\facade\Event;
use think\facade\Request;
use think\Validate;
use xiaoyaor\think\Jump;
use think\facade\View;
use think\facade\Session;

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
        // 控制器初始化
        parent::_initialize();
        Config::set(['layout_on'=>'','layout_name'=>''],'view');
    }

    /**
     * 后台首页
     * @throws \Exception
     */
    public function index()
    {
        //菜单标识
        $flag=[
            'dashboard' => 'hot','addon' => ['new', 'red', 'badge'],'auth/rule' => __('Menu'),'general' => ['new', 'purple']
        ];
        //左侧菜单
        list($menulist, $navlist, $fixedmenu, $referermenu) = $this->auth->getSidebar($flag, Config::get('site.fixedpage'));
        $action = Request::request('action');
        if (Request::isPost()) {
            if ($action == 'refreshmenu') {
                $this->success('', null, ['menulist' => $menulist, 'navlist' => $navlist]);
            }
        }
        View::assign('skin', Config::get('site.skin'));
        View::assign('menulist', $menulist);
        View::assign('navlist', $navlist);
        View::assign('fixedmenu', $fixedmenu);
        View::assign('referermenu', $referermenu);
        View::assign('title', __('Home'));
        return View::fetch();
    }

    /**
     * 管理员登录
     * @throws \Exception
     */
    public function login()
    {
        event_trigger("log_dev", ['name'=>'有人在试图登录','ip'=>$this->request->ip()]);
        $url = Request::get('url', 'index/index');
        if ($this->auth->isLogin()) {
            $data=[
                'url' =>$url,
                'id' =>$this->auth->id,
                'username' =>$this->auth->username,
                'avatar' =>$this->auth->avatar,
            ];
            $this->success(__("You've logged in, do not login again"), $url,$data);
        }
        if (Request::isPost()) {
            $username = Request::post('username');
            $password = Request::post('password');
            $keeplogin = Request::post('keeplogin');
            $token = Request::post('__token__');
            $rule = [
                'username'  => 'require|length:3,30',
                'password'  => 'require|length:3,30',
                '__token__' => 'require|token',
            ];
            $data = [
                'username'  => $username,
                'password'  => $password,
                '__token__' => $token,
            ];

            if (Config::get('easyadmin.login_captcha')){
                $captcha=Request::post('captcha') ;
                if(!captcha_check($captcha)){
                    Config::set(['default_return_type'=>'json'],'app');
                    $this->error(__('Captcha fault'));
                }
            }

            $validate = new Validate();
            $result = $validate->check($data,$rule);
            if (!$result) {
                $this->error($validate->getError(), $url, '');
            }
            $result = $this->auth->login($username, $password, $keeplogin ? 86400 : 0);
            if ($result === true) {
                event_trigger("admin_login_after", request());
                $this->success(__('Login successful'), $url, ['url' => $url, 'id' => $this->auth->id, 'username' => $username, 'avatar' => $this->auth->avatar]);
            } else {
                $msg = $this->auth->getError();
                $msg = $msg ? $msg : __('Username or password is incorrect');
                $this->error($msg, $url, '');
            }
        }

        // 根据客户端的cookie,判断是否可以自动登录
        if ($this->auth->autologin()) {
            $this->redirect($url);
        }

        $background = Config::get('easyadmin.login_background');
        $background = stripos($background, 'http') === 0 ? $background : config('site.cdnurl') . $background;
        View::assign('background', $background);
        View::assign('title', __('Login'));
        View::assign('easyadmin', Config::get('easyadmin'));
        event_trigger("adminLoginInit", request());
        return View::fetch();
    }

    /**
     * 注销登录
     */
    public function logout()
    {
        $this->auth->logout();
        event_trigger("admin_logout_after", request());
        $this->success(__('Logout successful'), 'index/login');
    }

    /*
     * 验证码
     *
     */
    public function captcha()
    {
        return captcha();
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
