<?php
/**
  * alltosun.com 专柜管理 index.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2017年9月13日 下午5:14:49 $
  * $Id$
  */
class Action
{
    private $per_page = 20;
    private $member_id  = 0;
    private $member_res_name = '';
    private $member_res_id   = 0;
    private $ranks           = 0;

    public function __construct()
    {
        $this->member_id   = member_helper::get_member_id();
        $this->time        = date('Y-m-d H:i:s');
        $member_info = member_helper::get_member_info($this->member_id);

        if ($member_info) {
            $this->member_res_name = $member_info['res_name'];
            $this->member_res_id   = $member_info['res_id'];
            $this->ranks           = $member_info['ranks'];
        }

        Response::assign('curr_member_ranks', $this->ranks);
    }

    public function __call($action='', $params=array()){

        if (!$this->member_id){
            return '请登录';
        }

        $search_filter = tools_helper::Get('search_filter', array());
        $page_no       = tools_helper::get('page_no', 1);

        $filter        = array(
                'status' => 1
        );

//        $filter =business_hall_helper::get_filter_by_member($this->member_res_name, $this->member_res_id);


//         if (isset($search_filter['title']) && $search_filter['title']) {

//         }

        if (!empty($search_filter['province'])) {
            $province = array('province_id' => $search_filter['province']);
            $filter['province_id'] = $search_filter['province'];
            Response::assign('where1' , $province);
        }
        if (!empty($search_filter['city'])) {
            $city = array('city_id' => $search_filter['city']);
            $filter['city_id']      = $search_filter['city'];
            Response::assign('where2' , $city);
        }

        $shoppe_list =get_data_list('rfid_shoppe', $filter, ' ORDER BY `id` DESC ', $page_no);

        Response::assign('search_filter', $search_filter);
        Response::assign('shoppe_list', $shoppe_list);
        Response::display('admin/shoppe_list.html');
    }

    /**
     * 添加专柜
     */
    public function add(){
        Response::display('admin/shoppe_add.html');
    }
}