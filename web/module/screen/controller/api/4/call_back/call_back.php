<?php

/**
 * alltosun.com 回调检查接口 class_back.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2018年1月8日 下午2:33:01 $
 * $Id$
 */

class Action
{
    public function index()
    {
        $time         = tools_helper::post('time', 0);
        $request_path = tools_helper::post('request_path', '');

//         $info = _mongo('screen', 'call_back_log')->find(array(
//             'time'          => (int)$time,
//             'request_path'  => $request_path
//         ));

//         $id = array();
        
//         foreach ($info as $k => $v) {
//             $id = (array)$v['_id'];
//         }

        $id = 1;

        if ($id) {
            
            $return_data = array(
                'status' => array(
                    'code'    => 1000,
                    'message' => 'success'
                ),
                'result' => array ( 'info' => 'ok' )
            );
            
        } else {
            
            $return_data = array(
                'status' => array(
                    'code'    => 1010,
                    'message' => 'error'
                ),
                'result' => array ( 'info' => 'fail' )
            );
            
        }
        
        //return json_encode($return_data, JSON_UNESCAPED_UNICODE);
        
        echo json_encode($return_data, JSON_UNESCAPED_UNICODE);
        exit();
    }
}