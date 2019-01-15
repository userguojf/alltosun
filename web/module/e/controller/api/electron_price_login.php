<?php

/**
 * alltosun.com  screen.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2017年12月14日 下午3:11:23 $
 * $Id$
 */

class Action
{
    private $member_id   = 0;
    private $member_info = 0;
    private $key         = 'f8460382-dfb0-11e7-a85c-020040bb0010';
//     private $url = 'https://smartxcx.360sides.com/login/login?';
    private $url      = 'http://smartxcx.360sides.com/manager/list.html?';
    private $test_url = 'http://test.360sides.com/smart/manager/list.html?';

    public function __construct(){
        $this->member_id   = member_helper::get_member_id();
        $this->member_info = member_helper::get_member_info($this->member_id);
    }

    public function index()
    {
//         if ( !$this->member_id ) {
//             return '请登录';
//         }

        // 登录验证
        e_helper::check_login($this->member_info['res_name'], 'e/api/electron_price_login');

        $date = time().'000';
        $user_name = $this->member_info['member_user'];
        $password  = md5($user_name.$date.$this->key);

//         $url = $this->url."userName={$user_name}&passWord={$password}&date={$date}";

        if ( ONDEV ) {
            $url = $this->test_url."userName={$user_name}&passWord={$password}&date={$date}";
        } else {
            $url = $this->url."userName={$user_name}&passWord={$password}&date={$date}";
        }

        Response::redirect($url);
        Response::flush();
        exit();
    }
}