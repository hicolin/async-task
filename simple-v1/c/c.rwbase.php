<?php
//多线程任务处理,定时任务只需要调取huo
class c_rwbase
{
    //发送邮件
    public function huo()
    {
        $re = D('m_rw_base')->huo(re('fs'));
        ajaxReturn($re);
    }

    //此方法是在huo调起的，无需单独配置定时任务
    public function fa()
    {
        set_time_limit(0);
        ignore_user_abort();
        $re = D('m_rw_base')->fa(re('id'),re('fs'));
        ajaxReturn($re);
    }
}