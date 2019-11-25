<?php
declare (strict_types = 1);

namespace app\listener;

use app\admin\model\AdminLog;

class AdminLoginErrorAfter
{
    /**
     * 事件监听处理
     *
     * @return mixed
     */
    public function handle($event)
    {
        AdminLog::setTitle(__('Login'));
        AdminLog::setContent(__('Login failure').'['.$event->param('username').']');
        AdminLog::record();
    }    
}
