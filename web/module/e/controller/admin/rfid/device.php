<?php
/**
 * alltosun.com  phone.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-5-28 下午12:00:26 $
 * $Id$
 */

class Action
{
    private $member_id   = 0;
    private $member_info = '';
    private $business_hall_info = '';

    public function __construct()
    {
        $this->member_id    = member_helper::get_member_id();
        $this->member_info  = member_helper::get_member_info($this->member_id);

        Response::assign('footer' , true);
        Response::assign('member_info' , $this->member_info);
    }

    public function __call($action = '', $param = array())
    {
        //登录地址（）
        $url = AnUrl('liangliang/e_login').'?redirect_url='.AnUrl('/e/admin/rfid/device');

        if (is_weixin() && !$this->member_id) {
            qydev_helper::check_qydev_auth($url);
        }

        //不是企业号或者企业号两次授权失败都去登录
        if (!$this->member_id) {
            Response::redirect($url);
        }

        if (!$this->member_id) {
            return '请从正确的入口查看设备';
        }

        $order = ' ORDER BY `id` DESC ';

        $filter = array();

        //权限
        if ($this->member_info['res_name'] == 'group') {
            $filter = array(1 => 1);

        } else if ($this->member_info['res_name'] == 'province') {
            $filter['province_id'] = $this->member_info['res_id'];

        } else if ($this->member_info['res_name'] == 'city') {
            $filter['city_id'] = $this->member_info['res_id'];

        } else if ($this->member_info['res_name'] == 'area') {
            $filter['area_id'] = $this->member_info['res_id'];

        } else if ($this->member_info['res_name'] == 'business_hall') {
            $filter['business_hall_id'] = $this->member_info['res_id'];

        }

        $list = _model('rfid_label')->getList($filter , $order);

        Response::assign('list',$list);
        //Response::display('admin/rfid/device_list.html');
        Response::display('admin/rfid/device_list2.html');
    }

}