<?php

/**
 * alltosun.com  设备总览  
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * szy: 宋志宇 (songzy@alltosun.com)
 * 2017年12月22日: 2016-7-26 下午3:05:10
 * Id
 */
require_once ROOT_PATH."/helper/PHPExcel.php";
require_once ROOT_PATH."/helper/PHPExcel/Writer/Excel2007.php";
probe_helper::load('func');


class Action
{

    private $per_page = 10;
    private $member_id  = 0;
    private $member_info = array();
    public function __construct()
    {
        // 获取当前登录用户
        $this -> member_info = member_helper::get_member_info();

        if ( $this -> member_info ) {
            // 当前登录ID
            $this -> member_id = $this -> member_info['id'];
        }

        Response::assign('member_info', $this -> member_info);
    }

  
    
    /**
     *导入设备后展示页
     */
    public function index()
    {   
        $search_filter   = Request::Get('search_filter', array());
        $flag            = Request::Get('flag', '');
        //判断是否是导入后返回方法
        $str             = Request::Get('str', '');
        $total           = Request::Get('total', '');
        $count           = 0;
        $error_total     = Request::Get('error_total', '');
        $is_export       = Request::Get('is_export', 0);
        $is_error_export = Request::Get('is_error_export', 0);
        $order         = " ORDER BY `id` DESC ";
        
        //$a = unserialize(base64_decode($str));
        if($str){
            $default_value  = array(
                    'search_type'     => 0,
                    'put_type'        => 1,
                    'search_flag'     => 0,
            );
        }else{
            $default_value  = array(
                    'search_type'     => 0, //0 探针 1 rfid
                    'put_type'        => 1,
                    'order_type'      => 1,
            );
        }
       
        $search_filter  = set_search_filter_default_value($search_filter, $default_value);
        $filter = $list = array();
        //集团权限
        if ('province' != $this -> member_info['res_name']) {
            return "省级权限特定功能";
        }
                if($str){
                    $list=explode (",",$str);
                
                    $error_total = array_pop($list);
                    $total = array_pop($list);
                    $filter_tmp =$list;
                    $filter['id'] =$filter_tmp;
                if($search_filter['put_type'] == '1' && $search_filter['search_type'] == '0' && $search_filter['search_flag'] == '0'){
                    $filter['error_type'] = '正确数据';
                }
                
                if($search_filter['put_type'] == '1' && $search_filter['search_type'] == '0' && $search_filter['search_flag'] == '1'){
                    $filter['error_type != '] = '正确数据';
                }
                
                
                //导出错误数据
                if ($is_error_export == 1) {
                    $list = _model('device_application')->getList($filter ,$order);
                  $this->error_export($list);
                   $del_list =array();
                   foreach ($list as $k => $v){
                       $del_list[] = $v['id'];
                   }
                    //导出后删除导出的错误数据
                      _model('device_application')->delete($del_list);
                      return array('导出成功','success', AnUrl("probe_pandect/admin/device_application"));
                      
                    exit();
                }
                
                $count = $total - $error_total;
                
                if ($count && $search_filter['search_flag'] == '0') {
                        $pager = new Pager($this->per_page);
                        $list  = _model('device_application')->getList($filter, $pager->getLimit());
                        if ($pager->generate($count)) {
                            Response::assign('pager', $pager);
                        }
                    }elseif ($error_total && $search_filter['search_flag'] == '1'){
                        $pager = new Pager($this->per_page);
                        $list  = _model('device_application')->getList($filter, $pager->getLimit());
                        if ($pager->generate($error_total)) {
                            Response::assign('pager', $pager);
                        }
                    }
                    
              }
             
              //待审批设备
              if($search_filter['put_type'] == '2' ){
                  $filter['error_type'] = '正确数据';
                  $filter['status'] = 0;
                  $tmp_list = _model('device_application')->getList($filter, 'GROUP BY create_time ,`city_id`  ORDER BY `create_time` desc');
                  $count = count($tmp_list);
                  $per_tpl    = MODULE_PATH.'/probe_pandect/template/widget/device_list_for_provnice.html';
                  
                  if($count){
                      $pager = new Pager($this->per_page);
                      $list = _model('device_application')->getList($filter, 'GROUP BY create_time ,`city_id`  ORDER BY `create_time` desc');
                      if($pager->generate($count)){
                          Response::assign('pager',$pager);
                      }
                  }
                  
                  Response::assign('list', $list);
                  if ($list) {
                      Response::fetch($per_tpl);
                  }
              }
              
              //已审批设备
              if($search_filter['put_type'] == '3' ){
                  $filter['error_type'] = '正确数据';
                  $filter['status'] = 1;
                  $filter['order_status'] = $search_filter['order_type'];
            
                  $tmp_list = _model('device_application')->getList($filter, 'GROUP BY create_time ,`city_id`  ORDER BY `create_time` desc');
                  $count = count($tmp_list);
                  
                  $per_tpl    = MODULE_PATH.'/probe_pandect/template/widget/approved_devices_of_status_list.html';
                  $html       = '';
                  
                  if($count){
                      $pager = new Pager($this->per_page);
                        $list = _model('device_application')->getList($filter, 'GROUP BY create_time ,`city_id`  ORDER BY `create_time` desc');
                      if($pager->generate($count)){
                          Response::assign('pager',$pager);
                      }
                  }
                  Response::assign('list', $list);
                  if ($list) {
                      $html = Response::fetch($per_tpl);
                  }
              }
              p($search_filter);
            Response::assign('filter',$filter);
            Response::assign('str',$str);
            Response::assign('total',$total);
            Response::assign('count',$count);
            Response::assign('error_total',$error_total);
            Response::assign('list',$list);
            Response::assign('search_filter',$search_filter);
            Response::display("admin/application.html");
        
        
    }
    
    public function approval_pending()
    {   
        $search_filter   = Request::Get('search_filter', array());
        $order         = " ORDER BY `id` DESC ";
        //标记
        $search_filter['put_type'] = 2;
        $filter = $list = array();
        $filter['error_type'] = '正确数据';
        // 搜索省
        if ( isset($search_filter['province']) && $search_filter['province'] ) {
            $filter['province_id'] = $search_filter['province'];
        }
        
        // 搜索市
        if ( isset($search_filter['city']) && $search_filter['city'] ) {
            $filter['city_id'] = $search_filter['city'];
        }
        
        if($search_filter['search_type'] == '0' ){
            $filter['device_type'] = '探针';
        }elseif ($search_filter['search_type'] == '1'){
            $filter['device_type'] = 'rfid';
        }
        //0  待审批 1 已审批 2已取消
        if($search_filter['probe_status'] == '1'){
            $filter['status'] = '0';
        }elseif ($search_filter['probe_status'] == '2'){
            $filter['status'] = '2';
        }elseif ($search_filter['probe_status'] == '0'){
            $filter['status !='] = '1';
        }
            $per_tpl    = MODULE_PATH.'/probe_pandect/template/widget/device_list_for_provnice.html';
            //total 不好分页
            $tmp_list = _model('device_application')->getList($filter,'GROUP BY create_time ,`city_id`  ORDER BY `create_time` desc');
            $count = count($tmp_list);
            if($count){
                $pager = new Pager($this->per_page);
                
                $list = _model('device_application')->getList($filter, 'GROUP BY create_time ,`city_id`  ORDER BY `create_time` desc');
                if($pager->generate($count)){
                    Response::assign('pager',$pager);
                }
            }
            Response::assign('list', $list);
            
            $html = Response::fetch($per_tpl);
            Response::assign('count',$count);
            Response::assign('search_filter',$search_filter);
            Response::display("admin/application.html");
            
    }
    
    
    /**
     * 已审批设备列表
     */
    public function device_order_list()
    {   
        $search_filter   = Request::Get('search_filter', array());
        $order         = " ORDER BY `id` DESC ";
        //标记
        $search_filter['put_type'] = 3;
        $filter = $list = array();
        $filter['error_type'] = '正确数据';
        // 搜索省
        if ( isset($search_filter['province']) && $search_filter['province'] ) {
            $filter['province_id'] = $search_filter['province'];
        }
    
        // 搜索市
        if ( isset($search_filter['city']) && $search_filter['city'] ) {
            $filter['city_id'] = $search_filter['city'];
        }
    
        if($search_filter['search_type'] == '0' ){
            $filter['device_type'] = '探针';
        }elseif ($search_filter['search_type'] == '1'){
            $filter['device_type'] = 'rfid';
        }
        //1  代发货 2 已发 3已拒绝
        if($search_filter['order_type'] == '1'){
            $filter['order_status'] = '1';
        }elseif ($search_filter['order_type'] == '2'){
            $filter['order_status'] = '2';
        }elseif ($search_filter['order_type'] == '3'){
            $filter['order_status'] = '3';
        }
        $per_tpl    = MODULE_PATH.'/probe_pandect/template/widget/approved_devices_of_status_list.html';
        //total 不好分页
        $tmp_list = _model('device_application')->getList($filter,'GROUP BY update_time ,`city_id`  ORDER BY `update_time` desc');
        $count = count($tmp_list);
        if($count){
            $pager = new Pager($this->per_page);
    
            $list = _model('device_application')->getList($filter, 'GROUP BY update_time ,`city_id`  ORDER BY `update_time` desc');
            if($pager->generate($count)){
                Response::assign('pager',$pager);
            }
        }
    
        Response::assign('list', $list);
        
        $html = Response::fetch($per_tpl);
        Response::assign('count',$count);
        Response::assign('search_filter',$search_filter);
        Response::display("admin/application.html");
    
    }
    
    /**
     * 待审批设备详情页
     */
    public function application_details()
    {
        $province_id = Request::Get('province_id','');
        $city_id     = Request::Get('city_id','');
        $create_time = Request::Get('create_time','');
        $device_type = Request::Get('device_type','');
        
        $filter = array(
                'device_type' => $device_type,
                'province_id' => $province_id,
                'city_id'     => $city_id,
                'create_time' => $create_time,
                'error_type' => '正确数据',
        );
        $list = array();
        $per_tpl    = MODULE_PATH.'/probe_pandect/template/widget/application_details_list.html';
        $list = _model('device_application')->getList($filter);
        
        Response::assign('list',$list);
        Response::assign('details','details');
        $html = Response::fetch($per_tpl);
        Response::display("admin/application.html");
    }
    //上传文件模板说明
    public function load_instruction()
    {
        $objPHPExcel = new PHPExcel();
        


        //设备类型 设备申请时间 省份 地市 设备号
        //设置宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(30);
    
        $excelobj = $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A1', '请在此列输入设备类型')
        ->setCellValue('B1', '请在此列输入省份')
        ->setCellValue('C1', '请在此列输入地市')
        ->setCellValue('D1', '请在此列输入地区')
        ->setCellValue('E1', '请在此列输入营业厅')
        ->setCellValue('F1', '请在此列输入渠道视图编码')
        ->setCellValue('G1', '请在此列输入收货地址')
        ->setCellValue('H1', '请在此列输入联系人')
        ->setCellValue('I1', '请在此列输入联系人电话')
        ->setCellValue('J1', '请在此列输入设备数量')
        ->setCellValue('K1', '请在此列输入备注');
    
        $excelobj = $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValueExplicit('A100', '')
        ->setCellValueExplicit('B100', '')
        ->setCellValueExplicit('C100', '')
        ->setCellValueExplicit('D100', '')
        ->setCellValueExplicit('E100', '')
        ->setCellValueExplicit('F100', '')
        ->setCellValueExplicit('G100', '')
        ->setCellValueExplicit('H100', '')
        ->setCellValueExplicit('I100', '')
        ->setCellValueExplicit('J100', '')
        ->setCellValueExplicit('K100', '');
    
        //设置sheet标题
        $objPHPExcel->getActiveSheet()->setTitle('导入探针信息');
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="探针申请模板.xls"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0
    
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }
    
    
    /**
     * 导入探针
     */
    public function add_device()
    {
        p(1);
        p($_FILES);exit;
        $time = date('Y-m-d H:i:s',time());
         if (isset($_FILES['probe']['name']) && $_FILES['probe']['name']) {
             $file = $_FILES['probe'];
             
             if (!$file['name']) {
                 return '请选择上传的Excel文件';
             }
            
          //   jpg png gif jpeg bmp flv swf rar zip msoffice xls xlsx doc docx txt psd mp3 mp4 mpeg-4 mpeg4
          
//              $allow_type = Config::get('allow_type');
             //  /data/svn_data/open/trunk/web/public/201711awifi_probe/web/upload
             $upload_path = UPLOAD_PATH;
             
             $fail_msg = check_upload($file, 0, 1);
              
             if ($fail_msg) {
                 return array($fail_msg, 'error', AnUrl('probe_pandect/admin/device_application'));
             }
             $ext = substr($file['name'], strrpos($file['name'], '.')+1);
             
//              if (!in_array(strtolower($ext), $allow_type)) {
//                  return '文件格式不正确';
//              }
             if(strtolower($ext) !='xls'){
                 return '文件格式不正确';
             }
             
             if (empty($fail_msg)) {
                 $file_path = an_upload($file['tmp_name'], $ext);
             }
         }
           $file_path = ROOT_PATH.'/upload'.$file_path;
            
           require_once MODULE_CORE.'/helper/reader.php';
           
           if (!file_exists($file_path)) {
               return '文件不存在';
           }
            
           $phpexcel = new Spreadsheet_Excel_Reader();
           $phpexcel->setOutputEncoding('CP936');
           $phpexcel->read($file_path);//正式机
           $results = $phpexcel->sheets[0]['cells'];
           $cols = $phpexcel->sheets[0]['numCols'];
           $rows = $phpexcel->sheets[0]['numRows'];
           //Excel第行 需要去掉
           array_shift($results);
           $data = $err_list = array();
           foreach ($results as $k => $v) {
              
               //转码
               for($i = 1; $i <= $cols; $i ++) {
                   if (!isset($v[$i]) || !$v[$i]) {
                       $v[$i] = '';
                       continue;
                   }
                   $v[$i] = iconv('GB2312', 'UTF-8//TRANSLIT//IGNORE', $v[$i]);
                   $v[$i] = trim($v[$i]);
               }
               
               //获取省id
               $provice_id= _model('province')->getFields('id',array('name' => $v[2]));
               //获取市id
               $city_id= _model('city')->getFields('id',array('name' => $v[3]));
               //获取区id
               $area_id= _model('area')->getFields('id',array('name' => $v[4]));
               //获取营业厅id
               $business_info= _model('business_hall')->read(array('title' => $v[5]));
               $data['device_type']     = $v[1];
               $data['province_id']     = $provice_id[0];
               $data['city_id']         = $city_id[0];
               $data['area_id']         = $area_id[0];
               $data['business_id']     = $business_info['id'];
               $data['user_number']     = $v[6];
               $data['address']         = $v[7];
               $data['linkman']         = $v[8];
               $data['phone']           = $v[9];
               $data['device_num']      = $v[10];
               $data['remark']          = $v[11];
               $data['error_type']      = '正确数据';
               $data['business_level']  = $business_info['store_level'];
          
               $user_number = probe_pandect_helper::auto_user_number($data['user_number']);
               
               if(!$user_number){
                   $data['error_type'] = '渠道编码错误';
               }
               
                    $is_phone = probe_pandect_helper::auto_phone($data['phone']);
                   
                   if(!$is_phone){
                       $data['error_type'] = '电话号码错误';
                   }
                   
                   if($data['device_num'] == ''){
                       $data['error_type'] = '申请数量为空';
                   }
                   
                   if($data['linkman'] == ''){
                       $data['error_type'] = '联系人为空';
                   }
                   
                   if($data['address'] == ''){
                       $data['error_type'] = '详细地址为空';
                   }
                   
                   $data['create_time']    = $time;
                    
                  $id = _model('device_application')->create($data);
                  $err_list[]= $id;
           }
                
                //添加的所有数据
                $tmp_list = _model('device_application')->getList($err_list);
                //删除正确数据
                $error_list = probe_pandect_helper::diff_array($tmp_list, 'error_type');
                 //导入总数量
                $total = count($results);
                //错误数
                $error_total = count($error_list);
                $err_list['total'] = $total;
                $err_list['error_total'] = $error_total;
                $str = implode(',',$err_list);
                
                //$str = base64_encode(serialize($err_list));
//                 var_dump($err_list);
//                 var_dump($str);exit;
                return array('操作成功','success', AnUrl("probe_pandect/admin/device_application?str=$str"));
    }
    
    
    /**
     *  导出错误数据
     */
    public function error_export($list)
    {
        $new_list = array();
        foreach ($list as $k => $v) {
    
            $tmp = array();
            $tmp['error_type'] = $v['error_type'];
            $tmp['device_type'] =$v['device_type'];
            $tmp['province'] = business_hall_helper::get_info_name('province', $v['province_id'], 'name');
            $tmp['city'] = business_hall_helper::get_info_name('city', $v['city_id'], 'name');
            $tmp['area'] = business_hall_helper::get_info_name('area', $v['area_id'], 'name');
            $tmp['business_title'] = business_hall_helper::get_info_name('business_hall', $v['business_id'], 'title');
            $tmp['user_number'] = business_hall_helper::get_info_name('business_hall', $v['business_id'], 'user_number');
            $tmp['address'] = $v['address'];
            $tmp['linkman'] = $v['linkman'];
            $tmp['phone'] = $v['phone'];
            $tmp['device_num'] = $v['device_num'];
            $tmp['remark'] = $v['remark'];
        
            $new_list[] = $tmp;
        }
    
        $params['filename'] = date('Ymd').'设备申请错误信息列表';
        $params['data']     = $new_list;
        $params['head']     = array('错误原因','设备类型', '省', '市', '区', '营业厅名称', '渠道码' ,'详细地址', '联系人','联系电话','设备数量', '备注' );
    
        Csv::getCvsObj($params)->export();
    }

  
    /**
     * 亮屏状态
     */
    public function device()
    {
    
        // 微信授权失败后手动登录标志
        $state       = tools_helper::get('state', '');
        // 权限不同权限不同
        $is_auth     = tools_helper::get('is_auth', 0);
        $user_number = tools_helper::get('user_number', '');
    
        if ( $is_auth && $user_number ) {
            $member_info = _model('member')->read(array('member_user' => $user_number));
    
            if ( $member_info ) {
                $this->member_info = $member_info;
                $this->member_id   = $member_info['id'];
                $this->res_name    = $member_info['res_name'];
                $this->res_id      = $member_info['res_id'];
            }
        }
    
    
        //获取企业号用户ID
        $qyde_user_id = qydev_helper::get_qydev_seession_user_id();
    
        // 不是手动登录
        if ( is_weixin() && !$this->member_id ) {
            qydev_helper::check_qydev_auth($url);
        }
    
        // 企业号失败重定向
        e_helper::check_login($this->res_name, 'screen_dm/device', $state);
    
        //非厅权限则跳转至集团、省、市界面
        //         if ($this->res_name != 'business_hall') {
        //             Response::redirect(AnUrl('screen_dm/status'));
        //             Response::flush();
        //             exit;
        //         }
    
        $date   = tools_helper::get('date', date('Ymd'));
        $status = tools_helper::get('status', 3);
        $filter = $record_filter = $stat_filter = array();
    
    
        // 读消息的记录表
        if ( $state && in_array($state, array('install', 'check', 'offline')) ) {
            $record_filter = array (
                    'res_name'         => $state,
                    'business_hall_id' => $this->member_info['res_id'],
                    'touser'           => $qyde_user_id ? $qyde_user_id : 'wx_error',
                    'date'             => date("Ymd"),
                    'month'            => date('Ym'),
                    'type'             => 1
            );
            _model ( 'screen_qydev_msg_record' )->create ( $record_filter );
        }
    
        if ( $state == 'install' ) {
            $order = 'add_time';
            // 今日在线量
            $status = 1;
        } else if ( $state == 'check' ) {
            $order = 'day';
            // 今日离线量
            $status = 0;
        } else if ( $state == 'offline' ) {
            $order = 'offline_days';
            // 今日离线量
            $status = 0;
        }else if($state == 'all'){
            $order = 'day';
            $status = 2;
        }else {
            $order = 'offline_days';
            // 今日离线量
            $status = 0;
        }
    
        if ( time() < strtotime($date) ) {
            $date = date('Ymd');
        }
    
        // 只要上架状态
        $filter['status'] = 1;
    
        //权限
        if ($this->member_info['res_name'] == 'province') {
            $filter['province_id'] = $this->member_info['res_id'];
        } else if ($this->member_info['res_name'] == 'city') {
            $filter['city_id']     = $this->member_info['res_id'];
        } else if ($this->member_info['res_name'] == 'area') {
            $filter['area_id']     = $this->member_info['res_id'];
        } else if ($this->member_info['res_name'] == 'business_hall') {
            $filter['business_id'] = $this->member_info['res_id'];
        }
    
        //所有设备
        $device_list =  _model('screen_device')->getList($filter);
        //在线、离线的数量
        $all_num = $online_num = $offline_num = 0;
        //离线在线的数据
        $online_list = $offline_list = array();
    
    
        foreach ($device_list as $k => &$v) {
            //在线数据查询添加条件  时间和device_unique_id
            $stat_filter['day']              = $date;
            $stat_filter['device_unique_id'] = $v['device_unique_id'];
            $device_online_stat_info = _model('screen_device_online_stat_day')->read($stat_filter);
            $v['offline_days']= '';
            //有设备，但在线无数据
            if ( !$device_online_stat_info ) {
                ++$offline_num;
                $v['offline_days'] = screen_helper::by_device_unique_id_get_offline_time($device_online_stat_info, $v['device_unique_id']);
    
                array_push( $offline_list, $v );
                continue;
            } else {
                if ( (strtotime($device_online_stat_info['update_time'])+ 1800) < time() ) {
                    ++$offline_num;
                    $v['is_online']   = '离线';
                    $v['online_time'] = screen_helper::by_device_unique_id_get_offline_time($device_online_stat_info, $v['device_unique_id']);
                    array_push($offline_list, $v);
                } else {
                    ++$online_num;
                    $v['is_online']   = '在线';
                    $v['online_time'] = screen_helper::format_timestamp_text($device_online_stat_info['online_time']);
                    array_push($online_list, $v);
                }
            }
        }
        //第一次展示如果没有离线设备  默认查看在线设备
        if($status == 3 && !$offline_list){
            $status = 1;
        }
    
         
        // 倒序
        $list = screen_helper::myself_sort($device_list, $order, 'desc');
    p($online_list);
    exit;
        //年月日
        Response::assign('year', substr($date, 0, 4));
        Response::assign('month', substr($date, 4, 2));
        Response::assign('day', substr($date, 6, 2));
        //状态数量
        Response::assign('status' , $status);
        Response::assign('all_num' , count($list));
        Response::assign('online_num' , $online_num);
        Response::assign('offline_num' , $offline_num);
        Response::assign('list' , $list);
        Response::assign('online_list' , $online_list);
        Response::assign('offline_list' , $offline_list);
        Response::assign('date' , $date);
    
       // Response::display('device2.html');
        }
        
    public function lan(){
        $coord_list = array();
        $coord_list[0]['lat'] = 1;
        $coord_list[0]['lng'] = 1;
        
        $coord_list[1]['lat'] = 5;
        $coord_list[1]['lng'] = 1;
        
        $coord_list[2]['lat'] = 1;
        $coord_list[2]['lng'] = 3;
        
        $coord = get_center_coord($coord_list);
        p($coord);
    }
}