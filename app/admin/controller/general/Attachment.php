<?php

namespace app\admin\controller\general;

use app\common\controller\Backend;
use app\common\model\Attachment as AttachmentModel;
use think\facade\Event;
use think\facade\Request;
use think\facade\View;

/**
 * 附件管理
 *
 * @icon fa fa-circle-o
 * @remark 主要用于管理上传到又拍云的数据或上传至本服务的上传数据
 */
class Attachment extends Backend
{

    /**
     * @var \app\common\model\Attachment
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model =new AttachmentModel();
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
            $mimetypeQuery = [];
            $filter = request()->request('filter');
            $filterArr = (array)json_decode($filter, TRUE);
            if (isset($filterArr['mimetype']) && stripos($filterArr['mimetype'], ',') !== false) {
                request()->get(['filter' => json_encode(array_merge($filterArr, ['mimetype' => '']))]);
                $mimetypeQuery = function ($query) use ($filterArr) {
                    $mimetypeArr = explode(',', $filterArr['mimetype']);
                    foreach ($mimetypeArr as $index => $item) {
                        $query->whereOr('mimetype', 'like', '%' . $item . '%');
                    }
                };
            }

            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->where($mimetypeQuery)
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->where($mimetypeQuery)
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            $local=Config('upload.localurl');//本地路径
            $cdn=Config('upload.cdnurl');//远程路径
            foreach ($list as $k => &$v) {
                $v['fullurl'] = ($v['storage'] == 'local' ? $local : $cdn) . $v['url'];
            }
            unset($v);
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return View::fetch();
    }

    /**
     * 选择附件
     * @throws \Exception
     */
    public function select()
    {
        if (request()->isAjax()) {
            return $this->index();
        }
        return View::fetch();
    }

    /**
     * 添加
     * @throws \Exception
     */
    public function add()
    {
        if (request()->isAjax()) {
            $this->error();
        }
        return View::fetch();
    }

    /**
     * 删除附件
     * @param array $ids
     */
    public function del($ids = "")
    {
        if ($ids) {
            event_listen('upload_delete', function ($params) {
                $attachmentFile = root_path() . '/public' . $params['url'];
                if (is_file($attachmentFile)) {
                    @unlink($attachmentFile);
                }
            });
            $attachmentlist = $this->model->where('id', 'in', $ids)->select();
            foreach ($attachmentlist as $attachment) {
                event_trigger("upload_delete", $attachment);
                $attachment->delete();
            }
            $this->success();
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }

}
