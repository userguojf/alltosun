<?php
/**
  * alltosun.com 坐标分析 analysis.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2018年5月7日 下午12:47:59 $
  * $Id$
  */

class Action
{
    private $per_page = 20;
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
        }
            Response::assign('member_info', $this->member_info);
    }

    public function __call($action = '', $params = array())
    {
        // 内容展示必须符合各省的条件
        $search_filter      = Request::Get('search_filter', array());
        $page               = tools_helper::get('page_no', 1);
        $is_export          = tools_helper::get('is_export', 0);

        $filter = _widget('screen')->init_filter($this->member_info, $search_filter);

        if (!empty($filter['business_hall_id']) && $filter['business_id'] = $filter['business_hall_id']) {
            unset($filter['business_hall_id']);
        }

        if (!$filter) {
            $filter = array(1=>1);
        }

        $coords = get_data_list('screen_business_hall_coord', $filter, ' ORDER BY `id` DESC ', $page, $this->per_page);

        Response::assign('coords', $coords);
        Response::assign('search_filter', $search_filter);
        Response::display("admin/analysis/index.html");
    }

    /**
     * 上报坐标详情
     * @param string $action
     * @param array $params
     */
    public function coord_detail()
    {
        // 内容展示必须符合各省的条件
        $business_id = tools_helper::get('business_id', 0);

        if (!$business_id) {
            return '营业厅信息不能为空';
        }

        $filter['business_id'] = $business_id;

        $coord_list = _model('gps_record')->getList($filter);
        $coord_info = _model('screen_business_hall_coord')->read($filter);

        Response::assign('coord_list', json_encode($coord_list));
        Response::assign('coord_info', json_encode($coord_info));
        Response::display("admin/analysis/detail.html");
    }

}
?>