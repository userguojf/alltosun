<?php
/**
 * alltosun.com 主页面 index.php
 * ============================================================================
 * 版权所有 (C) 2009-2018 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 赵高举 (zhaogj@alltosun.com) $
 * $Date: 2018/6/19 10:20 $
 * $Id$
 */

class Action
{
    private $per_page = 20;
    private $member_id = 0;
    private $member_res_name = '';
    private $member_res_id = 0;
    private $ranks = 0;
    private $time;

    public function __construct()
    {
        $this->member_id = member_helper::get_member_id();
        $this->time = date('Y-m-d H:i:s');
        $member_info = member_helper::get_member_info($this->member_id);

        if ($member_info) {
            $this->member_res_name = $member_info['res_name'];
            $this->member_res_id = $member_info['res_id'];
            $this->ranks = $member_info['ranks'];
        }

        Response::assign('curr_member_ranks', $this->ranks);
    }

    public function __call($action = '', $params = array())
    {
        $page        = Request::Get('page_no',1);

        $source_list = [];
        $source_count = _model('screen_version_record')->getCol('select count(distinct source) as source_count from screen_version_record');
        $count = $source_count[0];
        if ($count) {
            $pager = new Pager($this->per_page);
            if ($pager->generate($count)) {
                Response::assign('pager', $pager);
            }
            Response::assign('count', $count);

            $limit_start = ($page - 1) * $this->per_page;
            $source_list = _model('screen_version_record')->getCol("select distinct source from screen_version_record LIMIT {$limit_start}, {$this->per_page}");

        }
        // 获取所有的渠道
        Response::assign('source_list', $source_list);
        Response::display('m/index.html');
    }

    public function record_list()
    {
        $search_filter    = Request::Get('search_filter', array());

        $filter = [];
        if (!empty($search_filter['start_date'])) {
            $filter['add_time >='] =  $search_filter['start_date'];
        }

        if (!empty($search_filter['end_date'])) {
            $filter['add_time <='] =  $search_filter['end_date'];
        }
        $filter['type'] = $search_filter['type'];
        $filter['source'] = $search_filter['source'];
        $record_list = [];
        $count = _model('screen_version_record')->getTotal($filter);
        if ($count) {
            $pager = new Pager($this->per_page);

            if ($pager->generate($count)) {
                Response::assign('pager', $pager);
            }
            Response::assign('count', $count);
            $record_list = _model('screen_version_record')->getList($filter, ' ORDER BY `id` DESC '.$pager->getLimit());

        }
        Response::assign('record_list', $record_list);
        Response::assign('search_filter', $search_filter);
        Response::display('m/record_list.html');
    }
}