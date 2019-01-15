<?php
/**
  * alltosun.com 读写器管理 rwtool.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2017年11月22日 上午11:43:40 $
  * $Id$
  */

require_once ROOT_PATH."/helper/PHPExcel.php";
require_once ROOT_PATH."/helper/PHPExcel/Writer/Excel2007.php";

//操作redis
require_once MODULE_PATH.'/rfid/server/config.php';
require_once MODULE_PATH.'/rfid/server/lib/RedisCache.php';
require_once MODULE_PATH.'/rfid/server/src/secret_helper.php';

class Action
{

    private $per_page    = 100;
    private $member_id   = 0;
    private $member_info = array();

    public function __construct()
    {
        $this->member_id    = member_helper::get_member_id();
        $this->member_info  = member_helper::get_member_info($this->member_id);

        Response::assign('member_info',$this->member_info);
    }

//     /**
//      * 读写器管理首页
//      * @param string $action
//      * @param array $params
//      */
//     public function __call($action='', $params=array())
//     {
//         $search_filter = tools_helper::Get('search_filter', array());
//         $is_export     = tools_helper::Get('is_export', 0);
//         $page          = tools_helper::Get('page_no', 1);
//         $order_dir     = tools_helper::Get('order_dir', 'desc');
//         $order_field   = tools_helper::Get('order_field', 'label_num'); //默认根据标签总数排行

//         if ($order_field == 'label_num' && in_array($order_dir, array('desc', 'asc'))) {
//             $order         = " ORDER BY `{$order_field}`  {$order_dir} ";
//         }

//         $rwtool_status = 0;

//         $filter =  _widget('rfid')->init_filter($this->member_info, $search_filter);

//         if (!empty($search_filter['date'])) {
//             $date = $search_filter['date'];
//         } else {
//             $date = $search_filter['date'] = date('Y-m-d');
//         }

//         $date = str_replace('-', '', $date);

//         $filter['day'] = $date;

//         //按状态搜索
//         if (!empty($search_filter['rwtool_status']) && $search_filter['rwtool_status'] > 0) {
//             $rwtool_status = $search_filter['rwtool_status'];
//         }

//         //按营业厅名称搜索
//         if (isset($filter['business_hall_id'])) {
//             $filter['business_id'] = $filter['business_hall_id'];
//             unset($filter['business_hall_id']);
//         }

//         $rwtool_filter = array();

//         //读写器正常(标签正常) 或者  读写器正常(部分标签离线)
//         if (in_array($rwtool_status, array(1, 2))) {
//             $filter['status'] = $rwtool_status;
//             $rwtool_filter['business_id'] = _model('rfid_rwtool_stat_day')->getFields('business_id', $filter);
//         //读写器异常(全部标签离线)
//         } else if ($rwtool_status == 6) {
//             $business_ids   = _model('rfid_rwtool_stat_day')->getFields('business_id', $filter);
//             $new_filter = $filter; unset($new_filter['day']);
//             if (empty($new_filter)) {
//                 $new_filter = array(1 => 1);
//             }
//             $business_ids2  = _model('rfid_rwtool')->getFields('business_id', $new_filter);
//             //返回差集
//             $rwtool_filter['business_id'] = array_diff($business_ids2, $business_ids);

//         //未知 或 全部
//         } else {
//             $rwtool_filter = $filter;
//             unset($rwtool_filter['day']);
//         }

//         if ( isset($rwtool_filter['business_id']) && !$rwtool_filter['business_id'] ){
//             $rwtool_filter['business_id'] = 0;
//         }

//         if (!$rwtool_filter) {
//             $rwtool_filter[1] = 1;
//         }

//         if ($is_export == 1) {
//             $rwtool_list = _model('rfid_rwtool')->getList($rwtool_filter ,$order);
//             $this->export($rwtool_list, $date);
//             exit();
//         }
//         $count = _model('rfid_rwtool')->getTotal($rwtool_filter);

//         $rwtool_list = array();

//         if ($count) {
//             $pager  = new Pager($this->per_page);
//             $rwtool_list   = _model('rfid_rwtool')->getList($rwtool_filter ,$order.$pager->getLimit($page));
//             if ($pager->generate($count,$page)) {
//                 Response::assign('pager', $pager);
//             }
//         }

//         foreach ($rwtool_list as $k => $v) {
//            //查询在线标签统计
//            $stat_info = _model('rfid_rwtool_stat_day')->read(array('business_id' => $v['business_id'], 'day' => $date));

//            if (!$stat_info) {
//                //获取厅在线标签数
//                $rwtool_list[$k]['label_online_num'] = 0;
//                $rwtool_list[$k]['rwtool_status'] = 6;
//            } else {
//                $rwtool_list[$k]['label_online_num'] = $stat_info['online_label_num'];
//                $rwtool_list[$k]['rwtool_status']    = $stat_info['status'];
//            }
//         }

//         Response::assign('count', $count);
//         Response::assign('order_field', $order_field);
//         Response::assign('order_dir', $order_dir);
//         Response::assign('rwtool_list' , $rwtool_list );
//         Response::assign('search_filter',$search_filter);
//         Response::assign('page' , $page);
//         Response::display('admin/rwtool/rwtool_list.html');
//     }

    /**
     * 读写器管理首页
     * @param string $action
     * @param array $params
     */
    public function __call($action='', $params=array())
    {
        $search_filter = tools_helper::Get('search_filter', array());
        $is_export     = tools_helper::Get('is_export', 0);
        $page          = tools_helper::Get('page_no', 1);
        $order_dir     = tools_helper::Get('order_dir', 'desc');
        $order_field   = tools_helper::Get('order_field', 'day'); //默认根据标签总数排行
        $rwtool_stat_list = array();

        if (in_array($order_dir, array('desc', 'asc'))) {
            $order         = " ORDER BY `{$order_field}`  {$order_dir} ";
        }

        $rwtool_status = 0;

        //读写器表条件， 统计表条件
        $rwtool_filter = $filter =  _widget('rfid')->init_filter($this->member_info, $search_filter);

        if (!empty($search_filter['start_date'])) {
            $start_date = $search_filter['start_date'];
        } else {
            $start_date = $search_filter['start_date'] = date('Y-m-d', strtotime('-2 days'));
        }

        if (!empty($search_filter['end_date'])) {
            $end_date = $search_filter['end_date'];
        } else {
            $end_date = $search_filter['end_date'] = date('Y-m-d');
        }

        $filter['day >='] = date('Ymd', strtotime($start_date));
        $filter['day <='] = date('Ymd', strtotime($end_date));

        //按状态搜索
        if (!empty($search_filter['rwtool_status']) && $search_filter['rwtool_status'] > 0) {
            $rwtool_status = $search_filter['rwtool_status'];
        }

        //按营业厅名称搜索
        if (isset($filter['business_hall_id'])) {
            $rwtool_filter['business_id'] = $filter['business_id'] = $filter['business_hall_id'];
            unset($filter['business_hall_id']);
            unset($rwtool_filter['business_hall_id']);
        }


        //日期初始化
        $days   = array();
        $date   = $start_date;
        do {
            $days[] = $date;
            $date = date('Y-m-d', strtotime($date)+24*3600);
        } while ($date <= $end_date);

        //日期条件初始化
        if (!$rwtool_filter) {
            $rwtool_filter = array(1=>1);
        }

        //全部
        if ($rwtool_status == 0) {
            //查询全部
            $tmp_list           = _model('rfid_rwtool')->getList($rwtool_filter);
        //正常
        } else if (in_array($rwtool_status, array(1, 2))) {
            //查询符合条件的所有读写器统计
            $filter['status']   = $rwtool_status;
            $tmp_list           = _model('rfid_rwtool_stat_day')->getList($filter);
        //离线（异常）
        } else if ($rwtool_status == 6) {
            //查出所有范围内的读写器

            $business_ids = _model('rfid_rwtool')->getFields('business_id', $rwtool_filter, ' GROUP BY business_id ');
            //查出所有正常和在线的读写器
            $business_ids2 = _model('rfid_rwtool_stat_day')->getFields('business_id', $filter, ' GROUP BY business_id, day ');
            //计算相同值出现的次数
            $new_arr = array_count_values($business_ids2);
            foreach ($business_ids as $v) {
                //正常和在线的读写器出现的次数小于搜索天数，则存在某一天全部离线的情况
                if (empty($new_arr[$v]) || $new_arr[$v] < count($days)) {
                    $tmp_list[] = $v;
                }
            }
        }

        //循环日期查询
        foreach ($days as $v) {
            $new_filter = $rwtool_filter;
            $new_filter['day'] = date('Ymd', strtotime($v));
            //查询当前日期每个读写器的在线信息
            foreach ($tmp_list as $kk => $vv) {
                //离线数据
                if ($rwtool_status == 6) {
                    $vv = array('business_id' => $vv);
                }
                $new_filter['business_id'] = $vv['business_id'];
                $stat_info = _model('rfid_rwtool_stat_day')->read($new_filter);
                //没有在线的统计信息，视为当前日期设备异常，标签全部离线
                if (!$stat_info) {
                    //离线数据的 $vv 只有business_id
                    if ($rwtool_status == 6) {
                        $vv = _model('rfid_rwtool')->read($vv, ' ORDER BY `id` DESC LIMIT 1');
                    }
                    $vv['online_label_num'] = 0;
                    $vv['status'] = 6;
                    $vv['day'] = $new_filter['day'];
                    $info = $vv;
                } else {
                    $info = $stat_info;
                }

                //以设备为维度，拼接数据
                if (isset($rwtool_stat_list[$info['business_id']])) {
                    $rwtool_stat_list[$info['business_id']]['data_list'][$v] = $info;
                } else {
                    $tmp = array(
                            'business_id' => $info['business_id'],
                            'area_id'     => $info['area_id'],
                            'city_id'     => $info['city_id'],
                            'province_id'     => $info['province_id'],
                    );
                    $rwtool_stat_list[$info['business_id']]['region'] = $tmp;
                    $rwtool_stat_list[$info['business_id']]['data_list'][$v] = $info;
                }
            }
        }
        $count = count($rwtool_stat_list);

        if ($is_export == 1) {
            $this->export($rwtool_stat_list);
            exit();
        }

        Response::assign('count', $count);
        Response::assign('order_field', $order_field);
        Response::assign('order_dir', $order_dir);
        Response::assign('days' , $days );
        Response::assign('rwtool_list' , $rwtool_stat_list );
        Response::assign('search_filter', $search_filter);
        Response::assign('page' , $page);
        Response::display('admin/rwtool/rwtool_list.html');
    }
    /**
     * 添加
     */
    public function add()
    {
        Response::display('admin/rwtool/add.html');
    }

    public function edit()
    {
        $id = tools_helper::get('id', 0);

        if ( !$id ) {
            return '营业厅不能为空';
        }

        $rwtool_info = _model('rfid_rwtool')->read(array('business_id' => $id), ' ORDER BY `id` DESC LIMIT 1 ');

        if ( !$rwtool_info ) {
            return '不存在读写器';
        }

        $business_info = _model('business_hall')->read($id);

        Response::assign('rwtool_info', $rwtool_info);
        Response::assign('business_info', $business_info);
        Response::display('admin/rwtool/add.html');
    }

    /**
     * 保存
     * @return string|string[]
     */
    public function save()
    {
        $id         = Request::post('id' , 0);
        $rwtool_info = Request::post('rwtool_info' , array());

        if ($id || empty($rwtool_info['type']) || $rwtool_info['type'] != 2) {
            //不为空
            if ( empty($rwtool_info['business_id']) || strpos($rwtool_info['business_id'] , ',') !== false) {
                return '请选择营业厅';
            }

            if (empty($rwtool_info['label_num']) || strpos($rwtool_info['label_num'] , ',') !== false) {
                return '非法的标签数';
            }

            $business_filter = array('id' => $rwtool_info['business_id']);

            if ($this->member_info['res_name'] != 'group') {
                if ($this->member_info['res_name'] == 'business_hall') {
                    if ($this->member_info['res_id'] != $rwtool_info['business_id'])  return '选择的营业厅不在您的管辖之下';
                } else {
                    $business_filter[$this->member_info['res_name'].'_id'] = $this->member_info['res_id'];
                }
            }

            $business_hall_info = _model('business_hall')->read($business_filter);

            if ( !$business_hall_info ) {
                return '选择的营业厅不存在或者不在您的管辖之下';
            }

            $rfid_rwtool_info = _model('rfid_rwtool')->read(array('business_id' => $business_hall_info['id']));

            if ($id) {
                $res = _model('rfid_rwtool')->update($id, array('label_num' => $rwtool_info['label_num']));
            } else if ( $rfid_rwtool_info ) {
                $res = _model('rfid_rwtool')->update($rfid_rwtool_info['id'], array('label_num' => $rwtool_info['label_num']));
            } else {

                $new_data = array(
                        'province_id' => $business_hall_info['province_id'],
                        'city_id' => $business_hall_info['city_id'],
                        'area_id' => $business_hall_info['area_id'],
                        'business_id' => $business_hall_info['id'],
                        'label_num'   => $rwtool_info['label_num'],
                );

                $rwtool_id = _model('rfid_rwtool')->create($new_data);
            }

        } else {
            $data = $this->upload_excel();

            foreach ($data as $k => $v) {
                $business_hall_info = _model('business_hall')->read(array('user_number' => $v['user_number']));

                if (!$business_hall_info) {
                    return '不存在的渠道编码'.$v['user_number'];
                }

                $new_data = array(
                        'province_id' => $business_hall_info['province_id'],
                        'city_id' => $business_hall_info['city_id'],
                        'area_id' => $business_hall_info['area_id'],
                        'business_id' => $business_hall_info['id'],
                );

                //查询是否存在读写器
                $rwtool_info = _model('rfid_rwtool')->read($new_data);

                $new_data['label_num']   = $v['label_num'];

                if ( $rwtool_info ) {
                    $res = _model('rfid_rwtool')->update($rwtool_info['id'], $new_data);
                } else {
                    $rwtool_id = _model('rfid_rwtool')->create($new_data);
                }
            }
        }

        return array('操作成功', 'success', AnUrl("rfid/admin/rwtool"));
    }

    /**
     * 上传excel
     * @return string|string[]|unknown[]
     */
    public function upload_excel()
    {
        if (!isset($_FILES['phone_data']['name']) || !$_FILES['phone_data']['name']) {
            return '请选择上传的Excel文件';
        }

        $file = $_FILES['phone_data'];

        if (!$file['name']) {
            return '请选择上传的Excel文件';
        }

        $allow_type = Config::get('allow_type');

        $upload_path = UPLOAD_PATH;

        $fail_msg = check_upload($file, 0, 1);

        if ($fail_msg) {
            return array($fail_msg, 'error', AnUrl('rfid/admin/rwtool/add'));
        }

        $ext = substr($file['name'], strrpos($file['name'], '.')+1);

        if (!in_array(strtolower($ext), $allow_type)) {
            return '文件格式不正确';
        }

        if (empty($fail_msg)) {
            $file_path = an_upload($file['tmp_name'], $ext);
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

    //删除
    public function delete()
    {
        $id = Request::getParam('id',0);

        if (!$id) {
            return array('info'=>'请选择删除的数据');
        }

        $rfid_info = _model('rfid_rwtool')->read(array('business_id' => $id));

        if (!$rfid_info) {
            return array('info' => '读写器已被删除');
        }

        _model('rfid_rwtool')->delete($rfid_info['id']);

        return array('info' => 'ok');

    }


    /**
     * 上传文件模板说明 .excel文件
     */
    public function load_instruction()
    {
        $objPHPExcel = new PHPExcel();
        //设置宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);

        $excelobj = $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A1', '请在此列输入营业厅名称')
        ->setCellValue('B1', '请在此列输入营业厅渠道编码')
        ->setCellValue('C1', '请在此列输入读写器对应的标签个数');

        $excelobj = $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValueExplicit('A100', '')
        ->setCellValueExplicit('B100', '')
        ->setCellValueExplicit('C100', '');

        //设置sheet标题
        $objPHPExcel->getActiveSheet()->setTitle('导入读写器信息');
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="读写器信息模板.xls"');
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

    public function export($rwtool_list)
    {
        //p($rwtool_list);exit;
        if (!$rwtool_list) {
            return true;
        }

        $params['filename'] = date('Ymd').'读写器列表';
        $params['head']     = array('省', '市', '区', '营业厅名称', '渠道码');
        $new_list = array();
        $i = 0;
        foreach ($rwtool_list as $k => $v) {

            $tmp = array();
            $tmp['province'] = business_hall_helper::get_info_name('province', $v['region']['province_id'], 'name');
            $tmp['city'] = business_hall_helper::get_info_name('city', $v['region']['city_id'], 'name');
            $tmp['area'] = business_hall_helper::get_info_name('area', $v['region']['area_id'], 'name');
            $tmp['business_title'] = business_hall_helper::get_info_name('business_hall', $v['region']['business_id'], 'title');
            $tmp['user_number'] = business_hall_helper::get_info_name('business_hall', $v['region']['business_id'], 'user_number');

            foreach ($v['data_list'] as $kk => $vv) {
                $tmp[$kk] = '读写器状态：'.rfid_config::$rwtool_status[$vv['status']]['status']."\r\n总标签数：".$vv['label_num']."\r\n在线标签数：".$vv['online_label_num'];
                if ($i == 0) {
                    $params['head'][] = $kk;
                }
            }
            ++$i;
            $new_list[] = $tmp;
        }
        $params['data']     = $new_list;
        Csv::getCvsObj($params)->export();
    }
}