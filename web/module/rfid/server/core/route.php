<?php

/**
 * alltosun.com 路由分派 route.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 王敬飞 (wangjf@alltosun.com) $
 * $Date: 2017年6月17日 下午12:16:31 $
 * $Id$
 */
class route
{
    public static $_instance;

    public static function get_instance()
    {

        if (!is_object(self::$_instance)) {
            self::$_instance = new self();
            return self::$_instance;
        }

        return self::$_instance;
    }

    /**
     * 解析
     * @return boolean
     */
    public function parse($string, swoole_server $serv = NULL, $fd = NULL)
    {

        if (!$string) {
            return array('errno' => 1002, 'msg' => '数据为空[2]');
        }

        /**
         * 山脊设备
         * 山脊设备的数据是唯一存在 MessageId的厂家， 注：字符串MessageId是存在第0位的，所以 要使用 === false 或者 !== false
         */
        if (strpos($string, 'MessageId') !== false && strpos($string, 'DURATION') !== false && strpos($string, 'G_Data_X') !== false) {

            $company = 'shanj';

            /**
             * 金鑫设备
             * 金鑫设备和山脊类似，只不存在MessageId, 存在DURATION， 存在陀螺仪数据
             */
        } else if (strpos($string, 'MessageId') === false && strpos($string, 'DURATION') !== false && strpos($string, 'G_Data_X') !== false) {
            $company = 'jinx';

            /**
             * 联华设备
             * 联华设备和山脊类似，不存在 MessageId, 存在DURATIOIN， 不存在陀螺仪数据
             */
        } else if (strpos($string, 'MessageId') === false && strpos($string, 'DURATION') !== false && strpos($string, 'G_Data_X') === false) {

            $company = 'lianh';

            /**
             * 美斯特设备
             * 美斯特设备是唯一一个存在 F-TYPE 或者 SN 的厂家
             */
        } else if (strpos($string, 'F-TYPE') !== false) {

            $company = 'meist';

            //美斯特应答包 应答字符串为:
        } else if (trim($string) == 'user_server_get_time') {
            //发送应答
            $serv->send($fd, "user_server_set_systemtime date=" . date('YmdHis') . "\r\n");
            return false;

            //自定义接口规范数据, 暂时先返回应答
        } else if (strpos($string, 'action_id') !== false) {

            $common = AutoLoad::instance('common');
            $arr = $common->init($string, $serv, $fd);

            foreach ($arr as $k => $v) {
                //拿起
                if (isset($v['type']) && $v['type'] == 1) {
                    $common->up($v);
                    //放下
                } else if (isset($v['type']) && $v['type'] == 2) {
                    $common->down($v);
                }
            }
            return false;
        } else {
            return false;
        }

        //效验数据
        $result = AutoLoad::instance($company)->check($string, $serv, $fd);

        if ($result != 'success') {
            $result['string'] = $string;
            AutoLoad::instance('error_log')->write_error_log($result);
        }

    }

}

