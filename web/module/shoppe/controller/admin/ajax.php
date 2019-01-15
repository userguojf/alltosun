<?php
/**
  * alltosun.com 专柜ajax ajax.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2017年8月24日 上午10:39:21 $
  * $Id$
  */
class Action
{
    /**
     * 更新专柜状态
     */
    public function update_shoppe_status()
    {
        $shoppe_id  = Request::Post('id', 0);
        $from     = Request::Post('from', 0);

        if (!$shoppe_id) {
            return '信息错误';
        }

        $info = _uri('rfid_shoppe',$shoppe_id);

        if (!$info) {
            return '专柜不存在';
        }

        $rfid_count = shoppe_helper::get_shoppe_rfid_count($info['business_id'], $info['id']);

        if ($rfid_count >= 1) {
            return '此专柜存在RFID设备';
        }

        $screen_count = shoppe_helper::get_shoppe_screen_count($info['business_id'], $info['id']);

        if ($screen_count >= 1) {
            return '此专柜存在亮屏设备';
        }

        _widget('shoppe')->delete_shoppe($shoppe_id, $from);

        return 'ok';
    }

    /**
     * 生成后缀
     */
    public function generate_postfix()
    {
        $phone_name     = tools_helper::Post('phone_name', '');
        $shoppe_name    = tools_helper::Post('shoppe_name', '');

        if (!$phone_name || !$shoppe_name) {
            return array('info' => 'fail', 'msg' => '品牌或专柜名称不能为空');
        }

        //验证登录营业厅id
        $member_info = member_helper::get_member_info();

        if (!$member_info || $member_info['res_name'] != 'business_hall') {
            return array('info' => 'fail', 'msg' => '请使用营业厅管理员登录');
        }

        $postfix = shoppe_helper::generate_shoppe_ch_postfix($phone_name, $shoppe_name, $member_info['res_id']);

        if ($postfix) {
            return array('info' => 'ok', 'postfix' => $postfix);
        }

        return array('info' => 'fail', 'msg' => '此专柜个数超出本厅限制');

    }

}
