<?php

/**
 * 地区控制器
 *
 * @author  wangl
 */

class Action
{
    /**
     * 获取地区列表
     *
     * @author  wangl
     */
    public function get_list()
    {
        $res_name = Request::Post('res_name', '');

        if ( $res_name == 'province' ) {
            $filter = array();
            $table  = 'province';
        } else if ( $res_name == 'city' ) {
            $p_id  = Request::Post('p_id', 0);
            $table = 'city';

            if ( $p_id ) {
                $filter = array('province_id' => $p_id);
            } else {
                $filter = array();
            }
        } else if ( $res_name == 'area' ) {
            $c_id  = Request::Post('c_id', 0);
            $table = 'area';

            if ( $c_id ) {
                $filter = array('city_id' => $c_id);
            } else {
                $filter = array();
            }
        } else if ( $res_name == 'business' ) {
            $a_id  = Request::Post('a_id', 0);
            $table = 'business_hall';
            if ( $a_id ) {
                $filter = array('area_id' => $a_id);
            } else {
                $filter = array();
            }
        } else {
            return 'res_name不正确';
        }

        if ( !$filter ) {
            $filter = array('id >' => 0);
        }

        $list   = _model($table)->getList($filter);
        $return = array();

        foreach ( $list as $k => $v ) {
            $return[] = array(
                'id'    =>  $v['id'],
                'name'  =>  empty($v['title']) ? $v['name'] : $v['title']
            );
        }

        return array('info' => 'ok', 'list' => $return);
    }
}
