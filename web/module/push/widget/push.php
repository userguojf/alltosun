<?php
/**
  * alltosun.com 推送widget push.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2017年12月16日 下午9:27:46 $
  * $Id$
  */
class push_widget
{

    /**
     * 按指定机型推送
     * @param unknown $phone_name
     * @param unknown $phone_version
     */
    public function push_device_model($phone_name, $phone_version)
    {
        //获取标签
        $filter = array(
                'res_name' => 'phone_name_version',
                'res_id'   => $phone_name.'_'.$phone_version,
        );

        $tag_info = _model('screen_device_tag')->read($filter);

        if (!$tag_info) {
            return true;
        }

        push_helper::push_msg('2', array(), array($tag_info['tag']));
    }
}