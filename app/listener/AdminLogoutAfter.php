<?php
declare (strict_types = 1);

namespace app\listener;

use app\admin\model\AdminLog;

class AdminLogoutAfter
{
    /**
     * 事件监听处理
     *
     * @return mixed
     */
    public function handle($event)
    {
        //
        AdminLog::setTitle(__('Loginout'));
        AdminLog::setContent(__('Logout successful'));
        AdminLog::record();
    }    
}
