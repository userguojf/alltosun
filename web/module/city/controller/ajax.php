<?php
/**
 * alltosun.com  ajax.php
 * ============================================================================
 * 版权所有 (C) 2009-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: Shenxn 申小宁 (shenxn@alltosun.com) $
 * $Date: Jun 12, 2014 3:35:22 PM $
 * $Id: ajax.php 306682 2016-08-03 03:35:21Z shenxn $
 */


class Action
{
    /**根据省份id获取所有城市
     * @return array|bool|string
     * @throws AnException
     */
    public function get_city_list()
    {
        if (!tools_helper::is_safe(true)) {
            return '非法请求!';
        }

        $pid = Request::post('pid',0);

        if (!$pid) {
            return false;
        }

        $data = _model('city')->getList(array('province_id' => $pid));

        if (!$data) {
            return false;
        }

        return array('info' => 'ok','data' => $data);
    }

    public function get_district_list()
    {

        if (!tools_helper::is_safe(true)) {
            return '非法请求!';
        }

        //$pid = Request::post('pid',0);

        $city_id = Request::getParam('city_id');


        if (!$city_id ) {

            return false;

        }

        $filter=array(


            'city_id' => $city_id,
        );

        $order = ' ORDER BY `id` ASC ';

        $data = _model('area')->getList($filter,$order);

        if (!$data) {

            return false;
        }

        return array('info' => 'ok','data' => $data);
    }

    public function get_business_hall_list()
    {

        if (!tools_helper::is_safe(true)) {

            return '非法请求!';

        }

        //接受省市区

        $pid    = tools_helper::post('privince_id',0);

        $cid    = tools_helper::post('city_id',0);

        $aid    = tools_helper::post('area_id',0);

        $filter = array(

            'province_id'   => $pid,

            'city_id'       => $cid,

            'area_id'       => $aid,
        );


        $data=_model('business_hall')->getList($filter);

        if(!$data) {

            return '没有找到相关数据';

        }
        return array('info' => 'ok','data' => $data);

    }


}