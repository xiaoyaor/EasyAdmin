<?php

declare(strict_types=1);

namespace app\listener;

use app\admin\model\AdminLog;

class admin_login_after
{
    /**
     * 事件监听处理
     *
     * @return mixed
     */
    public function handle($event)
    {
        AdminLog::setTitle(__('Login'));
        AdminLog::setContent(__('Login'));
        AdminLog::record();
    }
}
