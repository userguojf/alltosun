<?php
/**
 * alltosun.com dm.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-11-1 下午5:08:42 $
 * $Id$
 */

/**
 * 说明：由于数字地图的接口返回服务器信息错误导致两边数据有差异，导出一份给数字地图单独处理
 */
 class Action
 {
     public function __call($action = '', $param = array())
     {
         $file_name = tools_helper::get('filename', 'screen');

         if (!in_array($file_name, array('screen', 'rfid'))) return '参数错误';

         if ($file_name == 'screen') {
             $table = 'screen_device';

             $list = _model($table)->getList(array('shoppe_id >' => 0));

             $this->export_screen($list, $file_name);

         } else {
             $table = 'rfid_label';

             $list = _model($table)->getList(array('shoppe_id >' => 0));

             $this->export_rfid($list, $file_name);
         }

     }

     public function export_rfid($data, $file_name)
     {
         if (!$data) {
             return '暂无数据';
         }

         foreach ($data as $k => $v) {
             $list[$k]['brand']        = $v['name'];
             $list[$k]['businessCode'] = focus_helper::get_field_info($v['business_hall_id'],'business_hall', 'user_number');
             $list[$k]['probeNo']      = $v['label_id'];
             $list[$k]['shoppeId']     = $v['shoppe_id'];
             $list[$k]['version']      = $v['version'];
         }

         $params['filename'] = $file_name;
         $params['data']     = $list;
         $params['head']     = array('brand', 'businessCode', 'probeNo' , 'shoppeId', 'version');

         Csv::getCvsObj($params)->export();
     }

     public function export_screen($data, $file_name)
     {
         if (!$data) {
             return '暂无数据';
         }

         foreach ($data as $k => $v) {
             $list[$k]['brand']        = $v['phone_name'];
             $list[$k]['businessCode'] = focus_helper::get_field_info($v['business_id'],'business_hall', 'user_number');
             $list[$k]['probeNo']      = $v['device_unique_id'];
             $list[$k]['shoppeId']     = $v['shoppe_id'];
             $list[$k]['version']      = $v['phone_version'];
         }
     
         $params['filename'] = $file_name;
         $params['data']     = $list;
         $params['head']     = array('brand', 'businessCode', 'probeNo' , 'shoppeId', 'version');
     
         Csv::getCvsObj($params)->export();
     }
 }