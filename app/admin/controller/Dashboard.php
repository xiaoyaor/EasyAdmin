<?php

namespace app\admin\controller;

use addons\conf\app\common\model\Config as ConfigModel;
use app\admin\model\Admin;
use app\admin\model\AdminLog;
use app\common\controller\Backend;
use app\common\model\Attachment;
use app\common\model\User;
use think\Exception;
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
        if ($this->request->isPost()) {
            $name = $this->request->param("addon_name");
            if (!$name) {

                $this->error("插件不存在");
            }
            if (!preg_match("/^[a-zA-Z0-9_]+$/", $name)) {
                $this->error(__('Addon name incorrect'));
            }
            if (!is_dir(ADDON_PATH . $name)) {
                $this->error(__('Directory not found'));
            }
            $info = get_addon_info($name);
            $config = get_addon_fullconfig($name);
            if (!$info) {
                $this->error(__('No Results were found'));
            }
            $params = $this->request->post("row/a");
            if ($params) {
                foreach ($params as $k => &$v) {
                    $v = is_array($v) ? implode(',', $v) : $v;
                }
                try {
                    if (in_array($params['type'], ['select', 'selects', 'checkbox', 'radio', 'array'])) {
                        $params['content'] = ConfigModel::decode($params['content']);
                    } else {
                        $params['content'] = '';
                    }
                    //更新配置文件
                    $config = array_merge($config, array($params));

                    set_addon_fullconfig($name, $config);
                    \think\addons\Service::refresh();
                    $this->success();
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        return View::fetch();
    }

}
