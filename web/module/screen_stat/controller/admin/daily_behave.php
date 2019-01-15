<?php
/**
 * alltosun.com  daily_behave.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-7-6 下午2:36:16 $
 * $Id$
 */
class Action
{
    private $per_page  = 25;

    public function __call($action = '', $param = array())
    {

        $type          = Request::Get('type', 0);
        $search_filter = Request::Get('search_filter', array());
        $page          = Request::get('page_no' , 1) ;

        $filter = $list = array();
        $behave_type = '';

        // 默认数据库
        $db_type     = 'mysql';

        if (isset($search_filter['date']) && $search_filter['date']) {
            $filter['record_day'] = date('Ymd', strtotime($search_filter['date']));
        }

        if (isset($search_filter['behave_type']) && $search_filter['behave_type'] != '') {
            if ( 'all' != $search_filter['behave_type'] )  {
                $search_filter['behave_type'] = $behave_type = (int) $search_filter['behave_type'];
                $filter['behave_type'] = $search_filter['behave_type'];
            }
        }

        if (isset($search_filter['business_hall_title']) && $search_filter['business_hall_title']) {
            $business_hall_info = _model('business_hall')->read(array('title' => trim($search_filter['business_hall_title'])));

            if (!$business_hall_info) return '请输入正确的营业厅名称';

            $filter['business_hall_id'] = $business_hall_info['id'];
        } 

        if (isset($search_filter['device_unique_id']) && $search_filter['device_unique_id']) {
            $filter['device_unique_id'] = $search_filter['device_unique_id'];
        }


        // 条件处理
        if ( $behave_type && 7 == $behave_type ){
            unset($filter['behave_type']);
            if ( isset($filter['record_day']) ) {
                $filter['date'] = $filter['record_day'];
                unset($filter['record_day']);
            }
            $filter['type'] = 1;

            $table = 'screen_device_version_record';
            $order = " ORDER BY `date` DESC ";

        } elseif( $behave_type && 6 == $behave_type ) {

            unset($filter['behave_type']);

            if ( isset($filter['record_day']) ) {
                $filter['day'] = $filter['record_day'];
                unset($filter['record_day']);
            }

            $table = 'screen_device_online';
            $order = "add_time" ;
            $db_type = 'mongodb';

        } else {
            $table = 'screen_daily_hebave_record';
            $order = " ORDER BY `time` DESC " ;
        }


        if (!$filter ) $filter = array( 1 => 1 );

        if ( 'mysql' == $db_type ) {
            $count = _model($table)->getTotal($filter);

            if ($count) {
                $pager  = new Pager($this->per_page);
                $list   = _model($table)->getList($filter ,  $order . $pager->getLimit($page));

                if ($pager->generate($count,$page)) {
                    Response::assign('pager', $pager);
                }
            }
        } else {
            $count = _mongo('screen', $table)->count(get_mongodb_filter($filter));
            if ($count) {

                $pager = new MongoDBPager( $this->per_page );

                if ( $pager->generate($count) ) {
                    Response::assign( 'pager', $pager );
                }

                $sql = $pager->getLimit($page);
                $sql['sort'] = ['add_time' => -1];

                $list = _mongo('screen', $table)->find(get_mongodb_filter($filter), $sql);
                if ( $list ) {
                    $list = $list->toArray();
                }
            }
        }

        // 结果处理
        if ( $behave_type && 7 == $behave_type ){
            foreach ($list as $k => &$v) {
                $v['record_day'] = $v['date'];
                $v['behave_type'] = 7;
            }
            unset($v);
        } elseif( $behave_type && 6 == $behave_type ) {
            foreach ($list as $k => &$v) {
                $v['record_day'] = $v['day'];
                $v['business_hall_id'] = $v['business_id'];
                $v['behave_type'] = 6;
            }
//             p($list);
//             exit();
        }

        Response::assign('count' , $count);
        Response::assign('page' , $page);
        Response::assign('list', $list);
        Response::assign('type', $type);

        Response::assign('search_filter' , $search_filter);
        Response::display('admin/daily_behave_list.html');
    }

}