<?php
/**
  * alltosun.com 停留详情 remain.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2018年5月25日 下午2:32:04 $
  * $Id$
  */
probe_helper::load('func');
class Action
{
    public function __construct()
    {
        $this->member_info = member_helper::get_member_info();

        if (!$this->member_info) {
            Response::redirect(AnUrl('liangliang/e_login'));
            Response::flush();
        }

        Response::assign('member_info', $this->member_info);
        Response::assign('curr_page', 'probe');
    }
    public function index()
    {
        if (!$this->member_info) {
            Response::redirect(AnUrl('liangliang/e_login'));
            Response::flush();
        }

        $start_time = tools_helper::Get('start_time', '');
        $end_time   = tools_helper::Get('end_time', '');
        $mac        = tools_helper::Get('mac', '');
        $toiletId   = tools_helper::Get('toiletId', '');
        $user_number = '1111111111001';  //暂时定死
        $device     = '';

//         $user_number = '1101021002051';
//         $start_time = date('2017-11-08 08:00:00');
//         $end_time   = date('2017-11-08 19:00:00');
//         $mac        = probe_helper::mac_decode(probe_helper::mac_encode('112720873921973'));
//         $toiletId   = '7b1d8ec4-dc77-45d8-9f69-6787b536f9b30';
//         $device     = '16120803';

        $toilet_type = 0;
        //截取最后一位 0-男 1-女
        if (substr($toiletId, -1) != 0 ) {
            $toilet_type = 1;
        }

        $business_hall_info = business_hall_helper::get_business_hall_info(array('user_number' => $user_number));

        if (!$business_hall_info) return '门店不存在';
        if (!$start_time) return '起始时间不能为空';
        if (!$end_time) return '截止时间不能为空';
        if (!$mac) return '用户不存在';

        $start_date = date('Ymd', strtotime($start_time));
        $end_date   = date('Ymd', strtotime($end_time));

        // 小时开始时间
        $start = strtotime(date('Y-m-d H:00:00', strtotime($start_time)));
        // 小时结束时间戳
        $end   = strtotime(date('Y-m-d H:59:59', strtotime($end_time)));

        //女 男
        $device = $toilet_type ? 'mz5a4dbf59d97f3' : 'mz5a4dbf59f1915';
        $filter = array(
                'date >=' => $start_date,
                'date <=' => $end_date,
                'dev'     => $device,
                'mac'     => $mac,
                'frist_time >=' => $start,
                'frist_time <=' => $end,
                'is_indoor' => 1,
        );

        $db = get_db($business_hall_info['id'], 'hour');

        if (!$db) api_helper::return_data(1, '暂不支持此厅');

        //查询详情
        $data_list      = $db->getList($filter);
        $time_lines     = '';
        $remain_time    = 0;
        foreach ($data_list as $k => $v) {
            $time_lines .= $v['time_line'].',';
            $remain_time += $v['remain_time'];
        }

        $time_lines = trim($time_lines, ',');

        $time_lines = explode(',', $time_lines);

        Response::assign('time_lines', $time_lines);
        Response::assign('remain_time', rfid_helper::format_timestamp_text($remain_time));
        Response::assign('start_time', $start_time);
        Response::assign('end_time', $end_time);
        Response::display('admin/probe/remain/index.html');
    }
}