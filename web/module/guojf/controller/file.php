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


class Action
{
    public function __call($action='',$param=array())
    {
            $file_path = ROOT_PATH.'/images/data/screen/yellow.xls';

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

//             array_shift($results);
            //Excel第行 需要去掉
            $arr = [];
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
p($v);
exit();
                $p_info = _model('province')->read(array('name' => '北京'));

//                 if ( !$p_info ) {
//                     // 源数据问题
//                     $errmsg = '未找到省信息';
//                     $this->error_data($v, $errmsg);
//                     continue;
//                 }

                $c_info = _model('city')->read(array(
                        'name' => '北京',
                        //'province_id' => $p_info['id']
                    ));
//                 if ( !$c_info ) {
//                     // 源数据问题
//                     $errmsg = '未找到市信息';
//                     $this->error_data($v, $errmsg);

//                     continue;
//                 }

                $a_info = _model('area')->read(array(
                        'name' => trim($v[3]),
                        'province_id' => $p_info['id'],
                        'city_id'     => $c_info['id'],
                ));

                if ( !$a_info ) {
                    p($v);
//                     $errmsg = '未找到地区信息';
//                     $this->error_data($v, $errmsg);
                    continue;
                }

                $data['province_id'] = $p_info['id'];
                $data['city_id'] = $c_info['id'];
                $data['area_id'] = $a_info['id'];
// // p($data);
// // exit();
// //                 // 因为有更新所以 一定判断为空值
                $v[6] ? $data['contact']  = trim($v[6]) : false;
                $v[7] ? $data['contact_way'] = trim($v[7]) : false;
                $v[9] ? $data['user_number'] = trim($v[9]) : false;
                $v[4] ? $data['title'] = trim($v[4]) : false;
                $v[5] ? $data['address'] = trim($v[5]) : false;
                $data['type'] = 4;
// p($data);
// exit();
                $results = $this->yyt_handle($data);

                //$this->record(trim($v[2]), trim($v[4]));
            }
//             p($arr);
        p('完成');
        exit();
    }

    public function error_data($data, $errmsg)
    {
        _model('file_export_error_json_record')->create(
                array(
                    'json'   => json_encode($data),
                    'errmsg' => $errmsg,
                    'day'    => date('Ymd')
                )
        );

        return true;
    }

    public function yyt_handle($business_info)
    {

        $result = _model('business_hall')->read(array('user_number' => $business_info['user_number']));

        if ( $result ) {
            _model('business_hall')->update($result['id'], $business_info);

            return true;
        }


        $id = _model('business_hall')->create($business_info);

        $member_info = _model('member')->read(array('member_user' => $business_info['user_number']));
        if ( $member_info ) return false;

        $member_id = 0;

        $member_id = _model('member')->create(
                array(
                        'member_user' => $business_info['user_number'],
                        'member_pass' => md5('Awifi@123#.dx'),
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

    public function record($user_number, $phone)
    {
        if (!$user_number || !$phone) return false;

        $info = _model('screen_tui_yyt_record')->read(array('phone' => $phone, 'date' => date('Ymd')));

        if ( !$info ) {
            _model('screen_tui_yyt_record')->create(array(
                'user_number' => $user_number,
                'phone'       => $phone,
                'date'        => date('Ymd')
                
            ));
        }

        return true;
    }
    public function add()
    {
        $info = _model('screen_tui_yyt_record')->read(array('phone' => 13311111090, 'date' => date('Ymd')));
 
        if ( !$info ) {
            _model('screen_tui_yyt_record')->create(array(
                'user_number' => 4211251000209,
                'phone'       => 13311111090,
                'date'        => date('Ymd')
            ));
            return 'ok';
        }
        return '已经添加';
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