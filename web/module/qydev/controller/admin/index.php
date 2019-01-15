<?php
/**
 * alltosun.com  index.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-4-15 下午6:22:07 $
 * $Id$
 */

require_once ROOT_PATH."/helper/PHPExcel.php";
require_once ROOT_PATH."/helper/PHPExcel/Writer/Excel2007.php";

class Action
{
    private $per_page  = 20;

    public function __call($action = '' , $param = '')
    {
        $page = Request::get('page_no' , 1) ;

        $search_filter = Request::get('search_filter' , array());

        $list = $filter = array();

        if (isset($search_filter['user_number']) && $search_filter['user_number']) {
            $filter['user_number'] = trim($search_filter['user_number']);
        }

        if (isset($search_filter['user_phone']) && $search_filter['user_phone']) {
            $filter['user_phone'] = trim($search_filter['user_phone']);
        }

        if (isset($search_filter['user_name']) && $search_filter['user_name']) {
            $filter['user_name'] = trim($search_filter['user_name']);
        }

        if (isset($search_filter['unique_id']) && $search_filter['unique_id']) {
            $filter['unique_id']    = trim($search_filter['unique_id']);
        }

        if (!$filter ) {
            $filter = array( 1 => 1 );
        }

        $count = _model('public_contact_user')->getTotal($filter);
        if ($count) {
            $pager  = new Pager($this->per_page);
            $list   = _model('public_contact_user')->getList($filter , $pager->getLimit($page));

            if ($pager->generate($count,$page)) {
                Response::assign('pager', $pager);
            }
        }

        Response::assign('list' , $list);
        Response::assign('count' , $count);
        
        Response::assign('page' , $page);
        Response::assign('search_filter' , $search_filter );

        Response::display('admin/user_list.html');
    }

    //添加数据
    public function add()
    {
        $id = Request::get('id' , 0);

        if ( $id ) {
            $user_info = _uri('public_contact_user' , array('id'=>$id));

            Response::assign('user_info' , $user_info);
        }

        Response::display('admin/add_user.html');

    }

    //保存
    public function save()
    {
        $user_info = Request::post('user_info' , array());
        $file      = $_FILES['excel_data'];
        $type      = Request::post('type' , 0);

        //判断
        if (isset($user_info['id']) && !$user_info['id'] && 2 == $type ) {

            $info = qydev_helper::handle_excel_upload($file);

            if (is_array($info)) {
                return $info['error'];
            }

             qydev_helper::handle_excel_data($info);
        }

        //判断
        if (!isset($user_info['user_name']) || empty($user_info['user_name']) ) {
            return '联系人不能为空';
        }

        if (!isset($user_info['user_phone']) || empty($user_info['user_phone']) ) {
            return '联系电话不能不为空';
        }

        if (!isset($user_info['unique_id']) || empty($user_info['unique_id']) ) {
            return '企业号账号不能为空';
        }

        if (!isset($user_info['user_number']) || empty($user_info['user_number']) ) {
            return '渠道码不能为空';
        }

        if (!isset($user_info['type']) || $user_info['type'] == 99 ) {
            return '请选择级别';
        }

        if (!isset($user_info['from_id']) ) {
            return '请选择所属部门';
        }

        $user_info['from_id'] = implode( $user_info['from_id'] , ",");

// p($user_info);exit();
        if ($user_info['id']) {
            _model('public_contact_user')->update($user_info['id'] , $user_info);
        }

        if (!$user_info['id']) {
            //账号唯一的判断
            $is_have_info = _model('public_contact_user')->read(array('unique_id' => $user_info['unique_id']));
            
            if ($is_have_info) {
                return '账号已经存在！注：企业号账号是唯一值';
            }

            _model('public_contact_user')->create($user_info);
        }

        return array('操作成功' , 'success' ,AnUrl("qydev/admin"));

    }
    
    //上传文件模板说明
    public function load_instruction()
    {
        $objPHPExcel = new PHPExcel();
        //设置宽度
//         $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
//         $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
//         $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);

        $objPHPExcel->getActiveSheet()->mergeCells('A1:C1');
        $objPHPExcel->getActiveSheet()->mergeCells('D1:F1');
        $objPHPExcel->getActiveSheet()->mergeCells('G1:H1');
        $objPHPExcel->getActiveSheet()->mergeCells('I1:M1');

        $excelobj = $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A1', '基本信息(姓名账号必填)')
        ->setCellValue('D1', '身份验证信息(三者不可同时为空)')
        ->setCellValue('G1', '职位信息')
        ->setCellValue('I1', '扩展字段')
        ->setCellValue('N1', '注：请先插入行')
        ->setCellValue('A2', '姓名')
        ->setCellValue('B2', '账号')
        ->setCellValue('C2', '性别')
        ->setCellValue('D2', '微信号')
        ->setCellValue('E2', '手机号(国际手机号码请加“+国际区号”)')
        ->setCellValue('F2', '邮箱')
        ->setCellValue('G2', '所在部门')
        ->setCellValue('H2', '职位')
        ->setCellValue('I2', '英文名')
        ->setCellValue('J2', '座机')
        ->setCellValue('K2', '虚拟帐号')
        ->setCellValue('L2', 'analog_id')
        ->setCellValue('M2', 'an_id');

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
        ->setCellValueExplicit('K100', '')
        ->setCellValueExplicit('M100', '');

        //设置sheet标题
        $objPHPExcel->getActiveSheet()->setTitle('导入通讯录成员信息');
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="通讯录成员信息模板.xls"');
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