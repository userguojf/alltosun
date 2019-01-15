<?php
/**
 * alltosun.com  ajax.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2016-8-1 下午6:30:26 $
 * $Id$
 */
class Action
{
    //四级联动
    //省
    public function get_province_name()
    {
        $province_info = _model('province')->getList(array('1'=>1));

        if (!$province_info) {
            return array('msg' => 'no');
        }

        return array('msg'=>'ok' , 'province_info' => $province_info);
    }
    
    //市
    public function get_city_name()
    {
        $province_id = Request::post('province_id');

        if (!$province_id) {
            return array('msg'=>'no');
        }

        $city_info = _model('city')->getList(array('province_id'=>$province_id));

        if (!$city_info) {
            return array('msg'=>'no');
        }

        return array('msg'=>'ok' , 'city_info'=> $city_info);
    }
    
    //地区
    public function get_area_name()
    {
        $city_id = Request::post('city_id');

        if (!$city_id) {
            return array('msg'=>'no');
        }

        $area_info = _model('area')->getList(array('city_id'=>$city_id));

        if (!$area_info) {
            return array('msg'=>'no');
        }

        return array('msg'=>'ok' , 'area_info'=> $area_info);
    }
    
    //营业厅
    public function get_business_title()
    {
        $province_id = Request::post('province_id');
        $city_id     = Request::post('city_id');
        $area_id     = Request::post('area_id');
        
        if (!$city_id || !$province_id || !$area_id) {
            return array('msg'=>'no');
        }

        $area_info = _model('business_hall')->getList(
                            array(
                                    'province_id' => $province_id,
                                    'city_id'  => $city_id,
                                    'area_id'  => $area_id,
                                    'activity' => 1
                            )
        );

        if (!$area_info) {
            return array('msg' => 'unactivity');
        }

        return array('msg'=>'ok' , 'area_info'=> $area_info);

    }
}