<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use easyadmin\Http;
use think\addons\AddonException;
use think\addons\Service;
use think\facade\Cache;
use think\facade\Config;
use think\Exception;
use think\facade\View;
use think\helper\Str;

/**
 * 插件管理
 *
 * @icon   fa fa-cube
 * @remark 可在线安装、卸载、禁用、启用插件，同时支持安装本地插件。EasyAdmin已上线插件商店 ，你可以发布你的免费或付费插件：<a href="https://cloud.easyadmin.vip" target="_blank">https://addons.easyadmin.vip</a>
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
        $name = request()->param("name");
        if (!$name) {
            $this->error(__('Parameter %s can not be empty', $ids ? 'id' : 'name'));
        }
        if (strpos($name,'.html') !== false) {
            $name = substr($name,0,strrpos($name ,".html"));
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
            $params = $this->request->post("row/a");
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
                    \think\addons\Service::refresh();
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
        View::assign("addoninfo", ['info' => $info, 'config' => $config, 'tips' => $tips]);
        View::assign('name', $name);




        $siteList = [];
        $groupList =  $this->getGroupList($name);
        foreach ($groupList as $k => $v) {
            $siteList[$k]['name'] = $k;
            $siteList[$k]['title'] = $v;
            $siteList[$k]['list'] = [];
        }
        $ss=get_addon_fullconfig($name);
        foreach ($ss as $k => $v) {
            isset($v['group'])?null:$v['group']='default';
            if (!isset($siteList[$v['group']])) {
                //continue;
                $siteList[$v['group']]='default';
                $siteList[$v['group']]['list']='';
            }
            $value = $v;
            $value['title'] = __($value['title']);
            if (in_array($value['type'], ['select', 'selects', 'checkbox', 'radio'])) {
                $value['value'] = explode(',', $value['value']);
            }
            $value['content'] = $value['content']?$value['content']:[];
            $value['tip'] = htmlspecialchars($value['tip']);
            $siteList[$v['group']]['list'][] = $value;
        }
        $index = 0;
        foreach ($siteList as $k => &$v) {
            $v['active'] = !$index ? true : false;
            $index++;
        }
        View::assign('siteList', $siteList);
        View::assign('groupList', $this->getGroupList($name));







        $configFile = ADDON_PATH . $name . DIRECTORY_SEPARATOR . 'config.html';
        $viewFile = is_file($configFile) ? $configFile : '';
        return View::fetch($viewFile);
    }


    /**
     * 读取分类分组列表
     * @param string $name
     * @return array
     */
    public static function getGroupList($name)
    {
        $groupList =get_addon_config($name);
//        foreach ($groupList as $k => &$v) {
//            $v = __($v);
//        }
        if (array_key_exists('configgroup',$groupList)){

            return $groupList['configgroup'];
        }else{
            return ['default'=>'默认设置'];
        }
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
        $app = request()->post("app","");
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
            $eaversion = request()->post("eaversion");
            $extend = [
                'app'       => $app,
                'uid'       => $uid,
                'token'     => $token,
                'version'   => $version,
                'eaversion' => $eaversion
            ];
            Service::install($name, $force, $extend);
            if (isset($extend['app']) && $extend['app'] && strpos($name,'app_' ) !== false){
                //重新组合的插件名称
                $name = str_replace('app_',$app.'_',$name);
            }
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
     * 获取所有应用列表
     */
    public function applist()
    {
        Config::set(['default_return_type'=>'json'],'app');
        $name = request()->param('name');
        if ($name){
            //获取所有应用插件信息
            $data = [];
            $applist = getAllApp();
            foreach ($applist as $item) {
                $data[] = [$item['name'],$item['title']];
            }
            $this->success(__('Applist Success'), null, ['app'=>$data]);
        }
    }

    /**
     * 本地上传安装插件
     */
    public function local()
    {
        Config::set(['default_return_type'=>'json'],'app');

        $file = request()->file('file');
        //临时文件存放目录
        $addonTmpDir = runtime_path() . 'addons' . DIRECTORY_SEPARATOR;
        if (!is_dir($addonTmpDir)) {
            @mkdir($addonTmpDir, 0755, true);
        }
        //移动上传文件到临时文件夹下。文件名为hash值
        $info = moveFile($file->getPathname(),$addonTmpDir,$file->hash().'.zip');
        if ($info) {
            //文件名，即hash
            $tmpName = substr($info->getFilename(), 0, stripos($info->getFilename(), '.'));
            //插件目录下的临时存放目录
            $tmpAddonDir = ADDON_PATH . $tmpName . DIRECTORY_SEPARATOR;
            //临时文件完整路径
            $tmpFile = $addonTmpDir . $info->getFilename();
            try {
                //解压压缩包，带入名称
                Service::unzip($tmpName);
                unset($info);
                //删除压缩包
                @unlink($tmpFile);
                $infoFile = $tmpAddonDir . 'addon.ini';
                if (!is_file($infoFile)) {
                    throw new Exception(__('Addon info file was not found'));
                }

                $config = (new \think\Config)->load($infoFile , $tmpName);

                //模块插件安装特殊处理
                if (isset($config['type']) && $config['type'] == 'appmodule'){
                    //获取所有应用插件信息
                    $data = [];
                    $applist = getAllApp();
                    foreach ($applist as $item) {
                        $data[] = [$item['name'],$item['title']];
                    }
                    $this->success(__('AppModule Install'), null, ['name'=>$tmpName,'appmodule'=>1,'app' => $data]);
                }

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

                //完成移动并重命名插件文件夹
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
     * 安装应用模块插件
     */
    public function appmodule()
    {
        //文件名，即hash
        $tmpName = request()->post('data','');
        if (isset($tmpName['name'])){
            $tmpName = $tmpName['name'];
            //插件目录下的临时存放目录
            $tmpAddonDir = ADDON_PATH . $tmpName . DIRECTORY_SEPARATOR;
            try {
                $infoFile = $tmpAddonDir . 'addon.ini';
                if (!is_file($infoFile)) {
                    throw new Exception(__('Addon info file was not found'));
                }

                //加载配置文件
                $config = (new \think\Config)->load($infoFile , $tmpName);

                //模块插件安装特殊处理
                if (isset($config['type']) && $config['type'] == 'appmodule'){
                    //要安装的应用名称
                    $app = request()->post('app','');
                    //安装在非系统的应用管理下
                    if ($app != 'app'){
                        //插件名称
                        $old_addon = $config['name'];
                        //重新组合的插件名称
                        $new_addon = str_replace('app_',$app.'_',$old_addon);
                        //下划线转驼峰
                        $oldaddon = Str::studly($old_addon);
                        //下划线转驼峰
                        $newaddon = Str::studly($new_addon);
                        //遍历所有文件夹和文件
                        $dirlist = getAllDir($tmpAddonDir);
                        //替换所有特定信息
                        replaceSignStr($tmpAddonDir,$dirlist,['app_',$oldaddon],[$app.'_',$newaddon]);
                        //重命名主文件
                        rename($tmpAddonDir.$oldaddon.'.php', $tmpAddonDir.$newaddon.'.php');
                        //重新写入父菜单
                        file_put_contents($tmpAddonDir.'addon.ini',str_replace('parent_menu = app','parent_menu = '.$app,file_get_contents($tmpAddonDir.'addon.ini')));
                        file_put_contents($tmpAddonDir.'addon.ini',str_replace('parent = easyadmin','parent = '.$app,file_get_contents($tmpAddonDir.'addon.ini')));
                    }
                }

                //再次加载配置文件
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

                //完成移动并重命名插件文件夹
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
                @rmdirs($tmpAddonDir);
                $this->error(__($e->getMessage()));
            }
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
            $eaversion = request()->post("eaversion");
            $extend = [
                'uid'       => $uid,
                'token'     => $token,
                'version'   => $version,
                'eaversion' => $eaversion
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
//            $v['dashboard'] = !$v['dashboard']?0:$v['dashboard'];
//            $v['tab'] = !$v['tab']?0:$v['tab'];
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

    /**
     * 引导推荐
     */
    public function guide()
    {
        return View::fetch();
    }
}
