<?php
/**
 * alltosun.com  screen_status.php 亮屏状态
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * szy: 宋志宇 (songzy@alltosun.com)
 * 2018年3月13日: 2016-7-26 下午3:05:10
 * Id
 */
class Action
{
    private $member_info;
    private $member_id = 0;
    private $dres_id = 0;
    private $member_res_name = '';
    private $subordinate_res_name;
    private $detail_field = '';
    public function __construct()
    {
        $this->member_id   = member_helper::get_member_id();
        $this->member_info = member_helper::get_member_info($this->member_id);
        if ($this->member_info) {
            $this->member_res_name = $this->member_info['res_name'];
            $this->res_id   = $this->member_info['res_id'];
            $this->ranks           = $this->member_info['ranks'];
        }
        Response::assign('member_info', $this->member_info);
    }

    public function __call($action='', $param=array())
    {
        if (!$this->member_info) {
            return '请先登录';
        }
        
        //接收变量-- today 今日  weekday 周  somedays 自定义时间
        $time_type  = tools_helper::get('time_type', 'weekday');
        $start_time = tools_helper::get('start_time', '');
        $end_time   = tools_helper::get('end_time', '');
        
        $filter     = [];
        $date       = date('Ymd');
        $start_date = $date;
        $end_date   = $date;

        
        //处理 开始 AND 结束 时间
        if ('weekday' == $time_type) {
            $start_date = date('Ymd', time() - 7 * 24 * 3600);;
        
        } else if ('somedays' == $time_type) {
            Response::assign('start_time', $start_time);
            Response::assign('end_time', $end_time);
        
            $start_date = date('Ymd' , strtotime($start_time));
            $end_date   = date('Ymd' , strtotime($end_time));
        
            if ($start_date > $end_date) {
                return '非法操作：开始时间大于结束时间';
            }
        }
        //组装filter 条件 用来查online 表中 day字段
        if ('today' == $time_type) {
            $filter['day'] = (int)$date;
        } else {
            $filter['day >'] = $start_date;
            $filter['day <='] = $end_date;
        }
       
         //获取覆盖厅店数
        $cover_business_hall_count  = count($this->get_cover_business_hall($this->member_info['res_name'], $this->member_info['res_id']));
        
        //获取权限下所有厅店
        $business_hall_count        = count($this->get_business_hall($this->member_info['res_name'], $this->member_info['res_id']));
        
        //获取安装设备数
        $install_device_count       = count($this->get_install_device($this->member_info['res_name'], $this->member_info['res_id']));

        //获取活跃台数
        $active_device_count        = count($this->get_active_device($this->member_info['res_name'], $this->member_info['res_id'],$filter));
        
        //获取设备总数数
        //$device_count               = count($this->get_device_all($this->member_info['res_name'], $this->member_info['res_id']));
        Response::assign('time_type', $time_type);   //时间类型
        Response::assign('end_date', $end_date);
        Response::assign('start_date', $start_date); //分配后端日期  供前台使用
        Response::assign('start_year', substr($start_date, 0, 4));
        Response::assign('start_month', substr($start_date, 4, 2));
        Response::assign('start_day', substr($start_date, 6, 2));
        
        Response::assign('end_year', substr($end_date, 0, 4));
        Response::assign('end_month', substr($end_date, 4, 2));
        Response::assign('end_day', substr($end_date, 6, 2)); //时间段
        Response::assign('cover_business_hall_count', $cover_business_hall_count); //覆盖营业厅数
        Response::assign('business_hall_count', $business_hall_count); //营业厅总数
        Response::assign('install_device_count', $install_device_count); //已安装设备数
        Response::assign('active_device_count', $active_device_count); //活跃设备数
        Response::display('status/index.html');
    }


   public function details()
   {
       $time_type  = tools_helper::get('time_type', '');
       $start_time = tools_helper::get('start_time', '');
       $end_time   = tools_helper::get('end_time', '');
       $type       = tools_helper::get('type', 0); // 1, 已覆盖厅店等 2，累计安装设备 3，活跃设备量 4，离线设备量
       $res_name   = ''; //要查看的下级
       $res_id     = '';  //要查看的下级id
       $date       = date('Ymd');
       $search_filter    = Request::Get('search_filter', array());
       
       $filter = array();
       if (empty($search_filter)) {
           //首页进来则按本身权限去查
           $res_name  = $this->member_info['res_name'];
           $res_id    = $this->member_info['res_id'];
       
       } else {
           //详情页查看下级详情列表
           $res_name   = $search_filter['res_name'];
           $res_id     = $search_filter['res_id'];
       }
            
       $filter = $this->get_default_device_filter($res_name, $res_id);
       
      
       if ($res_name == 'group') {
           $this->detail_field= 'province_id';
           $this->subordinate_res_name = 'province';
       } else if ($res_name == 'province') {
           $this->detail_field = 'city_id';
           $this->subordinate_res_name = 'city';
       } else if ($res_name == 'city') {
           $this->detail_field = 'area_id';
           $this->subordinate_res_name = 'area';
       } else {
           $this->detail_field = 'business_id';
           $this->subordinate_res_name = 'business_hall';
       }
       
       //获取归属地下的设备
       $device_list = _model('screen_device')->getList($filter);
     
       $new_device_list = array();
       //p($device_list);
       
       //地区id作为健
       foreach ( $device_list as $k => $v ) {
           //第一次
           if (empty($new_device_list[$v[$this->detail_field]])) {
               $new_device_list[$v[$this->detail_field]] = array(
                       'install_device'                => array($v['device_unique_id']),
                       'conver_business_hall'          => array($v['business_id']),//覆盖厅店
//                        'business_hall_all'             => count($this->get_business_hall($res_name, $res_id)),//所有厅店
                      // $this->get_business_hall($this->subordinate_res_name, $this->detail_field));
               );
           }
       }
       
       
       //处理详情页数据
       $new_data = array();
       foreach ( $new_device_list as $k => $v ) {
           //获取每行数据的归属地或机型昵称
           $title = $this->get_detail_title($k);
           if (!$title) {
               continue;
           }
           
           if(!empty($search_filter)){
               $res_name = $this->subordinate_res_name;
               $res_id   = $k;
             
               $active_device  = $this->get_active_device($res_name, $k,array());
           }else{
              
               $active_device  = $this->get_active_device($res_name, $res_id,array());
           }
           //获取活跃设备
           $active_device_count = count($active_device);
           if ($type == 4) {
               //获取离线数据
               $data    = count($v['install_device']) - $active_device_count;
               if ($data < 0) {
                   $data = 0;
               }
           } else {
               $data = 0;
           }
           
           $tmp = array(
                   'title'                 => $title,
                   'active_device_count'   => $active_device_count,
                   'install_device_count'  => count($v['install_device']),
                   'conver_business_hall_count'    => count($v['conver_business_hall']),
//                    'business_hall_count'    => $v['business_hall_all'],
                   'data'       => $data,
           );
           
           //排序所需
           $sorts[] = $data;
           
           $tmp['res_id']              = $k;
           
           $new_data[] = $tmp;
       }
       
       
       Response::assign('time_type', $time_type);
       Response::assign('start_time', $start_time);
       Response::assign('end_time', $end_time);
       Response::assign('type',$type);
       Response::assign('new_data',$new_data);
       Response::assign('search_filter',$search_filter);
       Response::assign('subordinate_res_name', $this->subordinate_res_name);
       Response::display('status/details.html');
   }

   /**
    * 获取详情页数据的标题
    * @param unknown $res_id
    */
   private function get_detail_title($res_id)
   {
       $title = '';
       //获取归属地名称
       if (in_array($this->subordinate_res_name, array('province', 'city', 'area'))) {
           $title = business_hall_helper::get_info_name($this->subordinate_res_name, $res_id, 'name');
       } else if ($this->subordinate_res_name == 'business_hall') {
           $title = business_hall_helper::get_info_name($this->subordinate_res_name, $res_id, 'title');
       } else {
           return false;
       }
       return $title;
   }
    
   
    /**
     * 获取活跃设备 （默认七日）
     * @param string $res_name
     * @param int    $res_id
     * @param array  $param
     */
    private function get_active_device($res_name, $res_id,$param)
    {
        //借用设备的默认条件
        $filter = $this->get_default_device_filter($res_name, $res_id);
        if(!empty($param)){
            foreach ($param as $k => $v){
                $filter[$k] = $v;
            }
        }
        //在线
        $filter['is_online']    = 1;
        //清除掉设备表特有的status字段条件
        unset($filter['status']);
        //获取设备
        $devices = _model('screen_device_online_stat_day')->getFields('device_unique_id', $filter, ' GROUP BY `device_unique_id`');
        return $devices;
    }
    
    
    
    /**
     * 获取设备 （默认今天）
     * @param unknown $res_name
     * @param unknown $res_id
     */
    private function get_device_all($res_name, $res_id)
    {
        //借用设备的默认条件
        $filter = array();
        $filter['day']          = date('Ymd');
        
        if (in_array($res_name, array('province', 'city', 'area'))) {
            $filter[$res_name.'_id'] = $res_id;
        } else if ($res_name == 'business_hall') {
            $filter['business_id'] = $res_id;
        } else if ($res_name != 'group') {
            return array('id' => 0);
        }
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

        //为了兼容后续有详情页，先把所有有设备营业厅id取出
        $business_hall_ids = _model('screen_device')->getFields('business_id', $filter, ' GROUP BY `business_id` ');
        return $business_hall_ids;
    }
    
    /**
     * 获取权限下所有营业厅(包括没有设备的营业厅)
     * @param unknown $res_name
     * @param unknown $res_id
     */
    private function get_business_hall($res_name, $res_id)
    {
        //初始化默认条件
        $filter = array();
        if (in_array($res_name, array('province', 'city', 'area'))) {
            $filter[$res_name.'_id'] = $res_id;
        } else if ($res_name == 'business_hall') {
            $filter['id'] = $res_id;
        } else if ($res_name == 'group') {
           $filter['1'] = 1;
        }
        //为了兼容后续有详情页，先把所有有设备营业厅id取出
        $business_hall_ids = _model('business_hall')->getFields('id', $filter, ' GROUP BY `id` ');
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