<?php
/**
 * alltosun.com RFID数据公共类 common.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 王敬飞 (wangjf@alltosun.com) $
 * $Date: 2017年10月26日 下午5:09:19 $
 * $Id$
 */
loadfile('trait', 'handle');

class common
{

    public $fields = array(
        'action_id',
        'label_id',
        'scanner_id',
        'mac',
        'type',
        'timestamp',
        'rssi',
        'bat',
        'g_data_x',
        'g_data_y',
        'g_data_z',
        'a_data_x',
        'a_data_y',
        'a_data_z'
    );

    use handle;

//     public function handle($string, $serv=NULL, $fp=NULL)
//     {
//         //解析为数组
//         $arr = $this->init();
// p($arr);
//         foreach ($arr as $k => $v) {
//             //拿起
//             if (!empty($v['type']) && $v['type'] == 1) {
//                 $this->up($v);
//             //放下
//             } else if (!empty($v['type']) && $v['type'] == 0) {
//                 $this->down($v);
//             }
//         }
//     }
}
