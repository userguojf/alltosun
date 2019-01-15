<?php
/**
 * alltosun.com  phone.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-5-9 下午3:17:19 $
 * $Id$
 */

require_once ROOT_PATH."/helper/PHPExcel.php";
require_once ROOT_PATH."/helper/PHPExcel/Writer/Excel2007.php";
require_once MODULE_CORE.'/helper/reader.php';

class Action
{
    private $per_page    = 20;
    private $member_id   = 0;
    private $member_info = array();

    public function __construct()
    {
        $this->member_id    = member_helper::get_member_id();
        $this->member_info  = member_helper::get_member_info($this->member_id);

        Response::assign('member_info',$this->member_info);
    }


    public function __call($action='',$param=array())
    {
            $file_path = ROOT_PATH.'/images/data/screen/0522_1.xls';

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
            foreach ($results as $k => $v) {
                $data = array();
                //转码
                for($i = 1; $i <= $cols; $i ++) {
                    if (!isset($v[$i]) || !$v[$i]) {
                        $v[$i] = '';
                        continue;
                    }
                    $v[$i] = @iconv('GB2312', 'UTF-8//TRANSLIT//IGNORE', $v[$i]);
                    $v[$i] = trim($v[$i]);
                }


                $p_admin = array(
                    '牵头人', 
                    '省渠道部', 
                    '省市场部', 
                    '营业中心', 
                    '市场营销部', 
                    '市场部', 
                    '办公室', 
                    '北分市场部',
                    '宣传主管',
                    '副主任',
                    '组长',
                    '渠道部'
                    
                );

                $p_info = _model('province')->read(array('name' => trim($v[1])));

                if ( !$p_info ) {
                    continue;
                }

                $member_info = [];

                $member_info['member_user'] = $v[3];

                //校验city管理员
                $city_info = _model('city')->read(['name' => $v[2]]);
                
                if ($city_info) {
                    //添加市级管理员账户
                    $member_info['ranks'] = 3;
                    $member_info['res_name'] = 'city';
                    $member_info['res_id'] = $city_info['id'];
                    $group_id = 24;
                } else {
                    if (!in_array($v[2], $p_admin)) {
                        p($v);
                        continue;
                    } else {
                        //添加省级管理员
                        $member_info['ranks'] = 2;
                        $member_info['res_name'] = 'province';
                        $member_info['res_id'] = $p_info['id'];
                        $group_id = 23;
                    }
                }

                $info = _model('member')->read(['member_user' => $v[3]]);

                if ($info) {
                    p($info);
                    continue;
                }

                $member_info['member_pass'] = md5('Awifi@123');
                $member_info['hash'] = uniqid();
                $member_info['status'] = 1;
                $member_id = _model('member')->create($member_info);

                _model('group_user')->create(
                  array(
                     'member_id'  => $member_id,
                     'group_id'   => $group_id
                   )
               );
         }
         p('完成');
         exit();
    }

    public function userid($user_id)
    {
        
    }

    public function record($user_number, $phone)
    {
        if (!$user_number || !$phone) return false;

        $info = _model('screen_tui_yyt_record')->read(
                array('phone' => $phone));

        if ( !$info ) {
            _model('screen_tui_yyt_record')->create(array(
                'user_number' => $user_number,
                'phone'       => $phone
            ));
        }

        return true;
    }

    public function member($business_info, $id)
    {

        $member_info = _model('member')->read(array('member_user' => $business_info['user_number']));

        $member_id = 0;

        if ( $member_info ) return false;

        $member_id = _model('member')->create(
                array(
                        'member_user' => $business_info['user_number'],
                        'member_pass' => md5('Awifi@123'),
                        'res_name'    => 'business_hall',
                        'res_id'      => $id,
                        'ranks'       => 5,
                        'hash'        => uniqid()
                )
        );

        if ( $member_id ) {
            _model('group_user')->create(
                array(
                    'member_id' => $member_id,
                    'group_id'  => 26,
                )
            );
        }

        return true;

    }

    public function upload_excel()
    {
//         if (!isset($_FILES['phone_data']['name']) || !$_FILES['phone_data']['name']) {
//             return '请选择上传的Excel文件';
//         }
    
//         $file = $_FILES['phone_data'];
    
//         if (!$file['name']) {
//             return '请选择上传的Excel文件';
//         }
    
//         $allow_type = Config::get('allow_type');
    
//         $upload_path = UPLOAD_PATH;
    
//         $fail_msg = check_upload($file, 0, 1);
    
//         if ($fail_msg) {
//             return array($fail_msg, 'error', AnUrl('rfid/admin/rwtool/add'));
//         }
    
//         $ext = substr($file['name'], strrpos($file['name'], '.')+1);
    
//         if (!in_array(strtolower($ext), $allow_type)) {
//             return '文件格式不正确';
//         }
    
//         if (empty($fail_msg)) {
//             $file_path = an_upload($file['tmp_name'], $ext);
//         }
    
//        $file_path = ROOT_PATH.'/upload'.$file_path;
    
        $file_path = ROOT_PATH.'/excel/business.xls';

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
    
        p($results);
        exit;
        //Excel第行 需要去掉
        array_shift($results);
    
        $data = array();
    
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
    
            if (empty($v[1]) || empty($v[2]) || empty($v[3])) {
                continue;
            }
    
            if (strpos($v[1] , ',') !== false || strpos($v[2] , ',') !== false || strpos($v[3] , ',') !== false) {
                continue;
            }
    
            $tmp = array();
    
            $tmp['hall_title']    = $v[1];
            $tmp['user_number'] = $v[2];
            $tmp['label_num']   = $v[3];
    
            $data[] = $tmp;
        }
    
        return $data;
    }
    

    //上传文件模板说明
    public function load_instruction()
    {
        $objPHPExcel = new PHPExcel();
        //设置宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);

        $excelobj = $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A1', '请在此列输入手机品牌')
        ->setCellValue('B1', '请在此列输入手机型号')
        ->setCellValue('C1', '请在此列输入手机颜色（保持此行不动）注：请检查行数，如不够请自行插入行，并且请不要添加特殊字符，程序会过滤掉该条信息');

        $excelobj = $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValueExplicit('A100', '')
        ->setCellValueExplicit('B100', '')
        ->setCellValueExplicit('C100', '');

        //设置sheet标题
        $objPHPExcel->getActiveSheet()->setTitle('导入手机信息');
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="手机信息模板.xls"');
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
}