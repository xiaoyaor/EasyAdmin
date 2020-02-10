<?php

namespace app\common\controller;

use app\BaseController;
use app\common\library\Auth;
use think\facade\Config;
use think\facade\Event;
use think\facade\Cookie;
use think\facade\View;
use think\Lang;
use think\Validate;
use xiaoyaor\think\Jump;

/**
 * 前台控制器基类
 */
class Frontend extends BaseController
{
    use Jump;

    /**
     * 无需登录的方法,同时也就不需要鉴权了
     * @var array
     */
    protected $noNeedLogin = [];

    /**
     * 无需鉴权的方法,但需要登录
     * @var array
     */
    protected $noNeedRight = [];

    /**
     * 权限Auth
     * @var Auth
     */
    protected $auth = null;
    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct(app());
        // 控制器初始化
        $this->_initialize();
    }

    public function _initialize()
    {
        //移除HTML标签
        $this->request->filter('trim,strip_tags,htmlspecialchars');
        $this->app     = app();
        $this->request = $this->app->request;
        $modulename = app('http')->getName();
        $controllername = strtolower(request()->controller());
        $actionname = strtolower(request()->action());

        if (hook('User')){
            $this->auth = Auth::instance();
            $token = $this->request->server('HTTP_TOKEN', $this->request->request('token', Cookie::get('token')));

            $path = str_replace('.', '/', $controllername) . '/' . $actionname;
            // 设置当前请求的URI
            $this->auth->setRequestUri($path);
            // 检测是否需要验证登录
            if (!$this->auth->match($this->noNeedLogin)) {
                //初始化
                $this->auth->init($token);
                //检测是否登录
                if (!$this->auth->isLogin()) {
                    $this->error(__('Please login first'), 'index/user/login');
                }
                // 判断是否需要验证权限
                if (!$this->auth->match($this->noNeedRight)) {
                    // 判断控制器和方法判断是否有对应权限
                    if (!$this->auth->check($path)) {
                        $this->error(__('You have no permission'));
                    }
                }
            } else {
                // 如果有传递token才验证是否登录状态
                if ($token) {
                    $this->auth->init($token);
                }
            }

            View::assign('user', $this->auth->getUser());
        }
        // 语言检测
        $lang = strip_tags(Config::get("lang.default_lang"));

        $site = Config::get("site");

        //$upload = \app\common\model\Config::upload();
        $upload = Config::get('upload');

        // 上传信息配置后
        event_listen("uploadConfigInit", $upload);

        // 配置信息
        $config = [
            'site'           => array_intersect_key($site, array_flip(['name', 'cdnurl', 'version', 'timezone', 'languages'])),
            'upload'         => $upload,
            'modulename'     => $modulename,
            'controllername' => $controllername,
            'actionname'     => $actionname,
            'jsname'         => 'frontend/' . str_replace('.', '/', $controllername),
            'moduleurl'      => rtrim("/{$modulename}", '/'),
            'language'       => $lang
        ];

        Config::set(array_merge(Config::get('upload'), $upload), 'upload');

        // 配置信息后
        event_trigger("configInit", $config);
        // 加载当前控制器语言包
        $this->loadlang($controllername);
        //渲染站点配置
        View::assign('site', $site);
        //框架信息
        View::assign('easyadmin', Config::get("easyadmin"));
        View::assign('config', $config);
    }

    /**
     * 加载语言文件
     * @param string $name
     */
    protected function loadlang($name)
    {
        \think\facade\Lang::load(app_path() .  '/lang/' . \think\facade\Config::get('lang.default_lang') . '/' . str_replace('.', '/', $name) . '.php');
    }

    /**
     * 渲染配置信息
     * @param mixed $name  键名或数组
     * @param mixed $value 值
     */
    protected function assignconfig($name, $value = '')
    {
        View::assign("config", array_merge(View::instance()->config,[$name => $value]));
    }

}
