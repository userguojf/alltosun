<?php

/**
 * alltosun.com 管理员公共类 member_helper.php
* ============================================================================
* 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
* 网站地址: http://www.alltosun.com
* ----------------------------------------------------------------------------
* 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
* ============================================================================
* $Author: 申小宁 (shenxn@alltosun.com) $
* $Date: 2015-12-15 下午3:12:16 $
* $Id: member_helper.php 368381 2017-09-06 09:29:01Z shenxn $
*/

class member_helper
{
    /**
     * 是否处于后台
     * @var unknown_type
     */
    public static $is_in_admin = false;

    /**
     * 当前进程p
     */
    private static $member_id = 0;

    /**
     * 获取管理员id
     * @return int
     */
    public static function get_member_id()
    {
        static $static_member_id = null;
    
        if ($static_member_id === null) {
            $static_member_id = isset($_SESSION['member_id']) ? $_SESSION['member_id'] : 0;
        }
    
        return $static_member_id;
    }

    /**
     * 获取管理员信息
     * @param number $member_id
     * @param string $field
     * @return boolean|unknown
     */
    public static function get_member_info($member_id = 0 , $field = '')
    {
        if (!$member_id) {
            $member_id = self::get_member_id();
        }

        if ($field) {
            $member_info = _uri('member', $member_id , $field);
        } else {
            $member_info = _uri('member', $member_id );
        }

        if (!$member_info) {
            return false;
        }

        return $member_info;
    }

    /**
     * 记录用户状态
     * @param array $member_info
     */
    public static function remember_me_set($member_info)
    {
        $id_hash = $member_info['id'].Config::get('cookie_delimiter').$member_info['hash'];

        $genpass = convert_uuencode(gen_pass($id_hash));

        Cookie::set_path(Config::get('cookie_path'));
        Cookie::set_domain(Config::get('cookie_domain'));
        Cookie::set('member_admin_tz', $genpass, time()+3600*24*30);

        // 更新session状态
        self::session_update($member_info);
    }

    /**
     * 更新session状态
     * @param array $info 用户信息数组
     */
    public static function session_update($info)
    {
        $_SESSION['member_id'] = $info['id'];
    }

    /**
     * hash登录
     * @return boolean
     */
    public static function remember_me()
    {
        // 已经登录
        if (!empty($_SESSION['member_id'])) {
            return true;
        }

        // 没有记住我
        if (!$set = Cookie::get('member_admin_tz')) {
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

        list($member_id, $hash) = explode($cookie_delimiter, $set);

        $member_id = (int)$member_id;
        $member_info = self::get_member_info($member_id);

        // 用户不存在
        if (empty($member_info)) {
            self::remember_me_expire();
            return false;
        }

        // 用户hash与cookie中不等（用户修改密码时需重置hash）
        if ($member_info['hash'] !== $hash) {
            self::remember_me_expire();
            return false;
        }

        // 更新session状态
        self::session_update($member_info);
        return true;
    }

    /**
     * 使用户的remember_me状态失效
     */
    public static function remember_me_expire()
    {
        Cookie::set_path(Config::get('cookie_path'));
        Cookie::set_domain(Config::get('cookie_domain'));
        Cookie::del('member_admin_tz');

        // 删除session
        session_destroy();
        return true;
    }

    /**
     * 根据管理员res_name和res_id获取管理员所属地区
     */
    public static function get_member_area_info($member_id)
    {
        if(!$member_id) {
            return '地区未知';
        }


        $member_info = self::get_member_info($member_id);
        $res_name    = $member_info['res_name'];
        $res_id      = $member_info['res_id'];

        if($res_name =='province' || $res_name =='city' || $res_name =='area') {

            $name = self::get_member_info($member_id, 'name');

            if($name) {
                return $name;
            }
        }

        if($res_name == 'group') {
            return '超级管理员';
        }

        return '归属未知';
    }

    /**根据res_name获取分组明细
     * @param $res_name
     * @return string
     */
    public static function get_res_name($res_name)
    {

        if(!$res_name) {
            return '未知组';
        }

        $arr = array(
            'group'             => '集团',
            'province'          => '省份',
            'city'              => '城市',
            'area'              => '区域',
            'business_hall'     => '营业厅',
        );

        if(array_key_exists($res_name,$arr)) {
            return $arr[$res_name];
        }else{
            return '未知组';
        }

    }

    /**
     * 获取营业厅名称
     * @return string
     */
    public static function get_title_info($member_id)
    {
        if(!$member_id) {
            return '地区未知';
        }

        $member_info = _uri('member',$member_id);
        $res_name    = $member_info['res_name'];
        $res_id      = $member_info['res_id'];

        if($res_name =='province') {

            $name = _uri($res_name,$res_id,'name');

            if($name) {
                return $name."_省级管理员";
            }

        }

        if ($res_name =='city') {
            $name = _uri($res_name,$res_id,'name');

            if($name) {
                return $name."_市级管理员";
            }

        }
        if ( $res_name =='area') {
            $name = _uri($res_name,$res_id,'name');

            if($name) {
                return $name."_县(区)级管理员";
            }
        }

        if ($res_name =='business_hall') {
            $name = _uri($res_name,$res_id,'title');

            if($name) {
                return $name;
            }
        }

        if($res_name == 'group') {
            return '超级管理员';
        }

        return '归属未知';
    }

    /**
     * 生成用户账号信息
     */
    public static function create_member_info($business_info)
    {
        if (empty($business_info)) {
            return false;
        }

        $member_info = _model('member')->read(
            array('res_name'=> 'business_hall',
                'res_id'      => $business_info['res_id']
            )
        );

        if ($member_info) {
            return false;
        }

        $member_id = _model('member')->create(
            array(
                'member_user' => $business_info['member_user'],
                'member_pass' => $business_info['member_pass'],
                'res_name'    => $business_info['res_name'],
                'res_id'      => $business_info['res_id'],
                'ranks'       => 5,
                'hash'       => uniqid()
            )
        );

        _model('group_user')->create(
            array(
                'member_id'  => $member_id,
                'group_id'   => 26,
            )
        );

        return true;
    }
}
