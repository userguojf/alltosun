<?php

/**
* alltosun.com  city_helper.php
* ============================================================================
* 版权所有 (C) 2009-2014 北京互动阳光科技有限公司，并保留所有权利。
* 网站地址: http://www.alltosun.com
* ----------------------------------------------------------------------------
* 许可声明: 这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
* ============================================================================
* $Author: 孙先水 (renf@alltosun.com) $
* $date:(2014-6-11 下午07:47:21) $
* $Id: city_helper.php 127592 2014-08-05 03:55:12Z shenxn $
*/

class city_helper {

    /**
     * 取信息
     * @param   array   $filter 查询条件
     * @return  array
     */
    public static function get_info($filter)
    {
        if (!$filter) {
            return array();
        }

        return _model('city')->read($filter);
    }

    /**
     * 根据res_name和res_id获取地区列表
     * add:sunxs
     * Time:2015-12-16
     */
    public static function get_area_list_by_id($res_name,$res_id)
    {

        $info = _uri($res_name,$res_id);
        if($res_name=='province') {
            $list = self::get_province_list();
            if($list) {
                $options = array_to_option($list,'name');
                Response::assign('options', $options);
            }
            Response::assign('pro_selectid',$res_id);
        }
        if($res_name == 'city') {
            $city_list = array_to_option(self::get_city_list_by_province_id($info['province_id']),'name');

            Response::assign('city_list',$city_list);
            Response::assign('pro_selectid',$info['province_id']);
            Response::assign('city_selectid',$res_id);
        }
        if($res_name == 'area') {
            $province_id    = $info['province_id'];
            $city_id        = $info['city_id'];
            $city_list      = _model('city')->getList(array('province_id'=>$province_id));
            $area_list      = _model('area')->getList(array('city_id'=>$city_id));
            $city_list      = array_to_option($city_list,'name');
            $area_list      = array_to_option($area_list,'name');

            Response::assign('city_list',$city_list);
            Response::assign('area_list',$area_list);
            Response::assign('pro_selectid',$province_id);
            Response::assign('city_selectid',$city_id);
            Response::assign('area_selectid',$res_id);
        }

        if($res_name=='business_hall') {
            $city_list      = _model('city')->getList(array('province_id' =>$info['province_id']));
            $area_list      = _model('area')->getList(array('city_id' => $info['city_id']));
            $business_list  = _model('business_hall')->getList(array('province_id' => $info['province_id'],'city_id' => $info['city_id'],'area_id' => $info['area_id']));
            $city_list      = array_to_option($city_list,'name');
            $area_list      = array_to_option($area_list,'name');
            $business_list  = array_to_option($business_list,'title');

            Response::assign('city_list',$city_list);
            Response::assign('area_list',$area_list);
            Response::assign('business_hall_list',$business_list);
            Response::assign('pro_selectid',$info['province_id']);
            Response::assign('city_selectid',$info['city_id']);
            Response::assign('area_selectid',$info['area_id']);
            Response::assign('business_selectid',$res_id);
        }
    }

    /**根据区域资源名和资源id获取路径
     * @param $res_name
     * @param $res_id
     * @return array|string
     */
    public static function get_area_path($res_name,$res_id)
    {
        if (!$res_name) {
            return '未知地区';
        }

        if($res_name == 'group') {
            $name = '全国';
        }

        if($res_name == 'province') { //省

            //图文推送表中res_id有可能为空

            if(!$res_id) {
                return '全省->全市->全厅';
            }

            $name = _uri('province',$res_id,'name');

        }

        if($res_name == 'city') { //市
            $city_info = _uri('city',$res_id);
            if(empty($city_info)) {
                return '城市';
            }

            $province_name = _uri('province',$city_info['province_id'],'name');
            $name = $province_name.'->'.$city_info['name'];
        }

        if($res_name == 'area') { //区

            $area_info          = _uri('area',$res_id);
            if(empty($area_info)) {
                return '地区';
            }

            $province_name      = _uri('province',$area_info['province_id'],'name');
            $city_name          = _uri('city',$area_info['city_id'],'name');
            $name               = $province_name.'->'.$city_name.'->'.$area_info['name'];
        }

        if($res_name == 'business_hall') { //厅

            if($res_id) {

                $business_hall_info = _uri('business_hall', $res_id);

                if(empty($business_hall_info)) {

                    return '全厅';

                }

                $province_name      = _uri('province',$business_hall_info['province_id'],'name');

                $city_name          = _uri('city',$business_hall_info['city_id'],'name');

                $area_name          = _uri('area',$business_hall_info['area_id'],'name');

                $name               = $province_name.'->'.$city_name.'->'.$area_name.'->'.$business_hall_info['title'];
            }



        }

        return $name;
    }

    /**获取到所有的省份
     * @param array $filter
     * @return bool
     * @throws AnException
     */
    public static function get_province_list($filter = array(1 => 1))
    {
        if (!$filter) {
            return false;
        }

        $province_list = _model('province')->getList($filter);

        return $province_list;
    }

    /**根据省份获取所有的城市
     * @param $province_id
     * @return bool
     * @throws AnException
     */
    public static function get_city_list_by_province_id($province_id)
    {
        if (!$province_id) {
            return false;
        }

        $city_list = _model('city')->getList(array('province_id' => $province_id));

        return $city_list;
    }

    /**根据城市获取所有的地区
     * @param $city_id
     * @return bool
     * @throws AnException
     */
    public static function get_area_list_by_city_id($city_id)
    {
        if (!$city_id) {
            return false;
        }

        $area_list = _model('area')->getList(array('city_id' => $city_id));

        return $area_list;
    }

    /**根据地区获取所有的区域
     * @param $area_id
     * @return bool
     * @throws AnException
     */
    public static function get_business_hall_list_by_area_id($area_id)
    {
        if (!$area_id) {
            return false;
        }

        $business_hall_list = _model('business_hall')->getList(array('area_id' => $area_id));

        return $business_hall_list;
    }

    /**
     *
     * @param str $res_name
     * @param int $res_id
     * @return number
     */
    public static function get_province_id($res_name , $res_id)
    {
        if ($res_name == 'group') {
            return 0;
        } elseif ($res_name == 'province') {
            return $res_id;
        } else{
            return _uri($res_name, $res_id ,'province_id');
        }
    }

    public static function get_list($filter, $order = '')
    {
        if (!$filter) {
            return array();
        }

        return _model('city')->getList($filter, $order);
    }

    /**
     * 获取区域列表
     *
     * @param   String  资源名
     * @param   String  资源ID
     */
    public static function get_region_list( $res_name, $res_id )
    {
        if ( !$res_name ) {
            return array();
        }

        if ( $res_name == 'group' ) {
            return self::get_province_list();
        } else if ( $res_name == 'province' ) {
            return self::get_city_list_by_province_id($res_id);
        } else if ( $res_name == 'city' ) {
            return self::get_area_list_by_city_id($res_id);
        } else if ( $res_name == 'area' ) {
            return self::get_business_hall_list_by_area_id($res_id);
        } else {
            return array();
        }
    }
}
?>