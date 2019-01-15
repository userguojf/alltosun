<?php
/**
  * alltosun.com 套餐图相关 helper screen_content_meal_helper.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2018年6月6日 下午6:24:20 $
  * $Id$
  */
class screen_content_meal_helper
{
    /**
     * 获取套餐图列表
     * @param unknown $filter
     */
    public static function get_set_meal_list($filter)
    {
        return _model('screen_content_set_meal')->getList($filter);
    }

    /**
     * 删除套餐图列表
     * @param unknown $filter
     */
    public static function delete_set_meal_by_content_id($content_id)
    {
        if (!$content_id) {
            return true;
        }

        $res = _model('screen_content_set_meal')->delete(array('content_id' => $content_id));

        //获取所有内容关联
        $content_res_list = screen_content_helper::get_content_res_list(array('content_id' => $content_id));

        //删除所有内容关联
        screen_content_helper::delete_content_res(array('content_id' => $content_id));

        //通知所有机型此资源已下线，需重新获取
        foreach ( $content_res_list as $k => $v ) {
            _widget('screen_content.put')->push_by_content_res($v, '2');
        }

        return true;
    }
}