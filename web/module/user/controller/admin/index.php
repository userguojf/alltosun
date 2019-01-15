<?php
/**
 * alltosun.com 会员列表 index.php
 * ============================================================================
 * 版权所有 (C) 2009-2014 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址:   http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 勾国印 (gougy@alltosun.com) $
 * $Date: 2014-5-28 下午6:26:51 $
 * $Id$
*/

class Action
{
    private $per_page = 20;
    private $time     = '';
    private $member_id  = 0;
    private $member_res_name = '';
    private $member_res_id   = 0;
    private $ranks           = 0;


    public function __construct()
    {
        $this->time        = date('Y-m-d H:i:s',time());
        $this->member_id   = member_helper::get_member_id();

        $member_info = member_helper::get_member_info($this->member_id);

        if ($member_info) {
            $this->member_res_name = $member_info['res_name'];
            $this->member_res_id   = $member_info['res_id'];
            $this->ranks           = $member_info['ranks'];
        }

        Response::assign('curr_member_ranks', $this->ranks);
    }

    public function __call($action = '', $params = array())
    {
        $type           = Request::Get('type', '');
        $search_filter  = $filter = array();
        $search_filter  = Request::Get('search_filter', array());
        $page_no = Request::Get('page_no',1);

        $default_value  = array();
        $search_filter  = set_search_filter_default_value($search_filter, $default_value);

        if (isset($search_filter['phone']) && $search_filter['phone']) {
            $filter['phone'] = trim($search_filter['phone']);
        }

        if (isset($search_filter['add_time']) && $search_filter['add_time']) {
            $filter['add_time'] = $search_filter['add_time'];
        }

        if (isset($search_filter['last_login_time']) && $search_filter['last_login_time']) {
            $filter['last_login_time'] = $search_filter['last_login_time'];
        }

        // 如果不是超级管理员，需分权限来能看到的用户
        if ($this->ranks > 1) {

            $business_hall_filter = array(
                $this->member_res_name.'_id'   => $this->member_res_id
            );

            $user_ids = _model('user_business_hall_num')->getFields('user_id', $business_hall_filter);

            if ($user_ids) {
                $filter['id'] = $user_ids;
            } else {
                $filter['id'] = -1;
            }
        }

        if (empty($filter)) {
            $filter = array( '1' => 1);
        }

        if ( $type == 'new_user' ) {
            $filter = array(
                'add_time >=' => date('Y-m-d').' 00:00:00',
                'add_time <=' => date('Y-m-d').' 23:59:59'
            );
            $order = ' ORDER BY `add_time` DESC, `id` DESC ';
        } else {
            $order = ' ORDER BY `last_login_time` DESC, `id` DESC ';
        }

        $list = array();
        $count = _model('user')->getTotal($filter);

        if ($count) {
            $pager = new Pager($this->per_page);
            if ($pager->generate($count)) {
                Response::assign('pager', $pager);
            }
            Response::assign('count', $count);

            $list  = _model('user')->getList($filter, ' '.$order.' '.$pager->getLimit($page_no));
        }

        Response::assign('type', $type);
        Response::assign('list', $list);
        Response::assign('search_filter', $search_filter);
        Response::assign('page', $page_no);

        Response::display("admin/user_list.html");
    }


    /**
     * 添加新的用户
     */
    public function add()
    {
        //获取地区列表

        $pro_list = _model('business_hall')->getList(
                array(
                        'ditch_num' => 0,
                        'p_id >' =>0
                ),
                ' ORDER BY `id` ASC '
        );

        Response::assign('pro_list',$pro_list);
        Response::display("admin/user_add.html");
    }

    /*
     * 保存数据
     */
    public function save()
    {
        $p_id       = Request::Post('p_id', 0);
        $c_id       = Request::Post('c_id', 0);
        $bus_id     = Request::Post('bus_id',0);

        $user_name  = trim(Request::Post('user_name', ''));
        $phone_num  = trim(Request::Post('phone_num', ''));


        if (!$user_name) {
            return '请填写用户名';
        }

        if (!$phone_num) {
            return '请填写手机号';
        }

        if (!$p_id) {
            return '请选择区域';
        }

        $user_info = _model('user')->read(array('phone_num' => $phone_num));
        if($user_info) {
            return "该手机号已经存在！";
        }

        $user_id = _model('user')->create(
               array(
                    'user_name'    => $user_name,
                    'phone_num'    => $phone_num
                )
            );

        if ($user_id) {

            $arr['user_id']        = $user_id;
            if ($bus_id) {
                $arr['business_hall_id'] = $bus_id;
            } else if ($c_id) {
                $arr['business_hall_id'] = $c_id;
            } else if ($p_id) {
                $arr['business_hall_id'] = $p_id;
            }

            _model('user_privilege')->create($arr);

        }

        return array("用户添加成功", 'success', AnUrl('user/admin'));
    }
}
?>