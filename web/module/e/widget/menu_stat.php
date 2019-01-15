<?php
/**
 * alltosun.com  menu_stat.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-4-15 下午5:55:55 $
 * $Id$
 */

class menu_stat_widget
{
    private $business_hall_id = 0;
    private $province_id      = 0;
    private $city_id          = 0;
    private $area_id          = 0;

    public  $day              = NUll;
    private $week             = NULL;
    private $month            = NULL;

    /**
     * 记录企业号的菜单点击量
     * @param unknown $params
     */
    public function qydev_stat($params) 
    {
        //赋值时间问题
        $this->day   = date('Ymd');
        $this->week  = date('YW');
        $this->month = date('Ym');

        $filter      = array();

        if (!isset($params['res_name']) || !$params['res_name']) {
            return false;
        }

        if (!in_array( $params['res_name'], e_config::$menu_res_name)) {
            return false;
        }

        if (!isset($params['unique_id']) || !$params['unique_id']) {
            return false;
        }

        if (!isset($params['user_number']) || !$params['user_number']) {
            return false;
        }

        //统计条件
        $filter['res_name'] = $params['res_name'];

        //查询对应营业厅信息
        $busines_hall_info = _model('business_hall')->read(array('user_number' => $params['user_number']));

        //查到赋值
        if ($busines_hall_info) {
            $this->business_hall_id = $busines_hall_info['id'];
            $this->province_id      = $busines_hall_info['province_id'];
            $this->city_id          = $busines_hall_info['city_id'];
            $this->area_id          = $busines_hall_info['area_id'];
        }

        //日统计
        self::qydev_menu_stat_day($filter);

        //周统计
        self::qydev_menu_stat_week($filter);

        //月统计
        self::qydev_menu_stat_month($filter);

        //记录
        self::qydev_menu_record($params);
    }

    /**
     * 菜单记录
     * @param unknown $params
     */
    private function qydev_menu_record($params)
    {
        $data = array(
                'res_name'         => $params['res_name'],
                'user_number'      => $params['user_number'],
                'unique_id'        => $params['unique_id'],
                'business_hall_id' => $this->business_hall_id,
                'province_id'      => $this->province_id,
                'city_id'          => $this->city_id,
                'area_id'          => $this->area_id,
                'stat_day'         => $this->day,
                'stat_week'        => $this->week,
                'stat_month'       => $this->month
        );

        //记录
        _model('qydev_menu_record')->create($data);

    }

    /**
     * 菜单日统计
     * @param array $filter
     */
    private function qydev_menu_stat_day($filter)
    {
        $table         = 'qydev_menu_stat_day';
        $filter['day'] = $this->day;

        $info = stat_helper::get_stat_day_info($table , $filter);

        if ($info) {
            _model($table)->update($info['id'], "SET `num` = num + 1 ");

        } else {
            $filter['num'] = 1;
            _model($table)->create($filter);

        }
    }

    /**
     * 菜单周统计 
     * @param array $filter
     */
    private function qydev_menu_stat_week($filter)
    {
        $table          = 'qydev_menu_stat_week';
        $filter['week'] = $this->week;

        $info = stat_helper::get_stat_day_info($table , $filter);

        if ($info) {
            _model($table)->update($info['id'], "SET `num` = num + 1 ");

        } else {
            $filter['num'] = 1;
            _model($table)->create($filter);

        }
    }

    /**
     * 菜单月统计
     * @param array $filter
     */
    private function qydev_menu_stat_month($filter)
    {
        $table           = 'qydev_menu_stat_month';
        $filter['month'] = $this->month;

        $info = stat_helper::get_stat_day_info($table , $filter);

        if ($info) {
            _model($table)->update($info['id'], "SET `num` = num + 1 ");

        } else {
            $filter['num'] = 1;
            _model($table)->create($filter);

        }
    }
}