<?php

namespace app\admin\controller;

use app\admin\model\Admin;
use app\admin\model\AdminLog;
use app\common\controller\Backend;
use app\common\model\Attachment;
use app\common\model\User;
use think\facade\Config;
use think\facade\Db;
use think\facade\View;
use think\facade\Env;
use \app\common\model\Category;

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
        $loginlist = $signuplist = [];
        for ($i = 1; $i <= 7; $i++)
        {
            $day = date("Y-m-d", $seventtime + ($i * 86400));
            $loginlist[$day]  = Db::name('user_token')->whereDay('createtime',$day)->group('user_id')->count();
            $signuplist[$day] = User::whereDay('createtime',$day)->count();
        }
        $hooks = config('addons.hooks');
        $uploadmode = isset($hooks['upload_config_init']) && $hooks['upload_config_init'] ? implode(',', $hooks['upload_config_init']) : 'local';

        $config = Config::load(root_path() . 'composer.json');
        $addonVersion = isset($config['require']['xiaoyaor/think-addons']) ? $config['require']['xiaoyaor/think-addons'] : __('Unknown');

        $userlist=User::where('id','>','0')->order('id desc')->limit(7)->select();
        $adminloglist=AdminLog::where('id','>','0')->order('id desc')->limit(11)->select();
        View::assign('userlist',$userlist);
        View::assign('loglist',$adminloglist);
        View::assign([
            'usercount'        => User::count(),
            'attachmentcount'  => Attachment::count(),
            'admincount'       => Admin::count(),
            'categorycount'    => Category::count(),
            'totalscore'       => User::sum('score'),
            'totalmoney'       => User::sum('money'),
            'todayusersignup'  => User::whereDay('createtime')->count(),
            'yestodayusersignup' => User::whereDay('createtime',	'yesterday')->count(),
            'sevendnu'         => User::whereWeek('createtime')->count(),
            'todayuserlogin'   => Db::name('user_token')->whereDay('createtime')->group('user_id')->count(),
            'yestodayuserlogin'=> Db::name('user_token')->whereDay('createtime','yesterday')->group('user_id')->count(),
            'sevendau'         => Db::name('user_token')->whereWeek('createtime')->group('user_id')->count(),
            'addonversion'     => $addonVersion,
            'app_version'      => app()::VERSION,
            'uploadmode'       => $uploadmode,
            'modulename'       => $modulename,
            'signuplist'       => $signuplist,
            'loginlist'        => $loginlist
        ]);
        return View::fetch();
    }

}
