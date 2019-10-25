<?php

namespace app\admin\controller\general;

use app\admin\model\Admin;
use app\admin\model\AdminLog;
use app\common\controller\Backend;
use app\common\model\Config as ConfigModel;
use fast\Random;
use think\facade\Request;
use think\facade\View;
use think\facade\Session;
use think\facade\Validate;

/**
 * 个人配置
 *
 * @icon fa fa-user
 */
class Profile extends Backend
{

    /**
     * @var \app\common\model\Config
     */
    protected $model = null;
    protected $noNeedRight = ['check'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model =new AdminLog();
    }

    /**
     * 查看
     * @throws \Exception
     */
    public function index()
    {
        //设置过滤方法
        request()->filter(['strip_tags']);
        if (Request::isAjax()) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $total = $this->model
                ->where('admin_id', $this->auth->id)
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->where('admin_id', $this->auth->id)
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return View::fetch();
    }

    /**
     * 更新个人信息
     */
    public function update()
    {
        if (request()->isPost()) {
            $this->token();
            $params = request()->post("row/a");
            $params = array_filter(array_intersect_key(
                $params,
                array_flip(array('email', 'nickname', 'password', 'avatar'))
            ));
            unset($v);
            if (!Validate::is($params['email'], "email")) {
                $this->error(__("Please input correct email"));
            }
            if (isset($params['password'])) {
                if (!Validate::is($params['password'], "/^[\S]{6,16}$/")) {
                    $this->error(__("Please input correct password"));
                }
                $params['salt'] = Random::alnum();
                $params['password'] = md5(md5($params['password']) . $params['salt']);
            }
            $exist = Admin::where('email', $params['email'])->where('id', '<>', $this->auth->id)->find();
            if ($exist) {
                $this->error(__("Email already exists"));
            }
            if ($params) {
                $admin = Admin::find($this->auth->id);
                $admin->save($params);
                //因为个人资料面板读取的Session显示，修改自己资料后同时更新Session
                Session::set("admin", $admin->toArray());
                $this->success();
            }
            $this->error();
        }
        return;
    }
}
