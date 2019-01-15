<?php

/**
 * alltosun.com 商家模块帮助类 seller_helper.php
 * ============================================================================
 * 版权所有 (C) 2009-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: zhulk (zhulk@alltosun.com) $
 * $$Id$$
 */
class business_hall_helper
{
    private static $table = 'business_hall';

    /**
     * 获取所有的省份
     */
    public static function get_province_list()
    {
        $list = array();
        $province_list = _model('province')->getList(array('1'=>1));

        if ($province_list) {
            foreach ($province_list as $k => $v) {
                $list[$v['id']] = $v['name'];
            }
        }

        return $list;
    }

    /**
     * 获取所有的城市
     * @param int $pid 对应的省表的ID
     */
    public static function get_city_list($pid)
    {
        $result = array();

        $list = _model('city')->getList(array('province_id'=>$pid));

        if ($list) {
            foreach ($list as $v) {
                $result[$v['id']] = $v['name'];
            }
        }

        return $result;
    }

    /**
     * 取某市下所有区
     *
     * @param   Int 市ID
     * @return  Array
     */
    public static function get_area_list($city_id)
    {
        if ( !$city_id ) {
            return array();
        }

        $data = array();

        $list = _model('area')->getList(array('city_id' => $city_id));

        foreach ($list as $v) {
            $data[$v['id']] = $v['name'];
        }

        return $data;
    }

    /**
     * 得到所有有格式的营业厅列表
     * @return multitype:unknown
     */
    public static function get_area_no_list()
    {
        $list = _model('business_hall')->getList(array(1=>1));

        $area_no_list = array();
        foreach ($list as $v) {
            if ($v['area_no']) {
                $area_no_list[$v['ditch_num']] = $v['area_no'];
            }
        }
        return $area_no_list;
    }

    /**
     * 获取营业厅信息
     * @param string $where
     * @param string $field
     * @return boolean|Ambigous <multitype:, string, unknown, Obj, mixed>
     */
    public static function get_business_hall_info($where = '', $field = '')
    {
        if(!$where) {
            return false;
        }

        if (empty($field)) {
            $business_hall_info = _uri('business_hall', $where);
        } else {
            $business_hall_info = _uri('business_hall', $where, $field);
        }

        return  $business_hall_info;
    }

    public static function  get_business_hall_id()
    {
        if (isset($_SESSION['business_hall_id']) && $_SESSION['business_hall_id']) {
            return $_SESSION['business_hall_id'];
        }

        $user_id = user_helper::get_user_id();

        if ($user_id) {
            $business_hall_id = user_helper::get_user_info($user_id,'business_hall_id');
            return $business_hall_id;
        }

        return false;
    }

    /**根据营业厅的渠道号id获取标题
     * @param $union_id
     * @return array|bool
     */
    public static function get_business_title($union_id)
    {

        if(!$union_id) {
            return false;
        }

        return _uri('business_hall',array('union_id'=>$union_id),'title');

    }

    /**
     * 根据渠道号id获取省份id
     * @param unknown $union_id
     */
    public static function get_promary_id_by_union_id($union_id)
    {
        $promary_id = _uri('business_hall',$union_id,'province_id');

        if ($promary_id) {

            $info = _uri('province', $promary_id);

            if ($info) return $promary_id;
        }

        return false;
    }

    /**根据渠道号查城市
     * @param $union_id
     * @return array|bool
     */
    public static  function get_city_id_by_union_id($union_id)
    {
        $city_id = _uri('business_hall',$union_id,'city_id');

        if ($city_id) {

            $info = _uri('city', $city_id);

            if ($info) return $city_id;
        }

        return false;
    }
    /**
     * @param table_name
     * @param array  $where
     * @param string $field
     * +
     * */
    public static function get_info_name( $table='' ,$where ,$field='' )
    {
        if (!$table || !$where) {
            return false;
        }

        if ($field) {
            $name = _uri($table , $where ,$field);
            if (!$name) {
                return false;
            }
            return $name;
        } else {
            $name = _uri($table , $where);
            if (!$name) {
                return false;
            }
            return $name;
        }

        return false;
    }
    /**
     * @param string table_name
     * @param array $where
     * +
     * */
    public static function get_info($table ,$where )
    {
        if (!$table) {
            return false;
        }
        if (!$where){
            $info = _model($table)->getList(array(1=>1));
        } else {
            $info = _model($table)->getList($where);
        }

        if(!$info) {
            return false;
        }
        return $info;
    }

    /**
     * 获取更新时间
     * @param string $field
     */
    public static function get_last_update_time($field = 'member_pass_page')
    {
        return _uri('setting', array('field' => $field), 'update_time');
    }

    /**
     * 取缓存数据 - wangjf
     * @param string $table 表名
     * @param string $action _model()的动作
     * @param array() $filter 条件
     * @param string $half_sql 附加条件
     * @return boolean|boolean|string[]|array[]
     */
    public static function get_cache_data($table,$action, $filter, $half_sql='')
    {

        if (!$table || !$filter || !$action) {
            return false;
        }

        //生成下标key
        $key = $table.json_encode($filter);
        if ($half_sql) {
            $key.=$half_sql;
        }

        if ($key) {
            $key = md5($key);
        }

        //全局化缓存
        global $mc_wr;

        //获取数据
        $result = $mc_wr->get($key);

        if (!$result) {
            //如果无数据则从数据库取
            $result = _model($table)->$action($filter, $half_sql);
            //写入缓存
            $mc_wr->set($key, $result);
        }

        return $result;
    }

    /**
     * 根据member信息获取初始搜索条件
     * @param string $member_res_name
     * @param number $member_res_id
     * @return number[]
     */
    public static function get_filter_by_member($member_res_name='', $member_res_id=0)
    {
        //p($member_res_name);exit;
        $filter = array();
        if ($member_res_name == 'group') {
            return $filter;
        } else if ($member_res_name == 'business_hall') {
            $filter = array('id' => $member_res_id);
        } else {
            $filter = array($member_res_name.'_id' => $member_res_id);
        }

        return $filter;

    }

    public static function get_region_by_member($member_res_name='', $member_res_id=0)
    {
        $result = array();
        if ($member_res_name == 'group'){
            $result['province_list'] = _model('province')->getList(array(1=>1));
        } else if ($member_res_name == 'province') {
            $result['province_info'] = _uri($member_res_name, $member_res_id);
            $result['city_list'] = _model('city')->getList(array('province_id'=>$member_res_id));
        } else if ($member_res_name == 'city') {
            $result['city_info'] = _uri($member_res_name, $member_res_id);
            $result['province_info'] = _uri('province', $result['city_info']['province_id']);
            $result['area_list'] = _model('area')->getList(array('city_id'=>$member_res_id));
        } else if ($member_res_name == 'area') {
            $result['area_info'] = _uri($member_res_name, $member_res_id);
            $result['city_info'] = _uri('city', $result['area_info']['city_id']);
            $result['province_info'] = _uri('province', $result['area_info']['province_id']);
            //$result['business_hall_list'] = _model('business_hall')->getList(array('province_id'=>$result['area_info']['province_id'], 'city_id'=>$result['area_info']['city_id'], 'area_id'=>$member_res_id));
        } else if ($member_res_name == 'business_hall') {
            $result['business_hall_info'] = _uri($member_res_name, $member_res_id);
            $result['province_info'] = _uri('province', $result['business_hall_info']['province_id']);
            $result['city_info'] = _uri('city', $result['business_hall_info']['city_id']);
            $result['area_info'] = _uri('area', $result['business_hall_info']['area_id']);
        }

        return $result;

    }

    /**
     * 获取营业厅列表
     * @param   $filter
     * @param   string $fields
     * @param   string $order
     * @return  multitype:
     */
    public static function get_list($filter, $fields = '', $order = '')
    {
        if ( !$filter ) {
            return array();
        }
    
        if ( $fields ) {
            return _model(self::$table)->getFields($fields, $filter, $order);
        }
        return _model(self::$table)->getList($filter, $order);
    }
}
?>