<?php
// 这是系统自动生成的公共文件
use app\common\model\Config as ConfigModel;
use Overtrue\Pinyin\Pinyin;
use think\exception\FileException;
use think\facade\Config;
use think\facade\Env;
use think\facade\Event;
use think\facade\Request;
use think\facade\Session;
use think\File;
use think\Model;
use app\common\model\Category;
use easy\Form;
use easy\Tree;
use think\facade\Db;

if (!function_exists('build_select')) {

    /**
     * 生成下拉列表
     * @param string $name
     * @param mixed $options
     * @param mixed $selected
     * @param mixed $attr
     * @return string
     */
    function build_select($name, $options, $selected = [], $attr = [])
    {
        $options = is_array($options) ? $options : explode(',', $options);
        $selected = is_array($selected) ? $selected : explode(',', $selected);
        return Form::select($name, $options, $selected, $attr);
    }
}

if (!function_exists('build_radios')) {

    /**
     * 生成单选按钮组
     * @param string $name
     * @param array $list
     * @param mixed $selected
     * @return string
     */
    function build_radios($name, $list = [], $selected = null)
    {
        $html = [];
        $selected = is_null($selected) ? key($list) : $selected;
        $selected = is_array($selected) ? $selected : explode(',', $selected);
        foreach ($list as $k => $v) {
            $html[] = sprintf(Form::label("{$name}-{$k}", "%s {$v}"), Form::radio($name, $k, in_array($k, $selected), ['id' => "{$name}-{$k}"]));
        }
        return '<div class="radio">' . implode(' ', $html) . '</div>';
    }
}

if (!function_exists('build_checkboxs')) {

    /**
     * 生成复选按钮组
     * @param string $name
     * @param array $list
     * @param mixed $selected
     * @return string
     */
    function build_checkboxs($name, $list = [], $selected = null)
    {
        $html = [];
        $selected = is_null($selected) ? [] : $selected;
        $selected = is_array($selected) ? $selected : explode(',', $selected);
        foreach ($list as $k => $v) {
            $html[] = sprintf(Form::label("{$name}-{$k}", "%s {$v}"), Form::checkbox($name, $k, in_array($k, $selected), ['id' => "{$name}-{$k}"]));
        }
        return '<div class="checkbox">' . implode(' ', $html) . '</div>';
    }
}


if (!function_exists('build_category_select')) {

    /**
     * 生成分类下拉列表框
     * @param string $name
     * @param string $type
     * @param mixed $selected
     * @param array $attr
     * @return string
     */
    function build_category_select($name, $type, $selected = null, $attr = [], $header = [])
    {
        $tree = Tree::instance();
        $tree->init(Category::getCategoryArray($type), 'pid');
        $categorylist = $tree->getTreeList($tree->getTreeArray(0), 'name');
        $categorydata = $header ? $header : [];
        foreach ($categorylist as $k => $v) {
            $categorydata[$v['id']] = $v['name'];
        }
        $attr = array_merge(['id' => "c-{$name}", 'class' => 'form-control selectpicker'], $attr);
        return build_select($name, $categorydata, $selected, $attr);
    }
}

if (!function_exists('build_toolbar')) {

    /**
     * 生成表格操作按钮栏
     * @param array $btns 按钮组
     * @param array $attr 按钮属性值
     * @return string
     */
    function build_toolbar($btns = NULL, $attr = [])
    {
        //授权验证hook
        if (Event::trigger('Auth')){
            $auth = \app\admin\library\Auth::instance();
        }
        $controller = str_replace('.', '/', strtolower(Request::instance()->controller()));
        $btns = $btns ? $btns : ['refresh', 'add', 'edit', 'del', 'import'];
        $btns = is_array($btns) ? $btns : explode(',', $btns);
        $index = array_search('delete', $btns);
        if ($index !== FALSE) {
            $btns[$index] = 'del';
        }
        $btnAttr = [
            'refresh' => ['javascript:;', 'btn btn-primary btn-refresh', 'fa fa-refresh', '', __('Refresh')],
            'add'     => ['javascript:;', 'btn btn-success btn-add', 'fa fa-plus', __('Add'), __('Add')],
            'edit'    => ['javascript:;', 'btn btn-success btn-edit btn-disabled disabled', 'fa fa-pencil', __('Edit'), __('Edit')],
            'del'     => ['javascript:;', 'btn btn-danger btn-del btn-disabled disabled', 'fa fa-trash', __('Delete'), __('Delete')],
            'import'  => ['javascript:;', 'btn btn-danger btn-import', 'fa fa-upload', __('Import'), __('Import')],
        ];
        $btnAttr = array_merge($btnAttr, $attr);
        $html = [];
        foreach ($btns as $k => $v) {
            //如果未定义或没有权限
            if (!isset($btnAttr[$v]) || ($v !== 'refresh' && !$auth->check("{$controller}/{$v}"))) {
                continue;
            }
            list($href, $class, $icon, $text, $title) = $btnAttr[$v];
            $extend = $v == 'import' ? 'id="btn-import-file" data-url="ajax/upload" data-mimetype="csv,xls,xlsx" data-multiple="false"' : '';
            $html[] = '<a href="' . $href . '" class="' . $class . '" title="' . $title . '" ' . $extend . '><i class="' . $icon . '"></i> ' . $text . '</a>';
        }
        return implode(' ', $html);
    }
}

if (!function_exists('build_heading')) {

    /**
     * 生成页面Heading
     *
     * @param string $path 指定的path
     * @return string
     */
    function build_heading($path = NULL, $container = TRUE)
    {
        $title = $content = '';
        if (is_null($path)) {
            $action = request()->action();
            $controller = str_replace('.', '/', request()->controller());
            $path = strtolower($controller . ($action && $action != 'index' ? '/' . $action : ''));
        }
        // 根据当前的URI自动匹配父节点的标题和备注
        // 验证表是否存在
        $data=[];
        $tableName=Env::get('database.prefix', '').'auth_rule';
        $isTable=Db::query('SHOW TABLES LIKE '."'".$tableName."'");
        if($isTable){
            $data = Db::name('auth_rule')->where('name', $path)->field('title,remark')->find();
        }else{
            $menu=Config::get('menu');
            foreach ($menu as $key=>$value){
                if ($path == $value['name']){
                    $data=['title' => "Addon", 'remark' => "Addon tips"];
                }
            }
        }
        if ($data) {
            $title = __($data['title']);
            $content = __($data['remark']);
        }
        if (!$content)
            return '';
        $result = '<div class="panel-lead"><em>' . $title . '</em>' . $content . '</div>';
        if ($container) {
            $result = '<div class="panel-heading">' . $result . '</div>';
        }
        return $result;
    }
}

if (!function_exists('change_site')) {

    /**
     * 修改site.php存储的网站配置文件
     * 有config数据库则写入信息到数据库
     * 使用时用Config调用
     * @param string $key
     * @param string $val
     * @return bool
     */
    function change_site($key='',$val='')
    {
        $config = [];
        $config_exist=event_trigger('Config');//Config插件函数钩子，使用此函数判断是否将信息写入数据表
        if($config_exist){
            $model = new ConfigModel();
            foreach ($model->select() as $k => $v) {
                $value = $v->toArray();
                if (in_array($value['type'], ['selects', 'checkbox', 'images', 'files'])) {
                    $value['value'] = explode(',', $value['value']);
                }
                if ($value['type'] == 'array') {
                    $value['value'] = (array)json_decode($value['value'], true);
                }
                $config[$value['name']] = $value['value'];
                if ($key != '' && $value['name'] == $key){
                    $config[$value['name']] = $val;
                    (new ConfigModel())::where(['name'=>$key])->save(['value'=>$val]);
                }
            }
        }else{
            $config=Config::get('site');
            $config[$key]=$val;
        }
        file_put_contents(root_path() . 'config' . DIRECTORY_SEPARATOR . 'site.php','<?php' . "\n\nreturn " . var_export($config, true) . ";");
        return true;
    }
}

if (!function_exists('get_modulename')) {

    /**
     * @param array $list
     * @return string
     */
    function get_modulename($list)
    {
        if (is_array($list)){
            foreach ($list as $key => $value){
                if($value=='admin'){
                    return $key;
                }
            }
        }
        return 'admin';
    }
}

if (!function_exists('getSidebar')) {

    /**
     * 获取左侧和顶部菜单栏
     *返回1.左侧菜单2.导航菜单3.默认页页面，4.当前页面
     * @param array  $params URL对应的badge数据
     * @param string $fixedPage 默认页
     * @return array
     */
    function getSidebar($params = [], $fixedPage = 'dashboard')
    {
        // 边栏开始
        event_trigger("adminSidebarBegin", $params);
        $colorArr = ['red', 'green', 'yellow', 'blue', 'teal', 'orange', 'purple'];
        $colorNums = count($colorArr);
        $badgeList = [];
        $modulename = get_modulename(Config::get('app.app_map'));
        // 生成菜单的badge
        foreach ($params as $k => $v) {
            $url = $k;
            if (is_array($v)) {
                $nums = isset($v[0]) ? $v[0] : 0;
                $color = isset($v[1]) ? $v[1] : $colorArr[(is_numeric($nums) ? $nums : strlen($nums)) % $colorNums];
                $class = isset($v[2]) ? $v[2] : 'label';
            } else {
                $nums = $v;
                $color = $colorArr[(is_numeric($nums) ? $nums : strlen($nums)) % $colorNums];
                $class = 'label';
            }
            //必须nums大于0才显示
            if ($nums) {
                $badgeList[$url] = '<small class="' . $class . ' pull-right bg-' . $color . '">' . $nums . '</small>';
            }
        }

        // 读取管理员当前拥有的权限节点
        //$userRule = $this->getRuleList();
        $selected = $referer = [];
        $refererUrl = Session::get('referer');
        $pinyin = new Pinyin('Overtrue\Pinyin\MemoryFileDictLoader');
        //读取menu文件
        $menu=Config::get('menu');
        $ruleList=$indexRuleList=[];
        foreach ($menu as $v) {
            if ($v['ismenu']==0&&strpos($v['name'],'/index') !== false) {
                $indexRuleList[] = $v;
            }
            if ($v['ismenu']==1) {
                $ruleList[] = $v;
            }
        }
        // 必须将结果集转换为数组
        //$ruleList = Collection(\app\admin\model\AuthRule::where('status', 'normal')->where('ismenu', 1)->order('weigh', 'desc')->cache("__menu__")->select())->toArray();
//        $indexRuleList = \app\admin\model\AuthRule::where('status', 'normal')
//            ->where('ismenu', 0)
//            ->where('name', 'like', '%/index')
//            ->column('name,pid');
        $pidArr = array_filter(array_unique(array_map(function ($item) {
            return $item['pid'];
        }, $ruleList)));
        foreach ($ruleList as $k => &$v) {
//            if (!in_array($v['name'], $userRule)) {
//                unset($ruleList[$k]);
//                continue;
//            }
//            $indexRuleName = $v['name'] . '/index';
//            if (isset($indexRuleList[$indexRuleName]) && !in_array($indexRuleName, $userRule)) {
//                unset($ruleList[$k]);
//                continue;
//            }
            $v['icon'] = $v['icon'] . ' fa-fw';
            $v['url'] = '/' . $modulename . '/' . $v['name'];
            $v['badge'] = isset($badgeList[$v['name']]) ? $badgeList[$v['name']] : '';
            $v['py'] = $pinyin->abbr($v['title'], '');
            $v['pinyin'] = $pinyin->permalink($v['title'], '');
            $v['title'] = __($v['title']);
            $selected = $v['name'] == $fixedPage ? $v : $selected;
            $referer = url($v['url']) == $refererUrl ? $v : $referer;
        }
        $lastArr = array_diff($pidArr, array_filter(array_unique(array_map(function ($item) {
            return $item['pid'];
        }, $ruleList))));
        foreach ($ruleList as $index => $item) {
            if (in_array($item['id'], $lastArr)) {
                unset($ruleList[$index]);
            }
        }
        if ($selected == $referer) {
            $referer = [];
        }
        $selected && $selected['url'] = url($selected['url']);
        $referer && $referer['url'] = url($referer['url']);

        $select_id = $selected ? $selected['id'] : 0;
        $menu = $nav = '';
        if (Config::get('site.multiplenav')) {
            $topList = [];
            foreach ($ruleList as $index => $item) {
                if (!$item['pid']) {
                    $topList[] = $item;
                }
            }
            $selectParentIds = [];
            $tree = Tree::instance();
            $tree->init($ruleList);
            if ($select_id) {
                $selectParentIds = $tree->getParentsIds($select_id, true);
            }
            foreach ($topList as $index => $item) {
                $childList = Tree::instance()->getTreeMenu(
                    $item['id'],
                    '<li class="@class" pid="@pid"><a href="@url@addtabs" addtabs="@id" url="@url" py="@py" pinyin="@pinyin"><i class="@icon"></i> <span>@title</span> <span class="pull-right-container">@caret @badge</span></a> @childlist</li>',
                    $select_id,
                    '',
                    'ul',
                    'class="treeview-menu"'
                );
                $current = in_array($item['id'], $selectParentIds);
                $url = $childList ? 'javascript:;' : url($item['url']);
                $addtabs = $childList || !$url ? "" : (stripos($url, "?") !== false ? "&" : "?") . "ref=addtabs";
                $childList = str_replace(
                    '" pid="' . $item['id'] . '"',
                    ' treeview ' . ($current ? '' : 'hidden') . '" pid="' . $item['id'] . '"',
                    $childList
                );
                $nav .= '<li class="' . ($current ? 'active' : '') . '"><a href="' . $url . $addtabs . '" addtabs="' . $item['id'] . '" url="' . $url . '"><i class="' . $item['icon'] . '"></i> <span>' . $item['title'] . '</span> <span class="pull-right-container"> </span></a> </li>';
                $menu .= $childList;
            }
        } else {
            // 构造菜单数据
            Tree::instance()->init($ruleList);
            $menu = Tree::instance()->getTreeMenu(
                0,
                '<li class="@class"><a href="@url@addtabs" addtabs="@id" url="@url" py="@py" pinyin="@pinyin"><i class="@icon"></i> <span>@title</span> <span class="pull-right-container">@caret @badge</span></a> @childlist</li>',
                $select_id,
                '',
                'ul',
                'class="treeview-menu"'
            );
            if ($selected) {
                $nav .= '<li role="presentation" id="tab_' . $selected['id'] . '" class="' . ($referer ? '' : 'active') . '"><a href="#con_' . $selected['id'] . '" node-id="' . $selected['id'] . '" aria-controls="' . $selected['id'] . '" role="tab" data-toggle="tab"><i class="' . $selected['icon'] . ' fa-fw"></i> <span>' . $selected['title'] . '</span> </a></li>';
            }
            if ($referer) {
                $nav .= '<li role="presentation" id="tab_' . $referer['id'] . '" class="active"><a href="#con_' . $referer['id'] . '" node-id="' . $referer['id'] . '" aria-controls="' . $referer['id'] . '" role="tab" data-toggle="tab"><i class="' . $referer['icon'] . ' fa-fw"></i> <span>' . $referer['title'] . '</span> </a> <i class="close-tab fa fa-remove"></i></li>';
            }
        }

        return [$menu, $nav, $selected, $referer];
    }
}

if (!function_exists('getBreadCrumb')) {
    /**
     * 获得面包屑导航
     * @param string $path
     * @return array
     */
    function getBreadCrumb($path = '')
    {
//        if ($this->breadcrumb || !$path) {
//            return $this->breadcrumb;
//        }
        $breadcrumb='';
        $path_rule_id = 0;
        $rules=Config::get('menu');
        foreach ($rules as $rule) {
            $path_rule_id = $rule['name'] == $path ? $rule['id'] : $path_rule_id;
        }
        if ($path_rule_id) {
            $breadcrumb = Tree::instance()->init($rules)->getParents($path_rule_id, true);
            foreach ($breadcrumb as $k => &$v) {
                $v['url'] = url($v['name']);
                $v['title'] = __($v['title']);
            }
        }
        return $breadcrumb;
    }
}


if (!function_exists('moveFile')){

    /**
     * 上传文件
     * @access public
     * @param string      $path 文件路径
     * @param string      $directory 保存路径
     * @param string|null $name      保存的文件名
     * @return File
     */
    function moveFile(string $path, string $directory, string $name = null): File
    {
        $file=new File($path,true);
        if ($file) {

            $directory = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            $target = $directory. $name;

            set_error_handler(function ($type, $msg) use (&$error) {
                $error = $msg;
            });

            $moved = move_uploaded_file($file->getPathname(), $target);
            restore_error_handler();
            if (!$moved) {
                throw new FileException(sprintf('Could not move the file "%s" to "%s" (%s)', $file->getPathname(), $target, strip_tags($error)));
            }

            @chmod($target, 0666 & ~umask());

            return new File($target,true);;
        }
    }
}

if (!function_exists('getAllFiles')){

    /**
     * 获取单层文件夹下的指定后缀文件
     * @access public
     * @param string      $path 文件路径
     * @param string      $suffix 后缀
     * @return array
     */
    function getAllFiles(string $path , string $suffix = '*')
    {
        $files=[];
        $handler = opendir($path);
        while (($filename = readdir($handler)) !== false) {//务必使用!==，防止目录下出现类似文件名“0”等情况
            if ($filename != "." && $filename != "..") {
                if ($suffix == "*.*" || $suffix == "*") {
                    if(is_file($filename)){
                        $files[] = $filename ;
                    }
                }else if(substr($filename,0-strlen($suffix))==$suffix){
                    $files[] = $filename ;
                }
            }
        }
        closedir($handler);
        return $files;
    }
}

if (!function_exists('readAllDir')){

    /**
     * 获取文件夹下的所有文件,树状结构
     * @access public
     * @param string      $path 路径
     * @return array
     */
    function readAllDir(string $path )
    {
        $arr = array();
        $hander = scandir($path);
        foreach ($hander as $v) {
            if (is_dir($path . DIRECTORY_SEPARATOR . $v) && $v != "." && $v != "..") {
                $arr[$v] = readAllDir($path . DIRECTORY_SEPARATOR . $v);//递归调用
            }else{
                if($v != "." && $v != ".."){
                    $arr[]=$v;
                }
            }
        }
        return $arr;
    }
}

if (!function_exists('delDirAndFile')){

    /**
     * 删除指定文件夹以及文件夹下的目录文件
     * @access public
     * @param string      $dirName 文件夹路径
     * @return boolean
     */
    function delDirAndFile( $dirName ){
        if($handle=opendir($dirName)){
            while(false!==($item=readdir($handle))){
                if($item!="."&&$item!=".."){
                    if(is_dir("$dirName/$item")){
                        delDirAndFile("$dirName/$item");
                    }else{
                        unlink("$dirName/$item");
                    }
                }
            }
            closedir($handle);
            //rmdir($dirName);
        }
        return true;
    }
}