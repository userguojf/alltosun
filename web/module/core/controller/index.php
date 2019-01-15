<?php

/**
 * alltosun.com  index.php
 * ============================================================================
 * 版权所有 (C) 2009-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: zhangdd (zhangdd@alltosun.com) $
 * $Date: 2012-12-12 上午10:38:31 $
 * $Id$
*/

class Action
{
	//直接访问前台的时候自动跳转
    public function __call($action = '', $params = array())
    {
        $user_id = user_helper::get_user_id();
		//判断是否是管理员
		if(!$user_id){
			Response::redirect(AnUrl('user/login'));
		}
		Response::redirect(AnUrl('app/admin/'));
    }
}
?>