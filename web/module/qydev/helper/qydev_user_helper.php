<?php
/**
 * alltosun.com  qydev_user_helper.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-4-9 下午12:23:15 $
 * $Id$
 */

class qydev_user_helper
{
    private static $token    = '';

    public static function test()
    {
        echo 123;
    }

    public static function get_token()
    {
        self::$token = _widget('qydev.token')->get_access_token('work');

        return self::$token;
    }

    /**
     * 获取成员的详情
     * @param unknown $user_id
     * @return boolean|mixed|string
     */
    public static function get_user_info($user_id)
    {
        self::get_token();

        if ( !$user_id || !self::$token ) return false;

        $url = qydev_config::$get_user_info_url.'access_token='. self::$token .'&userid='. $user_id;

        $info = json_decode(curl_get($url), true);

        if ( isset($info['errcode']) && !$info['errcode'] ) {
            return $info;
        }

        return '';
    }

    /**
     * 获取成员的某个额外字段
     * @param unknown $user_id
     * @param unknown $attr
     * @return boolean|unknown|string
     */
    public static function get_extra_field($user_info, $attr)
    {
        if ( !$user_info || !$attr ) return false;

        if ( !isset($user_info['extattr']) )  return '';

        foreach ($user_info['extattr']['attrs'] as $k => $v ) {
            if ( $v['name'] != $attr) {
                continue;
            }

            return $v['value'];
        }

        return '';
    }

    public static function loacal_operation($user_id)
    {

        if ( !$user_id ) return false;

        self::get_token();

        if ( !self::$token ) return false;

        $url = qydev_config::$get_user_info_url.'access_token='. self::$token .'&userid='. $user_id;

        $info = json_decode(curl_get($url), true);

        if ( !isset($info['errcode']) || $info['errcode'] ) return false;

        // 判断是否符合命名规则
        if ( !strpos($user_id, '_' ) ) return  false;

        $user_level = explode("_", $user_id);

        if ( 2 == count($user_level) ) $user_number = $user_level[0];

        $filter['unique_id'] = $info['userid'];
        $filter['user_name'] = $info['name'];
        $filter['from_id']   = implode(',', $info['department']);
        $filter['user_phone'] = $info['mobile'];
//         $filter['email']      = isset( $info['email'] ) ?: '';
//         $filter['avatar']     = $info['avatar'];
//         $filter['business_hall'] = self::get_extra_field($info, 'business_hall');
//         $filter['an_id']         = self::get_extra_field($info, 'an_id') ?: $user_number;
//         $filter['analog_i']     = self::get_extra_field($info, 'analog_id');

        $filter['user_number'] = $filter['an_id'];

//         if ( !$filter['unique_id'] || !$filter['business_hall'] ) return false;
// p($filter);
// exit();
        $local_info = _model('public_contact_user')->read(array('user_phone' => $info['mobile']));

        if ( $local_info ) {
            _model('public_contact_user')->update(array('user_phone' => $info['mobile']), $filter);
        } else {
            _model('public_contact_user')->create($filter);
        }

        return $filter['an_id'] ?: $filter['analog_id'];
    }

    public static function create($agent_id, $param)
    {
        if ( !$agent_id || !$param ) return false;

        self::get_token($agent_id);

        if ( !self::$token ) return false;

        $url = wework_config::$create_user_url . 'access_token=' . self::$token;

        $info = json_decode(curl_post($url, $param), true);

        if ( !isset($info['errcode']) || $info['errcode'] ) {
            return array('errcode' => 1, 'errmsg' => $info);
        }

        return $info;
    }

    public static function update($agent_id, $param)
    {
        if ( !$agent_id || !$param ) return false;

        self::get_token($agent_id);

        if ( !self::$token ) return false;

        $url = wework_config::$update_user_url . 'access_token=' . self::$token;

        $info = json_decode(curl_post($url, $param), true);

        if ( !isset($info['errcode']) || $info['errcode'] ) {
            return array('errcode' => 1, 'errmsg' => $info);
        }

        return $info;
    }

    public static function delete($agent_id, $user_id)
    {
        if (!$user_id || !$agent_id) return false;

        self::get_token($agent_id);

        if ( !self::$token ) return false;

        $url  = wework_config::$delete_user_url."access_token=".self::$token.'&userid='.$user_id;

        $info = json_decode(curl_get($url),true);

        if (isset($info['errmsg']) && 'deleted' != $info['errmsg'] ) {
            return array('errcode' => 1, 'errmsg' => $info);
        }

        return $info;
    }

}