<?php
/**
  * alltosun.com 专柜管理 shoppe.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2017年8月18日 下午2:57:09 $
  * $Id$
  */
class Action
{
    public $member_info;
    public $member_id;
    public $default_phone_names = array(
            '三星',
            '华为',
            '苹果',
            'OPPO',
            'VIVO'
    );

    public $from = '';
    public $from_rfid_id = 0;

    public function __construct()
    {
        //登录信息
        $this->member_id   = member_helper::get_member_id();

        if ($this->member_id) {
            $member_info = member_helper::get_member_info($this->member_id);
            if ($member_info) {
                $this->member_info     = $member_info;
                Response::assign('member_info', $this->member_info);
            }
        }

        //来源字段， 是否回调rfid添加页
        $this->from             = tools_helper::Get('from', '');

        //跳转rfid所需字段
        $this->from_rfid_id    = tools_helper::Get('id', 0);

        Response::assign('id', $this->from_rfid_id);
        Response::assign('from', $this->from);

    }

    public function __call($action='', $params=array())
    {

        if ($this->member_info['res_name'] != 'business_hall') {
            return '您没有权限';
        }

        //查询此厅专柜列表
        $shoppe_list = shoppe_helper::get_business_hall_shoppe($this->member_info['res_name'], $this->member_info['res_id']);

        Response::assign('shoppe_list', $shoppe_list);
        Response::display('admin/rfid/shoppe/index.html');

    }

    /**
     * rfid绑定专柜
     */
    public function bind_shoppe()
    {

        //查询此厅专柜
        if ($this->member_info['res_name'] != 'business_hall') {
            return '您没有权限';
        }

        $shoppe_list = shoppe_helper::get_business_hall_shoppe($this->member_info['res_name'], $this->member_info['res_id']);

        Response::assign('shoppe_list', $shoppe_list);

        Response::display('admin/rfid/shoppe/select.html');

    }

    /**
     * 添加专柜
     */
    public function add()
    {

        if ($this->member_info['res_name'] != 'business_hall') {
            return '您没有权限';
        }

        $phone_names = _model('rfid_phone')->getFields('name', array(1=>1), ' GROUP BY `name`');
        if (!$phone_names) {
            $phone_names = $this->default_phone_names;
        }

        $new_phone_names = array();
        foreach ($phone_names as $v) {
            $new_phone_names[] = array(
                    'id' => $v,
                    'value' => $v
            );
        }

        Response::assign('phone_names', json_encode($new_phone_names));
        Response::display('admin/rfid/shoppe/add.html');
    }

    /**
     * 保存专柜
     */
    public function save()
    {

        if ($this->member_info['res_name'] != 'business_hall') {
            return '您没有权限';
        }

        $phone_name = tools_helper::Post('phone_name', '');
        $shoppe_name = tools_helper::Post('shoppe_name', '');

        if (!$phone_name) {
            return '专柜品牌不能为空';
        }

        if (!$shoppe_name) {
            return '专柜名称不能为空';
        }

        $business_hall_info = _uri($this->member_info['res_name'], $this->member_info['res_id']);

        if (!$business_hall_info) {
            return '未知的登录管理员';
        }


        $shoppe_table = 'rfid_shoppe';


        $new_data = array(
                'province_id'   => $business_hall_info['province_id'],
                'city_id'       => $business_hall_info['city_id'],
                'area_id'       => $business_hall_info['area_id'],
                'business_id'   => $business_hall_info['id'],
                'phone_name'    => $phone_name,
                'shoppe_name'   => $shoppe_name,
                'add_from'      => 1  //来源是RFID
        );

        _widget('shoppe')->add_shoppe($new_data);

        //验证来源，如果是rfid添加页，则直接跳转到来源处
        if ($this->from == 'rfid_add'){
            $url = AnUrl("e/admin/rfid/add?id={$id}&shoppe_id={$result}");
        //默认跳到专柜管理页
        } else {
            $url = AnUrl("e/admin/rfid/shoppe");
        }

        Response::redirect($url);
        Response::flush();
    }
}