<?php
/**
 * alltosun.com  3days.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-12-15 下午5:00:01 $
 * $Id$
 */
class Action
{
    // private $member_id   = 0;
    private $member_info = array();
    private $user_number = '';

    public function __construct()
    {
        //验证secret
        api_helper::check_token('post', $_POST);

        // $this->member_id = member_helper::get_member_id();
        // 获取渠道码
        $this->user_number = tools_helper::post('business_code', '');

        if ( !$this->user_number ) {
            api_helper::return_data(1, '请上传营业厅渠道码');
        }

        $this->member_info =_model('member')->read(array('member_user' => $this->user_number));

        if ( !$this->member_info ) {
            api_helper::return_data(1, '未找到对应渠道码的账号信息');
        }
    }

    public function data()
    {
        $date = date('Ymd');

        $sql  = "SELECT device_unique_id,COUNT(*) AS offline_num ";
        $sql .= "FROM `screen_everyday_offline_record` "; 
        $sql .= "WHERE all_day=1 ";

        // 权限
        if ( $this->member_info['res_name'] != 'group' ) {
            $res_name = $this->member_info['res_name'].'_id';
            $sql .= "AND {$res_name}={$this->member_info['res_id']} ";
        }

        // 离线的时间
        $sql .= "AND date < {$date} ";
        $sql .= "GROUP BY device_unique_id ";
        $sql .= "ORDER BY `offline_num` DESC ";

        $offline_info = _model('screen_everyday_offline_record')->getAll($sql);

        // 该账号下没有离线设备
        if ( !$offline_info ) api_helper::return_data(0, 'success', array());

        $phone_name = screen_helper::get_phone_nickname('name', $offline_info[0]['device_unique_id']);
        $phone_version = screen_helper::get_phone_nickname('version', $offline_info[0]['device_unique_id']);

        $data = array('brand' => $phone_name, 'version' => $phone_version, 'days' => $offline_info[0]['offline_num']);

        api_helper::return_data(0, 'success', $data);
    }

}