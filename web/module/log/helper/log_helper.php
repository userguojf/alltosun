<?php
    /**
    * alltosun.com log_helper.php
    ================================================
    * 版权所有 (C) 2009-2014 北京互动阳光科技有限公司，并保留所有权利。
    * 网站地址: http://www.alltosun.com
    * ----------------------------------------------------------------------------
    * 许可声明: 这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
    * ============================================================================
    * @author: 祝利柯 (zhulk@alltosun.com)
    * @date:2015-2-13
    * @encoding PHP
    */

class log_helper
{
    /**
     * 获取表名
     * @param string $res_name
     */
    public static function get_res_name($res_name)
    {
        if (array_key_exists($res_name, log_config::$log_res_name)) {

            return log_config::$log_res_name[$res_name];

        }

        return $res_name;
    }

    /**
     * 去除json大括号
     * @param json $str_cut
     * @return String
     */
    public static  function handle_result($json)
    {

        if(empty($json)){
            return "没有数据";
        }

        //去掉json字符串左右括号
        $json     = str_replace ( '{"' ,  '' ,  $json );
        $json     = str_replace ( '"}' ,  '' ,  $json );

        $json = msubstr($json, 0, 37);
        return $json;
    }



}
?>