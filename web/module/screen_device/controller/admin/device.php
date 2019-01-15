<?php
/**
  * alltosun.com 设备管理 device.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2017年7月6日 下午3:55:34 $
  * $Id$
  */
class Action
{
    private $per_page = 20;
    private $member_id  = 0;
    private $member_res_name = '';
    private $member_res_id   = 0;
    private $member_info     = array();
    private $ranks           = 0;

    public function __construct()
    {

        $this->member_id   = member_helper::get_member_id();

        $this->member_info = member_helper::get_member_info($this->member_id);

        if ($this->member_info) {
            $this->member_res_name = $this->member_info['res_name'];
            $this->member_res_id   = $this->member_info['res_id'];
            $this->ranks           = $this->member_info['ranks'];
        }

        Response::assign('curr_member_ranks', $this->ranks);
    }
//http://wifi.pzclub.cn/screen/admin/device
// ?type=
// &search_filter%5Bprovince_id%5D=1
// &search_filter%5Bcity_id%5D=17
// &search_filter%5Barea_id%5D=120
// &search_filter%5Bonline_status%5D=1
// &hall_title=%E4%B8%AD%E5%85%B3%E6%9D%91%E8%90%A5%E4%B8%9A%E5%8E%85
// &device_unique_id=a0cc2b9efce0

    public function __call($action = '', $params = array())
    {
        $search_filter    = Request::Get('search_filter', array());
        $page             = tools_helper::get('page_no', 1);
        $search_device_unique_id = tools_helper::get('device_unique_id', '');

        $is_export = tools_helper::get('is_export', 0);

        $default_filter = _widget('screen')->init_filter($this->member_info, $search_filter);
        $filter = $default_filter;
        if (!empty($filter['business_hall_id']) && $filter['business_id'] = $filter['business_hall_id']) {
            unset($filter['business_hall_id']);
        }

        if ($search_device_unique_id) {
            $filter['device_unique_id'] = $search_device_unique_id;
        }

        $online_filter              = $filter;

        if (isset($search_filter['version_no']) && !empty($search_filter['version_no'])) {
            $filter['version_no'] = $search_filter['version_no'];
        }

        if (isset($search_filter['online_status']) && in_array($search_filter['online_status'], array(1, 2))){
            $online_filter['day']       = date('Ymd');
            $online_filter['update_time >='] = date('Y-m-d H:i:s', time()-1800);

            $device_unique_id  = _model('screen_device_online_stat_day')->getFields('device_unique_id', $online_filter, ' GROUP BY `device_unique_id`');

            if (!$device_unique_id) {
                $device_unique_id = '';
            }

            //离线
            if ($search_filter['online_status'] == 2) {
                $filter['device_unique_id !=']      = $device_unique_id;
            } else {
                $filter['device_unique_id']         = $device_unique_id;
            }

        }

//        if (isset($search_filter['status']) && $search_filter['status'] == 1) {
//             $filter['status']  = 1;
//         } elseif (isset($search_filter['status']) && $search_filter['status'] == 2) {
//             $filter['status'] = 0;
//         }

        if (!$filter) {
            $filter = array(1=>1);
        }

        $device_list = array();

// p($filter);
        //离线
        if (!empty($search_filter['online_status']) && $search_filter['online_status'] == 2) {
            $sql_filter = $this->to_where($filter);

            //导出
            if ($is_export) {
                $export_list = _model('screen_device')->getAll(" SELECT * FROM `screen_device` {$sql_filter}");

                $this->is_export($export_list);
            }

            $count_info = _model('screen_device')->getAll(" SELECT count(*) as device_count FROM `screen_device` {$sql_filter}");
            $count = $count_info[0]['device_count'];

            if ($count) {
                $pager = new Pager($this->per_page);
                if ($pager->generate($count)) {
                    Response::assign('pager', $pager);
                }

                Response::assign('count', $count);
                $limit_start = ($page-1)*$this->per_page;
                $device_list =  _model('screen_device')->getAll(" SELECT * FROM `screen_device` {$sql_filter} ORDER BY `id` DESC LIMIT {$limit_start}, {$this->per_page}");

            }
        } else {
            //导出
            if ($is_export) {
                $export_list = _model('screen_device')->getList($filter);

                $this->is_export($export_list);
            }

            $device_list = get_data_list('screen_device', $filter, ' ORDER BY `id` DESC ', $page, $this->per_page);
        }

        $version_infos = _model('screen_device')->getCol('select distinct version_no from screen_device');
        sort($version_infos);

        Response::assign('version_infos', $version_infos);
        Response::assign('device_unique_id', $search_device_unique_id);
        Response::assign('search_filter', $search_filter);
        Response::assign('device_list', $device_list);
        Response::display('admin/device/device_list.html');

    }

    /**
     * 删除
     */
    public function delete()
    {
        $id = Request::getParam('id');
        if (!$id) {
            return '对不起，请选择您要下架的信息！';
        }

        $device_info = _uri('screen_device', $id);

        if (!$device_info) {
            return '信息不存在';
        }

        _model('screen_device')->update($device_info['id'], array('status'=>0));

        // 手动下架
        screen_device_helper::drop_off($device_info, 3);

        //added by guojf
        $b_info = business_hall_helper::get_info_name('business_hall', $device_info['business_id']);

        $param = array(
                    'type'             => 'delete',
                    'user_number'      => $b_info['user_number'],
                    'brand'            => $device_info['phone_name'],
                    'version'          => $device_info['phone_version'],
                    'shoppe_id'        => $device_info['shoppe_id'],
                    'device_unique_id' => $device_info['device_unique_id']
        );

        //由于已上线（没有改造不足之处），但是该方法的流程代码已经在探针改造
        screen_helper::dm_create_app_log($param);

        return "ok";
    }

    /**
     * 数组条件转换where语句
     * @param unknown $filter
     * @return string
     */
    private function to_where($filter)
    {
        if (!$filter) {
            return '';
        }

        $where = '';

        if (is_array($filter)) {

            foreach ($filter as $k => $v) {

                if ( !$where ) {
                    $where = " WHERE ";
                }

                if (is_array($v) && strpos($k, '!=') !== false) {
                    foreach ($v as $v2) {
                        $where .= " {$k}'{$v2}' AND";
                    }

                    continue;
                }

                if (strpos($k, '!=') !== false) {
                    $where .= " {$k}'{$v}' AND";
                    continue;
                }

                if ( strpos($k, '<') || strpos($k, '>') ) {
                    $where .= " {$k}{$v} AND";
                } else {

                    //an_dump($k, $v);
                    if (is_array($v)) {
                        foreach ($v as $sk => $sv) {
                            $where .= " {$k}='{$sv}' AND";
                        }
                        continue;
                    } else {
                        $where .= " {$k}='{$v}' AND";
                    }

                }

            }

            $where = rtrim($where, 'AND');

        } else {

            if ( !$where ) {
                $where = " WHERE ";
            }

            $where .= "id={$filter} ";
        }

        return $where;
    }

    public function is_export($list)
    {
        if (!$list) {
            return '暂无数据';
        }

        foreach ($list as $k=>$v) {
            $info[$k]['proinvce_id']      = business_hall_helper::get_info_name('province', $v['province_id'],  'name');
            $info[$k]['city_id']          = business_hall_helper::get_info_name('city', $v['city_id'], 'name');
            $info[$k]['area_id']          = business_hall_helper::get_info_name('area', $v['area_id'], 'name');
            $info[$k]['business_hall_id'] = business_hall_helper::get_info_name('business_hall', $v['business_id'], 'title');
            $info[$k]['phone_name']       = $v['phone_name_nickname'] ? $v['phone_name_nickname'] : $v['phone_name'];
            $info[$k]['phone_version']    = $v['phone_version_nickname']? $v['phone_version_nickname'] : $v['phone_version'];
            $info[$k]['device_unique_id'] = $v['device_unique_id'];
            $info[$k]['imei']             = $v['imei'] ? $v['imei'] : '手机无imei';
            $info[$k]['add_time']         = substr($v['add_time'], 0, 10);
        }
// p($info);exit();
        $params['filename'] = '亮屏设备';
        $params['data']     = $info;
        $params['head']     = array('所属省', '所属市', '所属区县', '营业厅名称', '手机品牌', '手机型号', '标识ID', 'IMEI', '设备添加时间');

        Csv::getCvsObj($params)->export();
    }
}