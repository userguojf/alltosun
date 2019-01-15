<?php
use MongoDB\Operation\Delete;

/**
 * alltosun.com  
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * szy: 宋志宇 (songzy@alltosun.com)
 * 2018年3月7日: 2016-7-26 下午3:05:10
 * Id
 */
require_once ROOT_PATH."/helper/PHPExcel.php";
require_once ROOT_PATH."/helper/PHPExcel/Writer/Excel2007.php";
probe_helper::load('func');
class Action
{
    private $per_page = 10;
    private $member_id  = 0;
    private $member_res_name = '';
    private $member_res_id   = 0;
    private $member_info;
    private $ranks           = 0;
    private $time;

    public function __construct()
    {
        $this->member_id   = member_helper::get_member_id();
        $this->time        = date('Y-m-d H:i:s');
        $this->member_info = member_helper::get_member_info($this->member_id);

        if ($this->member_info) {
            $this->member_res_name = $this->member_info['res_name'];
            $this->member_res_id   = $this->member_info['res_id'];
            $this->ranks           = $this->member_info['ranks'];

            Response::assign('member_info', $this->member_info);
        }

        Response::assign('curr_member_ranks', $this->ranks);
    }

    public function __call($action = '', $params = array())
    {
//         _model('goods_contact_extend_relation')->delete(array('1' => 1));exit;
        
        // 内容展示必须符合各省的条件
        $search_filter = Request::Get('search_filter', array());
        $filter = array();
        $filter['error_type'] = '正确数据';
        $filter['status'] = 1;
        $filter['factory_account'] = $this->member_info['member_user'];
        //1 全部 2待发 3已发
        if (empty($search_filter['put_type']) || $search_filter['put_type'] == 1) {
            $filter['order_status !='] = 3;
            $search_filter['put_type'] = 1;
        } else if ($search_filter['put_type'] == 2) {
            $filter['order_status'] = 1;
        }else if ($search_filter['put_type'] == 3) {
            $filter['order_status'] = 2;
        }else if ($search_filter['put_type'] == 4) {
            $filter['order_status'] = 3;
        }
        //全部
        if (empty($search_filter['search_type']) || $search_filter['search_type'] == 0) {
            $search_filter['search_type'] = 0;
            //在线
        } else if ($search_filter['search_type'] == 1) {
            $filter['device_type'] = '探针';
            //过期
        } else if ($search_filter['search_type'] == 2) {
            $filter['device_type'] = 'rfid';
            //过期
        } 
        
        // 搜索省
        if ( isset($search_filter['province']) && $search_filter['province'] ) {
            $filter['province_id'] = $search_filter['province'];
        }
        
        // 搜索市
        if ( isset($search_filter['city']) && $search_filter['city'] ) {
            $filter['city_id'] = $search_filter['city'];
        }
        
        // 搜索区
        if ( isset($search_filter['area']) && $search_filter['area'] ) {
            $filter['area_id'] = $search_filter['area'];
        }
        
        if (!empty($search_filter['title'])) {
            $filter['title LIKE'] = '%'.$search_filter['title'].'%';
        }
        //end
        $list = _model('device_application')->getList($filter, ' ORDER BY `create_time` desc ');
        $count = count($list);
        if ($count) {
            $pager = new Pager($this->per_page);
            $content_list = _model('device_application')->getList($filter, ' ORDER BY `create_time` desc '.$pager->getLimit());
            
            Response::assign('content_list', $content_list);

            if ($pager->generate($count)) {
                Response::assign('pager', $pager);
            }
        }
        Response::assign('count', $count);
        Response::assign('search_filter', $search_filter);
        Response::display("admin/factory_device_list.html");
    }

    /**
     * 进入发货页面
     */
    public function send_goods()
    {
        $id          = Request::Get('id', 0);

        if (!$id) {
            return '请选择您要操作的信息';
        }

        $device_info = _uri('device_application', $id);

        if (!$device_info) {
            return '订单信息不存在';
        }

        Response::assign('device_info', $device_info);
        Response::display("admin/send_goods.html");
    }
    
    /**
     * 发货
     */
    public function goods_send()
    {
//         _model('goods_contact_extend_relation')->delete(array('1'=>1));exit;
        $id                 = Request::Post('id',0);//申请id
        $order_code         = Request::Post('order_code', '');
        $company            = Request::Post('company', 0);
        $device_num         = Request::Post('device_num', 0);
        $add_type           = Request::Post('add_type', 1);//1 手动填写  2外部导入
        $phone_list         = Request::Post('phone', array());
        $email_list         = Request::Post('email', array());
        $linkman_list       = Request::Post('linkman', array());
        $device_mac_label_id = Request::Post('device_mac_label_id','');
        //device_application表的状态改为已发增加 order_code  在goods_contact_extend_relation中填入发送的设备信息
        //单独验证
        
        if (!$id) {
            return '网络错误';
        }
        
        if (!$order_code) {
            return '快递单号不能为空';
        }
        
        if ($add_type == 1 && !$device_mac_label_id) {
            return '请输入设备编号';
        }
        
        if (empty($linkman_list)) {
            return '请输入联系人';
        }
        
        if (empty($phone_list)) {
            return '请输入联系人手机';
        }
        
        if (empty($email_list)) {
            return '请输入联系人邮箱';
        }
        
        if (!$company) {
            return '请选择快递公司';
        }
        
        $phone   = implode(',',$phone_list);
        $email   = implode(',',$email_list);
        $linkman = implode(',',$linkman_list);
        
        $data = array(
                'application_id' => $id,
                'phone'          => $phone,
                'email'          => $email,
                'linkman'        => $linkman,
                'company'        => $company,
        );
        if($add_type == 1){
          
            //$mac_list = explode (",",$device_mac_label_id);
            $mac_list = preg_split ("/[,\s]|(，)/", $device_mac_label_id);
            $mac_list = array_unique($mac_list);
            if((int)$device_num != count($mac_list)){
                return "应发".$device_num.'台设备';
            }
            //检查设备号是否已存在
            $res = _model('goods_contact_extend_relation')->getList(array('device_mac_label_id' =>$mac_list));
            if(!empty($res)){
                return "设备号重复请检查";
            }
            foreach ($mac_list as $k => $v){
                $data['device_mac_label_id'] = $v;
                _model('goods_contact_extend_relation')->create($data);
            }
        }else{
            // 从外部导入 start
                if (isset($_FILES['export_data']['name']) && $_FILES['export_data']['name']) {
                    $file = $_FILES['export_data'];
                    if (!$file['name']) {
                        return '请选择上传的Excel文件';
                    }
                    $allow_type = Config::get('allow_type');
                     
                    $upload_path = UPLOAD_PATH;
                    $fail_msg = check_upload($file, 0, 1);
                    if ($fail_msg) {
                        return array($fail_msg, 'error', AnUrl('factory/admin'));
                    }
                    $ext = substr($file['name'], strrpos($file['name'], '.')+1);
                    if (!in_array(strtolower($ext), $allow_type)) {
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
                //Excel第一个单元格标题需要去掉
                array_shift($results);
                
                $results = array_unique($results);
                
                if((int)$device_num != count($results)){
                    return "应发".$device_num.'台设备';
                }
                //检查是否又重复
                $res = _model('goods_contact_extend_relation')->getList(array('device_mac_label_id' =>$results));
                if(!empty($res)){
                    return "设备号重复请检查";
                }
                foreach ($results as $k=>$v) {
                    //转码
                    for($i=1; $i<=$cols; $i++) {
                        if (!isset($v[$i]) || !$v[$i]) {
                            $v[$i] = '';
                            continue;
                        }
            
                        $v[$i] = iconv('GB2312', 'UTF-8//TRANSLIT//IGNORE', $v[$i]);
                        $v[$i] = trim($v[$i]);
                    }
                    
                    
                    $data['device_mac_label_id'] = $v[1];
                    _model('goods_contact_extend_relation')->create($data);
                }
        }
        //修改申请表的状态
        _model('device_application')->update(array('id' => $id), array('order_code' => $order_code,'order_status' => 2));
        ///////////////////////推送信息//////////////////////////////
        //申请单信息
        $info = _model('device_application')->read(array('id' => $id));
        $param =array(
                'province_id' => $$info['province_id'],
                'city_id'     => $info['city_id'],
                'area_id'     => $info['area_id'],
                'business_id' => $info['business_id'],
                'device_type' => $info['device_type'],
                'factory_account' => $info['factory_account'],
                'num' => $device_num,
        );
        $res = factory_helper::push_email_info($param,$email_list,$phone_list,$order_code);
        
        return array('操作成功', 'success', AnUrl("factory/admin"));
    }
}
?>