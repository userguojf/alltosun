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
    private $member_id          = 0;
    private $member_info        = '';
    private $business_hall_info = '';
    private $login_url          = '';

    public function __construct()
    {
        $this->member_id    = member_helper::get_member_id();
        $this->member_info  = member_helper::get_member_info($this->member_id);

        Response::assign('footer' , true);
        Response::assign('member_info' , $this->member_info);
    }

    public function __call($action = "", $param = array())
    {

        $this->login_url = AnUrl('liangliang/e_login').'?redirect_url='.AnUrl('e/admin/rfid/add').'/'.$action;

        //页面重新授权
        if (is_weixin() && !$this->member_id) {
            qydev_helper::check_qydev_auth($this->login_url);
        }

        //不是企业号或者企业号两次授权失败都去登录
        if (!$this->member_id) {
            Response::redirect($this->login_url);
        }

        //修改ID
        $id        = Request::Get('id', 0);
        //选择柜台返回ID
        $shoppe_id = Request::Get('shoppe_id', 0);

        $rfid_info = $filter = array();
        //添加修改集一身的柜台ID
        $guitai_id = '';
        //标签ID
        $label_id  = '';

        //扫一扫进来了
        if ($action && $action != 'index') {
            $label_id  = $action;
            $info = _model('rfid_label')->read(array('label_id' => $label_id));

            if ($info) {
                $rfid_info = $info;
                $id        = $info['id'];
            }
        }

        if (!$rfid_info && $id) {
            //rfid_label表信息
            $rfid_info = _model('rfid_label')->read(array('id' => $id));
            if (!$rfid_info) return '非法操作';

            $label_id  = $rfid_info['label_id'];
        }

        //
        if (!$shoppe_id && isset($rfid_info['shoppe_id'])) {
            $filter    = array('id' => $rfid_info['shoppe_id']);
            $guitai_id = $rfid_info['shoppe_id'];
        }

        if ($shoppe_id) {
            $filter    = array('id' => $shoppe_id);
            $guitai_id = $shoppe_id;
        }

        //查看柜台名称
        $shoppe_name = shoppe_helper::get_shoppe_info($filter, 'shoppe_name');
        //查看是否柜台
        $is_shoppe = shoppe_helper::business_hall_is_exists_shoppe( $this->member_info['res_name'],  $this->member_info['res_id']);

        //修改/添加
        Response::assign('id' , $id);
        Response::assign('is_shoppe' , $is_shoppe);
        Response::assign('guitai_id' , $guitai_id);
        Response::assign('shoppe_name' , $shoppe_name);
        Response::assign('rfid_info' , $rfid_info);
        Response::assign('label_id' ,  $label_id);

        Response::display('admin/rfid/device_add.html');
    }
}