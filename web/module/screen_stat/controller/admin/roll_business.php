<?php
/**
 * alltosun.com 轮播图的数量列表  record.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-9-16 下午3:10:03 $
 * $Id$
 */
class Action
{

    //操作表
    private $per_page = 20;
    public $member_id = 0;
    public $res_id    = 0;
    public $res_name  = '';
    public $member_info = array();

    public function __construct()
    {
        $this->member_id   = member_helper::get_member_id();
        $this->member_info = member_helper::get_member_info($this->member_id);

        if (!$this->member_info) {
            return '您无权访问此页面';
        }

        $this->res_name = $this->member_info['res_name'];
        $this->res_id   = $this->member_info['res_id'];

        Response::assign('member_info', $this->member_info);
    }

    public function __call($action = '', $param = array())
    {
        $table = 'screen_roll_device_stat';

        $page       = Request::get ( 'page_no', 1 );
        $content_id = tools_helper::get ( 'content_id', 0 );
        $business_hall_id =tools_helper::get ( 'res_id', 0 );
        $date       = tools_helper::get ( 'date', date ( 'Ymd' ) );

        $filter = $list = array ();

        if (!$business_hall_id) return '请传入营业厅ID';
        if ($content_id) {
            $filter ['content_id'] = ( int ) $content_id;
        } else {
            return '请传轮播内容ID';
        }

        $filter ['date'] = ( int ) $date;

        // if (!$filter) {
        // $filter = array(1 => 1);
        // }

        $count = _mongo ( 'screen', $table )->count ( $filter );

        if ($count) {
            //MongoDB分页类
            $pager = new MongoDBPager( $this->per_page );

            $list  = _mongo ( 'screen', $table )->find ( $filter, $pager->getLimit ( $page ) );

            $list = $list->toArray();

            if ( $pager->generate($count) ) {
                Response::assign( 'pager', $pager );
            }
        }

        Response::assign ( 'count', $count );
        Response::assign ( 'page', $page );

        Response::assign ( 'content_id', $content_id );
        Response::assign ( 'list', $list );
        Response::display ( 'admin/roll_stat/business_hall.html' );
    }

}