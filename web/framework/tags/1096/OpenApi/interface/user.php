<?php

/**
 * alltosun.com 开放平台的接口用户类 user.php
 * ============================================================================
 * 版权所有 (C) 2009-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 宁海新 (ninghx@alltosun.com) $
 * $Date: 2012-9-18 上午10:30:14 $
 * $Id: user.php 643 2013-02-07 12:16:41Z anr $
*/

/**
 * 开放平台的接口用户类
 * @author nignhx@alltosun.com
 * @package AnOpenApi
 */
interface AnOpenApiUserWrapper extends AnOpenApiWrapper
{
    /**
     * 获取用户信息
     * @param string $info
     * @param string $fields
     */
    public function getUserInfo($info, $fields = 'id');

    /**
     * 根据用户id获取用户信息
     * @param int $uid
     */
    public function getUserInfoById($uid);

    /**
     * 根据用户昵称获取用户信息
     * @param string $screen_name
     */
    public function getUserInfoByName($screen_name);
}