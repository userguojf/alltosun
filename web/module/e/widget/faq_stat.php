<?php
/**
 * alltosun.com  faq_stat.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-4-21 下午2:22:19 $
 * $Id$
 */

class faq_stat_widget
{

    private $business_hall_id = 0;
    private $province_id      = 0;
    private $city_id          = 0;
    private $area_id          = 0;


    
    public function faq_stat($params)
    {
        $filter = array();

        //参数
        if (!isset($params['user_number']) || empty($params['user_number'])) {
            return '没有营业厅的登录信息';
        }

        if (!isset($params['faq_id']) || empty($params['faq_id'])) {
            return '没有常见问题的id';
        }

        //获取当前的营业厅信息
        $business_hall_info = _model('business_hall')->read(array('user_number' => $params['user_number']));

        if (!$business_hall_info) {
            return '没有找到营业厅信息';
        }

        //赋值
        $this->business_hall_id = $business_hall_info['id'];
        $this->province_id      = $business_hall_info['province_id'];
        $this->city_id          = $business_hall_info['city_id'];
        $this->area_id          = $business_hall_info['area_id'];

        //条件
        $filter['faq_id']       = $params['faq_id'];

        
        //常见问题的点击量的记录
        self::faq_stat_record($filter);

        //省统计
        self::faq_stat_province($filter);

        //市统计
        self::faq_stat_city($filter);

        //地区统计
        self::faq_stat_area($filter);

        //营业厅统计
        self::faq_stat_business_hall($filter);

    }

    /**
     * 常见问题的统计记录
     */
    private function faq_stat_record($filter)
    {
        $filter['business_hall_id'] = $this->business_hall_id;

        //有个站外过来的 对应字段is_outside

        _model('faq_stat_record')->create($filter);
    }

    /**
     *常见问题的统计的各省统计
     */
    private function faq_stat_province($filter)
    {
        $table                 = 'faq_stat_province';
        $filter['province_id'] = $this->province_id;

        $info = stat_helper::get_stat_day_info($table , $filter);

        if ($info) {

            _model($table)->update($info['id'], "SET `num` = num + 1 ");

        } else {

            $filter['num'] = 1;
            _model($table)->create($filter);

        }
    }

    /**
     *常见问题的统计的各市统计
     */
    private function faq_stat_city($filter)
    {
        $table                 = 'faq_stat_city';
        $filter['province_id'] = $this->province_id;
        $filter['city_id']     = $this->city_id;

        $info = stat_helper::get_stat_day_info($table , $filter);

        if ($info) {

            _model($table)->update($info['id'], "SET `num` = num + 1 ");

        } else {

            $filter['num'] = 1;
            _model($table)->create($filter);

        }
    }

    /**
     *常见问题的统计的各地区统计
     */
    private function faq_stat_area($filter)
    {
        $table             = 'faq_stat_area';
        $filter['city_id'] = $this->city_id;
        $filter['area_id'] = $this->area_id;

        $info = stat_helper::get_stat_day_info($table , $filter);

        if ($info) {

            _model($table)->update($info['id'], "SET `num` = num + 1 ");

        } else {

            $filter['num'] = 1;
            _model($table)->create($filter);

        }
    }

    /**
     *常见问题的统计的各营业厅统计
     */
    private function faq_stat_business_hall($filter)
    {
        $table                      = 'faq_stat_business_hall';
        $filter['area_id']          = $this->area_id;
        $filter['business_hall_id'] = $this->business_hall_id;

        $info = stat_helper::get_stat_day_info($table , $filter);

        if ($info) {

            _model($table)->update($info['id'], "SET `num` = num + 1 ");

        } else {

            $filter['num'] = 1;
            _model($table)->create($filter);

        }
    }
}