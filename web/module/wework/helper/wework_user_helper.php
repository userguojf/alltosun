<?php
/**
 * alltosun.com  wework_user_helper.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-3-1 下午6:12:24 $
 * $Id$
 */

class wework_user_helper
{
    private static $token    = '';
    private static $agent_id = '';

    public static function get_token($agent_id)
    {
        self::$token = _widget('wework.token')->get_access_token($agent_id);

        return self::$token;
    }

    /**
     * 获取成员的详情
     * @param unknown $user_id
     * @return boolean|mixed|string
     */
    public static function get_user_info($agent_id, $user_id)
    {
        self::get_token($agent_id);

        if ( !$user_id || !self::$token ) return false;

        $url = wework_config::$get_user_info_url.'access_token='. self::$token .'&userid='. $user_id;

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
            if ( $v['name'] == $attr) {
                return $v['value'];
            } else {
                return '';
            }
        }

        return '';
    }

    public static function loacal_create($agent_id, $user_id)
    {
        if ( !$user_id ) return false;

        $local_info = _model('wework_user')->read(array('user_id' => $user_id));

        self::get_token($agent_id);

        if ( !self::$token ) return false;

        $url = wework_config::$get_user_info_url.'access_token='. self::$token .'&userid='. $user_id;

        $info = json_decode(curl_get($url), true);

        if ( !isset($info['errcode']) || $info['errcode'] ) return false;

        $department = implode(',', $info['department']);

        $filter = array (
                'user_id'    => $info['userid'],
                'name'       => $info['name'],
                'department' => $department,
                'position'   => $info['position'],
                'mobile'     => $info['mobile'],
                'gender'     => $info['gender'],
                'email'      => $info['email'],
                'avatar'     => $info['avatar'],
                'status'     => $info['status'],
                'enable'     => $info['enable'],
                'isleader'   => $info['isleader'],
                'english_name' => $info['english_name'],
                'telephone'    => $info['telephone'],
                'qr_code'      => $info['qr_code'],
        );

        foreach ($info['extattr'] as $key => $val) {
            foreach ( $val as $value ) {
                $filter[$value['name']] = $value['value'];
            }
        }

        if ( !$filter['user_id'] || !$filter['an_id']) return false;

        // 操作基本的信息
        if ( !$local_info ) {
            _model('wework_user')->create($filter);
        } else {
            _model('wework_user')->update(array('user_id' => $user_id), $filter);
        }

        return $filter['an_id'];
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

        self::local_delete($user_id);

        return $info;
    }

    public static function local_delete($user_id)
    {
        if ( !$user_id ) return false;

        _model('wework_user')->delete(array('user_id' => $user_id), " LIMIT 1 ");

        return true;
    }
    public static function unsubscribe( $user_id )
    {
        if ( !$user_id ) return false;

        $local_info = _model('wework_user')->read(array('user_id' => $user_id));

        if ( !$local_info ) return false; 

        _model('wework_user')->update(array('user_id' => $user_id), array('status' => 4));
    }

}