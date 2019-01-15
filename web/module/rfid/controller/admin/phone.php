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
        $search_filter = Request::post('search_filter', array());
        $order         = " ORDER BY `id`  DESC ";

        $page          = tools_helper::get('page_no', 1);

        $filter = $list = array();

        //搜索
        if(isset($search_filter['name']) && !empty($search_filter['name'])) {
            $filter['name'] = trim($search_filter['name']);
        }

        if(isset($search_filter['version']) && !empty($search_filter['version'])) {
            $filter['version'] = trim($search_filter['version']);
        }

        if(isset($search_filter['color']) && !empty($search_filter['color'])) {
            $filter['color'] = $search_filter['color'];
        }

        if(!$filter) {
            $filter=array('1' => 1);
        }

        $count = _model('rfid_phone')->getTotal($filter);

        if ($count) {
            $pager  = new Pager($this->per_page);
            $list   = _model('rfid_phone')->getList($filter ,$order.$pager->getLimit($page));

            if ($pager->generate($count,$page)) {
                Response::assign('pager', $pager);
            }
        }

        Response::assign('count', $count);
        Response::assign('list' , $list );
        Response::assign('search_filter',$search_filter);
        Response::assign('page' , $page);

        Response::display('admin/phone_list.html');
    }

    public function add()
    {
        $id = Request::get('id' , 0);

        if ($id) {
            $phone_info = _model('rfid_phone')->read(array('id' => $id));

            Response::assign('id' , $id);
            Response::assign('phone_info' , $phone_info);
        }

        Response::display('admin/phone_add.html');
    }

    public function save()
    {
        $id         = Request::post('id' , 0);
        $phone_info = Request::post('phone_info' , array());

        if ($id) {
            //不为空
            if (!isset($phone_info['name']) || empty($phone_info['name']) || strpos($phone_info['name'] , ',')) {
               return '手机品牌不能为空（或者含有特殊字符）';
            }

            if (!isset($phone_info['version']) || empty($phone_info['version']) || strpos($phone_info['version'] , ',')) {
                return '手机型号不能为空（或者含有特殊字符）';
            }

            if (!isset($phone_info['color']) || empty($phone_info['color'])) {
                return '请选择手机颜色';
            }

            //更新
            _model('rfid_phone')->update($id , $phone_info);
        }

        //单独创建
        if(!$id  && 1 == $phone_info['type'] ) {
            //不为空
            if (!isset($phone_info['name']) || empty($phone_info['name']) || strpos($phone_info['name'] , ',')) {
                return '手机品牌不能为空（或者含有特殊字符）';
            }

            if (!isset($phone_info['version']) || empty($phone_info['version']) || strpos($phone_info['version'] , ',')) {
                return '手机型号不能为空（或者含有特殊字符）';
            }

            if (!isset($phone_info['color']) || empty($phone_info['color'])) {
                return '请选择手机颜色';
            }

            //判断是否有完全相同
            $is_info = _model('rfid_phone')->read($phone_info);

            if ($is_info) {
                return '填入手机的信息已经存在';
            }

            //创建
            _model('rfid_phone')->create($phone_info);
        }

        //批量创建
        if( 2 == $phone_info['type'] ) {

            if (isset($_FILES['phone_data']['name']) && $_FILES['phone_data']['name']) {

                $file = $_FILES['phone_data'];

                if (!$file['name']) {
                    return '请选择上传的Excel文件';
                }

                $allow_type = Config::get('allow_type');

                $upload_path = UPLOAD_PATH;

                $fail_msg = check_upload($file, 0, 1);

                if ($fail_msg) {
                    return array($fail_msg, 'error', AnUrl('rfid/admin/phone'));
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

                $data['name']    = $v[1];
                $data['version'] = $v[2];
                $data['color']   = $v[3];

                //判断是否有完全相同
                $is_info = _model('rfid_phone')->read($data);

                if ($is_info || strpos($v[1] , ',') || strpos($v[2] , ',') || strpos($v[3] , ',')) {
                    continue;
                }

                $data['type'] = 2;

                _model('rfid_phone')->create($data);
            }
        }

        return array('操作成功', 'success', AnUrl("rfid/admin/phone"));
    }

    //删除
    public function delete()
    {
        $id = Request::getParam('id',0);

        if (!$id) {
            return array('info'=>'请选择删除的数据');
        }

        $rfid_info = _model('rfid_label')->read(array('phone_id' => $id));

        if ($rfid_info) {
            return array('info' => '该手机信息已绑定标签，请先修改标签为其他手机信息，才能删除此手机信息');
        }

        _model('rfid_phone')->delete(array('id' => $id));

        return array('info' => 'ok');

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