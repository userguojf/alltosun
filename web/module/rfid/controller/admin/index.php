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
 * $Date: 2017-5-10 下午12:48:17 $
 * $Id$
 */
//config
include MODULE_PATH.'/rfid/server/config.php';
//放缓存
include MODULE_PATH.'/rfid/server/lib/RedisCache.php';
//清缓存
include MODULE_PATH.'/rfid/server/src/secret_helper.php';

//excel导出
include ROOT_PATH."/helper/PHPExcel.php";
include ROOT_PATH."/helper/PHPExcel/Writer/Excel2007.php";

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
        $search_filter = Request::get('search_filter', array());
        //导出
        $if_export     = tools_helper::Get('if_export', 0);

        $order         = " ORDER BY `id`  DESC ";

        $page          = tools_helper::get('page_no', 1);

        $filter =  _widget('rfid')->init_filter($this->member_info, $search_filter);

        //标签
        if(!empty($search_filter['label_id'])) {
            $filter['label_id'] = trim($search_filter['label_id']);
        }

        //专柜
        if(!empty($search_filter['shoppe_id'])) {
            $filter['shoppe_id'] = trim($search_filter['shoppe_id']);
        }

        if (!$filter) {
            $filter[1] = 1;
        }

        if ( isset($filter['business_id']) ) {
            $filter['business_hall_id'] = $filter['business_id'];
            unset($filter['business_id']);
        }

        $count = _model('rfid_label')->getTotal($filter);

        $rfid_list = array();

        if ($if_export == 1) {
            $rfid_list   = _model('rfid_label')->getList($filter, $order);
            $this->export_label_list($rfid_list);
            exit();
        }

        if ($count) {
            $pager  = new Pager($this->per_page);
            $rfid_list   = _model('rfid_label')->getList($filter ,$order.$pager->getLimit($page));

            if ($pager->generate($count,$page)) {
                Response::assign('pager', $pager);
            }
        }

        //获取柜台
        $shoppe_list = shoppe_helper::get_business_hall_shoppe($this->member_info['res_name'], $this->member_info['res_id']);

        Response::assign('count', $count);
        Response::assign('shoppe_list', $shoppe_list);
        Response::assign('rfid_list' , $rfid_list );
        Response::assign('search_filter',$search_filter);
        Response::assign('page' , $page);

        Response::display('admin/rfid_list.html');
    }

    /**
     * 导出标签
     * @param unknown $list
     * @return boolean
     */
    public function export_label_list($list)
    {
        if (!$list) {
            return true;
        }

        $objPHPExcel = new PHPExcel();
        //设置宽度

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(15);

        $excelobj = $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '省')
            ->setCellValue('B1', '市')
            ->setCellValue('C1', '区')
            ->setCellValue('D1', '厅')
            ->setCellValue('E1', '渠道编码')
            ->setCellValue('F1', '标签')
            ->setCellValue('G1', '柜台')
            ->setCellValue('H1', '手机品牌')
            ->setCellValue('I1', '手机型号')
            ->setCellValue('J1', '手机颜色')
            ->setCellValue('K1', '手机IMEI')
            ->setCellValue('L1', '体验次数')
            ->setCellValue('M1', '体验时长')
            ->setCellValue('N1', '状态');

        $i = 2;
        foreach ($list as $k => $v) {

            $business_hall_info = _model('business_hall')->read($v['business_hall_id']);

            if (!$business_hall_info) {
                continue;
            }

            $province   = business_hall_helper::get_info_name('province', $business_hall_info['province_id'], 'name');
            $city       = business_hall_helper::get_info_name('city', $business_hall_info['city_id'], 'name');
            $area       = business_hall_helper::get_info_name('area', $business_hall_info['area_id'], 'name');

            //查询标签体验次数
            $remain_times = _model('rfid_record_detail')->getFields('remain_time', array(
                    'status' => 1,
                    'end_timestamp >' => 100,
                    'label_id'      => $v['label_id'],
                    'business_id'   => $v['business_hall_id'],
            ));

            $action_num     = count($remain_times);
            $remain_times   = array_sum($remain_times);

            //查询状态
            $online_id = _model('rfid_online_stat_day')->read(array(
                    'label_id' => $v['label_id'],
                    'day'      => date('Ymd'),
                    'business_id' => $v['business_hall_id'],
            ));

            if ($online_id) {
                $status = '在线';
            } else {
                $status = '离线';
            }

            //查询柜台
            $shoppe_info = shoppe_helper::get_shoppe_info($v['shoppe_id']);
            $shoppe = $shoppe_info ? $shoppe_info['shoppe_name'] : '';
            $label_info = array(
                    'province' => $province,
                    'city' => $city,
                    'area' => $area,
                    'hall' => $business_hall_info['title'],
                    'user_number' => $business_hall_info['user_number']."\t",
                    'label_id' => $v['label_id']."\t",
                    'shoppe' => $shoppe,
                    'brand'  => $v['name'],
                    'version' => $v['version'],
                    'color'     => $v['color'],
                    'imei'      => $v['imei'],
                    'action_num' => $action_num."\t",
                    'remain_times' => sprintf("%.2f", $remain_times / 60).'分钟',
                    'status'        => $status,
            );

            $excelobj = $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValueExplicit('A'.$i, $province, PHPExcel_Cell_DataType::TYPE_STRING)
            ->setCellValueExplicit('B'.$i, $city, PHPExcel_Cell_DataType::TYPE_STRING)
            ->setCellValueExplicit('C'.$i, $area, PHPExcel_Cell_DataType::TYPE_STRING)
            ->setCellValueExplicit('D'.$i, $business_hall_info['title'], PHPExcel_Cell_DataType::TYPE_STRING)
            ->setCellValueExplicit('E'.$i, $business_hall_info['user_number'], PHPExcel_Cell_DataType::TYPE_STRING)
            ->setCellValueExplicit('F'.$i, $v['label_id'], PHPExcel_Cell_DataType::TYPE_STRING)
            ->setCellValueExplicit('G'.$i, $shoppe, PHPExcel_Cell_DataType::TYPE_STRING)
            ->setCellValueExplicit('H'.$i, $v['name'],PHPExcel_Cell_DataType::TYPE_STRING)
            ->setCellValueExplicit('I'.$i, $v['version'], PHPExcel_Cell_DataType::TYPE_STRING)
            ->setCellValueExplicit('J'.$i, $v['color'],PHPExcel_Cell_DataType::TYPE_STRING)
            ->setCellValueExplicit('K'.$i, $v['imei'],PHPExcel_Cell_DataType::TYPE_STRING)
            ->setCellValueExplicit('L'.$i, $action_num,PHPExcel_Cell_DataType::TYPE_STRING)
            ->setCellValueExplicit('M'.$i, sprintf("%.2f", $remain_times / 60).'分钟',PHPExcel_Cell_DataType::TYPE_STRING)
            ->setCellValueExplicit('N'.$i, $status, PHPExcel_Cell_DataType::TYPE_STRING);
            $i++;
        }

        //设置sheet标题
        $objPHPExcel->getActiveSheet()->setTitle(date('YmdHi').'RFID设备列表');
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename='.date('Y-m-d H-i').'RFID设备列表.xls');
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

    public function add()
    {
        $id = Request::get('id' , 0);

//         //权限
//          if ($this->member_info['res_name'] == 'province') {
//             Response::assign('province_arr' , array('province_id' => $this->member_info['res_id']));

//         } else if ($this->member_info['res_name'] == 'city') {
//             Response::assign('city_arr' , array('city_id' => $this->member_info['res_id']));

//         }

        $filter = array();
        //权限
        if ($this->member_info['res_name'] == 'group') {
            $filter = array('1' => 1);

        } else if ($this->member_info['res_name'] == 'province') {
            $filter['province_id'] = $this->member_info['res_id'];
            Response::assign('province_arr' , array('province_id' => $this->member_info['res_id']));

        } else if ($this->member_info['res_name'] == 'city') {
            $filter['city_id'] = $this->member_info['res_id'];
            Response::assign('city_arr' , array('city_id' => $this->member_info['res_id']));

        } else if ($this->member_info['res_name'] == 'area') {
            $filter['area_id'] = $this->member_info['res_id'];

        } else if ($this->member_info['res_name'] == 'business_hall') {
            $filter['business_id'] = $this->member_info['res_id'];

        }

        if ($id) {
            //rfid_label表信息
            $rfid_info = _model('rfid_label')->read(array('id' => $id));

            if (!$rfid_info) {
                return '该数据已经不存在，返回请刷新';
            }
            //rfid_phone表 手机信息
//             $phone_info = _model('rfid_phone')->read(array('id' => $rfid_info['phone_id']));

            //business_hall表营业厅信息
            $business_hall_info = _model('business_hall')->read(array('id' => $rfid_info['business_hall_id']));

            Response::assign('id' , $id);

            Response::assign('province_id' , $business_hall_info['province_id']);
            Response::assign('city_id' , $business_hall_info['city_id']);
            Response::assign('area_id' , $business_hall_info['area_id']);
            Response::assign('province_arr' , array('province_id' => $business_hall_info['province_id']));
            Response::assign('city_arr' , array('city_id' => $business_hall_info['city_id']));

//             Response::assign('phone_name' , $phone_info['name']);
//             Response::assign('phone_varsion' , $phone_info['version']);

            Response::assign('rfid_info' , $rfid_info);
        }

        Response::display('admin/rfid_add.html');
    }

    public function save()
    {
        $id         = Request::post('id' , 0);
        $rfid_info  = Request::post('rfid_info' , array());

        //传给数字地图参数
        $param = array();
        /**
         * 表单验证
         */

        if ($this->member_info['res_name'] != 'business_hall') {
            if (!isset($rfid_info['business_hall_id']) || !$rfid_info['business_hall_id']) return '请选择厅店';
        }

        if (!isset($rfid_info['label_id']) || !$rfid_info['label_id']) return '请填写标签ID';

        if (!$id) {
            //标签ID唯一判断
            $is_exist_info = _model('rfid_label')->read(array('label_id' => $rfid_info['label_id']));

            if ($is_exist_info) {
                return '填写的标签ID已存在';
            }
        }

        if (!isset($rfid_info['name']) || !$rfid_info['name']) {
            return '请选择手机品牌';
        }

        if (!isset($rfid_info['version']) || !$rfid_info['version']) {
            return '请选择型号';
        }

        if (!isset($rfid_info['color']) || !$rfid_info['color']) {
            return '请选择颜色';
        }

        if (!isset($rfid_info['imei']) || !$rfid_info['imei']) {
            return '请填写IMEI末六位';
        }

        if (strlen($rfid_info['imei']) != 6) {
            return 'IMEI输入的应该是六位的数字';
        }

        if (!isset($rfid_info['shoppe_id']) || !$rfid_info['shoppe_id'] ) {
            return '请选择柜台';
        }

        //获取手机信息的ID
        $phone_id = _uri('rfid_phone',
                        array(
                                'name'    => $rfid_info['name'],
                                'version' => $rfid_info['version'],
                                'color'   => $rfid_info['color']
                            ),
                    'id');

        if (!$phone_id) {
            return '手机信息刚刚被删除，请重新选择手机信息';
        }

        //手机信息
        $rfid_info['phone_id'] = $phone_id;

        if ($this->member_info['res_name'] == 'business_hall') {
            $business_hall_info = _model('business_hall')->read(array('id' => $this->member_info['res_id']));
            $rfid_info['business_hall_id'] = $business_hall_info['id'];

        } else {
            $business_hall_info = _model('business_hall')->read(array('id' => $rfid_info['business_hall_id']));

        }

        if (!$business_hall_info) {
            return '未找到营业厅信息，可能由于某些操作删除';
        }

        $rfid_info['province_id'] = $business_hall_info['province_id'];
        $rfid_info['city_id']     = $business_hall_info['city_id'];
        $rfid_info['area_id']     = $business_hall_info['area_id'];


        if(!$id) {
            //数字地图需要数据
            $param = array(
                    'type'        => 'create',
                    'user_number' => $business_hall_info['user_number'],
                    'label_id'    => $rfid_info['label_id'],
                    'phone_name'    => $rfid_info['name'],
                    'phone_version' => $rfid_info['version'],
                    'shoppe_id'     => $rfid_info['shoppe_id'],
            );

            //传给数字地图并记录日志
            rfid_helper::create_api_log($param);
// p($rfid_info);p($param);exit();
            //创建
            _model('rfid_label')->create($rfid_info);
        } else {
            //缓存
            secret_helper::update_secret($rfid_info['label_id']);

            //更新
            _model('rfid_label')->update($id , $rfid_info);
        }

        return array('操作成功', 'success', AnUrl("rfid/admin"));
    }

    //删除
    public function delete()
    {
        $id = Request::getParam('id' , 0);

        if (!$id) {
            return array('info'=>'请选择删除的数据');
        }

        $rfid_info = _model('rfid_label')->read(array('id' => $id));

        if (!$rfid_info) {
            return '未找到RFID信息，可能由于某些操作删除';
        }

        $business_hall_info = _model('business_hall')->read(array('id' => $rfid_info['business_hall_id']));

        if (!$business_hall_info) {
            return '未找到营业厅信息，可能由于某些操作删除';
        }

        //数字地图需要数据
        $param = array(
                'type'          => 'delete',
                'user_number'   => $business_hall_info['user_number'],
                'label_id'      => $rfid_info['label_id'],
                'phone_name'    => $rfid_info['name'],
                'phone_version' => $rfid_info['version'],
                'shoppe_id'     => $rfid_info['shoppe_id'],
        );

        //传给数字地图
        rfid_helper::create_api_log($param);

        //删除
        _model('rfid_label')->delete(array('id' => $id));

        return array('info' => 'ok');

    }

}