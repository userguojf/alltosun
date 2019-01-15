<?php

/**
 * alltosun.com 过滤类 Filter.php
 * ============================================================================
 * 版权所有 (C) 2009-2010 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 宁海新 (ninghx@alltosun.com) $
 * $Date: 2011-1-12 下午07:05:30 $
*/

class AnFilter
{
    /**
     * 过滤搜索关键词中的_ %
     * @param string $keyword
     * @author ninghx@alltosun.com
     */
    public static function filter_keyword($keyword)
    {
        return str_replace(array('_', '%'), array('\_', '\%'), $keyword);
    }

    /**
     * 过滤字符串
     * @param string $string
     * @param $quote_style
     * @return string
     */
    public static function filter_string($string, $quote_style = ENT_QUOTES)
    {
        return htmlspecialchars($string, $quote_style);
    }
}
?>