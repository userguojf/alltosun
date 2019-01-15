<?php

/**
 * alltosun.com 用户配置 user_config.php
 * ============================================================================
 * 版权所有 (C) 2009-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: Shenxn 申小宁 (shenxn@alltosun.com) $
 * $Date: Sep 16, 2013 2:10:36 PM $
 * $Id$
 */

class user_config
{
    /**
     * 用户id字典
     * @var array
     */
    public static $role_id_map = array(
            1   => '超级管理员',
            5   => '普通编辑',
            );

    // 角色对应的module权限
    public static $role_module_map = array(
            0 => array('stat'),
            1 => array('stat'),
            2 => array('stat')
            );

    // 角色对应的后台左边栏权限
    public static $role_side_bar = array(
            5 => array('3', '4', '5'),
            );
}
?>
