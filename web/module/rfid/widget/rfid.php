<?php
/**
  * alltosun.com  rfid.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2017年6月2日 下午7:40:49 $
  * $Id$
  */
class rfid_widget
{
        /**
     * 根据营业厅member获取搜索数据
     */
    public function get_search_by_member($params = array())
    {

        if (isset($params['member_info']['res_name']) && $params['member_info']['res_name'] && isset($params['member_info']['res_id'])) {
            if ($params['member_info']['res_name'] != 'group' && !$params['member_info']['res_id']) {
                return false;
            }
            //查询省厅地区信息
            $region_info = business_hall_helper::get_region_by_member($params['member_info']['res_name'], $params['member_info']['res_id']);

            return $region_info;
        } else {
            return false;
        }

    }

    /**
     * 根据管理员生成权限内的条件
     * @param unknown $member_info
     */
    public function default_search_filter($member_info)
    {
        $filter = array();

        if ($member_info['res_name'] == 'business_hall') {
            $filter['business_id'] = $member_info['res_id'];
        } else if ($member_info['res_name'] == 'group') {
            return $filter;
        } else {
            $filter["{$member_info['res_name']}_id"] = $member_info['res_id'];
        }

        return $filter;

    }

    /**
     * 初始化搜索条件
     * @param unknown $member_info
     */
    public function init_filter($member_info, $search_filter)
    {
        $filter = $this->default_search_filter($member_info);

        //搜索判断
        if (!empty($search_filter['province_id'])) {
            $filter['province_id'] = $search_filter['province_id'];

            $province = array('province_id' => $search_filter['province_id']);
            Response::assign('where1' , $province);
        }
        if (!empty($search_filter['city_id'])) {
            $filter['city_id'] = $search_filter['city_id'];

            $city = array('city_id' => $search_filter['city_id']);
            Response::assign('where2' , $city);
        }

        if (!empty($search_filter['area_id'])) {
            $filter['area_id'] = $search_filter['area_id'];
        }

        if (!empty($search_filter['business_hall_title'])) {
            $search_filter['business_hall_title'] = trim($search_filter['business_hall_title']);
            $business_hall_id = _uri('business_hall', array('title' => $search_filter['business_hall_title']), 'id');
            if ($business_hall_id) {
                $filter['business_hall_id'] = $business_hall_id;
            }
        }

        return $filter;

    }

}