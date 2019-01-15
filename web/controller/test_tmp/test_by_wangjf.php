<?php
/**
  * alltosun.com 测试模块 index.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2016-6-30 下午2:51:47 $
  * $Id$
  */
set_time_limit(0);
class Action
{

    private static $key = 'wangjf';

    public function __construct()
    {
        $key = tools_helper::Get('key', '');

        if ($key != self::$key) {
            return '验证失败';
            exit;
        }

        return false;
    }

    public function exec()
    {

        $sql_filter = tools_helper::Get('sql_filter', array());
        $table = tools_helper::Get('table', '');
        $action = tools_helper::Get('action', '');
        $handle = tools_helper::Get('handle', '');
        $is_json = tools_helper::Get('is_json', 0);


        if (!$sql_filter) {
            $sql_filter[1] = 1;
        }

        if (isset($sql_filter['sql'])) {
            $res = _model($table)->getAll($sql_filter['sql']);
        } else {
            $res = _model($table)->$action($sql_filter, $handle);
        }

        if ($is_json == 1) {
            echo json_encode($res);
        } else {
            p($res);
        }

    }

    private function delete_test()
    {
        $filter = tools_helper::Get('filter', array());
        $table = tools_helper::Get('table', '');
        $action = tools_helper::Get('action', '');
        $wangjf_test = tools_helper::Get('wangjf_test', '');

        if ($wangjf_test == 'wangjf_test') {
            $res = _model($table)->$action($filter);
            p($res);
        }

    }


    /**
     * 删除s
     */
    private function monDelete()
    {
        $filter         = tools_helper::get('filter', array());
        $db             = tools_helper::get('db', '');
        $action         = tools_helper::get('action', '');
        $table          = tools_helper::get('table', '');

        if (!$filter || !$db || !$action || !$table) {
            echo '参数不全';
            exit();
        }

        $filter = get_mongodb_filter($filter);

        $res = _mongo($db,$table)->$action($filter);

        p($res->getDeletedCount());

    }

    /**
     * 更新精品门店
     */
    public function update_boutique()
    {
        $content = file_get_contents(ROOT_PATH.'/images/data/screen/精品门店2018-03-15.csv');
        $content_arr = explode("\r", $content);
        $error_arr = array();

        unset($content_arr[0]);

        foreach ($content_arr as $k => $v) {
            $v = trim(trim($v, ','));
            $arr = explode(',', $v);


            //查询厅信息
            $hall_info = _uri('business_hall', array('user_number' => $arr[2]));

            //本地不存在此厅
            if (!$hall_info) {
                $error_arr[] = $arr;
                continue;
            }


            _model('business_hall')->update($hall_info['id'], array('is_boutique' => 1));
        }

        p($error_arr);
    }

    /**
     * 清空表
     */
//     private function empty_table()
//     {
//         $continue_tables = array(
//                 'screen_device_name_nickname',
//                 'screen_device_version_nickname',
//                 'screen_device_tag',
//                 'screen_version',

//         );
//         $res = _model('screen_device')->getAll('select table_name from information_schema.tables where table_schema="awifi_liangliang" and table_type="base table"');

//         foreach ($res as $v) {
//             if (in_array($v['table_name'], $continue_tables)) {
//                 continue;
//             }

//                 _model($v['table_name'])->getAll('truncate table '.$v['table_name']);


//         }
//     }


    private function task_test()
    {
        $res = _widget('screen_stat')->add_action_stat_by_business_task();
    }

    private function delete_test_data()
    {
        _model('screen')->read(array('experience_time <' => 0));
    }

    private function get_cf_data()
    {
        $start_day = tools_helper::Get('start_day', date('Ymd'));
        $end_day = tools_helper::Get('end_day', date('Ymd'));

        $filter = array('day >=' => $start_day, 'day <=' => $end_day);

        $res = _mongo('screen', 'screen_action_record')->aggregate(array(
                array('$match' => get_mongodb_filter($filter)),
                array('$group' => array(
                        '_id'  => array(
                                'add_time'           =>  '$add_time',
                                'update_time'        =>  '$update_time',
                                'device_unique_id'   =>  '$device_unique_id',
                        ),
                        'count'              => array('$sum' => 1),
                        'add_time'           =>  array('$first' => '$add_time'),
                        'update_time'        =>  array('$first' => '$update_time'),
                        'device_unique_id'   =>  array('$first' => '$device_unique_id'),
                        'business_id'        => array('$first' => '$business_id'),
                ),
                ),
        ))->toArray();

        $cf = array();
        foreach ($res as $k => $v) {
            if ($v['count'] > 1) {
                $cf[] = $v['count'];
            }
        }

        echo count($cf);
    }


    /**
     * 删除重复的动作数据
     */
    private function delete_cf_data()
    {
        //要删除的天数据
        $time     = tools_helper::Get('time', date('Y-m-d H:i:s'));
        $day      = date('Ymd', strtotime($time));

        $filter = array('day' => (int)$day);

        $res = _mongo('screen', 'screen_action_record')->aggregate(array(
                array('$match' => get_mongodb_filter($filter)),
                array('$group' => array(
                        '_id'  => array(
                                'add_time'           =>  '$add_time',
                                'update_time'        =>  '$update_time',
                                'device_unique_id'   =>  '$device_unique_id',
                        ),
                        'count'              => array('$sum' => 1),
                        'add_time'           =>  array('$first' => '$add_time'),
                        'update_time'        =>  array('$first' => '$update_time'),
                        'device_unique_id'   =>  array('$first' => '$device_unique_id'),
                        'business_id'        => array('$first' => '$business_id'),
                ),
                ),
        )
        )->toArray();

        foreach ($res as $k => $v) {
            $v = (array)$v;

            if ($v['count'] == 1) {
                continue;
            }

            $filter = array(
                    'add_time'      => $v['add_time'],
                    'update_time'   => $v['update_time'],
                    'device_unique_id' => $v['device_unique_id'],
                    'business_id'   => $v['business_id']
            );

            //清除重复数据
            $info = (array)(_mongo('screen', 'screen_action_record')->findOne($filter));

            if (!$info) {
                continue;
            }

            $del_filter = $filter;
            $del_filter['_id'] = array('$nin' => array($info['_id']));

            $res = _mongo('screen', 'screen_action_record')->deleteMany($del_filter);
            p($res->getDeletedCount());
        }

        //更新统计
        $this->update_action_data3($day);

        if ($day <= 20171001) {
            echo '已修复至20171001数据';
            exit();
        }

        $url = SITE_URL.'/test_by_wangjf/delete_cf_data?time='.date('Y-m-d H:i:s', strtotime($time)-24*3600);
        echo '<script>window.location.href="'.$url.'"</script>';
    }

    /**
     * 删除凌晨数据
     */
    private function delete_23_7_action_data()
    {
        //要删除的天数据
        $time     = tools_helper::Get('time', date('Y-m-d H:i:s'));
        $day      = date('Ymd', strtotime($time));

        $del_filter = array(
                'add_time >=' => date('Y-m-d 00:00:00'),
                'add_time <' => date('Y-m-d 07:00:00'),
        );

        $res = _mongo('screen', 'screen_action_record')->deleteMany(get_mongodb_filter($del_filter));
        p($res->getDeletedCount());

        $del_filter = array(
                'add_time >=' => date('Y-m-d 23:00:00'),
                'add_time <=' => date('Y-m-d 23:59:59'),
        );

        $res = _mongo('screen', 'screen_action_record')->deleteMany(get_mongodb_filter($del_filter));
        p($res->getDeletedCount());


        //更新统计
        $this->update_action_data3($day);

        if ($day <= 20171001) {
            echo '已修复至20171001数据';
            exit();
        }



        $url = SITE_URL.'/test_by_wangjf/delete_23_7_action_data?time='.date('Y-m-d H:i:s', strtotime($time)-24*3600);
        echo '<script>window.location.href="'.$url.'"</script>';


    }

    /**
     * 修复动作数据
     * 重新统计按设备、按厅的动作数据
     */
    private function update_action_data3($day=0)
    {

        if (!$day) {
            $day     = tools_helper::Get('day', 0);
        }

        echo $day.'<br>';

        if (!$day) {
            echo '请传递天数据';
            exit();
        }

        //查询小于0的数据
        $filter = array(
                'day'   => $day,
        );
//p($filter);exit;
        $action_list = _mongo('screen', 'screen_device_stat_day')->find(get_mongodb_filter($filter))->toArray();

        foreach ($action_list as $k => $v) {
            $v = (array)$v;

            $stat_filter = array(
                    'day'                   => $v['day'],
                    'device_unique_id'      => $v['device_unique_id'],
                    'type'                  => 2
            );

            //查询
            $action_num         = _mongo('screen', 'screen_action_record')->count($stat_filter);
            $record_list        = _mongo('screen', 'screen_action_record')->find($stat_filter);
            $experience_time    = 0;

            foreach ($record_list as $k2 => $v2) {
                $v2 = (array)$v2;
                $experience_time += $v2['experience_time'];
            }

            $update_stat = array(
                    'action_num'        => (int)$action_num,
                    'experience_time'   => (int)$experience_time,
                    'update_time'       => date('Y-m-d H:i:s')
            );

            $res = _mongo('screen', 'screen_device_stat_day')->updateOne(array('_id' => $v['_id']), array('$set' => $update_stat));

        }

        $this->add_action_stat_by_business_task($day);
    }

    /**
     * 按厅统计
     * @param unknown $action_id
     */
    private function add_action_stat_by_business_task($day)
    {
        //查询当天所有厅数据
        $filter = array(
                'day' => (int)$day,
        );

        $res = _mongo('screen', 'screen_device_stat_day')->aggregate(array(
                array('$match' => $filter),
                array('$group' => array(
                        '_id'  => array(
                                'day'           =>  '$day',
                                'business_id'    => '$business_id',
                        ),
                        'experience_times'  => array('$sum' => '$experience_time'),
                        'action_nums'       => array('$sum' => '$action_num'),
                        'province_id'       => array('$first' => '$province_id'),
                        'city_id'           => array('$first' => '$city_id'),
                        'area_id'           => array('$first' => '$area_id'),
                        'count'           => array('$sum' => 1),
                    ),
                ),
            ),
            array('$typeMap' => array()) //为空则默认数组格式
        );


        foreach ($res as $k => $v) {
            $v = (array)$v;
            $v_id = (array)$v['_id'];

            //查询营业厅统计是否存在
            $business_stat_info = _model('screen_business_stat_day')->read(array('day' =>$v_id['day'], 'business_id' => $v_id['business_id']));

            if (!$business_stat_info) {
                $new_data = array(
                        'province_id'       => $v['province_id'],
                        'city_id'           => $v['city_id'],
                        'area_id'           => $v['area_id'],
                        'business_id'       => $v_id['business_id'],
                        'day'               => $v_id['day'],
                        'experience_time'   => $v['experience_times'],
                        'action_num'        => $v['action_nums'],
                        'device_num'        => $v['count'],
                );
                $stat_id = _model('screen_business_stat_day')->create($new_data);
            } else {
                $update_data = array(
                        'experience_time'   => $v['experience_times'],
                        'action_num'        => $v['action_nums'],
                        'device_num'        => $v['count'],
                );

                $res = _model('screen_business_stat_day')->update(array('day' =>$v_id['day'], 'business_id' => $v_id['business_id']), $update_data);
                $stat_id = $business_stat_info['id'];
            }
        }
        return  'ok';
    }



    private function test2()
    {
        _widget('screen.online_stat_write')->write();
    }

    private function mongo_test()
    {
        $res = _mongo('test', 'wangjf_test')->insertOne(array('name' =>'wangjf3', 'b' => 1));
        p($res->getInsertedId());

        $res = _mongo('test', 'wangjf_test')->find();

        foreach ($res as $k => $v) {
            p((array)$v);
        }

        $res = _mongo('test', 'wangjf_test')->deleteMany(array('name' => 'wangjf3'));

        echo  '<hr>';
        $res = _mongo('test', 'wangjf_test')->find();

        foreach ($res as $k => $v) {
            p((array)$v);
        }
    }

    /**
     * 在线数据测试
     */
    private function online_test()
    {
        _widget('screen_stat.data_center')->get_screen_device_week_stat(array());
    }

    /**
     * 设置上架
     */
    private function set_screen_status()
    {
        $device_unique_id = tools_helper::Get('device_unique_id', '');
        if (!$device_unique_id) {
            echo '设备唯一ID不能为空';exit;
        }

        $res = _model('screen_device')->update(array('device_unique_id' => $device_unique_id), array('status' => 1));
        p($res);
    }


    private function update_device_nickname_id()
    {
        $device_list = _model('screen_device_nickname')->getList(array(1 =>1));

        foreach ($device_list as $k => $v) {
            $filter = array('phone_name' => $v['phone_name'], 'phone_version' => $v['phone_version']);

            $res  = _model('screen_device')->update($filter, array('device_nickname_id' => $v['id']));

            p($res);
        }
    }

    /**
     *删除重复的内容轮播
     */
    private function delete_repeat_content_roll()
    {
        _widget('screen_content')->delete_repeat_content_roll(5);
    }

    /**
     * 更新设备状态
     */
    private function update_device_status ()
    {
        $device_unique_id = tools_helper::Get('device_unique_id', '');
        $device_info = _model('screen_device')->read(array('device_unique_id' => $device_unique_id), ' ORDER BY `id` DESC LIMIT 1 ');
        if (!$device_info) {
            echo '设备不存在';
            exit();
        }

        _model('screen_device')->update($device_info['id'], array('status' => 1));
    }

    /**
     * 为空数据监控
     */
    private function empty_data_monitor()
    {
        _widget('monitor')->empty_data_monitor();
    }

    /**
     * 更新设备标签
     */
    private function update_device_tag()
    {
        global $mc_wr;
        $page = tools_helper::Get('page', 1);
        $limit = tools_helper::Get('limit', 20);

        $limit_start = ($page-1)*$limit;

        $device_list = _model('screen_device')->getList(array('status' => 1), 'limit '.$limit_start.','.$limit);

        if (!$device_list) {
            $error = $mc_wr->get('wangjf_test_screen_tag_error');
            p($error);
            echo '已经执行完毕';
            exit();
        }
        foreach ($device_list as $k => $v) {
            //查询营业厅
            $business_info = business_hall_helper::get_business_hall_info($v['business_id']);

            if (!$business_info) {
                $error = $mc_wr->get('wangjf_test_screen_tag_error');
                if (!$error) $error = array();
                $error[] = $v;
                $error = $mc_wr->set('wangjf_test_screen_tag_error', $error, 3600*12);
                continue;
            }

            //wangjf add: 绑定极光推标签
            $tags = array();
            $tags[] = push_helper::get_business_hall_tag($business_info['id']); //厅
            $tags[] = push_helper::get_area_tag($business_info['area_id']); //区
            $tags[] = push_helper::get_city_tag($business_info['city_id']); //市
            $tags[] = push_helper::get_province_tag($business_info['province_id']); //省
            $tags[] = push_helper::get_phone_name_version_tag($v['phone_name'], $v['phone_version']); //机型

            //p($tags);

            $res = push_helper::binding_tag($v['registration_id'], $tags);

        }

        ++$page;

        $url = SITE_URL.'/test_by_wangjf/update_device_tag?page='.$page.'&limit='.$limit;
        echo '<script>window.location.href="'.$url.'"</script>';
    }

    /**
     * 价签接口  添加品牌接口
     */
    private function add_brand()
    {
        $device_list = _model('screen_device')->getList(array('device_unique_id' => '4c189aea5919'));

        foreach ( $device_list as $k => $v ) {
            $res = _widget('screen_device.price_tag')->add_brand($v);

            $res = _widget('screen_device.price_tag')->update_brand($v);

            $res = _widget('screen_device.price_tag')->delete_brand($v);

            exit;
        }

    }

    /**
     * 删除指定设备拿起放下数据
     */
    private function delete_appoint_device_action_data()
    {
        //要删除的天数据
        $time     = tools_helper::Get('time', date('Y-m-d H:i:s'));
        $day      = date('Ymd', strtotime($time));

        $del_filter = array(
                'add_time >=' => date('Y-m-d 00:00:00'),
                'add_time <' => date('Y-m-d 07:00:00'),
        );

        $res = _mongo('screen', 'screen_action_record')->deleteMany(get_mongodb_filter($del_filter));
        p($res->getDeletedCount());

        $del_filter = array(
                'add_time >=' => date('Y-m-d 23:00:00'),
                'add_time <=' => date('Y-m-d 23:59:59'),
        );

        $res = _mongo('screen', 'screen_action_record')->deleteMany(get_mongodb_filter($del_filter));
        p($res->getDeletedCount());


        //更新统计
        $this->update_action_data3($day);

        if ($day <= 20171001) {
            echo '已修复至20171001数据';
            exit();
        }



        $url = SITE_URL.'/test_by_wangjf/delete_23_7_action_data?time='.date('Y-m-d H:i:s', strtotime($time)-24*3600);
        echo '<script>window.location.href="'.$url.'"</script>';

    }

    private function update_business_hall() {
        $data = Request::Post('data', '');
        $data = json_decode(htmlspecialchars_decode($data), true);
        //p($data);

        foreach ($data as $k => $v) {

            if (ONDEV) {
                $table = 'business_hall_bak';
            } else {
                $table = 'business_hall';
            }

                $res = _model($table)->create($v['business_hall']);
                p($res);
                $res = _model('member_info')->create($v['member_info']);
                p($res);
                $res = _model('group_user')->create($v['group_user']);
                p($res);
        }

        echo 'ok';

    }

    /**
     * 获取轮播次数最多的数据
     */
    private function get_scroll_top()
    {
        $end_date = tools_helper::Get('end_date', 20180201);

        if ( !$end_date ) {
            echo '截止日期不能为空';
            exit;
        }
        //screen_content_click_stat_day
        $list = _mongo('screen', 'screen_content_click_stat_day')->aggregate(array(
                    array('$match' => get_mongodb_filter(array('day <' => $end_date))),
                    array('$group' => array(
                            '_id'               => array(
                                    'device_unique_id'  => '$device_unique_id',
                                    'business_id'       => '$business_id',
                            ),
                            'action_nums'  => array('$sum' => '$action_num'),
                            'day_nums'  => array('$sum' => 1),
                        )
                    ),
                    array(
                            '$sort' => array('action_nums' => -1),
                    ),
                    array(
                            '$limit' => 50,
                    ),
            ));

        foreach ($list as $k => $v) {
            echo $k;
            p((array)$v);
        }
    }

    /**
     * 删除轮播数据最多的记录
     */
    private function delete_scroll_top()
    {

        $device = Request::Get('device_unique_id', '');


        $end_date = tools_helper::Get('end_date', 20180201);

        if (!$end_date) {
            echo '截止日期不能为空';
            exit;
        }
        if (!$device) {
            echo '设备不能为空';exit;
        }

        //查询营业厅
        $device_info = _model('screen_device')->read(array('device_unique_id' => $device));

        if (!$device_info) {
            echo '设备不存在';exit;
        }

        $filter = array('device_unique_id' => $device, 'day <' => $end_date);
        p(get_mongodb_filter($filter));

        $res = _mongo('screen', 'screen_content_click_record')->deleteMany(get_mongodb_filter($filter));


        p($res->getDeletedCount());
    }

    /**
     * 获取动作时长最大的数据
     */
    private function get_action_top()
    {
        $max_experience_time = tools_helper::Get('max_experience_time', 600);
        $max_limit = tools_helper::Get('max_limit', 30);
        $look      = tools_helper::Get('look', 1);

        if ( !$max_experience_time ) {
            echo '最大时间不能为空';
            exit;
        }

        $filter = array(
                'experience_time >' => $max_experience_time-1
        );


        $mongo_page = new MongoDBPager($max_limit);
        //screen_content_click_stat_day
        $list = _mongo('screen', 'screen_action_record')->find(get_mongodb_filter($filter), array_merge($mongo_page->getLimit(1), array('sort' => array('experience_time' => -1))));
        foreach ($list as $k => $v) {

            echo $v['day'].'<br>';

            $v = (array)$v;

            if ($look) {
                echo $k;
                p($v);
                continue;
            }

            $res = _mongo('screen', 'screen_action_record')->deleteOne(array('_id' => $v['_id']));
            p($res->getDeletedCount());

            //更新统计
            $this->update_action_data3($v['day']);
        }
    }

    /**
     * 探针设备状态统计
     */
    public function device_status_stat_day_test ()
    {
        $date = tools_helper::Get('date', date('Ymd'));
        if ($date == 20180101) {
            echo $date;
            exit();
        }
        _widget('probe_dev')->device_status_stat_day($date);

        $next = date('Ymd', strtotime($date)-3600*24);
        $url = SITE_URL.'/test_by_wangjf/device_status_stat_day_test?date='.$next;
        echo '<script>window.location.href="'.$url.'"</script>';
    }

    /**
     * 亮屏设备在线天统计表添加设备nickname_id
     */
    private function device_online_day_data()
    {

        //查询数据
        $devices = _model('screen_device_online_stat_day')->getFields('device_unique_id', array('device_nickname_id'=>0), ' GROUP BY `device_unique_id` ');

        foreach ($devices as $v) {
            $device_nickname_id = screen_device_helper::get_device_info_by_device($v, 'device_nickname_id');
            if (!$device_nickname_id) {
                continue;
            }

            _model('screen_device_online_stat_day')->update(array('device_unique_id' => $v), array('device_nickname_id' => $device_nickname_id));
        }
    }

    public function get_thubm()
    {
        $this->make_thumb('/2018/03/26/20180326153648000000_1_155617_100.png');
    }

    /**
     * 缩略图
     * @param unknown $file_path
     * @param string $prefix 缩略图前缀  ''-原始图片（默认）；small-小图； middle-中图；big-大图……
     * @return boolean
     */
    public function make_thumb($file_path, $prefix = 'middle') {

            if (empty($file_path)) return false;

            $file_path = UPLOAD_PATH.$file_path;
            $path_info = pathinfo($file_path);

            // 缩略图路径
            $thumb_path = $path_info['dirname'].'/'.$prefix.'_'.$path_info['basename'];



            // 按照最大宽度/最大高度进行等比缩放
                $gd = new Gd($file_path);

                $gd->scale(floor($gd->width/2), floor($gd->height/2));
                $gd->saveAs($thumb_path);

            return true;

    }

    /**
     * 批量生成缩略图
     */
    public function make_thumb_all()
    {
        $content_list = _model('screen_content')->getList(array('type'=>array(1, 4)));
        foreach ( $content_list as $k => $v ) {
            //生成缩略
            $path = ROOT_PATH.'/'.UPLOAD_FOLDER.$v['link'];
            if (!file_exists($path)) {
                continue;
            }
p($path);
            //生成缩略图
            p(_widget('screen_content')->make_thumb($v['link']));
        }
    }

    /**
     * 更新有效活跃设备
     */
    public function device_month_active_stat()
    {
        $month = tools_helper::Get('month', date('Ym'));

        _widget('screen_stat')->device_month_active_stat();

        $month = date('Ym', strtotime('-1 month', strtotime($month.'01')));

        echo '<script>window.location.href="'.SITE_URL.'/test_by_wangjf/device_month_active_stat?month='.$month.'"</script>';

    }

    public function push_test()
    {
        $device_unique_id = tools_helper::Get('device_unique_id', '');
        $title = tools_helper::Get('title', '2');

        $device_info = screen_device_helper::get_device_info_by_device($device_unique_id);

        if (!$device_info) {
            echo '设备不存在或已被下架';exit;
        }

        if (!$title) {
            echo '请输入推送类型';exit;
        }

        push_helper::push_msg((string)$title, array($device_info['registration_id']));

        echo '<h3>推送成功</h3>';
    }


    public function test()
    {
        //获取所有坐标点
        $list = _model('gps_record')->getList(array(1=>1));


    }

    /**
     * 根据两点获取距离
     * @param string $start_latlng 开始坐标 117.691648,39.010042
     * @param string $stop_latlng  目的坐标 115.131734,22.818246
     * @param number $unit          单位  1-米 2-千米
     * @return number
     */
    public function count_distance_by_latlng($start_latlng, $stop_latlng, $unit=1) {

        if(!$start_latlng && !$stop_latlng) {
            return 0;
        }

        list($lng1, $lat1) = explode(',', $start_latlng);
        list($lng2, $lat2) = explode(',', $stop_latlng);

        //地球半径系数
        $earth_radius = 6378.137;

        //圆周率
        $pi = 3.1415926;

        $radLat1 = $lat1 * ($pi / 180);
        $radLat2 = $lat2 * ($pi / 180);

        $a = $radLat1 - $radLat2;
        $b = ($lng1 * ($pi / 180)) - ($lng2 * ($pi / 180));

        $s = 2 * asin(sqrt(pow(sin($a/2),2) + cos($radLat1)*cos($radLat2)*pow(sin($b/2),2)));
        $s = $s * $earth_radius;
        $s = round(($s * 10000)/10);

        if ($unit=1) {
            return $s;
        } else {
            return $s/1000;
        }

    }

    /**
     * 修复大郊亭营业厅区id的问题
     */
    public function update_business_id_by_46435()
    {
        _model('screen_device')->update(array('business_id' => 46435), array('area_id' => 121));
    }

    /**
     * 取已知多个坐标中心位置
     */
    public function get_center_gps()
    {
        $business_hall_ids = _model('gps_record')->getFields('business_id', array(1=>1), 'GROUP BY business_id');
        foreach ($business_hall_ids as $business_id) {
            //查询
            $coord_list = _model('gps_record')->getList(array('business_id' => $business_id));

            if (!$coord_list) {
                continue;
            }

            $coord_key_arr = array();

            //相近坐标分组
            foreach ($coord_list as $k => $v) {
                //保留小数三位数并且不四舍五入
                $lat_formated = substr($v['lat'], 0, strpos($v['lat'], '.')+4);
                $lng_formated = substr($v['lng'], 0, strpos($v['lng'], '.')+4);
                $coord_key_arr[$lat_formated.'_'.$lng_formated][] = $k;
            }

            $keys = array();
            $keys_k = 0;
            //选举坐标数据最多的组为合法坐标组
            foreach ($coord_key_arr as $k => $v) {
                //第一次循环
                if (!$keys) {
                    $keys = $v;
                    $keys_k = $k;
                    //选举
                } else {
                    if (count($v) > count($coord_key_arr[$keys_k])) {
                        $keys = $v;
                        $keys_k = $k;
                    }
                }
            }
            $new_list = array();
            foreach ($keys as $k) {
                $new_list[] = $coord_list[$k];
            }

//             if ($business_id == 45971) {
//                 p($keys,$coord_key_arr,$new_list);
//             }

            $coord = get_center_coord($new_list);

            if (!$coord) {
                continue;
            }

            //查询省市区信息
            $business_info = business_hall_helper::get_business_hall_info($business_id);

            if (!$business_info) {
                continue;
            }

            $coord_info = _model('screen_business_hall_coord')->read(array('business_id' => $business_id));

            if ($coord_info) {
                _model('screen_business_hall_coord')->update(array('business_id' => $business_id), array(
                        'lat' => $coord['lat'],
                        'lng' => $coord['lng']
                ));
            } else {
                _model('screen_business_hall_coord')->create(array(
                        'lat' => $coord['lat'],
                        'lng' => $coord['lng'],
                        'business_id' => $business_id,
                        'province_id' => $business_info['province_id'],
                        'city_id'     => $business_info['city_id'],
                        'area_id'     => $business_info['area_id'],
                ));
            }
        }
    }

    /**
     * 更新统计表中的nickname_id
     */
    public function update_screen_device_stat_day()
    {
        //跑数据
        $data_list = _model('screen_device')->getList(array(1 => 1), ' GROUP BY `device_unique_id` ');

        //循环
        foreach ($data_list as $k => $v) {
            _mongo('screen', 'screen_device_stat_day')->updateMany(array('device_unique_id' => $v['device_unique_id']), array('device_nickname_id' => $v['device_nickname_id']));
        }


    }

//     public function insert_business()
//     {
//         $arr = array(
//                 'id' => 110536,
//                 'title' => '长春钜城国际自有营业厅',
//                 'type' => 4,
//                 'province_id' => 14,
//                 'city_id' => 161,
//                 'area_id' => 1391,
//                 'contact' => '戴微',
//                 'contact_way' => '18943150893',
//                 'wifi_res_id' => 246134,
//                 'user_number' => '2201011499926',
//                 'blat' => 0.0000000,
//                 'blng' => 0.0000000,
//                 'address' => '金宇大路与新明街交汇钜城国际117门市',
//                 'activity' => 0,
//                 'is_bounding' => 0,
//                 'is_online' => 0,
//                 'store_type' => 0,
//                 'store_level' => 0,
//                 'store_scope' => 0,
//                 'date' => '0000-00-00',
//                 'version' => '20180209152658',
//                 'yes' => 1,
//                 'update_time' => '2018-02-08 15:19:33',
//                 'add_time' => '2018-02-09 15:35:59'
//         );
//         $info  =_model('business_hall')->read(array('user_number'=> $arr['user_number']));
//         if (!$info) {
//            _model('business_hall')->create($arr);
//         } else {
//             echo '此营业厅已存在';
//         }
//    }

    /**
     * 处理纠错、数字地图和亮屏的坐标
     */
    public function handle_coord()
    {
        //查询所有亮屏
        $coords = _model('screen_business_hall_coord_diff')->getList(array(1=>1));

        foreach ($coords as $k => $v) {
            //查询营业厅信息
            $business_hall_info = _uri('business_hall', $v['business_id']);
            if (!$business_hall_info) {
                continue;
            }

            //查询数字地图坐标
            $szdt_info = _model('t_hall_info')->read(array('channel_code_encrypt' => $business_hall_info['user_number']));
            if ($szdt_info) {
                $szdt_lat = $szdt_info['latitude_baidu'];
                $szdt_lng = $szdt_info['longitude_baidu'];
            } else {
                $szdt_lat = '';
                $szdt_lng = '';
            }

            //查询纠错坐标
            $jc_info = _model('t_sys_store_data')->read(array('unio_view_code' => $business_hall_info['user_number']));
            if ($jc_info) {
                $jc_lat = $jc_info['latitude_baidu'];
                $jc_lng = $jc_info['longitude_baidu'];
            } else {
                $jc_lat = '';
                $jc_lng = '';
            }

            _model('screen_business_hall_coord_diff')->update($v['id'], array(
                    'lat_szdt' => $szdt_lat,
                    'lng_szdt' => $szdt_lng,
                    'lat_jc'    => $jc_lat,
                    'lng_jc'    => $jc_lng,
            ));

        }
    }

    /**
     * 处理文件数据（动作）
     */
    public function handle_file_action_data_test()
    {
        $res = _widget('screen_stat.handle_file_data')->handle_action_data();
        p($res);
    }

    /**
     * 处理文件数据（内容统计）
     */
    public function handle_file_content_stat_data_test()
    {
        $res = _widget('screen_stat.handle_file_data')->handle_content_stat_data();
        //_model('screen_file_data_record')->update(array('res_name' => 'content_stat'), array('processed' => 0));
        p($res);
    }

    /**
     * 处理文件数据（内容点击统计）
     */
    public function handle_file_content_click_data_test()
    {
        $res = _widget('screen_stat.handle_file_data')->handle_content_click_data();
        //_model('screen_file_data_record')->update(array('res_name' => 'add_click'), array('processed' => 0));
        p($res);
    }

    /**
     * 内容点击上报新增 click_count 字段， 需要跑数据将原有数据新增click_count字段，值设置为0
     */
    public function content_click_count()
    {
        $day = tools_helper::Get('day', 0);

        if (!$day) {
            $day = date('Ymd');
        }

        //按天更新不存在click_num字段的文档
        $filter = array(
                'day' => $day,
                'click_num' => array('$exists' => false),
        );

        $result = _mongo('screen', 'screen_click_record')->updateMany($filter, array('$set' => array(
                'click_num' => 1,
        )));

        echo $result->getMatchedCount();
        $day = date('Ymd', strtotime($day) - 24*3600);
        $url = SITE_URL.'/test_by_wangjf/content_click_count?day='.$day;

        if ($day <= 20170501) {
            echo '已完成';
            exit();
        }
        echo '<script>window.location.href="'.$url.'"</script>';
    }

    /**
     * 设备自动下架测试
     */
    public function device_auto_dropoff()
    {
        _widget('screen_device')->device_auto_dropoff();
    }

    public function get_mc()
    {
        $k = tools_helper::Get('key', 'wangjf_test');
        global $mc_wr;
        p($mc_wr->get($k));
    }

    /**
     * 添加管理员账号
     */
    public function add_member()
    {
        //查询营业厅id
        $info = _model('business_hall')->read(array('user_number' => '1111111111001'));

        if (!$info) {
            echo '用户名不存在';
            return false;
        }

        //CT409 添加营业厅
        $new_data = array(
                'member_user' => '1111111111001',
                'member_pass' => 'fa2a00984485f9438f24f38af18cb8e4',
                'ranks'       => 5,  //厅
                'res_name'    => 'business_hall',
                'res_id'      => $info['id'],
                'hash'        => uniqid()
        );

        $member_id = _model('member')->create($new_data);

        _model('group_user')->create(
                array(
                        'member_id'  => $member_id,
                        'group_id'   => 26,
                )
        );
    }

    public function update_status()
    {
        $filter = array(
                'status' => '2'
        );

        _model('screen_device')->update($filter, array('status' => 0));
    }










}