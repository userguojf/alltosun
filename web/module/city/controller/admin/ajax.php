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
 * $Id$
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

        $province_id = Request::post('province_id',0);

        if (!$province_id) {
            return false;
        }

        $data = _model('city')->getList(array('province_id' => $province_id));

        if (!$data) {
            return '城市不存在';
        }

        return array('info' => 'ok','data' => $data);
    }

    /**根据省份id获取所有城市
     * @return array|bool|string
    * @throws AnException
    */
    public function get_area_list()
    {

        if (!tools_helper::is_safe(true)) {
            return '非法请求!';
        }

        $city_id = Request::post('city_id',0);

        if (!$city_id) {
            return false;
        }

        $data = _model('area')->getList(array('city_id' => $city_id),'LIMIT 45');

        if (!$data) {
            return '区域不存在';
        }

        return array('info' => 'ok','data' => $data);
    }

    public function get_business_hall_list()
    {
        if (!tools_helper::is_safe(true)) {
            return '非法请求!';
        }

        $area_id = Request::post('area_id',0);

        if (!$area_id) {
            return false;
        }

        $data = _model('business_hall')->getList(array('area_id' => $area_id));

        if (!$data) {
            return '区域不存在';
        }

        return array('info' => 'ok','data' => $data);
    }
}