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

    protected $layout_name = 'default';
    /**
     * 查看
     * @throws \Exception
     */
    public function index()
    {
        Config::set(['layout_on'=>'true','layout_name'=>'layout/default'],'view');
        $seventtime = \fast\Date::unixtime('day', -7);
        $paylist = $createlist = [];
        for ($i = 0; $i < 7; $i++)
        {
            $day = date("Y-m-d", $seventtime + ($i * 86400));
            $createlist[$day] = mt_rand(20, 200);
            $paylist[$day] = mt_rand(1, mt_rand(1, $createlist[$day]));
        }
        $hooks = config('addons.hooks');
        $uploadmode = isset($hooks['upload_config_init']) && $hooks['upload_config_init'] ? implode(',', $hooks['upload_config_init']) : 'local';
        //$addonComposerCfg = ROOT_PATH() . '/vendor/karsonzhang/fastadmin-addons/composer.json';
        //Config::parse($addonComposerCfg, "json");
        $config = Config::get("composer");
        $addonVersion = isset($config['version']) ? $config['version'] : __('Unknown');
        View::assign([
            'totaluser'        => 35200,
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
        $site=Config::get('site');
        View::assign('site',$site);
        View::assign('json_site',json_encode($site));

        return View::fetch();
    }

}
