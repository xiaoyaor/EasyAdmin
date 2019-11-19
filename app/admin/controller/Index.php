<?php

namespace app\admin\controller;

use app\admin\model\AdminLog;
use app\common\controller\Backend;
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
    public function __construct(){
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
        //左侧菜单
        list($menulist, $navlist, $fixedmenu, $referermenu) = $this->auth->getSidebar([
            'dashboard' => 'hot',
            'addon'     => ['new', 'red', 'badge'],
            'auth/rule' => __('Menu'),
            'general'   => ['new', 'purple'],
        ], Config::get('app.fixedpage'));//View::site['fixedpage']
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
        return View::fetch();
    }

    /**
     * 管理员登录
     * @throws \Exception
     */
    public function login()
    {
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

            if (Config::get('app.EasyAdmin.login_captcha')){
                $captcha=Request::post('captcha') ;
                if(!captcha_check($captcha)){
                    Config::set(['default_return_type'=>'json'],'app');
                    $this->error('验证失败');
                }
            }

            $validate = new Validate();
            $result = $validate->check($data,$rule);
            if (!$result) {
                $this->error($validate->getError(), $url, '');
            }
            $result = $this->auth->login($username, $password, $keeplogin ? 86400 : 0);
            if ($result === true) {
                Event::trigger("admin_login_after", request());
                $this->success(__('Login successful'), $url, ['url' => $url, 'id' => $this->auth->id, 'username' => $username, 'avatar' => $this->auth->avatar]);
            } else {
                $msg = $this->auth->getError();
                $msg = $msg ? $msg : __('Username or password is incorrect');
                //$this->error($msg, $url, ['token' => token()]);
                $this->error($msg, $url, '');
            }
        }

        // 根据客户端的cookie,判断是否可以自动登录
        if ($this->auth->autologin()) {
            $this->redirect($url);
        }
        $background = Config::get('app.EasyAdmin.login_background');
        //$background = stripos($background, 'http') === 0 ? $background : config('app.site') . $background;
        View::assign('background', $background);
        View::assign('title', __('Login'));
        View::assign('EasyAdmin', Config::get('app.EasyAdmin'));
        Event::listen("admin_login_init", request());
        return View::fetch();
    }

    /**
     * 注销登录
     */
    public function logout()
    {
        $this->auth->logout();
        Event::trigger("admin_logout_after", request());
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

}
