<?php
/**
 * alltosun.com  wework_department_helper.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-3-13 上午10:35:34 $
 * $Id$
 */

class wework_department_helper
{
    public static function create($agent_id, $filter)
    {
        if ( !$agent_id ) return false;
        if ( !isset($filter['name']) || !$filter['name'] ) return false;
        if ( !isset($filter['work_pid']) || !$filter['work_pid'] ) return false;

        $access_token = wework_user_helper::get_token($agent_id);

        $url = wework_config::$create_department_url.'access_token='.$access_token;

        $data = '{"name" : "'.$filter['name'].'","parentid" : '.$filter['work_pid'].'}';

        $res_json = curl_post($url, $data);

        // 记录
        $param = [];

        $param['opreate_type'] = 'department';
        $param['api_type']     = 'create';
        $param['param']        = $data;
        $param['result']       = $res_json;
        $param['api_url']      = $url;

        self::all_api_record($param);

        $result   = json_decode($res_json, true);

        if ( isset($result['errcode']) && $result['errcode'] ) {
            return array('errcode' => 1, 'errmsg' => $result);
        }

        return $result;
    }

    public static function update($agent_id, $filter)
    {
        if ( !$agent_id ) return false;
        if ( !isset($filter['name']) || !$filter['name'] ) return false;
        if ( !isset($filter['depart_id']) || !$filter['depart_id'] ) return false;

        $access_token = wework_user_helper::get_token($agent_id);

        $url = wework_config::$update_department_url.'access_token='.$access_token;

        $data = '{"id" : ' .$filter['depart_id']. ', "name" : "' .$filter['name']. '"}';

        $res_json = curl_post($url, $data);
        // 记录
        $param = [];

        $param['opreate_type'] = 'department';
        $param['api_type']     = 'update';
        $param['param']        = $data;
        $param['result']       = $res_json;
        $param['api_url']      = $url;

//         self::all_api_record($param);

        $result   = json_decode($res_json, true);

        if ( isset($result['errcode']) && $result['errcode'] ) {
            return array('errcode' => 1, 'errmsg' => $result);
        }

        return $result;
    }

    public static function delete($agent_id, $work_depart_id)
    {
        if ( !$agent_id ) return false;
        if ( !$work_depart_id ) return false;

        $access_token = wework_user_helper::get_token($agent_id);

        $url = wework_config::$delete_department_url.'access_token='.$access_token.'&id='.$work_depart_id;

        $res_json = curl_get($url);

        // 记录
        $param = [];

        $param['opreate_type'] = 'department';
        $param['api_type']     = 'delete';
        $param['param']        = '';
        $param['result']       = $res_json;
        $param['api_url']      = $url;

        self::all_api_record($param);

        $result   = json_decode($res_json, true);

        if ( isset($result['errcode']) && $result['errcode'] ) {
            return array('errcode' => 1, 'errmsg' => $result);
        }

        return $result;
    }

    public static  function create_api($name, $work_pid)
    {
        if ( !$name || !$work_pid ) return false;
        // 调用接口
        $filter = [];

        $filter['name']     = $name;
        $filter['work_pid'] = $work_pid;

        $result = self::create('work', $filter);

        if ( isset($result['errcode']) && $result['errcode'] ) {
            $errmsg = _widget('wework.errmsg')->get_errmsg($result['errmsg']['errcode']);
            return array('errcode' => 1, 'errmsg' => $errmsg);
        }

        return array('errcode' => 0, 'id' => $result['id']);
    }

    public static function get_depart_user_list($agent_id, $work_depart_id)
    {
        if ( !$agent_id ) return false;
        if ( !$work_depart_id ) return false;

        $access_token = wework_user_helper::get_token($agent_id);

        $url  = wework_config::$deaprt_user_detail_url;
        $url .= 'access_token=' . $access_token;
        $url .= '&department_id=' . $work_depart_id;
        $url .= '&fetch_child=0';

        $res_json = curl_get($url);

        // 记录
        $param = [];

        $param['opreate_type'] = 'depart_user_list';
        $param['api_type']     = 'get';
        $param['param']        = '';
        $param['result']       = $res_json;
        $param['api_url']      = $url;

        self::all_api_record($param);

        $result   = json_decode($res_json, true);

        if ( isset($result['errcode']) && $result['errcode'] ) {
            return array('errcode' => 1, 'errmsg' => $result);
        }

        return $result;
    }

    
    public static function update_api($depart_id, $name)
    {
        if ( !$depart_id || !$name ) return false;
        // 调用接口
        $filter = [];
        $filter['name'] = $name;
        $filter['depart_id'] = $depart_id;

        $result = self::update('work', $filter);

        if ( isset($result['errcode']) && $result['errcode'] ) {
            $errmsg = _widget('wework.errmsg')->get_errmsg($result['errmsg']['errcode']);
            return array('errcode' => 1, 'errmsg' => $errmsg);
        }

        return $result;
    }

    public static function all_api_record($param)
    {
        if ( !isset($param['opreate_type']) || !$param['opreate_type'] ) return false;
        if ( !isset($param['api_type']) || !$param['api_type'] ) return false;
        if ( !isset($param['param']) || !$param['param'] ) return false;
        if ( !isset($param['result']) || !$param['result'] ) return false;
        if ( !isset($param['api_url']) || !$param['api_url'] ) return false;

        $param['date'] = date('Ymd');

        _model('wework_api_log')->create($param);

        return true;
    }

    public static function get_level_department($type)
    {
        if ( !in_array($type, wework_config::$department_type)) return false;

        return _model('wework_department')->getList(array('type' => $type));
    }

    /**
     * @param string $str
     * @return boolean|Ambigous <string, multitype:string >
     */
    public static function get_depart_name($str)
    {
        if ( !$str ) return false;

        $msg_title = '';

        $depart_ids = explode(',', $str);

        $names = _model('wework_department')->getFields('name', array('work_depart_id' => $depart_ids));

        if ( !$names ) return '--';

        return implode(',', $names);
    }
    
    public static function get_department_id($depart_ids,$province_id)
    {
        if ( !$depart_ids || !$province_id ) return false;

        $province_info = _model('province')->read(array('id' => $province_id));

        if ( !$province_info ) return false;

        // 3级部门
        $depart_list = _model('wework_department')->getList(
                array('name' => $province_info['name'], 'type' => 2)
        );

        $ids = $have_depart_ids = [];

        foreach ($depart_list as $v) {
            if ( !in_array($v['work_pid'], $depart_ids) ) continue;

            array_push($ids, $v['work_depart_id']);
            array_push($have_depart_ids, $v['work_pid']);
        }

        $diff_ids = array_diff($depart_ids, $have_depart_ids);

        if ( !$diff_ids ) return $ids;

        foreach ($diff_ids as $val) {
            $work_depart_id = self::create_department($province_info['name'], $val);

            if ( !$work_depart_id ) continue;

            array_push($ids, $work_depart_id);
        }

        return $ids;
    }

    public static function create_department($province_name, $work_pid)
    {
        if ( !$province_name ||  !$work_pid ) return false;

        $result = self::create_api($province_name, $work_pid);

        if ( isset($result['errcode']) && $result['errcode'] ) {
            return false;
        }
// p($result);exit();
        // 中国电信股份有限公司增值业务运营中心 下一级部门
        $department_info['name'] = $province_name;
        $department_info['work_pid'] = $work_pid;
        $department_info['work_depart_id'] = $result['id'];

        $info = _model('wework_department')->read(array('work_depart_id' => $work_pid, 'type' => 1));

        $department_info['pid']  = $info['id'];
        $department_info['type'] = 2;

        _model('wework_department')->create($department_info);

        return $result['id'];
    }

    public static function get_business_line($str)
    {
        if ( !$str ) return '未设置';

        $arr = explode(',', $str);

        $business_line_ids = array_unique( _model('wework_department')->getFields('type', array('depart_id' => $arr)) );

        $business_line = '';
        foreach ($business_line_ids as $v) {
            if ( !isset(wework_config::$depart_i[$v]) ) continue;
            $business_line .= '&nbsp;&nbsp;' . wework_config::$depart_i[$v].'<br>';
            if ( !$business_line ) return '未设置';

        }


        return $business_line;
    }
}