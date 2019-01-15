<?php
/**
 * alltosun.com  stat.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-9-16 上午11:33:55 $
 * $Id$
 */
trait stat
{

    private $member_id   = 0;
    private $member_info = array();
    private $res_name    = 'group';
    private $res_id      = 0;

    private $per_page       = 20;

    //日期类型，分别对应：待分配（时间段）、今天按小时、本周按天、本月按天, 时间段按小时、时间段按天、时间段按月
    private static $date_types     = array(
        0, 2, 3, 4, 5, 6, 7
    );

    //小时范围
    private static $hour_range      = array(
        0, 1, 2, 3, 4,5, 6, 7, 8, 9, 10, 11, 12,13,14,15,16,17,18, 19, 20, 21, 22, 23
    );

    //当前搜索的日期类型， 默认天
    private $date_type      = 0;

    //数据处理过程中的错误信息
    private $error_info     = '暂无权限访问，请联系管理员！';

    //当前访问的公共方法


    public function __construct()
    {

        //获取管理员身份
        $this->member_id   = member_helper::get_member_id();
        $this->member_info = member_helper::get_member_info($this->member_id);
        $this->res_name    = $this->member_info['res_name'];
        $this->res_id      = $this->member_info['res_id'];

        Response::assign('res_name', $this->res_name);
        Response::assign('res_id', $this->res_id);
        Response::assign('member_info', $this->member_info);
    }

    /**
     * 当前管理员区域条件
     */
    private function region_filter()
    {
        $filter = array();

        if ($this->member_info['res_name'] == 'group') {
            return array();
        } else if ($this->member_info['res_name'] == 'business_hall') {
            return array('business_id' => $this->member_info['res_id']);
        } else {
            return array("{$this->member_info['res_name']}_id" => $this->member_info['res_id']);
        }

    }

    /**
     * 处理搜索条件
     */
    private function search_filter()
    {
        $search_filter  = tools_helper::get('search_filter', array());
        $filter         = array();
        $start_time     = '';
        $end_time       = '';

        //搜索的日期类型
        if (isset($search_filter['date_type']) && in_array($search_filter['date_type'], self::$date_types)) {
            $this->date_type        = $search_filter['date_type'];
        } else {
            //0待分配
            $this->date_type = 0;
        }

        //搜索条件字符串，用于子页面继承
        $search_filter_str  = '?search_filter[date_type]='.$this->date_type;

        //日期类型为0，待分配
        if ($this->date_type == 0) {

            //按指定时间段搜索
            if (isset($search_filter['start_time']) && $search_filter['start_time']) {
                $search_filter_str  .= '&search_filter[start_time]='.$search_filter['start_time'];

                $start_time = $search_filter['start_time'].' 00:00:00';
            }

            if (isset($search_filter['end_time']) && $search_filter['end_time']) {

                $search_filter_str  .= '&search_filter[end_time]='.$search_filter['end_time'];
                $end_time = $search_filter['end_time'].' 23:59:59';
            }

            //按照时间段规则设置日期类型
            if ($this->set_date_type($start_time, $end_time) === false) {
                return false;
            }
        }

        //兼容首次进来
        if ($this->date_type == 2) {
            $search_filter['date_type'] = $this->date_type;
        }

        Response::assign('search_filter_str', $search_filter_str);
        Response::assign('search_filter', $search_filter);

        return $this->get_date_type_filter($start_time, $end_time);

    }

    /**
     * 设置日期类型
     * @param unknown $start_time
     * @param unknown $end_time
     */
    private function set_date_type($start_time, $end_time)
    {
        if ($this->date_type != 0) {
            return true;
        }

        //没有时间段条件则默认为今日
        if (!$start_time && !$end_time) {
            $this->date_type = 2;
            return true;
        }

        if (!$start_time) {
            $start_time = date('Y-m-d 00:00:00');
        }

        if (!$end_time) {
            $end_time = date('Y-m-d 23:59:59');
        }

        //时间转换为Ymd格式
        $start_Ymd = date('Ymd', strtotime($start_time));
        $end_Ymd   = date('Ymd', strtotime($end_time));

        //同一天, 此时间段内按小时展示
        if ($end_Ymd == $start_Ymd) {
            $this->date_type = 5;
            return true;
        }

        //32天内，此时间段内按天展示
        //         if ($end_Ymd - $start_Ymd <= 32) {
        //             $this->date_type = 6;
        //             return true;
        //         }

        //按照当前的统计规则，按月展示有问题，
        if ($end_Ymd - $start_Ymd >= 1) {
            $this->date_type = 6;
            return true;
        }

        //大于32天，按月展示
        //         if ($end_Ymd - $start_Ymd > 32) {
        //             $this->date_type = 7;
        //             return true;
        //         }

        $this->error_info= '系统错误';
        return false;


        }

        /**
         * 获取日期条件
         */
        private function get_date_type_filter($start_time='', $end_time='')
        {
            if (!$start_time) {
                $start_time = date('Y-m-d 00:00:00');
            }

            if (!$end_time) {
                $end_time = date('Y-m-d 23:59:59');
            }

            $filter = array();

            //按今天 或 按指定时间段（小时展示）
            if ($this->date_type == 2 || $this->date_type == 5) {

                $date_info = explode(' ', $start_time);
                if (empty($data_info[1])) {
                    $ymd = $date_info[0];
                    $his = '00:00:00';
                } else {
                    $ymd = $date_info[0];
                    $his = $date_info[1];
                }

                if (!$ymd || !$his) {
                    $this->error_info = '日期格式错误';
                    return false;
                }

                $filter['day'] = str_replace('-', '', $ymd);
                $filter['hour']= self::$hour_range;

                //按本周
            } else if ($this->date_type == 3) {

                //获取本周开始日期和结束日期
                $date_info          = screen_helper::get_day_by_time($start_time);

                list($start)        = explode(' ', $date_info['start']);
                list($end)          = explode(' ', $date_info['end']);
                $filter['day >='] = str_replace('-', '', $start);
                $filter['day <='] = str_replace('-', '', $end);

                //按本月
            } else if ($this->date_type == 4) {
                $timestamp = strtotime($start_time);
                $filter['day >='] = date('Ym01', $timestamp);
                $filter['day <'] =  date('Ym01', strtotime('+1 month', $timestamp));

                //按时间段（天展示），注：当 date_type == 7时， 不能查询月表，因为选择的时间段不一定是整月
            } else if ($this->date_type == 6 || $this->date_type == 7) {
                $start_timestamp = strtotime($start_time);
                $end_timestamp   = strtotime($end_time);
                $filter['day >='] = date('Ymd', $start_timestamp);
                $filter['day <='] =  date('Ymd', $end_timestamp);

            } else {
                $this->error_info = '不存在的日期类型';
                return false;
            }

            return $filter;

        }

        /**
         * 根据区域分组去重
         * @param unknown $list
         */
        private function group_by_region($list, $search_filter)
        {
            //区域组的key
            $key = '';

            //表格首行数据
            $first_data = array(
                    'device_num' => 0,
                    'active_num' => 0,
                    'experience_time' => 0,
                    'online_num'      => 0,
                    'business_hall_num' => 0,
                    'region_name'   => ''
            );

            switch($this->member_info['res_name'])
            {
            	case 'group':
            	    $key    = 'province_id';
            	    $region_type = 'province';
            	    break;
            	case 'province':
            	    $key = 'city_id';
            	    $region_type = 'city';
            	    break;
            	case 'city':
            	    $key = 'area_id';
            	    $region_type = 'area';
            	    break;
            	case 'area':
            	    $key = 'business_id';
            	    $region_type = 'business_hall';
            	    break;
            	case 'business_hall':
            	    $key = 'business_id';
            	    $region_type = 'business_hall';
            	    break;
            	default:
            	    $this->error_info = '未知管理员';
            }

            if (!$key) {
                return false;
            }

            //用户子页面继承
            Response::assign('region_type', $region_type);

            if ($this->member_info['res_name'] == 'group') {
                $first_data['region_name']  = '全国';
            } else {
                $first_data['region_name']  = _uri($this->member_info['res_name'], $this->member_info['res_id'], 'name');
            }

            $new_list = array();

            //时间条件
            if ($this->date_type == 2 || isset($search_filter['hour'])) {
                unset($search_filter['hour']);
            }

            foreach ($list as $k => $v) {

                //地区id
                $region_id = $v[$key];

                if (isset($new_list[$region_id])) {
                    $new_list[$region_id]['experience_time'] += $v['experience_time'];
                    $first_data['experience_time']           += $v['experience_time'];
                } else {
                    //因本周或本月数据，设备数量和活跃数量不能叠加，需查询

                    //设备数   先查device_unique_id 在按device_unique_id查活跃数  防止 换厅设备数据不对
                    $unique_ids = _model('screen_device')->getFields('device_unique_id', array('status' => 1, $key => $v[$key]));

                    $device_num = count($unique_ids);

                    $active_filter          = $search_filter;
                    $active_filter[$key]    = $v[$key];
                    $active_filter['device_unique_id'] = $unique_ids;

                    //活跃数
                    //$active_num      = count(_model('screen_device_online')->getFields('device_unique_id', $active_filter, ' GROUP BY `device_unique_id`'));
                    $active_num      = count(_model('screen_device_online_stat_day')->getFields('device_unique_id', $active_filter, ' GROUP BY `device_unique_id`'));
                    //_model('screen_device_online_stat_day')->getFields('device_unique_id', $active_filter, ' GROUP BY `device_unique_id` ');
//p($active_num, $active_filter, $v[$key], $key);
                    if ($this->date_type == 2) {
                        $online_filter=array(
                                'province_id' => $v['province_id'],
                                'day'         => date('Ymd'),
                                //'add_time >=' => date('Y-m-d H:i:s', time()-1800)
                                'update_time >='  => date('Y-m-d H:i:s', time()-1800)
                        );
                        //$online_num         = count(_model('screen_device_online')->getList($online_filter, ' GROUP BY device_unique_id '));
                        $online_num         = count(_model('screen_device_online_stat_day')->getList($online_filter, ' GROUP BY device_unique_id '));
                    }


                    //查询有亮屏设备的营业厅数量
                    $first_data['business_hall_num'] += $new_list[$region_id]['business_hall_num']  = count(_model('screen_device')->getList(array('status' => 1, $key => $v[$key]), ' GROUP BY `business_id`'));

                    //首行数据叠加
                    $new_list[$region_id]['active_num']           = $active_num;
                    $new_list[$region_id]['device_num']           = $device_num;
                    $new_list[$region_id]['experience_time']      = $v['experience_time'];

                    if ($this->date_type == 2) {
                        $new_list[$region_id]['online_num'] = $online_num;
                        $first_data['online_num']          += $online_num;
                    }

                    //列表数据叠加
                    $first_data['active_num'] += $active_num;
                    $first_data['device_num'] += $device_num;
                    $first_data['experience_time'] += $v['experience_time'];

                    //查询地区名称
                    if ($key == 'business_id') {
                        $region_table = 'business_hall';
                    } else {
                        $region_table = str_replace('_id', '', $key);
                    }
                    if ($this->member_info['res_name'] != 'business_hall') {
                        $field = 'name';
                    } else {
                        $field = 'title';
                    }
                    $new_list[$region_id]['region_name'] = _uri($region_table, $v[$key], $field);
                }
            }

            $return_data = array();

            if ($this->member_info['res_name'] != 'business_hall') {
                $return_data[] = $first_data;
            }

            foreach ($new_list as $k => $v) {
                $v['region_id'] = $k;
                $return_data[] = $v;
            }

            return $return_data;
        }

        /**
         * 处理图表数据
         * @param unknown $list
         */
        private function handle_echart_data($list)
        {
            $new_list = array();

            //今日 或 时间段为一天的数据，根据小时分组
            if (in_array($this->date_type, array(3, 4, 6, 7))) {
                $key = 'day';
            } else {
                return false;
            }

            foreach ($list as $k => $v) {

                //大于32天数据需要根据月
                if ($this->date_type == 7){
                    $new_key = substr($v[$key], 0, strlen($v[$key]) -2);
                } else {
                    $new_key = $v[$key];
                }

                if (isset($new_list[$new_key])) {
                    $new_list[$new_key]['experience_time']    += $v['experience_time'];
                } else {
                    if ($key == 'hour') {
                        $active_num = $this->get_active_num($v['day'], $new_key);
                    } else {

                        $active_num = $this->get_active_num($v['day']);
                    }
                    $new_list[$new_key]['active_num']       = $active_num;
                    $new_list[$new_key]['experience_time']  = $v['experience_time'];
                }
            }

            $data['dateData'] = json_encode(array_keys($new_list));

            $date_data                  = array();
            $active_num_data            = array();
            $experience_time_data       = array();

            foreach($new_list as $k => $v) {
                $date_data[]        = $k;
                $active_num_data[]  = $v['active_num'];
                $experience_time_data[]  = round($v['experience_time']/60, 1);
            }

            return json_encode(array(
                    'date_data'     => $date_data,
                    'active_num'    => $active_num_data,
                    'experience_time' => $experience_time_data
            ));
        }

        //根据时间段获取活跃设备数
        private function get_active_num($day, $hour=0)
        {
            //今日或时间段为一天查询小时数据
            if ($this->date_type == 2 || $this->date_type == 5) {
                //查询本时段活跃数量
                if (strlen($hour) == 1) {
                    $start_time = date('Y-m-d', strtotime($day))." 0{$hour}:00:00";
                    $end_time   = date('Y-m-d', strtotime($day))." 0{$hour}:59:59";
                } else {
                    $start_time = date('Y-m-d', strtotime($day))." {$hour}:00:00";
                    $end_time   = date('Y-m-d', strtotime($day))." {$hour}:59:59";
                }

                //$filter = array('add_time >=' => $start_time, 'add_time <=' =>  $end_time);
                $filter = array('update_time >=' => $start_time, 'update_time <= ' => $end_time);
                //本周、本月、时间段超过1天的查询天数据
            } else {
                $filter = array('day' => $day);
            }

            //$device_unique_ids = _model('screen_device_online')->getFields('device_unique_id', $filter, ' GROUP BY device_unique_id ');

            $device_unique_ids = _model('screen_device_online_stat_day')->getFields('device_unique_id', $filter, ' GROUP BY device_unique_id ');

            return count($device_unique_ids);
        }
}