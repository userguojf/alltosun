<?php
/**
 * alltosun.com 成员账号的生成规则 wework_rule_helper.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-5-29 下午6:23:54 $
 * $Id$
 */

class wework_rule_helper
{

    public static function name($name)
    {
        if ( !$name ) return false;

        if ( !strpos($name, '_') ) return false;

        $name_arr = explode('_', $name);

        if ( count($name_arr) < 3 ) return false;

        $code = intval($name_arr[2]) + 1;

        if ( 1 == strlen($code) ) return $name_arr[0] . '_' . $name_arr[1] . '_0' . $code;

        return $name_arr[0] . '_' . $name_arr[1] . '_' . $code;
    }

}