<?php/** * alltosun.com 消息模块 Controller.php * ============================================================================ * 版权所有 (C) 2009-2011 北京互动阳光科技有限公司，并保留所有权利。 * 网站地址: http://www.alltosun.com * ---------------------------------------------------------------------------- * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。 * ============================================================================ * Author: 孙先水(sunxs@alltosun.com)  * Date: 上午11:10:47  *  $Id$*/class Action{    private $pre_page = 30;        public function __call($action='',$params=array())    {                $page             = Request::Get('page_no',1);        $search_filter    = Request::getParam('search_filter',array());        $user_id = user_helper::get_user_id();                $filter = array();                if (isset($search_filter['type']) && $search_filter['type']) {            $filter['`type`'] = $search_filter['type'];        }                if (isset($search_filter['start_time']) && $search_filter['start_time']) {            $filter['`add_time` >='] = $search_filter['start_time'].' 00:00:00';        }                if (isset($search_filter['end_time']) && $search_filter['end_time']) {            $filter['`add_time` <='] = $search_filter['end_time'].' 23:59:59';                }               if (user_helper::is_admin()) {                       if(!$filter){                $filter=array(1=>1);            }                   } else {           $filter =array('user_id'=>$user_id);       }        $order = ' ORDER BY `add_time` DESC ';                $message_list = get_data_list('message',$filter,$order,$page,$this->pre_page);        Response::assign('search_filter',$search_filter);        Response::assign('message_list',$message_list);        Response::display('admin/message_list.html');    }}