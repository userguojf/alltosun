<?php
/**
  * alltosun.com 设置内容套餐 set_meal.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2018年1月15日 下午4:15:00 $
  * $Id$
  */
class Action
{
    /**
     * 设置套餐
     */
    public function set_up_set_meal()
    {
        $content_id = tools_helper::Get('content_id', 0);

        Response::assign('content_id', $content_id);
        Response::display('admin/set_meal/set_up_set_meal.html');
    }
}

