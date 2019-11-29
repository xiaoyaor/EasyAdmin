<?php

namespace app\admin\controller\user;

use app\admin\model\User as UserModel;
use app\common\controller\Backend;
use think\facade\Request;
use think\facade\View;

/**
 * 会员管理
 *
 * @icon fa fa-user
 */
class User extends Backend
{

    /**
     * 是否关联查询
     * @var bool
     */
    protected $relationSearch = false;


    /**
     * @var \app\admin\model\User
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new UserModel();
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
            //如果发送的来源是Selectpage，则转发到Selectpage
            if (request()->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->with('group')
                ->where($where)
                ->order($sort, $order)
                ->count();
            $list = $this->model
                //->alias('User')
                ->with('group')
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            foreach ($list as $k => $v) {
                $v->hidden(['password', 'salt']);
            }
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return View::fetch();
    }

    /**
     * 编辑
     */
    public function edit($ids = NULL)
    {
        $row = $this->model->find($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        View::assign('groupList', build_select('row[group_id]', \app\admin\model\UserGroup::column('id,name'), $row['group_id'], ['class' => 'form-control selectpicker']));
        return parent::edit($ids);
    }

}
