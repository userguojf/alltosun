<?php
/**
  * alltosun.com 亮屏统计widget screen_stat.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2017年10月19日 上午11:26:23 $
  * $Id$
  */
class screen_stat_widget
{

    /**
     * 添加在线统计
     * 离线说明
     * 如果记录上线数据，但是离线了，下次接口被请求了一并更离线字段；
     * 如果一直不更新超过5小时，计划任务会统计到；
     * 如果不够5小时，前端展示离线时间是当前时间 - 最后一次更新时间
     * @param array $info
     * return int
     */
    public function add_device_online_stat_day($info)
    {
        if (!$info) {
            return 0;
        }

        $info['is_online'] = 1;

        $online_stat_info = _model('screen_device_online_stat_day')->read(array('device_unique_id'=>$info['device_unique_id'], 'day'=>date('Ymd'), 'business_id'=>$info['business_id']));

        if ($online_stat_info) {
            //更新sql
            $update_sql = '';

            //如果大于一分钟小于5小时 更新
            if (5*3600 > time() - strtotime($online_stat_info['update_time']) && time() - strtotime($online_stat_info['update_time']) > 61) {
                $offline_time = time() - strtotime($online_stat_info['update_time']);
                $update_sql   = " SET `offline_time` = `offline_time` + $offline_time ,";
            }

            //大于5小时更新 （目前规定一天就记录第一次）
            if (!$online_stat_info['offline_of_time'] && time() - strtotime($online_stat_info['update_time']) >= 5*3600) {
                $time         = time();
                $offline_time = $time - strtotime($online_stat_info['update_time']);

                $update_sql   = " SET `offline_time` = `offline_time` + $offline_time ,
                `offline_of_time` = `offline_of_time` + $time , ";
            }

            if ($update_sql) {
                $update_sql .= " `online_time` = `online_time` + 60 ";

            } else {
                $update_sql = " SET `online_time` = `online_time` + 60 ";

            }
            // p($update_sql);exit();
            // 更新在线时长
            _model('screen_device_online_stat_day')->update($online_stat_info['id'], $update_sql);

        } else {

            _model('screen_device_online_stat_day')->create($info);
        }

        return 1;
    }

    /**
     * 更新小时统计
     * @param unknown $stat_info
     * @param unknown $stat_type
     */
    public function update_business_stat_hour()
    {

        $time = time();
        //查询上一小时
        $hour = date('H', $time - 3600);
        $day  = date('Ymd', $time - 3600);

        $hour_filter = array(
                'hour' => $hour,
                'day'  => $day,
        );

        //查询动作
        //$record_info = _model('screen_action_record')->getList();

        if (!$record_info || $record_info['type'] != 2) {
            return false;
        }


        $hour_filter = array(
                'hour' => $hour,
                'day'  => $day,
                'business_id' => $record_info['business_id']
        );

        $hour_stat = _model('screen_business_stat_hour')->read($hour_filter);

        //获取体验时长
        $action_filter = array(
                'add_time >=' => date('Y-m-d H:00:00', strtotime($record_info['add_time'])),
                'add_time <=' => date('Y-m-d H:59:59', strtotime($record_info['add_time'])),
                'type'        => 2,
                'business_id' => $record_info['business_id']
        );

        $experience_times = _model('screen_action_record')->getFields('experience_time', $action_filter);

        unset($action_filter['business_id']);

        //查询设备数
        $devices = _model('screen_action_record')->getFields('device_unique_id', $action_filter, 'GROUP BY device_unique_id,business_id');


        $update_data = array(
                'experience_time' => array_sum($experience_times),
                'action_num'      => count($experience_times),
                'device_num'      => count($devices),
        );

        //更新
        if ($hour_stat) {
            $result = _model('screen_business_stat_hour')->update($hour_stat['id'], $update_data);
            return $result;

        }
        //添加
        $new_data = array_merge($update_data, $hour_stat);

        $update_data['area_id']         = $record_info['area_id'];
        $update_data['city_id']         = $record_info['city_id'];
        $update_data['province_id']     = $record_info['province_id'];
        $update_data['business_id']     = $record_info['business_id'];
        $update_data['day']             = $day;
        $update_data['hour']            = $hour;

        return _model('screen_business_stat_hour')->create($update_data);

    }


    /**
     * 按厅统计
     * @param unknown $action_id
     * @return boolean
     */
    public function add_action_stat_by_business($device_stat_id)
    {
        if (!$device_stat_id) {
            return false;
        }

        $device_stat_info = _model('screen_device_stat_day')->read($device_stat_id);

        if (!$device_stat_info) {
            return false;
        }

        $stat_filter = array(
                'day'           => $device_stat_info['day'],
                'business_id'   => $device_stat_info['business_id']
        );

        //查询统计
        $stat_info = _model('screen_business_stat_day')->read($stat_filter);

        //添加统计
        if (!$stat_info) {
            $new_stat = array(
                    'province_id' => $device_stat_info['province_id'],
                    'city_id'     => $device_stat_info['city_id'],
                    'area_id'     => $device_stat_info['area_id'],
                    'business_id' => $device_stat_info['business_id'],
                    'day'         => $device_stat_info['day'],
                    'device_num'  => 1,
                    'experience_time' => $device_stat_info['experience_time'],
                    'action_num'  => $device_stat_info['action_num']
            );
            _model('screen_business_stat_day')->create($new_stat);
            //更新统计
        } else {

            $update_stat = array(
                    'device_num'        => _model('screen_device_stat_day')->getTotal($stat_filter),
                    'action_num'        => array_sum(_model('screen_device_stat_day')->getFields('action_num', $stat_filter)),
                    'experience_time'   => array_sum(_model('screen_device_stat_day')->getFields('experience_time', $stat_filter))
            );

            _model('screen_business_stat_day')->update($stat_info['id'], $update_stat);
        }
        return  'ok';
    }

    /**
     * 按厅统计计划任务版（支持手动统计）
     * @param unknown $action_id
     */
    public function add_action_stat_by_business_task( $day = 0 )
    {
        if (!$day) {

            $time = time();
            //如果当前时间距离昨日在1小时之内，则继续更新昨日数据
            $yday = date('Ymd', $time-3600);
            $nday = date('Ymd', $time);
            if ( $yday!= $nday) {
                $day = $yday;
            } else {
                $day = $nday;
            }
        }


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

    /**
     * 按设备统计动作
     * @param unknown $action_id
     * @return boolean
     */
    public function add_action_stat_by_device($action_id)
    {
        if (!$action_id) {
            return false;
        }

        $action_info = (array)(_mongo('screen', 'screen_action_record')->findOne(array('_id' => $action_id)));

        if (!$action_info) {
            return false;
        }

        $stat_filter = array(
                'day'                   => (int)$action_info['day'],
                'device_unique_id'      => $action_info['device_unique_id'],
                'business_id'           => (int)$action_info['business_id'],
        );

        $stat_info = (array)(_mongo('screen', 'screen_device_stat_day')->findOne($stat_filter));

        $date_time = date('Y-m-d H:i:s');

        //添加统计
        if (!$stat_info) {

            $new_stat = array(
                    'province_id' => (int)$action_info['province_id'],
                    'city_id'     => (int)$action_info['city_id'],
                    'area_id'     => (int)$action_info['area_id'],
                    'business_id' => (int)$action_info['business_id'],
                    'device_unique_id' => $action_info['device_unique_id'],
                    'day'         => (int)$action_info['day'],
                    'action_num'  => 1,
                    'experience_time' => (int)$action_info['experience_time'],
                    'add_time'    => $date_time,
                    'update_time' => $date_time
            );

            $res = _mongo('screen', 'screen_device_stat_day')->insertOne($new_stat);
            return $res->getInsertedId();
        //更新统计
        } else {

            $update_stat = array(
                    'action_num'        => 1,
                    'experience_time'   => (int)$action_info['experience_time'],
            );

            _mongo('screen', 'screen_device_stat_day')->updateOne(array('_id' => $stat_info['_id']), array('$set' =>  array('update_time' => $date_time), '$inc' => $update_stat));
            return $stat_info['_id'];
        }

        return false;
    }

    /**
     * 初始化搜索条件 （根据管理员生成权限内的条件）
     * @param unknown $member_info
     */
    public function default_search_filter($member_info)
    {
        $filter = array();

        if ($member_info['res_name'] == 'business_hall') {
            $filter['business_id'] = $member_info['res_id'];
        } else if ($member_info['res_name'] == 'group') {
            return $filter;
        } else {
            $filter["{$member_info['res_name']}_id"] = $member_info['res_id'];
        }

        return $filter;

    }

    /**
     * 计划任务 每天执行一次,
     * 设备月活跃统计
     * @param unknowtype
     * @return return_type
     * @author 王敬飞 (wangjf@alltosun.com)
     * @date 2018年4月8日下午12:11:08
     */
    public function device_month_active_stat()
    {
        //当前月1号至当前月31号
        $month = tools_helper::Get('month', date('Ym'));
        $start_date = (int)($month.'01');
        $end_date = (int)($month.'31');
        $where = to_where_sql(array('day >=' => $start_date, 'day <=' => $end_date));

        $sql = "SELECT *, count(*) as day_count FROM `screen_device_online_stat_day` {$where} GROUP BY `device_unique_id`, `business_id` ";
        $online_list = _model('screen_device_online_stat_day')->getAll($sql);

        foreach ($online_list as $k => $v) {

            $filter = array(
                    'business_id'       => $v['business_id'],
                    'device_unique_id'  => $v['device_unique_id'],
                    'month'             => $month
            );

            //查询活跃设备
            $active_device = _model('screen_device_active_stat_month')->read($filter);

            if ($active_device) {
                _model('screen_device_active_stat_month')->update($active_device['id'], array('active_days'   => $v['day_count']));
            } else {
                _model('screen_device_active_stat_month')->create(array(
                        'province_id'   => $v['province_id'],
                        'city_id'       => $v['city_id'],
                        'area_id'       => $v['area_id'],
                        'business_id'   => $v['business_id'],
                        'device_unique_id'      => $v['device_unique_id'],
                        'device_nickname_id'    => $v['device_nickname_id'],
                        'month'         => $month,
                        'active_days'   => $v['day_count']
                ));
            }
        }
    }



}