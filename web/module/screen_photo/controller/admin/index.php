<?php
/**
  * alltosun.com 套餐图列表 index.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2018年1月22日 上午10:10:31 $
  * $Id$
  */
class Action
{
    private $per_page    = 20;
    private $member_info = array();

    public function __construct()
    {
        $this->member_info = member_helper::get_member_info();
    }

    public function __call($action = '', $param = array())
    {
        if (!$this->member_info) {
            return '请先登录';
        }

        $type          = Request::Get('type', 0);
        $content_id    = Request::Get('content_id', 0);
        $page          = Request::Get('page_no' , 1) ;
        $list          = array();

        $filter        = array();


        if ($content_id) {
            $content_info  = _model('screen_content')->read($content_id);
            if (!$content_info) {
                return '内容不存在或已被删除';
            }
            $filter['content_id']   = $content_id;
            $filter['res_link']     = $content_info['link'];
        //搜索
        } else {

            $search_filter = Request::Get('search_filter', array());

            $default_filter = _widget('screen')->init_filter($this->member_info, $search_filter);

            if (!empty($default_filter['business_hall_id'])) {
                $filter['issuer_res_name']  = 'business_hall';
                $filter['issuer_res_id']    = $default_filter['business_hall_id'];
            } else if (!empty($default_filter['area_id'])) {
                $filter['issuer_res_name']  = 'area';
                $filter['issuer_res_id']    = $default_filter['area_id'];
            } else if (!empty($default_filter['city_id'])) {
                $filter['issuer_res_name']  = 'city';
                $filter['issuer_res_id']    = $default_filter['city_id'];
            } else if (!empty($default_filter['province_id'])) {
                $filter['issuer_res_name']  = 'province';
                $filter['issuer_res_id']    = $default_filter['province_id'];
            }
        }

        // 点击途径
        if ( !$filter ) {
            $filter = array( 1 => 1);
        }

        $order          = " ORDER BY `id` DESC ";
        $count          = _model('screen_content_set_meal')->getTotal($filter);
        $set_meal_list  = array();
        if ($count) {
            $pager = new Pager($this->per_page);
            $set_meal_list = _model('screen_content_set_meal')->getList($filter, $order.$pager->getLimit($page));

            if ($pager->generate($count,$page)) {
                Response::assign('pager', $pager);
            }
        }

        Response::assign('count', $count);
        Response::assign('page', $page);
        Response::assign('set_meal_list', $set_meal_list);
        Response::display("admin/index.html");
    }
}