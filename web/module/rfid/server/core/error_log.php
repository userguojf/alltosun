<?php

/**
 * alltosun.com 错误日志 error_log.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 王敬飞 (wangjf@alltosun.com) $
 * $Date: 2017年6月17日 下午12:30:08 $
 * $Id$
 */
class error_log
{

    /**
     * 写入错误log
     * @param array $error_info 错误详情
     * @param array $error_data 错误数据
     * @param number $label_id 标签id
     */
    public function write_error_log($params)
    {
        $new_error = array();

        if (!isset($params['errno']) || $params['errno'] != 1001) {
            return $params;
        }

        if (!empty($params['string'])) {
            $new_error['original'] = json_encode($params['string']);
        }
        if (!empty($params['msg'])) {
            $new_error['error_info'] = "'" . $params['msg'] . "'";
        }

        if (!empty($params['error_data'])) {
            $new_error['error_data'] = "'" . json_encode($params['error_data'], JSON_UNESCAPED_UNICODE) . "'";
        }

        $new_error['add_time'] = "'" . date('Y-m-d H:i:s') . "'";

        $id = AutoLoad::instance('model')->create('rfid_error_logs', $new_error);

        //插入失败
        if (!$id) {
            echo '<pre>';
            echo '插入失败，error data:';
            var_dump($params);
        }

    }

    public function log($level, $message, $content = array(), $module = '')
    {
        echo '<pre>';
        var_dump($level);
        var_dump($message);
    }
}