<?php

namespace app\admin\controller;

use app\admin\model\Admin;
use app\admin\model\AdminLog;
use app\common\controller\Backend;
use app\common\model\Attachment;
use app\common\model\User;
use think\facade\Config;
use think\facade\View;
use think\facade\Env;

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
        Config::set(['layout_on'=>'','layout_name'=>''],'view');
    }

    /**
     * 查看
     * @throws \Exception
     */
    public function index()
    {
        $modulename = get_modulename(Config::get('app.app_map'));
        $seventtime = \easy\Date::unixtime('day', -7);
        $paylist = $createlist = [];
        for ($i = 0; $i < 7; $i++)
        {
            $day = date("Y-m-d", $seventtime + ($i * 86400));
            $createlist[$day] = mt_rand(20, 200);
            $paylist[$day] = mt_rand(1, mt_rand(1, $createlist[$day]));
        }
        $hooks = config('addons.hooks');
        $uploadmode = isset($hooks['upload_config_init']) && $hooks['upload_config_init'] ? implode(',', $hooks['upload_config_init']) : 'local';
        $addonComposerCfg = root_path() . '\vendor\xiaoyaor\think-addons\composer.json';
        $config = Config::load($addonComposerCfg, "json");
        $addonVersion = isset($config['version']) ? $config['version'] : __('Unknown');
        $userlist=User::where('id','>','0')->order('id desc')->limit(7)->select();
        $adminloglist=AdminLog::where('id','>','0')->order('id desc')->limit(11)->select();
        View::assign('userlist',$userlist);
        View::assign('loglist',$adminloglist);
        View::assign([
            'totaluser'        => User::count(),
            'attachmentcount'  => Attachment::count(),
            'admincount'  => Admin::count(),
            'totalviews'       => 219390,
            'totalorder'       => 32143,
            'totalorderamount' => 174800,
            'todayuserlogin'   => 321,
            'todayusersignup'  => 430,
            'todayorder'       => 2324,
            'unsettleorder'    => 132,
            'sevendnu'         => '80%',
            'sevendau'         => '32%',
            'paylist'          => $paylist,
            'createlist'       => $createlist,
            'addonversion'       => $addonVersion,
            'app_version'       => app()::VERSION,
            'uploadmode'       => $uploadmode
        ]);
        View::assign('modulename',$modulename);
        return View::fetch();
    }

}
