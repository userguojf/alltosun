<?php
/**
  * alltosun.com index管理 device.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2017年7月6日 下午3:55:34 $
  * $Id$
  */
class Action
{
    private $per_page = 10;
    private $member_id  = 0;
    private $member_res_name = '';
    private $member_res_id   = 0;
    private $member_info     = array();
    private $ranks           = 0;

    public function __construct()
    {

        $this->member_id   = member_helper::get_member_id();

        $this->member_info = member_helper::get_member_info($this->member_id);

        if ($this->member_info) {
            $this->member_res_name = $this->member_info['res_name'];
            $this->member_res_id   = $this->member_info['res_id'];
            $this->ranks           = $this->member_info['ranks'];
        }

        Response::assign('curr_member_ranks', $this->ranks);
    }

    public function __call($action = '', $params = array())
    {
        $search_filter  = Request::Get('search_filter', array());
        $status         = Request::Get('status', 1);
        $page           = tools_helper::get('page_no', 1);
        $hall_title          = tools_helper::get('hall_title', '');


        $default_filter = _widget('screen')->default_search_filter($this->member_info);

        $filter = $default_filter;


        //营业厅权限跳过标题搜索
        if ($this->member_res_name != 'business_hall' && $hall_title) {
            $business_hall_list = _model('business_hall')->getList(array('title' => $hall_title));
            $business_hall_ids = array();
            foreach ($business_hall_list as $k => $v) {
                //非集团管理员并且搜索的营业厅不在本身权限之内则跳过
                if ($this->member_res_name != 'group' && $v["{$this->member_res_name}_id"] != $this->member_res_id) {
                        continue;
                }
                $business_hall_ids[] = $v['id'];
            }

            if (!$business_hall_ids) {
                $business_hall_ids = 0;
            }
            $filter['business_id'] = $business_hall_ids;
        }

        //搜索判断
        if (isset($search_filter['province_id']) && !empty($search_filter['province_id'])) {
            $filter['province_id'] = $search_filter['province_id'];

            $province = array('province_id' => $search_filter['province_id']);
            Response::assign('where1' , $province);
        }

        if (isset($search_filter['city_id']) && !empty($search_filter['city_id'])) {
            $filter['city_id'] = $search_filter['city_id'];

            $city = array('city_id' => $search_filter['city_id']);
            Response::assign('where2' , $city);
        }

        if (isset($search_filter['area_id']) && !empty($search_filter['area_id'])) {
            $filter['area_id'] = $search_filter['area_id'];
        }

        $filter['status'] = $status;


        $device_list = get_data_list('screen_business_wifi_pwd', $filter, ' ORDER BY `id` DESC ', $page, $this->per_page);

        Response::assign('hall_title', $hall_title);
        Response::assign('status', $status);
        Response::assign('search_filter', $search_filter);
        Response::assign('wifi_list', $device_list);
        Response::display('admin/index.html');

    }

}