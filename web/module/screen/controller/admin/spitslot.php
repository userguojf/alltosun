<?php

/**
 * alltosun.com  spitslot.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2017年7月12日 下午4:53:43 $
 * $Id$
 */

include ROOT_PATH."/helper/PHPExcel.php";
include ROOT_PATH."/helper/PHPExcel/Writer/Excel2007.php";
class Action
{
    private $per_page = 20;

    private $time               = '';
    private $member_id          = 0;
    private $member_res_name    = '';
    private $member_res_id      = 0;
    private $ranks              = 0;

    public function __construct()
    {
        $this->time         = date('Y-m-d H:i:s',time());
        $this->member_id    = member_helper::get_member_id();
        $member_info        = member_helper::get_member_info($this->member_id);

        if($member_info) {
            $this->member_res_name = $member_info['res_name'];
            $this->member_res_id   = $member_info['res_id'];
            $this->ranks           = $member_info['ranks'];
        }

        Response::assign('curr_member_ranks', $this->ranks);
    }

    public function __call($action = '', $params = array())
    {
        $search_filter  = $filter = array();
        $search_filter  = Request::Get('search_filter', array());
        $page_no        = Request::Get('page_no',1);
        $is_export      = Request::Get('is_export', 0);

        $default_value  = array(
            'search_status' =>0,
        );

        $search_filter  = set_search_filter_default_value($search_filter, $default_value);

        if ($search_filter['search_status']==0) {  //正常【已经阅读、未阅读】
            $filter['status >'] = 0;
        }else{
            $filter['is_read'] = $search_filter['search_status'];
        }

        if (isset($search_filter['tel']) && $search_filter['tel']) {
            $user_id = _model('user')->getFields('id', array('phone' => $search_filter['tel']));
            $filter['user_id'] = $user_id;
        }
        if (isset($search_filter['start_add_time']) && $search_filter['start_add_time']) {
            $filter['add_time >='] = $search_filter['start_add_time'].' 00:00:00';
        }

        if (isset($search_filter['end_add_time']) && $search_filter['end_add_time']) {
            $filter['add_time <'] = $search_filter['end_add_time'].' 23:59:59';
        }

        if ($this->member_res_name != 'group' && $this->member_res_name) {
            $filter['business_id'] = screen_helper::get_business_id_by_member($this->member_res_name, $this->member_res_id);

            if (!$filter['business_id']) {
                $filter['business_id'] = 0;
            }
        }

        $order = ' ORDER BY `add_time` DESC ';

        if (empty($filter)) {
            $filter = array( 1 => 1);
        }

        if ($is_export == 1) {
            $list = _model('screen_spitslot')->getList($filter, $order);
            $this->export_excel($list);
        } else {
            $list = get_data_list('screen_spitslot',$filter, $order, $page_no, $this->per_page);
        }

        Response::assign('list',$list);
        Response::assign('search_filter', $search_filter);
        Response::assign('page', $page_no);

        Response::display("admin/spitslot/spitslot_list.html");
    }

    function export_excel($list = array())
    {
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
        ->setCellValue('B1', '内容')
        ->setCellValue('C1', '联系方式')
        ->setCellValue('D1', '营业厅')
        ->setCellValue('E1', '省份')
        ->setCellValue('f1', '城市')
        ->setCellValue('G1', '吐槽时间')
        ->setCellValue('H1', '图片')
        ->setCellValue('I1', '评星')
        ->setCellValue('J1', '标签');
        //省略...

        $i = 2;
        foreach($list as $v){
            //p($v);exit;
            //处理图片
            $imgids = explode(',',$v['imgs']);
            if ($imgids) {
                $imgs           = '"';
                foreach ($imgids as $vv) {
                    if ($vv) {
                        if ($imgs) {
                            $imgs.="\n";
                        }
                        $img = _uri('file',$vv,'path');
                        $imgs.=_image($img,'','comment');
                    }
                }
                $imgs.='"';
            } else {
                $imgs = '';
            }

            $star_num = $v['star_num']==10?'无':$v['star_num'].'星';

            $tags = explode(',',$v['tags']);
            $tag_list = '';
            foreach($tags as $tagv) {
                if ($tagv) {
                    $tag_info = _uri('tag',$tagv);
                    $tag_list .= $tag_info['title']."\n";
                }

            }
            $tag_list = rtrim($tag_list, "\n");

            $business_info = _uri('business_hall',$v['business_hall_id']);

            if (!$business_info) {
                continue;
            }

            $excelobj = $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValueExplicit('A'.$i, $v['id'],PHPExcel_Cell_DataType::TYPE_STRING)
            ->setCellValueExplicit('B'.$i, $v['content'],PHPExcel_Cell_DataType::TYPE_STRING)
            ->setCellValueExplicit('C'.$i, user_helper::get_user_info($v['user_id'],'phone'),PHPExcel_Cell_DataType::TYPE_STRING)
            ->setCellValueExplicit('D'.$i, $business_info['title'],PHPExcel_Cell_DataType::TYPE_STRING)
            ->setCellValueExplicit('E'.$i, business_hall_helper::get_info_name('province', $business_info['province_id'], 'name'), PHPExcel_Cell_DataType::TYPE_STRING)
            ->setCellValueExplicit('F'.$i, business_hall_helper::get_info_name('city', $business_info['city_id'], 'name'),PHPExcel_Cell_DataType::TYPE_STRING)
            ->setCellValueExplicit('G'.$i, $v['add_time'],PHPExcel_Cell_DataType::TYPE_STRING)
            ->setCellValueExplicit('H'.$i, $imgs,PHPExcel_Cell_DataType::TYPE_STRING)
            ->setCellValueExplicit('I'.$i, $star_num,PHPExcel_Cell_DataType::TYPE_STRING)
            ->setCellValueExplicit('J'.$i, $tag_list,PHPExcel_Cell_DataType::TYPE_STRING);
            $i++;
        }

//         exit();
        //设置sheet标题
        $objPHPExcel->getActiveSheet()->setTitle(date('YmdHi').'吐槽记录');
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename='.date('Y-m-d H-i').'吐槽记录.xls');
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

    public function update_res_is_read()
    {
        $remakes_id = Request::Post('id', 0);
        $table      = tools_helper::post('table', 'screen_spitslot');
        $status = Request::Post('status', 0);

        if (!$remakes_id) {
            return '信息错误';
        }



        $info = _uri($table,$remakes_id);

        if (!$info) {
            return '我要吐槽不存在';
        }

        if ($status == 0) {
            _model($table)->update($remakes_id,array('status' => $status));
        } else {
            _model($table)->update($remakes_id,array('is_read' => $status));
        }

        return 'ok';
    }
}