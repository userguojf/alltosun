<?php
/**
  * alltosun.com 亮屏统计 stat.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2018年3月12日 下午4:00:25 $
  * $Id$
  */
class Action
{
    private $member_info;
    private $echarts_num = 6;  //图表所需数量

    public function __construct()
    {
        $this->member_info = member_helper::get_member_info();
    }

    public function __call($action='', $param=array())
    {
        if (!$this->member_info) {
            return '请先登录';
        }

        //获取覆盖厅店数
        $cover_business_hall_count  = count($this->get_cover_business_hall($this->member_info['res_name'], $this->member_info['res_id']));

        //获取安装设备数
        $install_device_count       = count($this->get_install_device($this->member_info['res_name'], $this->member_info['res_id']));

        //获取活跃台数
        $active_device_count        = $this->get_active_device($this->member_info['res_name'], $this->member_info['res_id']);

        //获取设备体验时长
        $device_experience_time     = $this->get_device_experience_by_field($this->member_info['res_name'], $this->member_info['res_id'], 'experience_time');

        //体验时长按设备分组
        $experience_time_brand      = $this->group_experience_by_brand($device_experience_time, 'experience_time');

        $this->handle_echarts($experience_time_brand, 'experience_time');

        //Response::display('admin/screen/stat/index.html');
    }

    private function handle_echarts($data, $field)
    {
        $sorts = array();
        //将device_nickname_id 放到数组值中
        $new_data = array();
        foreach ( $data as $k => $v ) {
            $sorts[]                    = $v[$field];
            $v['device_nickname_id']    = $k;
            $new_data[]                 = $v;
        }

        if ($sorts) {
            array_multisort($sorts, SORT_DESC, $new_data);
        }

        $new_data = array_slice($new_data, 0, $this->echarts_num);

        $echarts_data = array();

        foreach ($new_data as $k => $v) {
            //获取设备昵称
            $nickname_info = screen_device_helper::get_device_nickname_info($v['device_nickname_id']);

            if ( !$nickname_info ) {
                continue;
            }

            $brand_name = empty($nickname_info['name_nickname']) ? $nickname_info['phone_name'] : $nickname_info['name_nickname'];
            $brand_name .= ' ';
            $brand_name .= empty($nickname_info['version_nickname']) ? $nickname_info['phone_version'] : $nickname_info['version_nickname'];

            $echarts_data[] = array($brand_name => $v[$field]);
        }

        return $echarts_data;
    }

    /**
     * 获取设备体验时长或体验次数
     * @param unknown $res_name 登录者res_name
     * @param unknown $res_id 登录者res_id
     * @param unknown $field  screen_device_stat_day集合中的字段
     * @return unknown
     */
    private function get_device_experience_by_field($res_name, $res_id, $field)
    {
        //借用设备的默认条件
        $filter = $this->get_default_device_filter($res_name, $res_id);

        //清除掉设备表特有的status字段条件
        unset($filter['status']);

        $result       = _mongo('screen', 'screen_device_stat_day')->aggregate(array(
                //array('$match' => get_mongodb_filter($filter)),
                array('$group' => array(
                        '_id'       => array('device_unique_id'  => '$device_unique_id'),
                        $field      => array('$sum' =>'$'.$field),
                        'device_unique_id' => array('$first' => '$device_unique_id'),
                ))
        ));

        return $result;
    }

    /**
     * 按品牌分组体验数据
     * @param unknown $data
     */
    private function group_experience_by_brand($data, $field)
    {
        //按品牌型号分组
        $arr = array();
        foreach ($data as $k => $v) {
            $v = (array)$v;
            //获取机型id
            $device_nickname_id = screen_device_helper::get_device_info_by_device($v['device_unique_id'], 'device_nickname_id');
            if (!$device_nickname_id) {
                continue;
            }
            if (empty($arr[$device_nickname_id])) {
                $arr[$device_nickname_id][$field]  = $v[$field];
                $arr[$device_nickname_id]['device_num']        = 1;
            } else {
                $arr[$device_nickname_id][$field]  += $v[$field];
                $arr[$device_nickname_id]['device_num']        += 1;
            }
        }

        return $arr;
    }


    /**
     * 获取活跃设备 （默认今天）
     * @param unknown $res_name
     * @param unknown $res_id
     */
    private function get_active_device($res_name, $res_id)
    {
        //借用设备的默认条件
        $filter = $this->get_default_device_filter($res_name, $res_id);

        //在线
        $filter['is_online']    = 1;
        $filter['day']          = date('Ymd');

        //清除掉设备表特有的status字段条件
        unset($filter['status']);

        //获取设备
        $devices = _model('screen_device_online_stat_day')->getFields('device_unique_id', $filter, ' GROUP BY `device_unique_id`');

        return $devices;
    }

    /**
     * 生成获取设备的默认条件
     * @param unknown $res_name
     * @param unknown $res_id
     */
    private function get_default_device_filter($res_name, $res_id)
    {
        //获取设备状态为1
        $filter = array('status' => 1);
        if (in_array($res_name, array('province', 'city', 'area'))) {
            $filter[$res_name.'_id'] = $res_id;
        } else if ($res_name == 'business_hall') {
            $filter['business_id'] = $res_id;
        } else if ($res_name != 'group') {
            return array('id' => 0);
        }

        return $filter;
    }

    /**
     * 获取覆盖营业厅
     * @param unknown $res_name
     * @param unknown $res_id
     */
    private function get_cover_business_hall($res_name, $res_id)
    {
        //初始化默认条件
        $filter = $this->get_default_device_filter($res_name, $res_id);

        //为了兼容后续有详情页，先把所有营业厅id取出
        $business_hall_ids = _model('screen_device')->getFields('business_id', $filter, ' GROUP BY `business_id` ');

        return $business_hall_ids;
    }

    /**
     * 获取已安装设备
     * @param unknown $res_name
     * @param unknown $res_id
     */
    private function get_install_device($res_name, $res_id)
    {
        //初始化默认条件
        $filter = $this->get_default_device_filter($res_name, $res_id);

        //为了兼容后续有详情页， 先把所有设备取出
        $devices = _model('screen_device')->getFields('device_unique_id', $filter, 'GROUP BY `device_unique_id`');

        return $devices;
    }

}