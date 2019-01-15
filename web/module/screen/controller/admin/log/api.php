<?php

/**
 * alltosun.com 导出接口日志 api.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2017年9月29日 下午2:44:58 $
 * $Id$
 */
include ROOT_PATH."/helper/PHPExcel.php";
include ROOT_PATH."/helper/PHPExcel/Writer/Excel2007.php";

class Action
{
    public function index()
    {
        $list = _model('api_log')->getList(array('1'=>1));
        
        $objPHPExcel = new PHPExcel();
        //设置宽度
        
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(35);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(35);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(35);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(100);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(50);
        
        $excelobj = $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A1', 'ID')
        ->setCellValue('B1', '来源')
        ->setCellValue('C1', 'api_path')
        ->setCellValue('D1', 'request_params')
        ->setCellValue('E1', 'response')
        ->setCellValue('f1', '添加时间')
        ->setCellValue('G1', '更新时间');
        //省略...
        
        $i = 2;
        foreach($list as $v){
            //p($v);exit;
            //处理图片
//             $imgids = explode(',',$v['imgs']);
//             if ($imgids) {
//                 $imgs           = '"';
//                 foreach ($imgids as $vv) {
//                     if ($vv) {
//                         if ($imgs) {
//                             $imgs.="\n";
//                         }
//                         $img = _uri('file',$vv,'path');
//                         $imgs.=_image($img,'','comment');
//                     }
//                 }
//                 $imgs.='"';
//             } else {
//                 $imgs = '';
//             }
        
//             $star_num = $v['star_num']==10?'无':$v['star_num'].'星';
        
//             $tags = explode(',',$v['tags']);
//             $tag_list = '';
//             foreach($tags as $tagv) {
//                 if ($tagv) {
//                     $tag_info = _uri('tag',$tagv);
//                     $tag_list .= $tag_info['title']."\n";
//                 }
        
//             }
//             $tag_list = rtrim($tag_list, "\n");
            $excelobj = $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValueExplicit('A'.$i, $v['id'],PHPExcel_Cell_DataType::TYPE_STRING)
            ->setCellValueExplicit('B'.$i, $v['source'],PHPExcel_Cell_DataType::TYPE_STRING)
            ->setCellValueExplicit('C'.$i, $v['api_path'],PHPExcel_Cell_DataType::TYPE_STRING)
            ->setCellValueExplicit('D'.$i, $v['request_params'],PHPExcel_Cell_DataType::TYPE_STRING)
            ->setCellValueExplicit('E'.$i, $v['response'],PHPExcel_Cell_DataType::TYPE_STRING)
            ->setCellValueExplicit('F'.$i, $v['add_time'],PHPExcel_Cell_DataType::TYPE_STRING)
            ->setCellValueExplicit('G'.$i, $v['update_time'],PHPExcel_Cell_DataType::TYPE_STRING);
            $i++;
        }
        //         exit();
        //设置sheet标题
        $objPHPExcel->getActiveSheet()->setTitle(date('YmdHi').'接口日志');
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename='.date('Y-m-d H-i').'接口日志.xls');
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