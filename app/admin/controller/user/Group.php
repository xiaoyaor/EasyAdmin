<?php

namespace app\admin\controller\user;

use app\common\controller\Backend;
use \app\admin\model\UserGroup;
use think\facade\View;

/**
 * 会员组管理
 *
 * @icon fa fa-users
 */
class Group extends Backend
{

    /**
     * @var \app\admin\model\UserGroup
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new UserGroup();
        View::assign("statusList", $this->model->getStatusList());
    }

    public function add()
    {
        $nodeList = \app\admin\model\UserRule::getTreeList();
        View::assign("nodeList", $nodeList);
        return parent::add();
    }

    public function edit($ids = NULL)
    {
        $row = $this->model->find($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        $rules = explode(',', $row['rules']);
        $nodeList = \app\admin\model\UserRule::getTreeList($rules);
        View::assign("nodeList", $nodeList);
        return parent::edit($ids);
    }

}
