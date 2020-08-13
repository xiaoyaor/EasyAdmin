<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\facade\Config;
use think\facade\View;

/**
 * 控制台
 *
 * @icon fa fa-dashboard
 * @remark 用于展示当前系统中的统计数据、统计报表及重要实时数据
 */
class Dashboard extends Backend
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
        $loginlist = $signuplist = [];
        $hooks = config('addons.hooks');
        $uploadmode = isset($hooks['upload_config_init']) && $hooks['upload_config_init'] ? implode(',', $hooks['upload_config_init']) : 'local';
        $config = Config::load(root_path() . 'composer.json');
        $addonVersion = isset($config['require']['xiaoyaor/think-addons']) ? $config['require']['xiaoyaor/think-addons'] : __('Unknown');
        View::assign([
            'addonversion'     => $addonVersion,
            'app_version'      => app()::VERSION,
            'uploadmode'       => $uploadmode,
            'signuplist'       => $signuplist,
            'loginlist'        => $loginlist
        ]);
        return View::fetch();
    }

    /**
     * 配置
     */
    public function config($name = null)
    {
        if (request()->isPost()) {
            $params = request()->param("row/a");
            if ($params) {
                $info[$params['action']]=$params['value']?0:1;
                set_addon_info($name,$info);
                $this->success();
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        return View::fetch();
    }

}
