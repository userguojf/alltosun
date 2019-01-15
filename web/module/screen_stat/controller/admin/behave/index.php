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

        $filter = $list = [];

        $time = time();
        $date = date('Ymd', $time);
        $group_by_id = 'province_id';

        $device_sql  = "SELECT {$group_by_id}, COUNT(*) AS num FROM `screen_device`";
        $device_sql .=" WHERE status=1 GROUP BY {$group_by_id} ORDER BY $group_by_id  ASC ";
        $device_list = _model('screen_device')->getAll($device_sql);

        foreach ($device_list as $k => $v) {
            if ( !isset($list['all']['all_device_num'])) {
                $list['all']['all_device_num'] = 0;
            }

            $list['all']['all_device_num'] += $v['num'];
            $list[$v['province_id']]['all_device_num'] = $v['num'];
        }

        foreach ( $list as $k => &$v ) {
            $field_filter = [];
            if ( 'all' == $k  ) {
                $field_filter = [1 => 1];
            } else {
                $field_filter['province_id'] = $k;
            }

            $v['today_unusual_device_num'] = $v['seven_unusual_device_num'] = 0;

            // 可监测的设备数量
            $today_device_unique_id_list= _model('screen_daily_hebave_device_record')->getList(
                    $field_filter, " GROUP BY `device_unique_id` ");

            $v['monitor_device_num'] = count($today_device_unique_id_list);

            // 今日目前为止异常设备数量
            foreach ($today_device_unique_id_list as $val) {
                $online_result = $this->get_ten_minute_online($val['business_hall_id'], $val['device_unique_id']);
                if ( !$online_result ) ++ $v['today_unusual_device_num'];
            }

            // 近7天的异常设备数量
            foreach ($today_device_unique_id_list as $vv) {
                $seven_list = _model('screen_daily_behave_happening_record')->getList(
                        array(
                                'device_unique_id' => $vv['device_unique_id'],
                                'record_day <' => date('Ymd', $time),
                                'record_day >' => date('Ymd', $time - 9 * 24 * 3600),
                        )
                );

                // 正常次数
                $is_usual =  0;
                foreach ($seven_list as  $vvv) {
                    if ( !$vvv['auto_start'] && ( $vvv['heart_hour_num'] > 7 || $vvv['heart_hour_num'] > $vvv['time_num']) ) {
                        ++ $is_usual;
                    }
                }

                // 7天有4天就正常了
                if ( $is_usual < 4 ) {
                    $v['seven_unusual_device_num'] += 1;;
                }
            }

            if ( $k == 'all') {
                $v['name'] = '全国';
                $v['res_id'] = 0;
            } else {
                $v['name'] = business_hall_helper::get_info_name('province', array('id'=> $k), 'name');
                $v['res_id'] = $k;
            }
        }
        unset($v);

        Response::assign('page' , $page);
        Response::assign('list', $list);
        Response::assign('type', $type);

        Response::assign('search_filter' , $search_filter);
        Response::display('admin/behave/list.html');
    }

    public function unusual_device()
    {
        $page          = Request::get('page_no' , 1) ;
        $res_id        = Request::get('res_id' , 0) ;
        $search_filter = Request::Get('search_filter', array());

        $list = $filter = [];
        $time = time();

        if ($res_id) {
            $filter['province_id'] = $res_id;
        }

        if (isset($search_filter['date']) && $search_filter['date']) {
            $filter['record_day'] = date('Ymd', strtotime($search_filter['date']));
        }

        if (isset($search_filter['business_hall_title']) && $search_filter['business_hall_title']) {
            $business_hall_info = _model('business_hall')->read(array('title' => trim($search_filter['business_hall_title'])));

            if (!$business_hall_info) return '请输入正确的营业厅名称';

            $filter['business_hall_id'] = $business_hall_info['id'];
        }

        if (isset($search_filter['device_unique_id']) && $search_filter['device_unique_id']) {
            $filter['device_unique_id'] = $search_filter['device_unique_id'];
        }

        if ( !$filter ) $filter = [1 => 1];

//         $count_list = _model('screen_daily_hebave_device_record')->getList($filter ,  " GROUP BY device_unique_id ");

//         $count = count($count_list);

//         if ($count) {
//             $pager  = new Pager($this->per_page);
            $list   = _model('screen_daily_hebave_device_record')->getList($filter ,  " GROUP BY device_unique_id "); 
                    //.  $pager->getLimit($page));

//             if ($pager->generate($count,$page)) {
//                 Response::assign('pager', $pager);
//             }
//         }
        foreach ($list as $k => $v) {
                $seven_list = _model('screen_daily_behave_happening_record')->getList(
                        array(
                                'device_unique_id' => $v['device_unique_id'],
                                'record_day <' => date('Ymd', $time),
                                'record_day >' => date('Ymd', $time - 9 * 24 * 3600),
                        )
                );

                // 正常次数
                $is_usual =  0;
                foreach ($seven_list as  $vvv) {
                    if ( !$vvv['auto_start'] && ( $vvv['heart_hour_num'] > 7 || $vvv['heart_hour_num'] > $vvv['time_num']) ) {
                        ++ $is_usual;
                    }
                }

                // 7天有4天就正常了
                if ( $is_usual > 3 ) {
                    unset($list[$k]);
                }
            }

        Response::assign('count' , count($list));
        Response::assign('page' , $page);
        Response::assign('list', $list);

        Response::assign('search_filter' , $search_filter);
        Response::display('admin/behave/unusual_list.html');
    }


    public  function unusual_detail()
    {
        $device_unique_id = Request::Get('device_unique_id', '');
        $type          = Request::Get('type', 0);
        $search_filter = Request::Get('search_filter', array());
        $page          = Request::get('page_no' , 1) ;

        if ( $device_unique_id ) $search_filter['device_unique_id'] = $device_unique_id;

        if ( isset($search_filter['device_unique_id']) && $search_filter['device_unique_id'] ) {
            $device_unique_id = $search_filter['device_unique_id'];
        }

        if (!$device_unique_id &&  (!isset($search_filter['device_unique_id']) || !$search_filter['device_unique_id'])) {
            return '参数错误';
        }

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
        }

        Response::assign('device_unique_id' , $device_unique_id);
        Response::assign('count' , $count);
        Response::assign('page' , $page);
        Response::assign('list', $list);
        Response::assign('type', $type);

        Response::assign('search_filter' , $search_filter);
        Response::display('admin/behave/unusual_device_detail.html');
    }

    /**
     * 代码块
     * @param unknown $business_hall_id
     * @param unknown $device_unique_id
     */
    public function get_ten_minute_online($business_hall_id, $device_unique_id)
    {
        $filter = [
                'business_id'      => (int)$business_hall_id,
                'device_unique_id' => $device_unique_id,
                'day'              => (int)date('Ymd'),
                'add_time' => ['$gt' => date('Y-m-d H:i:s', time() - 30 * 60)]
        ];

        $list = _mongo('screen', 'screen_device_online')->findOne($filter);

        if ( $list['_id'] ) {
            return true;
        }

        return false;
    }

    public function today_unusual()
    {
        $page          = Request::get('page_no' , 1) ;
        $res_id        = Request::get('res_id' , 0) ;
        $search_filter = Request::Get('search_filter', array());

        $device_unique_ids = $filter = [];
        $time = time();

        if (isset($search_filter['business_hall_title']) && $search_filter['business_hall_title']) {
            $business_hall_info = _model('business_hall')->read(array('title' => trim($search_filter['business_hall_title'])));
            if (!$business_hall_info) return '请输入正确的营业厅名称';
            $filter['business_hall_id'] = $business_hall_info['id'];
        }

        if (isset($search_filter['device_unique_id']) && $search_filter['device_unique_id']) {
            $filter['device_unique_id'] = $search_filter['device_unique_id'];
        }

        if ($res_id) {
            $filter['province_id'] = $res_id;
        }

        if ( !$filter ) {
            $filter = [1 => 1];
        }

        $today_device_unique_id_list= _model('screen_daily_hebave_device_record')->getList(
                $filter, " GROUP BY `device_unique_id` ");

        // 今日目前为止异常设备数量
        foreach ($today_device_unique_id_list as $val) {
            $online_result = $this->get_ten_minute_online($val['business_hall_id'], $val['device_unique_id']);
            if ( !$online_result ) array_push($device_unique_ids, $val['device_unique_id']);
        }

        $param = ['device_unique_id' => $device_unique_ids, 'status' => 1];
        $count = _model('screen_device')->getTotal($param);

        if ($count) {
            $pager  = new Pager($this->per_page);
            $list   = _model('screen_device')->getList($param, $pager->getLimit($page));

            if ($pager->generate($count,$page)) {
                Response::assign('pager', $pager);
            }
        }

        Response::assign('count' , $count);
        Response::assign('page' , $page);
        Response::assign('list', $list);

        Response::assign('search_filter' , $search_filter);
        Response::display('admin/behave/today_unusual_list.html');
    }
    
    public function monitor_device()
    {
        $page          = Request::get('page_no' , 1) ;
        $res_id        = Request::get('res_id' , 0) ;
        $search_filter = Request::Get('search_filter', array());

        $device_unique_ids = $filter = [];

        if (isset($search_filter['business_hall_title']) && $search_filter['business_hall_title']) {
            $business_hall_info = _model('business_hall')->read(array('title' => trim($search_filter['business_hall_title'])));
            if (!$business_hall_info) return '请输入正确的营业厅名称';
            $filter['business_hall_id'] = $business_hall_info['id'];
        }

        if (isset($search_filter['device_unique_id']) && $search_filter['device_unique_id']) {
            $filter['device_unique_id'] = $search_filter['device_unique_id'];
        }

        if ($res_id) {
            $filter['province_id'] = $res_id;
        }

        if ( !$filter ) {
            $filter = [1 => 1];
        }

        $today_device_unique_id_list= _model('screen_daily_hebave_device_record')->getList(
                $filter, " GROUP BY `device_unique_id` ");

        // 今日目前为止异常设备数量
        foreach ($today_device_unique_id_list as $val) {
            array_push($device_unique_ids, $val['device_unique_id']);
        }

        $param = ['device_unique_id' => $device_unique_ids, 'status' => 1];
        $count = _model('screen_device')->getTotal($param);

        if ($count) {
            $pager  = new Pager($this->per_page);
            $list   = _model('screen_device')->getList($param, $pager->getLimit($page));

            if ($pager->generate($count,$page)) {
                Response::assign('pager', $pager);
            }
        }

        Response::assign('count' , $count);
        Response::assign('page' , $page);
        Response::assign('list', $list);

        Response::assign('search_filter' , $search_filter);
        Response::display('admin/behave/monitor_device_list.html');
    
    }
}