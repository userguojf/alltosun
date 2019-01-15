<?php
/**
 * alltosun.com 自动加载目前没有用 ajax.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-7-9 下午4:12:29 $
 * $Id$
 */

class Action
{
    private $page_size   = 6;
    private $member_id   = '';
    private $member_info = NULL;

    public function __construct()
    {
        $this->member_id   = member_helper::get_member_id();
        $this->member_info = member_helper::get_member_info($this->member_id);

    }

    public function load_more_data()
    {

        $status = tools_helper::post('status', '2');
        $date   = tools_helper::post('date', date('Ymd'));
        $page   = tools_helper::post('page', 1);

        $page  = ($page-1)*$this->page_size;

        $filter = array();

        //权限
        if ($this->member_info['res_name'] == 'province') {
            $filter['province_id']      = $this->member_info['res_id'];

        } else if ($this->member_info['res_name'] == 'city') {
            $filter['city_id']      = $this->member_info['res_id'];

        } else if ($this->member_info['res_name'] == 'business_hall') {
            $filter['business_id']      = $this->member_info['res_id'];
        }

        $filter['status'] = 1;

        $device_list = _model('screen_device')->getList($filter , ' LIMIT '.$page.','.$this->page_size);

        if (!$device_list) {
            return array('info' => 'no', 'errno' => 10000);
        }

        foreach ($device_list as $k => &$v) {
            //在线数据查询添加条件  时间和imei
            $filter['day']  = $date;
            $filter['device_unique_id'] = $v['device_unique_id'];

            $device_is_online_info = _model('screen_device_online_stat_day')->read($filter);

            //有设备，但在线无数据
            if (!$device_is_online_info) {
                $v['is_online']   = '离线';
                $v['online_time'] = '0秒';
                continue;
            }

            //判断在线
            if (($device_is_online_info['update_time']+ 60) > time() ) {
                $v['is_online']   = '在线';
                $v['online_time'] = screen_helper::handle_hours_mins_secs($device_is_online_info['online_time']);

            } else {
                $v['is_online']   = '离线';
                $v['online_time'] = screen_helper::handle_hours_mins_secs($device_is_online_info['online_time']);
            }
        }

        $html = '';

        foreach ($device_list as $v) {
            $html .= '<li style="padding: .27rem; background-color: #fff; margin-bottom: .173rem; position: relative;" class="all status-all ';

            if ( $v['is_online'] == '在线') {
                $html .= 'status-online online';
            } else if ($v['is_online'] == '离线') {
                 $html .= 'status-offline offline';
            }

            $html .= '">';
            $html .= '<div class="con">';

            if ($v['phone_name_nickname']){
                $phone_name = $v['phone_name_nickname'];
            } else {
                $phone_name = $v['phone_name'];
            }

            if ($v['phone_version_nickname']){
                $phone_version = $v['phone_version_nickname'];
            } else {
                $phone_version = $v['phone_version'];
            }

            $html .= '<p>机型：<span>'.$phone_name.'&nbsp;'.$phone_version.'</span></p>';
            $html .= '<p>IMEI：<span>'.$v['imei'].'</span></p>';
            $html .= ' <p>上线时间：<span>'.$v['add_time'].'</span></p>';
            $html .= '</div>';
            $html .= '<div class="infos">';

            if ($v['is_online'] == '在线') {
                $html .= '<p class="status">在线 </p>';
            } else if ($v['is_online'] == '离线') {
                $html .= '<p class="status">离线 </p>';
            }

            $html .= '<p class="time">'.$v['online_time'].'</p>';
            $html .= '</div>';
            $html .= '</li>';
        }

        return array('info' => 'ok', 'errno' => 0, 'list' => $html);
    }
}