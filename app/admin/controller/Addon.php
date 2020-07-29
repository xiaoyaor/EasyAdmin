<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use easyadmin\Http;
use think\addons\Service;
use think\facade\Cache;
use think\facade\Config;
use think\Exception;
use think\facade\View;

/**
 * 插件管理
 *
 * @icon   fa fa-cube
 * @remark 可在线安装、卸载、禁用、启用插件，同时支持添加本地插件。EasyAdmin已上线插件商店 ，你可以发布你的免费或付费插件：<a href="https://addons.easyadmin.vip" target="_blank">https://addons.easyadmin.vip</a>
 */
class Addon extends Backend
{
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 查看
     * @throws \Exception
     */
    public function index()
    {
        $addons = get_addon_list();
        foreach ($addons as $k => &$v) {
            $config = get_addon_config($v['name']);
            $v['config'] = $config ? 1 : 0;
            $v['url'] = str_replace(request()->server('SCRIPT_NAME'), '', $v['url']);
        }
        $this->assignconfig('addons',$addons);
        $address=Config::get('easyadmin.api_url');
        View::assign(['api_url' => $address]);
        View::engine()->layout(true);
        return View::fetch();
    }

    /**
     * 配置
     * @throws \Exception
     */
    public function config($ids = null)
    {
        $name = request()->get("name");
        if (!$name) {
            $this->error(__('Parameter %s can not be empty', $ids ? 'id' : 'name'));
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
        if (request()->isPost()) {
            $params = request()->post("row/a");
            if ($params) {
                foreach ($config as $k => &$v) {
                    if (isset($params[$v['name']])) {
                        if ($v['type'] == 'array') {
                            $params[$v['name']] = is_array($params[$v['name']]) ? $params[$v['name']] : (array)json_decode($params[$v['name']], true);
                            $value = $params[$v['name']];
                        } else {
                            $value = is_array($params[$v['name']]) ? implode(',', $params[$v['name']]) : $params[$v['name']];
                        }
                        $v['value'] = $value;
                    }
                }
                try {
                    //更新配置文件
                    set_addon_fullconfig($name, $config);
                    Service::refresh();
                    $this->success();
                } catch (Exception $e) {
                    $this->error(__($e->getMessage()));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $tips = [];
        foreach ($config as $index => &$item) {
            if ($item['name'] == '__tips__') {
                $tips = $item;
                unset($config[$index]);
            }
        }
        View::assign("addon", ['info' => $info, 'config' => $config, 'tips' => $tips]);
        $configFile = ADDON_PATH . $name . DIRECTORY_SEPARATOR . 'config.html';
        $viewFile = is_file($configFile) ? $configFile : '';
        return View::fetch($viewFile);
    }

    /**
     * 依赖插件
     */
    public function addons($ids = null)
    {
        $name = request()->get("name");
        if (!$name) {
            $this->error(__('Parameter %s can not be empty', $ids ? 'id' : 'name'));
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
        if (request()->isPost()) {
            $params = request()->post("row/a");
            if ($params) {
                foreach ($config as $k => &$v) {
                    if (isset($params[$v['name']])) {
                        if ($v['type'] == 'array') {
                            $params[$v['name']] = is_array($params[$v['name']]) ? $params[$v['name']] : (array)json_decode($params[$v['name']], true);
                            $value = $params[$v['name']];
                        } else {
                            $value = is_array($params[$v['name']]) ? implode(',', $params[$v['name']]) : $params[$v['name']];
                        }
                        $v['value'] = $value;
                    }
                }
                try {
                    //更新配置文件
                    set_addon_fullconfig($name, $config);
                    Service::refresh();
                    $this->success();
                } catch (Exception $e) {
                    $this->error(__($e->getMessage()));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $tips = [];
        foreach ($config as $index => &$item) {
            if ($item['name'] == '__tips__') {
                $tips = $item;
                unset($config[$index]);
            }
        }
        View::assign("addon", ['info' => $info, 'config' => $config, 'tips' => $tips]);
        $configFile = ADDON_PATH . $name . DIRECTORY_SEPARATOR . 'config.html';
        $viewFile = is_file($configFile) ? $configFile : '';
        return View::fetch($viewFile);
    }

    /**
     * 安装
     */
    public function install()
    {
        $name = request()->post("name");
        $force = (int)request()->post("force");
        if (!$name) {
            $this->error(__('Parameter %s can not be empty', 'name'));
        }
        if (!preg_match("/^[a-zA-Z0-9_]+$/", $name)) {
            $this->error(__('Addon name incorrect'));
        }
        try {
            $uid = request()->post("uid");
            $token = request()->post("token");
            $version = request()->post("version");
            $faversion = request()->post("faversion");
            $extend = [
                'uid'       => $uid,
                'token'     => $token,
                'version'   => $version,
                'faversion' => $faversion
            ];
            Service::install($name, $force, $extend);
            $info = get_addon_info($name);
            $info['config'] = get_addon_config($name) ? 1 : 0;
            $info['state'] = 1;
            $this->success(__('Install successful'), null, ['addon' => $info]);
        } catch (AddonException $e) {
            $this->result($e->getData(), $e->getCode(), __($e->getMessage()));
        } catch (Exception $e) {
            $this->error(__($e->getMessage()), $e->getCode());
        }
    }

    /**
     * 卸载
     */
    public function uninstall()
    {
        $name = request()->post("name");
        $force = (int)request()->post("force");
        if (!$name) {
            $this->error(__('Parameter %s can not be empty', 'name'));
        }
        if (!preg_match("/^[a-zA-Z0-9_]+$/", $name)) {
            $this->error(__('Addon name incorrect'));
        }
        try {
            Service::uninstall($name, $force);
            $this->success(__('Uninstall successful'));
        } catch (AddonException $e) {
            $this->result($e->getData(), $e->getCode(), __($e->getMessage()));
        } catch (Exception $e) {
            $this->error(__($e->getMessage()));
        }
    }

    /**
     * 禁用启用
     */
    public function state()
    {
        $name = request()->post("name");
        $action = request()->post("action");
        $force = (int)request()->post("force");
        if (!$name) {
            $this->error(__('Parameter %s can not be empty', 'name'));
        }
        if (!preg_match("/^[a-zA-Z0-9_]+$/", $name)) {
            $this->error(__('Addon name incorrect'));
        }
        try {
            $action = $action == 'enable' ? $action : 'disable';
            //调用启用、禁用的方法
            Service::$action($name, $force);
            Cache::delete('__menu__');
            $this->success(__('Operate successful'));
        } catch (AddonException $e) {
            $this->result($e->getData(), $e->getCode(), __($e->getMessage()));
        } catch (Exception $e) {
            $this->error(__($e->getMessage()));
        }
    }

    /**
     * 本地上传
     */
    public function local()
    {
        Config::set(['default_return_type'=>'json'],'app');

        $file = request()->file('file');
        $addonTmpDir = runtime_path() . 'addons' . DIRECTORY_SEPARATOR;
        if (!is_dir($addonTmpDir)) {
            @mkdir($addonTmpDir, 0755, true);
        }
        //$info = $file->move($addonTmpDir,$file->getOriginalName());
        $info = moveFile($file->getPathname(),$addonTmpDir,$file->hash().'.zip');
        if ($info) {
            $tmpName = substr($info->getFilename(), 0, stripos($info->getFilename(), '.'));
            $tmpAddonDir = ADDON_PATH . $tmpName . DIRECTORY_SEPARATOR;
            $tmpFile = $addonTmpDir . $info->getFilename();
            try {
                Service::unzip($tmpName);
                unset($info);
                @unlink($tmpFile);
                $infoFile = $tmpAddonDir . 'addon.ini';
                if (!is_file($infoFile)) {
                    throw new Exception(__('Addon info file was not found'));
                }

                $config = (new \think\Config)->load($infoFile , $tmpName);
                $name = isset($config['name']) ? $config['name'] : '';
                if (!$name) {
                    throw new Exception(__('Addon info file data incorrect'));
                }
                if (!preg_match("/^[a-zA-Z0-9_]+$/", $name)) {
                    throw new Exception(__('Addon name incorrect'));
                }

                $newAddonDir = ADDON_PATH . $name . DIRECTORY_SEPARATOR;
                if (is_dir($newAddonDir)) {
                    throw new Exception(__('Addon already exists'));
                }

                //重命名插件文件夹
                rename($tmpAddonDir, $newAddonDir);
                try {
                    //默认禁用该插件copydirs
                    $info = get_addon_info($name);
                    if ($info['state']) {
                        $info['state'] = 0;
                        set_addon_info($name, $info);
                    }

                    //执行插件的安装方法
                    $class = get_addon_class($name);
                    if (class_exists($class)) {
                        $addon = new $class(app());
                        $addon->install();
                    }

                    //导入SQL
                    Service::importsql($name);

                    $info['config'] = get_addon_config($name) ? 1 : 0;
                    $this->success(__('Offline installed tips'), null, ['addon' => $info]);
                } catch (Exception $e) {
                    @rmdirs($newAddonDir);
                    throw new Exception(__($e->getMessage()));
                }
            } catch (Exception $e) {
                unset($info);
                @unlink($tmpFile);
                @rmdirs($tmpAddonDir);
                $this->error(__($e->getMessage()));
            }
        } else {
            // 上传失败获取错误信息
            $this->error(__($file->getError()));
        }
    }

    /**
     * 更新插件
     */
    public function upgrade()
    {
        $name = request()->post("name");
        $addonTmpDir = runtime_path() . 'addons' . DIRECTORY_SEPARATOR;
        if (!$name) {
            $this->error(__('Parameter %s can not be empty', 'name'));
        }
        if (!preg_match("/^[a-zA-Z0-9_]+$/", $name)) {
            $this->error(__('Addon name incorrect'));
        }
        if (!is_dir($addonTmpDir)) {
            @mkdir($addonTmpDir, 0755, true);
        }
        try {
            $uid = request()->post("uid");
            $token = request()->post("token");
            $version = request()->post("version");
            $faversion = request()->post("faversion");
            $extend = [
                'uid'       => $uid,
                'token'     => $token,
                'version'   => $version,
                'faversion' => $faversion
            ];
            //调用更新的方法
            Service::upgrade($name, $extend);
            Cache::delete('__menu__');
            $this->success(__('Operate successful'));
        } catch (Exception $e) {
            $this->error(__($e->getMessage()));
        }
    }

    /**
     * 已装插件
     */
    public function downloaded()
    {
        $offset = (int)request()->get("offset");
        $limit = (int)request()->get("limit");
        $filter = request()->get("filter");
        $search = request()->get("search");
        $search = htmlspecialchars(strip_tags($search));
        $onlineaddons = Cache::get("onlineaddons");
        if (!is_array($onlineaddons)) {
            $onlineaddons = [];
            $result = Http::sendRequest(Config::get('easyadmin.api_url') . '/addon/index');
            if ($result['ret']) {
                $json = (array)json_decode($result['msg'], true);
                $rows = isset($json['rows']) ? $json['rows'] : [];
                foreach ($rows as $index => $row) {
                    $onlineaddons[$row['name']] = $row;
                }
            }
            Cache::set("onlineaddons", $onlineaddons, 600);
        }
        $filter = (array)json_decode($filter, true);
        $addons = get_addon_list();
        $list = [];
        foreach ($addons as $k => $v) {
            if ($search && stripos($v['name'], $search) === false && stripos($v['intro'], $search) === false) {
                continue;
            }

            if (isset($onlineaddons[$v['name']])) {
                $v = array_merge($v, $onlineaddons[$v['name']]);
            } else {
                $v['category_id'] = 0;
                $v['flag'] = '';
                $v['banner'] = '';
                $v['image'] = '';
                $v['donateimage'] = '';
                $v['demourl'] = '';
                $v['price'] = __('None');
                $v['screenshots'] = [];
                $v['releaselist'] = [];
            }
            $v['url'] = addons_url($v['name']);
            $v['url'] = str_replace(request()->server('SCRIPT_NAME'), '', $v['url']);
            $v['createtime'] = filemtime(ADDON_PATH . $v['name']);
            if ($filter && isset($filter['category_id']) && is_numeric($filter['category_id']) && $filter['category_id'] != $v['category_id']) {
                continue;
            }
            $list[] = $v;
        }
        $total = count($list);
        if ($limit) {
            $list = array_slice($list, $offset, $limit);
        }
        $result = array("total" => $total, "rows" => $list);

        $callback = request()->get('callback') ? "jsonp" : "json";
        $call=$callback($result);
        return $call;
    }

    /**
     * 生成所有文件hash值
     */
    public function hash()
    {
        Config::set(['default_return_type'=>'json'],'app');

        $file = request()->file('file');
        $addonTmpDir = runtime_path() . 'addons' . DIRECTORY_SEPARATOR;
        if (!is_dir($addonTmpDir)) {
            @mkdir($addonTmpDir, 0755, true);
        }
        $info = $file->move($addonTmpDir,$file->getOriginalName());
        if ($info) {
            $tmpName = substr($info->getFilename(), 0, stripos($info->getFilename(), '.'));
            $tmpAddonDir = ADDON_PATH . $tmpName . DIRECTORY_SEPARATOR;
            $tmpFile = $addonTmpDir . $info->getFilename();
            try {
                Service::unzip($tmpName);
                unset($info);
                @unlink($tmpFile);
                $infoFile = $tmpAddonDir . $tmpName.'.ini';
                if (!is_file($infoFile)) {
                    throw new Exception(__('Addon info file was not found'));
                }

                $config = (new \think\Config)->load($infoFile , $tmpName);
                $name = isset($config['name']) ? $config['name'] : '';
                if (!$name) {
                    throw new Exception(__('Addon info file data incorrect'));
                }
                if (!preg_match("/^[a-zA-Z0-9_]+$/", $name)) {
                    throw new Exception(__('Addon name incorrect'));
                }

                $newAddonDir = ADDON_PATH . $name . DIRECTORY_SEPARATOR;
                if (is_dir($newAddonDir)) {
                    //throw new Exception(__('Addon already exists'));
                }

                //重命名插件文件夹
                rename($tmpAddonDir, $newAddonDir);
                try {
                    //默认禁用该插件
                    $info = get_addon_info($name);
                    if ($info['state']) {
                        $info['state'] = 0;
                        set_addon_info($name, $info);
                    }

                    //执行插件的安装方法
                    $class = get_addon_class($name);
                    if (class_exists($class)) {
                        $addon = new $class(app());
                        $addon->install();
                    }

                    //导入SQL
                    Service::importsql($name);

                    $info['config'] = get_addon_config($name) ? 1 : 0;
                    $this->success(__('Offline installed tips'), null, ['addon' => $info]);
                } catch (Exception $e) {
                    @rmdirs($newAddonDir);
                    throw new Exception(__($e->getMessage()));
                }
            } catch (Exception $e) {
                unset($info);
                @unlink($tmpFile);
                @rmdirs($tmpAddonDir);
                $this->error(__($e->getMessage()));
            }
        } else {
            // 上传失败获取错误信息
            $this->error(__($file->getError()));
        }
    }
}
