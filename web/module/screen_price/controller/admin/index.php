<?php
/**
 * alltosun.com  修改价格统计
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * szy: 宋志宇 (songzy@alltosun.com)
 * 2017年12月12日: 2016-7-26 下午3:05:10
 * Id
 */


class Action
{
    private $per_page = 20;
    private $member_id  = 0;
    private $member_res_name = '';
    private $member_res_id   = 0;
    private $member_info     = array();
    private $ranks           = 0;

    
public function __call($action = '', $params = array())
    {
        $search_filter = Request::Get('search_filter', array());
        $order         = 'ORDER BY `id` DESC';
        $page              = Request::get('page_no' , 1) ;
        $filter = $content_list =array();
        if (isset($search_filter['date_type']) && $search_filter['date_type']) {
            if (1 == $search_filter['date_type']) {
                $search_filter['start_time'] = $search_filter['end_time'] = date('Y-m-d');
        
            } else if (2 == $search_filter['date_type']) {
                $search_filter['start_time'] = date('Y-m-d',time() - 7 * 24 * 3600);
                $search_filter['end_time']   = date('Y-m-d');
        
            } else if (3 == $search_filter['date_type']) {
                $search_filter['start_time'] = date('Y-m-d',time() - 30 * 24 * 3600);
                $search_filter['end_time']   = date('Y-m-d');
            }
        }
        if (isset($search_filter['start_time']) && $search_filter['start_time']) {
            $filter['date >='] = date('Ymd', strtotime($search_filter['start_time']));
        } else {
            $filter['date >='] = date('Ymd', time() - 30 * 24 * 3600);
            $search_filter['start_time'] = date('Y-m-d', time() - 30 * 24 * 3600);
        }
        
        if (isset($search_filter['end_time']) && $search_filter['end_time']) {
            $filter['date <='] = date('Ymd', strtotime($search_filter['end_time']));
        } else {
            $filter['date <='] = $search_filter['end_time'] = date('Ymd', time());
            $search_filter['end_time'] = date('Y-m-d', time());
        }
        
        if (strtotime($search_filter['start_time']) + 30 * 24 * 3600 == strtotime($search_filter['end_time'])) {
            $search_filter['date_type'] = 3;
        }
        
        if ($search_filter['start_time'] == $search_filter['end_time']) {
            $search_filter['date_type'] = 1;
        }
        
        if (isset($search_filter['device_unique_id']) && $search_filter['device_unique_id']) {
            $filter['device_unique_id'] = $search_filter['device_unique_id'];
        }
        
         
        if (!$filter) {
            $filter = array(1 => 1);
        }        
        $count = _model('screen_device_price_stat')->getTotal($filter);
        if ($count) {
            $pager = new Pager($this->per_page);
            $stat_list = _model('screen_device_price_stat')->getList($filter, $order,$pager->getLimit());

            Response::assign('stat_list', $stat_list);
            if ($pager->generate($count,$page)) {
                Response::assign('pager', $pager);
            }
        }
        Response::assign('count' , $count);
        Response::assign('page' , $page);
        Response::assign('search_filter' , $search_filter);
        Response::display("admin/screen_price/content_list.html");
    }
    
    public function test2(){
//         _model('screen_device_price_record')->delete(array('1' => 1));
        _model('screen_device_price_stat')->delete(array('1' => 1));exit;
        $device_unique_id = '94fe2291bc83';
        $price            = 9527;
        $content_id = 47;
        $device_info = _model('screen_device')->read(array('device_unique_id' => $device_unique_id));
        screen_price_helper::record($device_info, $price, $content_id);
    }

    public function test()
    {

        
            set_time_limit ( 0 );
        
            // 今天取昨天的
            $date = date ( 'Y-m-d',time() - 3600*24 );
            $table = 'screen_device_price_record';
            $filter = array(
                    'add_time >=' => $date,
            );
            
            
            $list = _model($table)->getList($filter, 'GROUP BY `device_unique_id` ORDER BY `add_time` desc');
            
            foreach ($list as $k => $v){
               $this->szy($v['device_unique_id']);
            }
            echo '完成';
    }
    
    public function szy($device_unique_id)
    {
        if(!$device_unique_id){
            return false;
        }
        
        $change_num  = _model('screen_device_price_record')->getTotal(array('device_unique_id' => $device_unique_id));
        if(!$change_num){
            $change_num = 1;
        }
        $list = _model('screen_device_price_record')->read(array('device_unique_id' => $device_unique_id));
        $stat_list =array();
        $stat_list['date']             = date('Ymd',time($list['add_time']));
        $stat_list['add_time']         = $list['add_time'];
        $stat_list['city_id']          = $list['city_id'];
        $stat_list['area_id']          = $list['area_id'];
        $stat_list['change_num']       = $change_num;
        $stat_list['content_id']       = $list['content_id'];
        $stat_list['province_id']      = $list['province_id'];
        $stat_list['business_hall_id'] = $list['business_hall_id'];
        $stat_list['device_unique_id'] = $list['device_unique_id'];
        //查询统计表是否有数据
        $flag_stat = _model('screen_device_price_stat')->read(array('device_unique_id' => $device_unique_id));
        if ( !$flag_stat ) {
            _model('screen_device_price_stat')->create($stat_list);
        } else {
            _model('screen_device_price_stat')->update( $flag_stat['id'],
                    " SET `change_num` = $change_num + 1 "
                    );
        }
    }
    /**
     * 价格统计表记录
     * @param unknown $device_unique_id
     */
    public function screen_device_price_stat($device_unique_id)
    {
        if ( !$device_unique_id) {
            return false;
            //$device_unique_id = 'fc64ba905fa1';
        }
    
        $today = date('Y-m-d H:i:s',time());
        
        $order = ' ORDER BY `id` DESC ' ;
        $filter = array(
                'device_unique_id' => $device_unique_id,
        );
        $price_record_list = _model('screen_device_price_record')->read($filter,$order);
        
        $stat = _model('screen_device_price_stat')->read($filter);
        //没有统计表添加数据
        if ( !$stat ) {
            _model('screen_device_price_stat')->create( array(
                    'add_time'         => $today,
                    'device_unique_id' => $device_unique_id,
                    'price'            => $price_record_list['price'],
                    'province_id'      => $price_record_list['province_id'],
                    'city_id'          => $price_record_list['city_id'],
                    'area_id'          => $price_record_list['area_id'],
                    'business_id'      => $price_record_list['business_id'],
                    'date'             => date('Ymd'),
                    'change_num'       => 1
            ) );
        } else {
            _model('screen_device_price_stat')->update($filter,array(
                    'update_time'      => $today,
                    'device_unique_id' => $device_unique_id,
                    'price'            => $price_record_list['price'],
                    'province_id'      => $price_record_list['province_id'],
                    'city_id'          => $price_record_list['city_id'],
                    'area_id'          => $price_record_list['area_id'],
                    'business_id'      => $price_record_list['business_id'],
                    'date'             => date('Ymd'),
                    'change_num'       => $stat['change_num'] + 1
            ) );
        }
    
    }
   
    
    /**
     * 进入设备详情页
     */
    public function device_info()
    {
        $device_unique_id = Request::Get('device_unique_id' , '');
        
        $device_info = _model('screen_device')->read(array('device_unique_id' => $device_unique_id));
        Response::assign('device_info', $device_info);
        
        Response::display('admin/screen_price/device_info.html');
        
    }
    
    /**
     * 修改记录列表
     */
    public function price_record()
    {
        $order            = ' ORDER BY `id` DESC ';
        $page             = Request::get ( 'page_no', 1 );
        $device_unique_id = Request::Get('device_unique_id' , '');
        
        $filter =$price_list= array();
        
        $filter = array(
                'device_unique_id' => $device_unique_id
        );
        
        if($filter['device_unique_id'] == '') {
            $filter=array('1'=>1);
        }
        
        $price_list = get_data_list('screen_device_price_record', $filter, $order, $page, $this->per_page);
        
        Response::assign('page' , $page);
        Response::assign('price_list',$price_list);
        Response::display('admin/screen_price/record_list.html');
    }
}