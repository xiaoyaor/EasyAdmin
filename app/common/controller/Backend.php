<?php

namespace app\common\controller;

use app\BaseController;
use think\App;
use think\facade\Config;
use think\facade\Lang;
use think\facade\Log;
use think\facade\Session;
use think\facade\Validate;
use think\facade\View;
use think\facade\Event;
use xiaoyaor\think\Jump;
use app\admin\library\Auth;

/**
 * 后台控制器基类
 */
class Backend extends BaseController
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
     * 权限控制类
     * @var Auth
     */
    protected $auth = null;

    /**
     * 模型对象
     * @var \think\Model
     */
    protected $model = null;

    /**
     * 快速搜索时执行查找的字段
     */
    protected $searchFields = 'id';

    /**
     * 是否是关联查询
     */
    protected $relationSearch = false;

    /**
     * 是否开启数据限制
     * 支持auth/personal
     * 表示按权限判断/仅限个人
     * 默认为禁用,若启用请务必保证表中存在admin_id字段
     */
    protected $dataLimit = false;

    /**
     * 数据限制字段
     */
    protected $dataLimitField = 'admin_id';

    /**
     * 数据限制开启时自动填充限制字段值
     */
    protected $dataLimitFieldAutoFill = true;

    /**
     * 是否开启Validate验证
     */
    protected $modelValidate = false;

    /**
     * 是否开启模型场景验证
     */
    protected $modelSceneValidate = false;

    /**
     * Multi方法可批量修改的字段
     */
    protected $multiFields = 'status';

    /**
     * Selectpage可显示的字段
     */
    protected $selectpageFields = '*';

    /**
     * 前台提交过来,需要排除的字段数据
     */
    protected $excludeFields = "";

    /**
     * 导入文件首行类型
     * 支持comment/name
     * 表示注释或字段名
     */
    protected $importHeadType = 'comment';

    /**
     * 引入后台控制器的traits
     */
    use \app\admin\library\traits\Backend;

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
        $this->app     = app();
        $modulename = get_modulename(Config::get('app.app_map'));
        $controllername = strtolower(request()->controller());
        $actionname = strtolower(request()->action());

        $path = str_replace('.', '/', $controllername) . '/' . $actionname;

        // 定义是否Addtabs请求
        !defined('IS_ADDTABS') && define('IS_ADDTABS', input("addtabs") ? true : false);

        // 定义是否Dialog请求
        !defined('IS_DIALOG') && define('IS_DIALOG', input("dialog") ? true : false);

        // 定义是否AJAX请求
        !defined('IS_AJAX') && define('IS_AJAX', request()->isAjax());

        //授权验证hook
        if (Event::trigger('Auth')){
        $this->auth = Auth::instance();
        }

        if ($this->auth){
            // 设置当前请求的URI
            $this->auth->setRequestUri($path);
            // 检测是否需要验证登录
            if (!$this->auth->match($this->noNeedLogin)) {
                //检测是否登录
                if (!$this->auth->isLogin()) {
                    event_trigger('adminNologin', $this);
                    $url = Session::get('referer');
                    $url = $url ? $url : request()->url();
                    $url2='/'.$modulename.'/';
                    if ($url == $url2) {
                        $this->redirect('login/index', 302, ['referer' => $url]);
                        exit;
                    }
                    $this->error(__('Please login first'), url('login/index', ['url' => $url]));
                }
                // 判断是否需要验证权限
                if (!$this->auth->match($this->noNeedRight)) {
                    // 判断控制器和方法判断是否有对应权限
                    if (!$this->auth->check($path)) {
                        event_trigger('adminNopermission', $this);
                        $this->error(__('You have no permission'), '');
                    }
                }
            }

        }
        // 非选项卡时重定向
        if (!request()->isPost() && !IS_AJAX && !IS_ADDTABS && !IS_DIALOG && input("ref") == 'addtabs') {
            $url = preg_replace_callback("/([\?|&]+)ref=addtabs(&?)/i", function ($matches) {
                return $matches[2] == '&' ? $matches[1] : '';
            }, request()->url());
//            if (Config::get('app.app_map')) {
//                if (stripos($url, request()->server('SCRIPT_NAME')) === 0) {
//                    $url = substr($url, strlen(request()->server('SCRIPT_NAME')));
//                }
//                $url = url($url, [], false);
//            }
            $this->redirect('index/index',  302, ['referer' => $url]);
            exit;
        }

        // 设置面包屑导航数据
        if ($this->auth){
            $breadcrumb = $this->auth->getBreadCrumb($path);
            View::assign('breadcrumb' , $breadcrumb);
        }else{
            $breadcrumb = getBreadCrumb($path);
        }
        View::assign('breadcrumb' , $breadcrumb);

        // 语言检测
        $lang = strip_tags(Config::get("lang.default_lang"));

        if(request()->get('act')){
            $multiplenav=request()->get('act');
            if ($multiplenav=='switch-multiplenav-on'){
                Config::set(['multiplenav' => 1],'site');
                change_site('multiplenav','1');
            }
            else if ($multiplenav=='switch-multiplenav-off'){
                Config::set(['multiplenav' => 0],'site');
                change_site('multiplenav','0');
            }
        }
        Config::set(['multiplenav' => (boolean)Config::get("site.multiplenav")],'site');
        //站点信息
        $site = Config::get("site");
        //上传信息
        //$upload = \app\common\model\Config::upload();
        $upload = Config::get('upload');
        // 上传信息配置后触发
        //event_trigger("uploadConfigInit", $upload);
        // 配置信息
        $config = [
            'site'           => array_intersect_key($site, array_flip(['name', 'indexurl', 'cdnurl', 'version', 'timezone', 'languages'])),
            'upload'         => $upload,
            'modulename'     => $modulename,
            'controllername' => $controllername,
            'actionname'     => $actionname,
            'jsname'         => 'backend/' . str_replace('.', '/', $controllername),
            'moduleurl'      => rtrim(url("/{$modulename}", [], false), '/'),
            'language'       => $lang,
            'easyadmin'      => Config::get('easyadmin'),
            'api_url'      => Config::get('easyadmin.api_url'),
            'multiplenav'      => Config::get('site.multiplenav'),
            'referer'        => Session::get("referer")
        ];

        $config = array_merge($config, Config::get("view.tpl_replace_string"));

        Config::set(array_merge(Config::get('upload'), $upload),'upload');
        //初始化更改日志级别配置项
        event_trigger("AddonsInit", $config);
        // 配置信息后
        event_trigger("configInit", $config);
        //加载当前控制器语言包
        $this->loadlang($controllername);
        //后台映射模块地址名称
        View::assign('modulename', $modulename);
        //渲染站点配置
        View::assign('site', $site);
        //框架信息
        View::assign('easyadmin', Config::get("easyadmin"));
        //渲染配置信息
        View::assign('config', $config);
        if ($this->auth){
            //渲染权限对象
            View::assign('auth', $this->auth);
            //渲染管理员对象
            View::assign('admin', Session::get('admin'));
        }else{
            //渲染权限对象
            View::assign('auth', null);
            //渲染管理员对象
            View::assign('admin', null);
        }
    }

    /**
     * 加载语言文件
     * @param string $name
     */
    protected function loadlang($name)
    {
        Lang::load(app_path() .  '/lang/' . Config::get('lang.default_lang') . '/' . str_replace('.', '/', $name) . '.php');
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

    /**
     * 生成查询所需要的条件,排序方式
     * @param mixed   $searchfields   快速查询的字段
     * @param boolean $relationSearch 是否关联查询
     * @return array
     */
    protected function buildparams($searchfields = null, $relationSearch = null)
    {
        $searchfields = is_null($searchfields) ? $this->searchFields : $searchfields;
        $relationSearch = is_null($relationSearch) ? $this->relationSearch : $relationSearch;
        $search = request()->get("search", '');
        $filter = request()->get("filter", '');
        $op = request()->get("op", '', 'trim');
        $sort = request()->get("sort", !empty($this->model) && $this->model->getPk() ? $this->model->getPk() : 'id');
        $order = request()->get("order", "DESC");
        $offset = request()->get("offset", 0);
        $limit = request()->get("limit", 0);
        $filter = (array)json_decode($filter, true);
        $op = (array)json_decode($op, true);
        $filter = $filter ? $filter : [];
        $where = [];
        $tableName = '';
        if ($relationSearch) {
            if (!empty($this->model)) {
                $name = basename(str_replace('\\', '/', get_class($this->model)));
                $tableName = $name . '.';
            }
            $sortArr = explode(',', $sort);
            foreach ($sortArr as $index => & $item) {
                $item = stripos($item, ".") === false ? $tableName . trim($item) : $item;
            }
            unset($item);
            $sort = implode(',', $sortArr);
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            $where[] = [$tableName . $this->dataLimitField, 'in', $adminIds];
        }
        if ($search) {
            $searcharr = is_array($searchfields) ? $searchfields : explode(',', $searchfields);
            foreach ($searcharr as $k => &$v) {
                $v = stripos($v, ".") === false ? $tableName . $v : $v;
            }
            unset($v);
            $where[] = [implode("|", $searcharr), "LIKE", "%{$search}%"];
        }
        foreach ($filter as $k => $v) {
            $sym = isset($op[$k]) ? $op[$k] : '=';
            if (stripos($k, ".") === false) {
                $k = $tableName . $k;
            }
            $v = !is_array($v) ? trim($v) : $v;
            $sym = strtoupper(isset($op[$k]) ? $op[$k] : $sym);
            switch ($sym) {
                case '=':
                case '<>':
                    $where[] = [$k, $sym, (string)$v];
                    break;
                case 'LIKE':
                case 'NOT LIKE':
                case 'LIKE %...%':
                case 'NOT LIKE %...%':
                    $where[] = [$k, trim(str_replace('%...%', '', $sym)), "%{$v}%"];
                    break;
                case '>':
                case '>=':
                case '<':
                case '<=':
                    $where[] = [$k, $sym, intval($v)];
                    break;
                case 'FINDIN':
                case 'FINDINSET':
                case 'FIND_IN_SET':
                    $where[] = "FIND_IN_SET('{$v}', " . ($relationSearch ? $k : '`' . str_replace('.', '`.`', $k) . '`') . ")";
                    break;
                case 'IN':
                case 'IN(...)':
                case 'NOT IN':
                case 'NOT IN(...)':
                    $where[] = [$k, str_replace('(...)', '', $sym), is_array($v) ? $v : explode(',', $v)];
                    break;
                case 'BETWEEN':
                case 'NOT BETWEEN':
                    $arr = array_slice(explode(',', $v), 0, 2);
                    if (stripos($v, ',') === false || !array_filter($arr)) {
                        continue 2;
                    }
                    //当出现一边为空时改变操作符
                    if ($arr[0] === '') {
                        $sym = $sym == 'BETWEEN' ? '<=' : '>';
                        $arr = $arr[1];
                    } elseif ($arr[1] === '') {
                        $sym = $sym == 'BETWEEN' ? '>=' : '<';
                        $arr = $arr[0];
                    }
                    $where[] = [$k, $sym, $arr];
                    break;
                case 'RANGE':
                case 'NOT RANGE':
                    $v = str_replace(' - ', ',', $v);
                    $arr = array_slice(explode(',', $v), 0, 2);
                    if (stripos($v, ',') === false || !array_filter($arr)) {
                        continue 2;
                    }
                    //当出现一边为空时改变操作符
                    if ($arr[0] === '') {
                        $sym = $sym == 'RANGE' ? '<=' : '>';
                        $arr = $arr[1];
                    } elseif ($arr[1] === '') {
                        $sym = $sym == 'RANGE' ? '>=' : '<';
                        $arr = $arr[0];
                    }
                    $where[] = [$k, str_replace('RANGE', 'BETWEEN', $sym) . ' time', $arr];
                    break;
                case 'LIKE':
                case 'LIKE %...%':
                    $where[] = [$k, 'LIKE', "%{$v}%"];
                    break;
                case 'NULL':
                case 'IS NULL':
                case 'NOT NULL':
                case 'IS NOT NULL':
                    $where[] = [$k, strtolower(str_replace('IS ', '', $sym))];
                    break;
                default:
                    break;
            }
        }
        //闭包函数，use为带入外部的where，在内部使用，然后再导出外部，use意思是连接【闭包】和【外界】变量
        $where = function ($query) use ($where) {
            foreach ($where as $k => $v) {
                if (is_array($v)) {
                    call_user_func_array([$query, 'where'], $v);
                } else {
                    $query->where($v);
                }
            }
        };
        return [$where, $sort, $order, $offset, $limit];
    }

    /**
     * 获取数据限制的管理员ID
     * 禁用数据限制时返回的是null
     * @return mixed
     */
    protected function getDataLimitAdminIds()
    {
        if (!$this->dataLimit) {
            return null;
        }
        if ($this->auth->isSuperAdmin()) {
            return null;
        }
        $adminIds = [];
        if (in_array($this->dataLimit, ['auth', 'personal'])) {
            $adminIds = $this->dataLimit == 'auth' ? $this->auth->getChildrenAdminIds(true) : [$this->auth->id];
        }
        return $adminIds;
    }

    /**
     * Selectpage的实现方法
     *
     * 当前方法只是一个比较通用的搜索匹配,请按需重载此方法来编写自己的搜索逻辑,$where按自己的需求写即可
     * 这里示例了所有的参数，所以比较复杂，实现上自己实现只需简单的几行即可
     *
     */
    protected function selectpage()
    {
        //设置过滤方法
        request()->filter(['strip_tags', 'htmlspecialchars']);

        //搜索关键词,客户端输入以空格分开,这里接收为数组
        $word = (array)request()->request("q_word/a");
        //当前页
        $page = request()->request("pageNumber");
        //分页大小
        $pagesize = request()->request("pageSize");
        //搜索条件
        $andor = request()->request("andOr", "and", "strtoupper");
        //排序方式
        $orderby = (array)request()->request("orderBy/a");
        //显示的字段
        $field = request()->request("showField");
        //主键
        $primarykey = request()->request("keyField");
        //主键值
        $primaryvalue = request()->request("keyValue");
        //搜索字段
        $searchfield = (array)request()->request("searchField/a");
        //自定义搜索条件
        $custom = (array)request()->request("custom/a");
        //是否返回树形结构
        $istree = request()->request("isTree", 0);
        $ishtml = request()->request("isHtml", 0);
        if ($istree) {
            $word = [];
            $pagesize = 99999;
        }
        $order = [];
        foreach ($orderby as $k => $v) {
            $order[$v[0]] = $v[1];
        }
        $field = $field ? $field : 'name';

        //如果有primaryvalue,说明当前是初始化传值
        if ($primaryvalue !== null) {
            $where = [$primarykey => ['in', $primaryvalue]];
        } else {
            $where = function ($query) use ($word, $andor, $field, $searchfield, $custom) {
                $logic = $andor == 'AND' ? '&' : '|';
                $searchfield = is_array($searchfield) ? implode($logic, $searchfield) : $searchfield;
                foreach ($word as $k => $v) {
                    $query->where(str_replace(',', $logic, $searchfield), "like", "%{$v}%");
                }
                if ($custom && is_array($custom)) {
                    foreach ($custom as $k => $v) {
                        if (is_array($v) && 2 == count($v)) {
                            $query->where($k, trim($v[0]), $v[1]);
                        } else {
                            $query->where($k, '=', $v);
                        }
                    }
                }
            };
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            $this->model->where($this->dataLimitField, 'in', $adminIds);
        }
        $list = [];
        $total = $this->model->where($where)->count();
        if ($total > 0) {
            if (is_array($adminIds)) {
                $this->model->where($this->dataLimitField, 'in', $adminIds);
            }
            $datalist = $this->model->where($where)
                ->order($order)
                ->page($page, $pagesize)
                ->field($this->selectpageFields)
                ->select();
            foreach ($datalist as $index => $item) {
                unset($item['password'], $item['salt']);
                $list[] = [
                    $primarykey => isset($item[$primarykey]) ? $item[$primarykey] : '',
                    $field      => isset($item[$field]) ? $item[$field] : '',
                    'pid'       => isset($item['pid']) ? $item['pid'] : 0
                ];
            }
            if ($istree && !$primaryvalue) {
                $tree = Tree::instance();
                $tree->init(collection($list)->toArray(), 'pid');
                $list = $tree->getTreeList($tree->getTreeArray(0), $field);
                if (!$ishtml) {
                    foreach ($list as &$item) {
                        $item = str_replace('&nbsp;', ' ', $item);
                    }
                    unset($item);
                }
            }
        }
        //这里一定要返回有list这个字段,total是可选的,如果total<=list的数量,则会隐藏分页按钮
        return json(['list' => $list, 'total' => $total]);
    }

}
