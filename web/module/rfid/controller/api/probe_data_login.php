<?php
/**
  * alltosun.com 开发机探针数据登录 probe_data_login.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2017年6月3日 上午8:56:04 $
  * $Id$
  */
class Action
{
    private $secret = '';
    public function __construct()
    {
        $this->secret = md5('ondev_by_alltusun');
    }
    private function set_login($account)
    {

        $member_info = _model('member')->read(array('member_user'=>$account));
        if (!$member_info) {
            Response::redirect(SITE_URL.'/admin');
            Response::flush();
            exit;
        }

        // 设置登录状态
        member_helper::remember_me_set($member_info);
    }

    public function mac_detail ()
    {
        $secret = tools_helper::get('secret', '');
        $account = tools_helper::get('account', '');
        $mac    = tools_helper::get('mac', '');
        $date   = tools_helper::get('date', '');
        $dev    = tools_helper::get('dev', '');
        $b_id   = tools_helper::get('b_id', 0);
        $start  = tools_helper::get('start', '');
        $end    = tools_helper::get('end', '');

        //清除上次登录时记录的member_admin_me
        member_helper::remember_me_expire();

        if (!$secret || $secret != $this->secret || !$account) {
            Response::redirect(SITE_URL.'/admin');
            Response::flush();
            exit;
        }

        $this->set_login($account);

        $url = "http://201512awifi.alltosun.net/probe/admin/stat/business/mac_detail?mac={$mac}&date={$date}&dev={$dev}&b_id={$b_id}&start={$start}&end={$end}";
        Response::redirect($url);
        Response::flush();
        exit;
    }


}

