<?php

/**
 * alltosun.com 数组处理类 AnArray.php
 * ============================================================================
 * 版权所有 (C) 2009-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址：http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 钱有明 (qianym@alltosun.com) $
 * $Date: 2012-6-25 下午06:03:55 $
 * $Id: Array.php 774 2014-01-21 01:48:14Z qianym $
*/

class AnArray
{
    /**
     * 模板中的select
     * @param $array array 二维数组
     * @param string 显示的字段名称
     * @param string 做为键的字段名称
     * @return array 一维数组
     * @author ninghx@alltosun.com
     */
    public static function arrayToOption($array, $value = 'title', $key = 'id')
    {
        $result = array();
        foreach ($array as $v) {
            if (!$v) {
                continue;
            }
            $result[$v[$key]] = $v[$value];
        }

        return $result;
    }

    /**
     * 二维数组按照子级数组中指定的某个值进行排序
     * @param array $list        二维数组
     * @param string $order_key  指定的某个值
     * @return array
     */
    public static function arraySortByKey($list, $order_key) {
        $tmp = array();
        foreach ($list as &$ma) {
            $tmp[] = &$ma[$order_key];
        }
        array_multisort($tmp, $list);

        return $list;
    }

    /**
     * 对数组的所有key, value进行编码转换
     * @param string $in_charset
     * @param string $out_charset
     * @param string|array $array
     * @return string|array
     */
    public static function iconvArray($in_charset, $out_charset, $array)
    {
        if (is_array($array)) {
            foreach ($array as $k=>$v) {
                $k = iconv($in_charset, $out_charset, $k);
                $array[$k] = self::iconvArray($in_charset, $out_charset, $v);
            }
        } else {
            $array = iconv($in_charset, $out_charset, $array);
        }

        return $array;
    }
}
?>