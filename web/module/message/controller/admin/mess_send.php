<?php
/**
 * alltosun.com  mess_send.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 申小宁 (shenxn@alltosun.com) $
 * $Date: 2015-10-15 下午2:53:46 $
 * $Id$
 */

class Action
{
    private $pre_page = 30;

    public function __call($action='',$params=array())
    {

        $page   = Request::Get('page_no',1);
        $phone  = Request::getParam('phone',0);
        $filter = array();

        if ($phone) {
            $filter['phone'] = $phone;
        }

        if (empty($filter)) {
            $filter = array(1 => 1);
        }

        $order = ' ORDER BY `add_time` DESC ';

        $message_log_list = get_data_list('message_log',$filter,$order,$page,$this->pre_page);

        Response::assign('phone',$phone);
        Response::assign('message_log_list',$message_log_list);
        Response::display('admin/mess_send_list.html');
    }
}
?>