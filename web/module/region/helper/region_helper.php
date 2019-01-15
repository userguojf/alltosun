<?php

class region_helper
{
   /**
    * 获取下级列表
    *
    * @param    String  资源名
    * @param    Int     资源ID
    * @return   Array
    *
    * @author   wangl
    */
    public static function get_subordinate_list($res_name, $res_id)
   {
        if ( $res_name == 'group' ) {
            return _model('province')->getList(array('id >' => 0));
        } else if ( $res_name == 'province' ) {
            return _model('city')->getList(array('province_id' => $res_id));
        } else if ( $res_name == 'city' ) {
            return _model('area')->getList(array('city_id' => $res_id));
        } else if ( $res_name == 'area' ) {
            return _model('business_hall')->getList(array('area_id' => $res_id));
        } else {
            return array();
        }
   }

   /**
    * 获取省列表
    *
    * @return   Array
    */
    public static function get_province_list()
    {
        return _model('province')->getList(array('id >' => 0));
    }

   /**
    * 获取市列表
    *
    *
    */
    public static function get_city_list($filter)
    {
        if ( !$filter ) {
            return array();
        }

        if ( is_numeric($filter) ) {
           // 省ID
            $p_id = $filter;

            $filter = array(
                'province_id'   =>  $p_id
            );
        }

        return _model('city')->getList($filter);
   }

   /**
    * 获取区列表
    *
    *
    */
    public static function get_area_list($filter)
    {
        if ( !$filter ) {
            return array();
        }

        if ( is_numeric($filter) ) {
            $c_id = $filter;

            $filter = array(
                'city_id'   =>  $c_id
            );
        }

        return _model('area')->getList($filter);
    }

    /**
     * 获取区列表
     *
     *
     */
    public static function get_business_hall_list($filter)
    {
        if ( !$filter ) {
            return array();
        }

        if ( is_numeric($filter) ) {
            $a_id = $filter;

            $filter = array(
                'area_id'   =>  $a_id
            );
        }

        return _model('business_hall')->getList($filter);
    }
}
