<?php
/**
 * alltosun.com  index.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-1-8 上午10:10:47 $
 * $Id$
 */

class Action
{
    private $per_page    = 20;
    private $member_info = array();

    public function __call($action = '', $params = array())
    {
        $type          = Request::Get('type', 0);
        $search_filter = Request::Get('search_filter', array());
        $page          = Request::get('page_no' , 1) ;

        $filter = $list =array();

        if (isset($search_filter['date']) && $search_filter['date']) {
            $filter['date'] = date('Ymd', strtotime($search_filter['date']));
        }

        if (isset($search_filter['res_name']) && $search_filter['res_name']) {
            $filter['res_name'] = $search_filter['res_name'];
        }
// p($filter);exit();
        if (isset($search_filter['business_hall_title']) && $search_filter['business_hall_title']) {
            $business_hall_info = _model('business_hall')->read(array('title' => $search_filter['business_hall_title']));
            // 
            if (!$business_hall_info) return '请输入正确的营业厅名称';
            
            $filter['business_hall_id'] = $business_hall_info['id'];
        } 

        // 点击途径
        $filter['type'] = $type;
        // 过滤掉我谷歌测试的
        $filter['business_hall_id >'] = 0;

        $order          = " ORDER BY `id` DESC ";

        $count = _model('screen_qydev_msg_record')->getTotal($filter);

        if ($count) {
            $pager = new Pager($this->per_page);
            $list = _model('screen_qydev_msg_record')->getList($filter, $order.$pager->getLimit($page));

            if ($pager->generate($count,$page)) {
                Response::assign('pager', $pager);
            }
        }

        Response::assign('count' , $count);
        Response::assign('page' , $page);
        Response::assign('list', $list);
        Response::assign('type', $type);

        Response::assign('search_filter' , $search_filter);
        Response::display("admin/msg_record_list.html");
    }
    
}