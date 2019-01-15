<?php
/**
  * alltosun.com 更新wifi状态 ajax.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2017年10月18日 下午2:39:02 $
  * $Id$
  */
class Action
{
    /**
     * 更新wifi状态
     */
    public function update_wifi_status()
    {
        $wifi_id = Request::Post('id', 0);
        $status = Request::Post('status', 0);

        if (!$wifi_id) {
            return 'wifi信息错误';
        }

        $info = _uri('screen_business_wifi_pwd', $wifi_id);

        if (!$info) {
            return 'wifi不存在';
        }

        _model('screen_business_wifi_pwd')->update($wifi_id,array('status' => $status));

        return 'ok';
    }
}