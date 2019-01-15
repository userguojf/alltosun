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

        $page        = tools_helper::get('page_no' , 1) ;
        $content_id  = tools_helper::get('content_id', 0);
        $action_type = tools_helper::get('res_name', '');
        $action_id   = tools_helper::get('res_id', 0);
        $search_filter = tools_helper::get('search_filter', array());

        $action_type = $action_type ? $action_type : $this->res_name;
        $action_id = $action_id ? $action_id : $this->res_id;


        // 表名
        if ( $action_type == 'group' ) {
            $table = 'screen_roll_count_stat';

        } else if ( $action_type == 'province' ) {
            $table = 'screen_roll_province_stat';
            $filter['province_id'] = $action_id;

        } else if ( $action_type == 'city' ) {
            $table = 'screen_roll_city_stat';
            $filter['city_id'] = $action_id;

        } else if ( $action_type == 'business_hall' ) {
            $table = 'screen_roll_business_stat';
            $filter['business_hall_id'] = $action_id;

        } else {
            return  '暂无统计数据';
        }

        $filter = $list = array();

        if ( $content_id ) {
           $filter['content_id'] = $content_id;
        } else {
            return '请选择内容ID';
        }
// http://mac.pzclub.cn/screen_stat/admin/roll_stat
// ?content_id=323
// &res_name=business_hall
// &res_id=0
// &search_filter[start_time]=2018-04-01
// &search_filter[end_time]=2018-05-01
        
        if ( isset($search_filter['date_type']) && $search_filter['date_type'] ) {
            if ( 1 == $search_filter['date_type'] ) {
                $search_filter['start_time'] = $search_filter['end_time'] = date('Y-m-d');

            } else if ( 2 == $search_filter['date_type'] ) {
                $search_filter['start_time'] = date('Y-m-d',time() - 7 * 24 * 3600);
                $search_filter['end_time']   = date('Y-m-d');

            } else if ( 3 == $search_filter['date_type'] ) {
                $search_filter['start_time'] = date('Y-m-d',time() - 30 * 24 * 3600);
                $search_filter['end_time']   = date('Y-m-d');

            } else {
                $search_filter['start_time'] = $search_filter['end_time'] = date('Y-m-d');
            }
        }

        if ( isset($search_filter['start_time']) && $search_filter['start_time'] ) {
            $filter['date >='] = date('Ymd', strtotime($search_filter['start_time']));
        } else {
            $filter['date >='] = date('Ymd', time() - 30 * 24 * 3600);
            $search_filter['start_time'] = date('Y-m-d', time() - 30 * 24 * 3600);
        }

        if ( isset($search_filter['end_time']) && $search_filter['end_time'] ) {
            $filter['date <='] = date('Ymd', strtotime($search_filter['end_time']));
        } else {
            $filter['date <='] = $search_filter['end_time'] = date('Ymd', time());
            $search_filter['end_time'] = date('Y-m-d', time());
        }

        if ( strtotime($search_filter['start_time']) + 30 * 24 * 3600 == strtotime($search_filter['end_time']) ) {
            $search_filter['date_type'] = 3;
        }

        if ($search_filter['start_time'] == $search_filter['end_time']) {
            $search_filter['date_type'] = 1;
        }

        //权限控制
//         if ('group' == $this->res_name) {

//         } else if ('province' == $this->res_name) {
//             $filter['province_id'] = $this->res_id;

//         } else if ('city' == $this->res_name) {
//             $filter['city_id'] = $this->res_id;

//         } else if ('area' == $this->res_name) {
//             $filter['area_id'] = $this->res_id;

//         } else if ('business_hall' == $this->res_name) {
//             $filter['business_id'] = $this->res_id;
//         }

        if (!$filter) {
            $filter = array(1 => 1);
        }

        $count = _model($table)->getTotal($filter);
        if ($count) {
            $pager  = new Pager($this->per_page);
            $list   = _model($table)->getList($filter , " ORDER BY `id` DESC " . $pager->getLimit($page));

            if ($pager->generate($count,$page)) {
                Response::assign('pager', $pager);
            }
        }

        Response::assign('count' , $count);
        Response::assign('page' , $page);

        Response::assign('action_type', $action_type);
        Response::assign('action_id', $action_id);

        Response::assign('content_id', $content_id);
        Response::assign('search_filter', $search_filter);
        Response::assign('list', $list);
        Response::display('admin/roll_stat/stat_count.html');
    }

    public function stat_business()
    {
        $table = 'screen_roll_business_stat';

        $page          = Request::get('page_no' , 1) ;
        $content_id    = tools_helper::get('content_id', 0);
        $date          = tools_helper::get('date', date('Ymd'));

        $filter = $list = array();

        if ($content_id) {
            $filter['content_id'] = $content_id;
        } else {
            return '请选择内容ID';
        }

        $filter['date'] = $date;

        $count = _model($table)->getTotal($filter);
        if ($count) {
            $pager = new Pager($this->per_page);
            $list  = _model($table)->getList($filter , $pager->getLimit($page));

            if ($pager->generate($count,$page)) {
                Response::assign('pager', $pager);
            }
        }

        Response::assign('count' , $count);
        Response::assign('page' , $page);
        Response::assign('date' , $date);

        Response::assign('content_id', $content_id);
        Response::assign('list', $list);
        Response::display('admin/roll_stat/stat_business.html');
    }


    public function stat_device()
    {
        $table = 'screen_roll_device_stat';

        $page       = Request::get ( 'page_no', 1 );
        $content_id = tools_helper::get ( 'content_id', 0 );
        $date       = tools_helper::get ( 'date', date ( 'Ymd' ) );

        $action_type = tools_helper::get('res_name', '');
        $action_id   = tools_helper::get('res_id', 0);

        $action_type = $action_type ? $action_type : $this->res_name;
        $action_id = $action_id ? $action_id : $this->res_id;

        $filter = $list = array ();
        // 表名
        if ( $action_type == 'group' ) {

        } else if ( $action_type == 'province' ) {
            $filter['province_id'] = ( int ) $action_id;

        } else if ( $action_type == 'city' ) {
            $filter['city_id'] = ( int ) $action_id;

        } else if ( $action_type == 'business_hall' ) {
            $filter['business_hall_id'] = ( int ) $action_id;

        } else {
            return  '暂无统计数据';
        }

        if ( $content_id ) {
            $filter ['content_id'] = ( int ) $content_id;
        } else {
            return '请传轮播内容ID';
        }

        $filter ['date'] = ( int ) $date;

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
        Response::display ( 'admin/roll_stat/stat_device.html' );
    }

    public function record()
    {
        $table = 'screen_content_click_record';

        $page       = Request::get ( 'page_no', 1 );
        $content_id = tools_helper::get ( 'content_id', 0 );
        $date       = tools_helper::get ( 'date', '' );

        $filter = $list = array ();

        if ($content_id) {
            $filter ['content_id'] = ( int ) $content_id;
        }

        if ($date) {
            $filter ['day'] = ( int ) $date;
        }

        // if (!$filter) {
        // $filter = array(1 => 1);
        // }
        // $filter = $this->get_mongodb_filter($filter);

        $count = _mongo ( 'screen', 'screen_content_click_record' )->count ( $filter );

        if ($count) {
            // MongoDB分页类
            $pager = new MongoDBPager ( $this->per_page );
            $page_filter = $pager->getLimit ( $page );
            $page_filter['sort'] = array('_id' => -1);

            $list  = _mongo ( 'screen', 'screen_content_click_record' )->find ( $filter, $page_filter );

            $list = $list->toArray ();

            if ($pager->generate ( $count )) {
                Response::assign ( 'pager', $pager );
            }
        }

        Response::assign ( 'count', $count );
        Response::assign ( 'page', $page );

        Response::assign ( 'list', $list );
        Response::display ( 'admin/roll_stat/record.html' );
    }

    private function get_mongodb_filter($filter)
    {
        $new_filter = array();
    
        if (isset($filter[1])) {
            $filter = array();
        }
    
        foreach ($filter as $k => $v) {
    
            if (is_array($v)) {
                $new_filter[trim($k)] = array('$in' => $v);
            } else if (is_numeric($v)) {
                $new_filter[trim($k)] = (int)$v;
            } else {
    
                if ( strpos($k, '<=') ) {
                    $new_filter[trim(str_replace('<=', '', $k))]['$lte'] = $v;
                } else if ( strpos($k, '>=') ) {
                    $new_filter[trim(str_replace('>=', '', $k))]['$gte'] = $v;
                } else if (strpos($k, '<')) {
                    $new_filter[trim(str_replace('<', '', $k))]['$lt'] = $v;
                } else if (strpos($k, '>')) {
                    $new_filter[trim(str_replace('<', '', $k))]['$gt'] = $v;
                } else {
                    $new_filter[trim($k)] = $v;
                }
            }
    
        }
    
        return $new_filter;
    }
}