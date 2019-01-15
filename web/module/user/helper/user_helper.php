<?php

/**
 * alltosun.com 用户模块公共函数库 user_helper.php
 * ============================================================================
 * 版权所有 (C) 2009-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: Shenxn 申小宁 (shenxn@alltosun.com) $
 * $Date: Sep 6, 2013 4:13:09 PM $
 * $Id$
 */

class user_helper
{
    /**
     * 是否处于后台
     * @var unknown_type
     */
    public static $is_in_admin = false;

    /**
     * 当前进程的user_id
     */
    private static $user_id = null;

    /**
     * 保存电信手机号信息并返回用户ID
     */
    public static function create_user_info($user_login_data, $user_res_data)
    {
        //保存登录用户信息
        if (!$user_login_data || !$user_res_data) {
            return false;
        }

        $filter = array('phone' => $user_login_data['phone']);
        $time   = date('Y-m-d H:i:s');

        $user_info = self::get_user_info($filter);

        if (!$user_info) {

            //保存用户信息
            $user_login_data['hash']             = uniqid();
            $user_login_data['last_login_time']  = $time;

            // 取用户登录来源
            $user_login_data['source'] = source_record_helper::get_source();

            $user_id = _model('user')->create($user_login_data);

            $params = array(
                    'business_hall_id' => $user_login_data['business_hall_id'],
                    'user_id'           => $user_id,
                    'type'              => 1
            );

            _widget('user_count')->stat($params);

        } else {
            _model('user')->update(
                array('id' => $user_info['id']),
                array(
                    'business_hall_id' => $user_login_data['business_hall_id'],
                    'last_login_time'  => $time
                )
            );

            $user_id = $user_info['id'];
        }

        $user_res_data['user_id']           = $user_id;
        $user_res_data['business_hall_id']  = $user_login_data['business_hall_id'];

        //保存用户登录记录
        self::save_user_res($user_res_data);

        self::save_user_business_hall_num($user_res_data);

        //更新activity字段的信息
        business_helper::update_activity_info($user_login_data['business_hall_id']);

        return $user_id;
    }

    /**
     * 保存用户登录营业厅记录
     * @param unknown $user_res_data
     * @return boolean
     */
    public static function save_user_business_hall_num($user_res_data)
    {
        if (!$user_res_data) {
            return false;
        }

        //user_business_hall_num
        $filter = array(
            'user_id'          => $user_res_data['user_id'],
            'business_hall_id' => $user_res_data['business_hall_id']
        );

        $user_business_hall_info = _model('user_business_hall_num')->read($filter);

        $login_time = date('Y-m-d H:i:s');

        if (!$user_business_hall_info) {
            //
            $business_hall_info = _uri('business_hall', $user_res_data['business_hall_id']);

            $filter['province_id']     = $business_hall_info['province_id'];
            $filter['city_id']         = $business_hall_info['city_id'];
            $filter['area_id']         = $business_hall_info['area_id'];
            $filter['last_login_time'] = $login_time;

            _model('user_business_hall_num')->create($filter);

        } else {
            //
            _model('user_business_hall_num')->update(
                $user_business_hall_info['id'],
                array(
                    'last_login_time' => $login_time,
                    'login_num' => ++ $user_business_hall_info['login_num']
                )
            );
        }

        return  true;
    }

    /**
     * 保存用户登录记录
     * @param array $user_res_data
     * @return boolean
     */
    public static function  save_user_res($user_res_data)
    {
        if (!$user_res_data) {
            return false;
        }

        _model('user_login_record')->create($user_res_data);
        _model('user')->update($user_res_data['user_id'], ' SET `login_num`=login_num+1 ');

        return true;
    }

    /**
     * 检查用户是否有进入后台的权限
     * @param $admin_id 默认为当前登录用户的admin_id
     * @return bool
     */
    public static function have_privilege($admin_id = 0)
    {

        return true;
        $admin_id = (int)$admin_id;

        if (empty($admin_id)) {

            $admin_id = user_helper::get_admin_id();

        }

        if (empty($admin_id)) {

            return FALSE;

        }

        // @FIXME 临时采用privilege来给菜单赋权限 START
        $role_ids = user_helper::get_user_role_ids($admin_id);

        foreach ($role_ids as $v) {

            $privilege = _uri('role', $v, 'privilege');
            if ($privilege) {
                return TRUE;
            }
        }

        return FALSE;
        // @FIXME 临时采用privilege来给菜单赋权限 END

        //    return AnAcl::hasDefinedAuth($admin_id);
    }
    /**
     * 设置用户的永久登录状态
     * @param array $info 用户信息数组（必须含id和hash键）
     */
    public static function remember_me_set($user_info)
    {

        $id_hash = $user_info['id'].Config::get('cookie_delimiter').$user_info['hash'];

        $genpass = convert_uuencode(gen_pass($id_hash));

        Cookie::set_path(Config::get('cookie_path'));
        Cookie::set_domain(Config::get('cookie_domain'));

        // 永久登录默认为8小时
        Cookie::set('reuser_last_me', $genpass, time()+3600*12);

        // 更新session状态
        self::session_update($user_info);
    }

    /**
     * 更新session状态
     * @param array $info 用户信息数组
     */
    public static function session_update($info)
    {
        Cookie::set_path(Config::get('cookie_path'));
        Cookie::set_domain(Config::get('cookie_domain'));

        $_SESSION['user_id']          = $info['id'];
        $_SESSION['business_hall_id'] = $info['business_hall_id'];
//         p($_SESSION);exit();
        return true;
    }

    /**
     * 记住用户登录状态
     */
    public static function remember_me()
    {
        // 已经登录
        if (!empty($_SESSION['user_id'])) {
            return true;
        }

        // 没有记住我
        if (!$set = Cookie::get('reuser_last_me')) {
            return false;
        }

        // cookie格式不对
        if (!$set = gen_pass(convert_uudecode($set))) {
            self::remember_me_expire();
            return false;
        }

        $cookie_delimiter = Config::get('cookie_delimiter');
        // cookie格式不对
        if (strpos($set, $cookie_delimiter) === false) {
            self::remember_me_expire();
            return false;
        }

        list($user_id, $hash) = explode($cookie_delimiter, $set);

        $user_id = (int)$user_id;
        $user_info = _uri('user', $user_id);

        // 用户不存在
        if (empty($user_info)) {
            self::remember_me_expire();
            return false;
        }

        // 用户hash与cookie中不等（用户修改密码时需重置hash）
        if ($user_info['hash'] !== $hash) {
            self::remember_me_expire();
            return false;
        }

        // 更新session状态
        self::session_update($user_info);
        return true;
    }

    /**
     * 使用户的remember_me状态失效
     */
    public static function remember_me_expire()
    {
        Cookie::set_path(Config::get('cookie_path'));
        Cookie::set_domain(Config::get('cookie_domain'));
        Cookie::del('reuser_last_me');

        // 删除session
        session_destroy();
        return true;
    }

    /**
     * 获取用户的电话号码
     */
    public static function get_user_phone()
    {

        $user_id = user_helper::get_user_id();
        if ($user_id) {
            if (isset($_SESSION['is_phone']) && $_SESSION['is_phone']) {
                return user_helper::display_name($user_id);
            }
            return '';
        }

        return false;
    }

    /**
     * 随机产生A-Z, a-z, 0-9的字符串
     * @param int $length 随机数的长度
     * @return string
     */
    public static function random_hash($length = 10)
    {
        $salt = array_merge(range('A', 'Z'), range('a', 'z'), range(0, 9));
        $count = count($salt);
        $hash = '';
        for ($i = 0; $i < $length; $i++) {
            $hash .= $salt[mt_rand(0, $count-1)];
        }
        return $hash;
    }


    /**
     * 获取当前登录的管理员id
     * @return int
     */
    public static function get_admin_id()
    {

        static $static_get_admin_id = null;
        if ($static_get_admin_id === null) {
            $static_get_admin_id = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : 0;
        }
        return $static_get_admin_id;
    }

    /**
     * 获取当前登录的用户id
     * @return int
     */
    public static function get_user_id()
    {
        if (ONDEV) {
//             return 1;
        }

        if (isset($_SESSION['user_id']) && $_SESSION['user_id']) {
            return $_SESSION['user_id'];
        }
        return 0;
    }

    public static function get_visit_id()
    {
        $url = AnUrl::getInstance();
        if (isset($url['visit_id']) && is_numeric($url['visit_id'])) {
            $static_get_visit_id = $url['visit_id'];
        } else {
            $static_get_visit_id = !empty($url['visit_id']) ? $url['visit_id'] : user_helper::get_user_id();
        }

        return $static_get_visit_id;
    }


    /**
     * 获取用户名以及附加信息
     * @param $user_id 用户信息
     * @return string
     */
    public static function display_tag($user_id)
    {
       $verified = self::verified_type_for_html($user_id);
       $vip = self::is_weibo_vip_for_html($user_id);

       return $verified.$vip;
    }

    /**
     * 根据用户名模糊搜索相应的用户id列表
     * @param $name
     * @return array()
     */
    public static function get_user_ids_by_name($name)
    {
        if (empty($name)) return array();
        $user_ids = _model('user')->getFields('id', array('user_name like' => "%$name%"));
        return $user_ids;
    }

    /**
     * 生成通用用户选择部分被选中的用户用于展示的html
     * @param mixed $id
     * @return string 生成的html
     */
    public static function get_selected_user_html($id)
    {
         $result = '';
        if (!$id) {
            return $result;
        }
        if (!is_array($id)) {
            $id = explode(',', $id);
        }
        foreach ($id as $k=>$v) {
            $user_name = self::display_name($v);
            $result .= '<div class="selectedUserName"><span>'.$user_name.'</span><a href="javascript:void(0);" class="selectedUser'.$v.'">×</a></div>';
        }

        return $result;
    }

    /**
     * 获取用户名
     * @param unknown_type $user_id
     */
    public static function display_phone($user_id)
    {
        return '';
    }

    /**
     * 按照用户空间设置的姓名显示方式显示姓名
     * 如果是管理员则显示：注册名（真实姓名）/注册名（昵称）
     * @param int $user_id
     * @return string
     * @TODO 空间自定义设置的姓名显示方式
     */
    public static function display_name($user_id)
    {
        static $display_name_list = array();

        if (isset($display_name_list[$user_id])) {
            return $display_name_list[$user_id];
        }

//         // 判断当前登录用户是否手机登陆
//         if (isset($_SESSION['is_phone']) && $_SESSION['is_phone']) {
//             $user_info = _model('user')->read($user_id);
//             if ($user_info) {
//                 $display_name_list[$user_id] = $user_info['phone'];
//                 return $user_info['phone'];
//             }
//             return '';
//         } else if (isset($_SESSION['type']) && $_SESSION['type'] == 'sinaweibo') {
//             // sinaweibo
//             $user_info = _uri('user', $user_id);

//             if ($user_info) {
//                 $display_name_list[$user_id] = $user_info['user_name'];
//                 return $user_info['user_name'];
//             }
//         }

        $display_name = '';
        if ($user_id) {
            $user_info = _uri('user', $user_id);
            if ($user_info) {
                // 电信手机号
                if ($user_info['is_type'] == '189') {
                    $display_name = $user_info['phone'];
                } else if ($user_info['is_type'] == '189wx') {
                    $display_name = $user_info['phone'];
                } else if ($user_info['is_type'] == '189yx') {
                    $display_name = $user_info['phone'];
                } else {
                    // 其他登录用户
                    $display_name = $user_info['user_name'];
                }
            }
        }

        $display_name_list[$user_id] = $display_name;
        return $display_name_list[$user_id];
    }

    /**
     * 在2个字的姓名之间加上字符填充
     * @param $name
     * @param $pad_string
     * @author gaojj@alltosun.com
     */
    public static function fill_space_between_name($name, $pad_string = ' ')
    {
        if (mb_strlen($name, 'utf-8') == 2) {
            return mb_substr($name, 0, 1, 'utf-8').$pad_string.mb_substr($name, 1, 1, 'utf-8');
        }
        return $name;
    }


    /**
     * 特殊登录
     */
    public static function special_login($user_id)
    {
        $user_info = _uri('user', $user_id);
        self::session_update($user_info);
    }

    /**
     * 生成公钥
     * @param int $user_id
     * @param string $site_url
     */
    public static function get_public_key($user_id, $site_url)
    {
        $str = $user_id.$site_url.time();
        return base64_encode(hash_hmac('sha1', $str, $user_id.'public_key', true));
    }

    /**
     * 生存密钥
     * @param string $params
     */
    public static function get_secret_key($params)
    {
        $str = '';
        foreach ($params as $v) {
            $str .= $v;
        }
        $str.= time();
        return base64_encode(hash_hmac('sha1', $str, 'secret_key', true));
    }


    /**
     * 是否为管理员
     * @param int $user_id
     */
    public static function is_admin($admin_id = 0)
    {

        if (!$admin_id) {
            $admin_id = self::get_admin_id();
        }

        if (!$admin_id) {
            return false;
        }

        $user_info = _uri('user', array('id'=>$admin_id));
        if (!$user_info) {
            return false;;
        }

        return true;
    }

    /**
     * 检查用户是否登录
     */
    public static function check_login()
    {
        $user_id = self::get_user_id();
        if (!$user_id) {
            Response::redirect("user/login");
        }
        return true;
    }

    /**
     * 根据用户名获取用户id
     * @param $name
     * @return int
     */
    public static function get_user_id_by_name($name)
    {
        return _uri('user', array('user_name' => $name),'id');
    }

    /**
     * 根据用户id列表获取用户信息列表
     * @param array $user_ids 用户id列表
     * @return array
     */
    public static function get_user_list_by_ids($user_ids)
    {
        if (empty($user_ids)) return array();

        $user_ids = (array)$user_ids;

        $user_list = array();
        foreach ($user_ids as $v) {
            $user_list[] = _uri('user', $v);
        }

        return $user_list;
    }

    /**
     * 返回指定的用户ID是否存在
     * @param int $user_id 用户ID
     * @return number 用户数量
     */
    public static function get_user_count($where = array())
    {
        $user_count = _model('user')->getTotal($where);
        return $user_count;
    }

    /**
     * 获取用户表的某些字段值
     * @param array()|int $where
     * @param string $field
     * @return boolean|string
     */
    public static function get_user_info($where = '', $field = '')
    {
        if (!$where) {
            $where = self::get_user_id();
        }

        if (empty($field)) {
            $user_info = _uri('user', $where);
            return $user_info;
        } else {
            $user_field = _uri('user', $where, $field);
            return $user_field;
        }
    }

    /**
     *
     * @return Ambigous <multitype:, string, unknown, Obj, mixed>
     */
    public static function is_4g_user($user_id)
    {
        return _uri('user_4g',$user_id,'user_id');
    }

    /**
     *
     * @return Ambigous <multitype:, string, unknown, Obj, mixed>
     */
    public static function is_4g_user_by_phone($phone)
    {
        $info = _model('user_4g')->read(array('phone'=>$phone));

        if ($info) {
            return true;
        }

        return false;
    }

    /**
     * 获取用户所属的角色id列表
     * @param int|array $user_id 用户id或者id数组
     * @param string $order 排序以及LIMIT限制，默认所有角色id
     * @return array
     */
    public static function get_user_role_ids($user_id, $order = 'ORDER BY `add_time` DESC')
    {
        if (empty($user_id)) {
            return array();
        }

        $filter = array('member_id' => $user_id);

        $role_ids = _model('role_user')->getFields('role_id', $filter);

        return array_unique($role_ids);
    }

    /**
     * 获取用户昵称
     */
    public static function get_nick_name($user_id)
    {
        if ($user_id == '') {
            $user_id = user_helper::get_user_id();
        }

        $user_info = _uri('user', (int)$user_id);
        $user_name = $user_info['user_name'];
        if (empty($user_name)) {
            if (!empty($user_info['phone'])) {
                $user_name = substr($user_info['phone'], 0,3).'*****'.substr($user_info['phone'], -3);
            } else {
                $user_name = 'BSZB_'.$user_info['id']*2014;
            }
        }
        return $user_name;
    }

    /**
     * 读取保存在session的值
     * @return
     */
    public static function get_cookie_by_key($key)
    {
        return Cookie::get($key);
    }

    /**
     * 设置session的值
     * @return
     */
    public static function set_cookie_by_key($key,$value)
    {
        Cookie::set($key, $value, time()+3600*24*365);
    }

    public static function get_user_login_record($user_id)
    {
        return _uri('user_login_record',array('user_id' => $user_id));
    }

    /**
     * 获取用户的奖品信息
     * @param unknown $card_id
     * @param string $field
     * @return boolean
     */
    public static function get_user_prize_info($card_id, $field = '')
    {
        if (!$card_id) {
            return false;
        }

        if ($field) {
            return _uri('user_prize', array('card_id' => $card_id),$field);
        } else {
            return _uri('user_prize', array('card_id' => $card_id));
        }
    }

    public static function get_user_prize_record_info($card_id, $field = '')
    {
        if (!$card_id) {
            return false;
        }

        if ($field) {
            return _uri('prize_card', array('id' => $card_id), $field);
        } else {
            return _uri('prize_card', array('id' => $card_id));
        }
    }

    /**
     * @param table_name
     * @param int $id
     * @param string $field
     * @return boolean
     * +
     * */
    public static function get_info($table='', $id , $field = '')
    {
        if (!$id) {
            return false;
        }

        if ($field) {
            return _uri($table, array('id' => $id), $field);
        } else {
            return _uri($table, array('id' => $id));
        }
    }

    public static function get_prize_card_info($prize_id, $field = '')
    {
        if (!$prize_id) {
            return false;
        }

        if ($field) {
            return _uri('prize_card', array('id' => $prize_id), $field);
        } else {
            return _uri('prize_card', array('id' => $prize_id));
        }
    }
    /**
     * @param table_name
     * @param array() $where
     * @param string  $field
     * */
    public static  function get_info_field($table='' , $where ,$field='')
    {
        if (!$table || !$where) {
            return false;
        }
        if ($field) {
            $table_field = _uri($table,$where,$field);
            if ($table_field) {
                return $table_field;
            }
        } else {
            $table_info  = _uri($table,$where);
            if ($table_field) {
                return $table_info;
            }
        }
        return false;
    }

    public static function set_mc_login_info($info)
    {
        global $mc_wr;

        return $mc_wr ->set('mc_login_info', json_encode($info), 4000);
    }

    public static function get_mc_login_info()
    {
        global $mc_wr;

        $info = $mc_wr ->get('mc_login_info');


        if ($info) {
            $mc_wr ->delete('mc_login_info');

            return json_decode($info);
        }

        return false;
    }
    //微信开发添加的方法 guojf  add
    
    public static function get_ip()
    {
    	if (isset($_SERVER))
    	{
    		if (isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
    		{
    			$realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
    		}
    		else if (isset($_SERVER["HTTP_CLIENT_IP"]))
    		{
    			$realip = $_SERVER["HTTP_CLIENT_IP"];
    		}
    		else
    		{
    			$realip = $_SERVER["REMOTE_ADDR"];
    		}
    	}
    	else
    	{
    		if (getenv("HTTP_X_FORWARDED_FOR"))
    		{
    			$realip = getenv("HTTP_X_FORWARDED_FOR");
    		}
    		else if (getenv("HTTP_CLIENT_IP"))
    		{
    			$realip = getenv("HTTP_CLIENT_IP");
    		}
    		else
    		{
    			$realip = getenv("REMOTE_ADDR");
    		}
    	}
    	return $realip;
    }

}
?>