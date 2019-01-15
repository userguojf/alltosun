<?php

/**
 * alltosun.com 内容管理 index.php
 * ============================================================================
 * 版权所有 (C) 2009-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: Wangjf (wangjf@alltosun.com) $
 * $Date: Jun 13, 2014 6:02:25 PM $
 * $Id$
 */
require_once ROOT_PATH."/helper/PHPExcel.php";
require_once ROOT_PATH."/helper/PHPExcel/Writer/Excel2007.php";
class Action
{
    private $per_page = 10;
    private $member_id  = 0;
    private $member_res_name = '';
    private $member_res_id   = 0;
    private $member_info;
    private $ranks           = 0;
    private $time;

    public function __construct()
    {
        $this->member_id   = member_helper::get_member_id();
        $this->time        = date('Y-m-d H:i:s');
        $this->member_info = member_helper::get_member_info($this->member_id);

        if ($this->member_info) {
            $this->member_res_name = $this->member_info['res_name'];
            $this->member_res_id   = $this->member_info['res_id'];
            $this->ranks           = $this->member_info['ranks'];

            Response::assign('member_info', $this->member_info);
        }

        Response::assign('curr_member_ranks', $this->ranks);
    }

    public function __call($action = '', $params = array())
    {
        Response::display("admin/admin-add.html");
    }

}
?>