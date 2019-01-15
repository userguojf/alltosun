<?php
/**
  * alltosun.com 营业厅widget文件 business_hall.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2016-7-26 下午3:24:20 $
  * $Id$
  */
set_time_limit(0);
class business_hall_widget
{
    private $app_id = 'af93510d';
    private $app_key = '2e76277aae45';

    /**
     * 计划任务
     * 更新营业厅信息
     * @param $page 页码
     */
    public function update_new_business($page=1, $max_count_page=0)
    {
        $type = 1;

        if (!(int)$page) {
            exit('请传递页码');
        }

        //更新进度状态,并获取新的页码信息
        $page_info        = $this->update_schedule($page, $type, $max_count_page);

        if ($page_info == 'fail') {
            echo '更新至'.$page.'页';
            return false;
        }
        $page             = $page_info[0];
        $max_count_page   = $page_info[1];

        $url = 'http://toe.51awifi.com/eapi/customer/list?';
        $serarch_filter['appid']      = $this->app_id;
        $serarch_filter['timestamp']  = time();
        $serarch_filter['token']      = md5($this->app_id.'_'.$this->app_key.'_'.$serarch_filter['timestamp']);
        $serarch_filter['projectId']  = 'MLye8zOzB2M=';
        $serarch_filter['pageNo']     = $page;
        $serarch_filter['pageSize']   = 100;

        $url .= http_build_query($serarch_filter);
        //echo htmlspecialchars($url);exit;


        $result = curl_get($url);

        $result = json_decode($result, true);

        if ($result['result'] != 'OK') {
            p($result);
            //更新进度表
            _model('setting')->update(array('field' => 'page_number_old'), array('value' => $page, 'update_time' => date('Y-m-d H:i:s')));
            exit('更新失败，等待下次重试');
        } else if (!isset($result['records']) || !$result['records']) {
            //更新进度表
            _model('setting')->update(array('field' => 'page_number_old'), array('value' => 1, 'update_time' => date('Y-m-d H:i:s')));
            exit('已经全部抓取！共'.$page.'页');
        } else {
            $list = $result['records'];
        }

        foreach ($list as $v) {
            $business_info  = _uri('old_business', array('customerId' => $v['customerId']));
            if (!$business_info) {
                $v['updateDate'] = number_format($v['updateDate'], 0, '', '');
                $res = _model('old_business')->create($v);
            } else {
               if ($business_info['updateDate'] < number_format($v['updateDate'], 0, '', '')){
                   $res = _model('old_business')->update(array('customerId' => $v['customerId']),$v);
               }
            }

        }

        ++$page;

        $this->update_new_business($page, $max_count_page);
//         $url = AnUrl("business_hall/admin/business_source/update_business_all?page={$page}&max_count_page={$max_count_page}&debug=1&powerby=alltosun");
//         echo "<script> window.location.href = '{$url}';</script>";
//         exit();
    }


    /**
     * 更新营业厅相关信息
     * @param $page 页码
     */
    public function update_business_info($page=1, $max_count_page=0) {
        $type = 2;
        if (!$page) {
            exit('请传递页码');
        }
        //更新进度状态,并获取新的页码信息
        $page_info        = $this->update_schedule($page, $type, $max_count_page);
        if ($page_info == 'fail') {
            echo '更新至'.$page.'页';return;
        }
        $page             = $page_info[0];
        $max_count_page   = $page_info[1];

        //查询营业厅信息
        $business_info = $this->get_business_hall_by_old($page);

        if ($business_info['info'] !='ok') {
            //更新进度
            _model('setting')->update(array('field' => 'page_number_new'), array('value' => $page, 'update_time' => date('Y-m-d H:i:s')));
            exit($business_info['msg']);
        } else if (empty($business_info['business_list'])) {
            _model('setting')->update(array('field' => 'page_number_new'), array('value' => 1, 'update_time' => date('Y-m-d H:i:s')));
            exit('已全部抓取完毕， 共'.$page.'页' );
        }

        foreach ($business_info['business_list'] as $v) {

            $business = _uri('business_hall', array('wifi_res_id' => $v['customerId']));

            if ($v['cascadeLevel'] != 4) {
                continue;
            }

            $v['updateDate'] = date('Y-m-d H:i:s', substr($v['updateDate'], 0, -3));
            if (!$business) {

                $area_info   = _uri('area', array('wifi_res_id' => $v['countyId']));

                if (!$area_info) {
                    echo $v['countId'];
                    continue;
                }

                $filter = array(
                        'title'       => $v['customerName'],
                        'type'        => 4,
                        'contact'     => $v['contact'],
                        'contact_way' => $v['contactWay'],
                        'area_id'     => $area_info['id'],
                        'city_id'     => $area_info['city_id'],
                        'province_id' => $area_info['province_id'],
                        'wifi_res_id' => $v['customerId'],
                        'user_number' => $v['account'],
                        'address'     => $v['address'],
                        'store_type'  => $v['storeType'],
                        'store_level' => $v['storeLevel'],
                        'store_scope' => $v['storeScope'],
                        'connect_type'=> $v['connectType'],
                        'update_time' => $v['updateDate']
                );

                $business_id = _model('business_hall')->create($filter);

                $member_info = _model('member')->read(
                        array('res_name'=> 'business_hall',
                                'res_id'      => $business_id
                        )
                );

                if ($member_info) {
                    continue;
                }

                $member_id = _model('member')->create(
                        array(
                                'member_user' => $v['account'],
                                'member_pass' => $v['password'],
                                'res_name'    => 'business_hall',
                                'res_id'      => $business_id,
                                'ranks'       => 5,
                                'hash'       => uniqid()
                        )
                );

                _model('group_user')->create(
                    array(
                    'member_id'  => $member_id,
                    'group_id'   => 26
                    )
                );
            } else if ($business['update_time'] < $v['updateDate']) {

                $area_info   = _uri('area', array('wifi_res_id' => $v['countyId']));

                if (!$area_info) {
                    continue;
                }

                $filter = array(
                        'title'       => $v['customerName'],
                        'type'        => 4,
                        'contact'     => $v['contact'],
                        'contact_way' => $v['contactWay'],
                        'area_id'     => $area_info['id'],
                        'city_id'     => $area_info['city_id'],
                        'province_id' => $area_info['province_id'],
                        'wifi_res_id' => $v['customerId'],
                        'user_number' => $v['account'],
                        'address'     => $v['address'],
                        'store_type'  => $v['storeType'],
                        'store_level' => $v['storeLevel'],
                        'store_scope' => $v['storeScope'],
                        'connect_type'=> $v['connectType'],
                        'update_time' => $v['updateDate']
                );

                _model('business_hall')->update($business['id'], $filter);

            }

        }

        ++$page;
        $this->update_business_info($page, $max_count_page);
        //$url = AnUrl("business_hall/admin/business_source/update_business_info?page={$page}&max_count_page={$max_count_page}&debug=1&powerby=alltosun");
        //echo "<script> window.location.href = '{$url}';</script>";
        //exit();
    }

    /**
     * 更新进度
     * @param $page 页码
     * @param $type 更新类型
     * @param $max_count_page 本次更新的最大页码
     */
    public function update_schedule($page, $type, $max_count_page=0)
    {
        //执行每次执行的页码数
        $count_page = business_hall_config::$count_page;

        //第一次查询, 记录进度表

        if ($type == 1) {
            $field = 'page_number_old';
        } else {
            $field = 'page_number_new';
        }

        $field_info = _uri('setting', array('field' => $field));
        if ($page == 1) {
            if (!$field_info) {
                _model('setting')->create(array('field' => $field, 'value' =>1));
            } else {
                $page = $field_info['value'];
            }
            $max_count_page = $page+$count_page;

        }

        //写入更新页码
        if ($page == $max_count_page) {

            _model('setting')->update(array('field' => $field), array('value' => $page));
            //exit('更新至'.$page.'页，待续');
            return 'fail';
        }

        return array($page, $max_count_page);
    }

    /**
     * 本地获取营业厅信息接口
     * @param $page 页码
     * @param $max_page 每页最大数据条数
     */
    public function get_business_hall_by_old($page='', $max_page =100)
    {

         $business_list = array();
         if (!empty($page) && !(int)$page) {
             return array('info' => 'FAIL', 'msg' => '不合法的页码', 'business_list' => $business_list);
         } else if ($page){
             $pager = ($page-1)*$max_page;
             $business_list = _model('old_business')->getList(array('1'=>'1'), 'LIMIT '.$pager.','.$max_page);
             return array('info' => 'ok', 'msg' => 0, 'business_list' => $business_list);
         } else {
             $business_list = _model('old_business')->getList(array('1'=>'1'));
             return array('info' => 'ok', 'msg' => 0, 'business_list' => $business_list);
         }


    }

    /**
     *  更新member表字段-wangjf
     *  @param string $field 字段名
     *  @param string $value 字段值
     *  @param boolean $is_all 是否更新全部，如果false则更新活跃营业厅
     *  @param int    $max 最大数据量
     */
    public function update_member_field($value='', $is_all=false, $field = 'member_pass', $max=1000)
    {
        if (!$value) {
            return '请传递字段值';
        }
        $value = md5($value);
        $page = _uri('setting', array('field' => 'member_pass_page'), 'value');
        if (!$page) {
            _model('setting')->create(array('field' => 'member_pass_page', 'value' => 1));
            $page = 1;
        }

        $business_filter    = array();

        if (!$is_all) {
            $business_filter['activity'] = 1;
        } else {
            $business_filter[1] = 1;
        }

        $pager = ($page-1)*$max;
        //$count = _model('business_hall')->getTotal($business_filter);
        $list = _model('business_hall')->getList($business_filter, ' LIMIT '.$pager.','.$max);

        if (!$list) {
            exit('更新完毕');
        }

        foreach ($list as $k => $v) {
            $member_filter['member_user'] = $v['user_number'];
            $member_info = _uri('member', $member_filter);
            if (!$member_info) {
                continue;
            }
            $member_pass = _uri('member', $member_filter, 'member_pass');
            if ($member_pass == $value) {
                contrnue;
            }
            //更新
            $res = _model('member')->update($member_filter, array('member_pass' => $value));
        }
        echo $page;
        ++$page;
        _model('setting')->update(array('field' => 'member_pass_page'), array ('value' => $page));

    }

    /**
     * 计划任务
     * 根据user_business_hall_num表更新活跃营业厅
     */
    public function add_activity_by_user_table()
    {
        $page       = 1;
        //取记录表页码
        $page_info  = _uri('setting', array('field' => 'update_activity_page'));

        if (!$page_info) {
            _model('setting')->create(array('field' => 'update_activity_page', 'value' => 1));
        } else {
            $page = $page_info['value']?$page_info['value']:1;
        }

        $max      = 1000;
        $pager    = ($page-1)*$max;
        $list = _model('user_business_hall_num')->getAll('SELECT DISTINCT business_hall_id FROM user_business_hall_num WHERE add_time >"2016-04-16 00:00:00" LIMIT '.$pager.','.$max);

        //p($list);exit;
        if (!$list) {

            //更新记录表置1
            _model('setting')->update(array('field' => 'update_activity_page'), array('value' => 1));
            exit('已更新完毕');
        }
        $count = 0;
        foreach($list as $k => $v) {
            $business_hall_info = _uri('business_hall', $v['business_hall_id']);
            if (!$business_hall_info || $business_hall_info['activity'] == 1){
                continue;
            }
            ++$count;
            $result = _model('business_hall')->update($v['business_hall_id'], array('activity' => 1));
            //p($result);exit;
            if (!$result) {
                continue;
            }
        }
        ++$page;
        //更新记录表
        _model('setting')->update(array('field' => 'update_activity_page'), array('value' => $page));
        echo $page;
    }

    /**
     * 获取活跃厅列表
     */
    public function get_active_list()
    {
        $page       = 1;
        //取记录表页码
        $page_info  = _uri('setting', array('field' => 'get_active_page'));

        if (!$page_info) {
            _model('setting')->create(array('field' => 'get_active_page', 'value' => 1));
        } else {
            $page = $page_info['value']?$page_info['value']:1;
        }

        $max      = 1000;
        $pager    = ($page-1)*$max;
        $list = _model('user_business_hall_num')->getAll('select business_hall_id from user_business_hall_num where add_time >"2016-04-16 00:00:00" GROUP BY business_hall_id LIMIT '.$pager.','.$max);
        if (!$list) {
            //更新记录表置1
            _model('setting')->update(array('field' => 'get_active_page'), array('value' => 1));
            return array('info' => 'fail', 'errno' => 1, 'page' => $page);
        }
        ++$page;
        //更新记录表
        _model('setting')->update(array('field' => 'get_active_page'), array('value' => $page));
        return array('info' => 'ok', 'errno' => 0, 'list' => $list, 'page' => $page-1);
    }

    /**
     * 更新营业厅类别
     * @param int $page
     */
    public function update_store($page) {

        $appid      = 'af93510d';
        $app_key    = '2e76277aae45';
        $url        = 'http://toe.51awifi.com/eapi/report/wifidetail?';

        $serarch_filter['appid']      = $appid;
        $serarch_filter['timestamp']  = time();
        $serarch_filter['token']      = md5($appid.'_'.$app_key.'_'.$serarch_filter['timestamp']);
        $serarch_filter['merchantid'] = Security::encrypt(56739, $app_key);
        $serarch_filter['projectId']  = 'MLye8zOzB2M=';
        $serarch_filter['pageNo']     = $page;
        $serarch_filter['pageSize']   = 100;
        //拼接
        $url .= http_build_query($serarch_filter);
        $result = curl_get($url);
        $result_arr = json_decode($result, true);
        if ($result_arr['result'] != 'OK') {
            p($result_arr);exit;
        }

        if (empty($result_arr['records'])) {
            exit('更新完毕');
        }
        foreach ($result_arr['records'] as $k => $v) {

            $hall_info = _uri('business_hall', array('wifi_res_id' => $v['customerId']));
            if (!$hall_info) {
                continue;
            }

            $filter = array();
            if ($v['storeType']) {
                $filter['store_type'] = $v['storeType'];
            }

            if ($v['storeLevel']){
                $filter['store_level'] = $v['storeLevel'];
            }

            if ($v['storeScope']) {
                $filter['store_scope'] = $v['storeScope'];
            }

            if ($v['connectType']) {
                $filter['connect_type'] = $v['connectType'];
            }

            if (empty($filter)){
                continue;
            }
            $res = _model('business_hall')->update(array('wifi_res_id' => $v['customerId']), $filter);
        }

    }

    /**
     * 根据营业厅member获取搜索数据
     */
    public function get_search_by_member($params = array())
    {

        if (isset($params['member_info']['res_name']) && $params['member_info']['res_name'] && isset($params['member_info']['res_id'])) {
            if ($params['member_info']['res_name'] != 'group' && !$params['member_info']['res_id']) {
                return false;
            }
            //查询省厅地区信息
            $region_info = business_hall_helper::get_region_by_member($params['member_info']['res_name'], $params['member_info']['res_id']);

            return $region_info;
        } else {
            return false;
        }

    }

    /**
     * 根据管理员生成权限内的条件
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
     * 初始化搜索条件
     * @param unknown $member_info
     */
    public function init_filter($member_info, $search_filter)
    {
        $filter = $this->default_search_filter($member_info);

        //搜索判断
        if (!empty($search_filter['province_id'])) {
            $filter['province_id'] = $search_filter['province_id'];

            $province = array('province_id' => $search_filter['province_id']);
            Response::assign('where1' , $province);
        }
        if (!empty($search_filter['city_id'])) {
            $filter['city_id'] = $search_filter['city_id'];

            $city = array('city_id' => $search_filter['city_id']);
            Response::assign('where2' , $city);
        }

        if (!empty($search_filter['area_id'])) {
            $filter['area_id'] = $search_filter['area_id'];
        }

        if (!empty($search_filter['business_hall_title'])) {
            $search_filter['business_hall_title'] = trim($search_filter['business_hall_title']);
            $business_hall_id = _uri('business_hall', array('title' => $search_filter['business_hall_title']), 'id');
            if ($business_hall_id) {
                $filter['business_hall_id'] = $business_hall_id;
            }
        }

        return $filter;

    }

}