<?php

namespace app\admin\controller\auth;

use app\admin\model\AuthGroup;
use app\common\controller\Backend;
use \app\admin\model\AdminLog as AdminLogModel;
use think\facade\View;

/**
 * 管理员日志
 *
 * @icon fa fa-users
 * @remark 管理员可以查看自己所拥有的权限的管理员日志
 */
class Adminlog extends Backend
{

    /**
     * @var \app\admin\model\AdminLog
     */
    protected $model = null;
    protected $childrenGroupIds = [];
    protected $childrenAdminIds = [];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new AdminLogModel();

        $this->childrenAdminIds = $this->auth->getChildrenAdminIds(true);
        $this->childrenGroupIds = $this->auth->getChildrenGroupIds($this->auth->isSuperAdmin() ? true : false);

        $groupName = AuthGroup::where('id', 'in', $this->childrenGroupIds)
                ->column('id,name');

        View::assign('groupdata', $groupName);
    }

    /**
     * 查看
     * @throws \Exception
     */
    public function index()
    {
        if (request()->isAjax())
        {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->whereIn('admin_id', $this->childrenAdminIds)
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->whereIn('admin_id', $this->childrenAdminIds)
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
     * 详情
     * @throws \Exception
     */
    public function detail($ids)
    {
        $row = $this->model->find(['id' => $ids]);
        if (!$row)
            $this->error(__('No Results were found'));
        View::assign("row", $row->toArray());
        return View::fetch();
    }

    /**
     * 添加
     * @internal
     */
    public function add()
    {
        $this->error();
    }

    /**
     * 编辑
     * @internal
     */
    public function edit($ids = NULL)
    {
        $this->error();
    }

    /**
     * 删除
     */
    public function del($ids = "")
    {
        if ($ids)
        {
            $childrenGroupIds = $this->childrenGroupIds;
            $adminList = $this->model->where('id', 'in', $ids)->where('admin_id', 'in', function($query) use($childrenGroupIds) {
                        $query->name('auth_group_access')->field('uid');
                    })->select();
            if ($adminList)
            {
                $deleteIds = [];
                foreach ($adminList as $k => $v)
                {
                    $deleteIds[] = $v->id;
                }
                if ($deleteIds)
                {
                    $this->model->destroy($deleteIds);
                    $this->success();
                }
            }
        }
        $this->error();
    }

    /**
     * 批量更新
     * @internal
     */
    public function multi($ids = "")
    {
        // 管理员禁止批量操作
        $this->error();
    }
    
    public function selectpage()
    {
        return parent::selectpage();
    }

}
