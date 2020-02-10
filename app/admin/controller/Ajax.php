<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use easy\Random;
use think\addons\Service;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Db;
use think\facade\Lang;
use think\facade\Event;
use app\common\model\Attachment;

/**
 * Ajax异步请求接口
 * @internal
 */
class Ajax extends Backend
{

    protected $noNeedLogin = ['lang'];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();

        //设置过滤方法
        request()->filter(['strip_tags', 'htmlspecialchars']);
    }

    /**
     * 加载语言包
     */
    public function lang()
    {
        header('Content-Type: application/javascript');
        $controllername = input("controllername");
        //默认只加载了控制器对应的语言名，你还根据控制器名来加载额外的语言包
        $this->loadlang($controllername);
        return jsonp(Lang::get(), 200, [], ['json_encode_param' => JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE]);
    }

    /**
     * 清空系统缓存
     */
    public function wipecache()
    {
        $type = request()->request("type");
        switch ($type) {
            case 'all':
            case 'content':
                Cache::clear();
                if ($type == 'content')
                    break;
            case 'template':
                rmdirs(runtime_path() . 'temp' .DIRECTORY_SEPARATOR, false);
                if ($type == 'template')
                    break;
            case 'addons':
                Service::refresh();
                if ($type == 'addons')
                    break;
        }

        event_trigger("wipecacheAfter");
        $this->success();
    }

}
