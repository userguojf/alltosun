<?php
/**
 * alltosun.com 轮播图的统计修改版（1、数据处理；2、缓存）的 roll_stat_default.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-10-11 下午5:07:53 $
 * $Id$
 */
class roll_stat_default_widget
{

    private $count_stat_arr  = [];
    private $device_stat_arr = [];
    private $yyt_stat_arr    = [];

    /**
     * 时间 门店量 设备量  轮播量
     */
    public function screen_roll_stat()
    {

        set_time_limit(0);

        //分页处理数据
        $page_size = 5000;

        $date  = date('Ymd');
        $table = 'screen_content_click_record';

        //获取记录结束ID
        $id   = $this->id_get($table, $date);

        $list = _model($table)->getList(array('id >' => $id, 'day' => $date), " LIMIT $page_size");

        //没有终止计划任务
        if (!$list) {
            exit();
        }

        foreach ($list as $k => $v) {
            //总量统计表：轮播一次就更新一次轮播次数
            $this->count_stat($v['content_id'], 'roll_num', $date);

            //设备统计表
            $this->device_stat($v, $date);

            //设备统计
            $stat_record_info = _model($table)->read(
                    array(
                            'id <'             => $v['id'],
                            'content_id'       => $v['content_id'],
                            'business_id'      => $v['business_id'],
                            'device_unique_id' => $v['device_unique_id'],
                            'day'              => $date
                    ),
                    " ORDER BY `id` DESC "
            );

            if ($stat_record_info) {
                $is_eq_device = true;
            } else {
                $is_eq_device = false;
            }

            //营业厅统计
            $this->yyt_stat($v, $date, $is_eq_device);

            //最后一条要更新的ID
            $op_id = $v['id'];
        }

        p($this->count_stat_arr);
        p($this->device_stat_arr);
        p($this->yyt_stat_arr);

        //更新ID记录表
        $this->id_record($table, $date, $op_id);
    }

    /**
     * 营业厅统计
     * 营业厅  设备数  轮播数  时间
     */
    public function yyt_stat($v, $date, $is_eq_device)
    {
        $key = $v['content_id'].'&'.$v['business_id'].'&'.$date;

        if (!isset($this->yyt_stat_arr[$key])) {
            //定义二维数组
            $this->yyt_stat_arr[$key] = [];

            $this->yyt_stat_arr[$key]['province_id'] = $v['province_id'];
            $this->yyt_stat_arr[$key]['city_id']     = $v['city_id'];
            $this->yyt_stat_arr[$key]['area_id']     = $v['area_id'];
            $this->yyt_stat_arr[$key]['roll_num']    = 1;
            $this->yyt_stat_arr[$key]['device_num']  = 1;

            //总量统计表：门店量
            $this->count_stat($v['content_id'], 'business_hall_num', $date);
        } else {
            //营业厅统计
            if (!$is_eq_device) {
                $this->yyt_stat_arr[$key]['roll_num']    += 1;
                $this->yyt_stat_arr[$key]['device_num']  += 1;

            } else {
                $this->yyt_stat_arr[$key]['roll_num']    += 1;

            }
        }

        return true;
    }

    /**
     * 设备统计
     * 设备  营业厅  轮播数 时间
     */
    public function device_stat($v, $date)
    {
        $key = $v['content_id'].'&'.$v['business_id'].'&'.$v['device_unique_id'].'&'.$date;

        if (!isset($this->device_stat_arr[$key])) {
            //定义二维数组
            $this->device_stat_arr[$key] = [];

            $this->device_stat_arr[$key]['province_id'] = $v['province_id'];
            $this->device_stat_arr[$key]['city_id']     = $v['city_id'];
            $this->device_stat_arr[$key]['area_id']     = $v['area_id'];
            $this->device_stat_arr[$key]['roll_num']    = 1;

            //总量统计表：设备量
            $this->count_stat($v['content_id'], 'device_num', $date);

        } else {
            //轮播次数叠加
            $this->device_stat_arr[$key]['roll_num']  += 1;
        }

        return true;
    }

    /**
     * 总体统计
     * 时间  设备数  门店数  轮播数
     */
    public function count_stat($content_id, $field, $date)
    {
        $key = $content_id.'&'.$date;

        if (!isset($this->count_stat_arr[$key])) {
            //定义二维数组
            $this->count_stat_arr[$key] = [];
        }

        if (!isset($this->count_stat_arr[$key][$field])) {
            $this->count_stat_arr[$key][$field] = 1;

        } else {
            //继续叠加
            $this->count_stat_arr[$key][$field] += 1;
        }

        return true;
    }
///////////////////////////////////////////////////////////////////////////

    /**
     * ID更新操作表
     * @param string $table
     * @param string $date
     * @param int    $id
     * @return boolean
     */
    public function id_record($table, $date, $id)
    {
        $id_info = _model('screen_id_record')->read(array('data_table' => $table, 'date' => $date));

        if ($id_info && $id_info['data_table_id']) {
            _model('screen_id_record')->update($id_info['id'], array('data_table_id' => $id));
        } else {
            _model('screen_id_record')->create(array('data_table' => $table, 'data_table_id' => $id, 'date' => $date));
        }

        return true;
    }


    /**
     * 记录ID获取
     * @param string $table
     * @param string $date
     * @return unknown|number
     */
    public function id_get($table, $date)
    {
        $id_info = _model('screen_id_record')->read(array('data_table' => $table, 'date' => $date));

        if ($id_info) {
            return $id_info['data_table_id'];
        }

        return 0;
    }

}
