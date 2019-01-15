<?php
/**
 * alltosun.com 处理企业维系的xml数据 wework_xml_helper.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-5-9 上午10:23:55 $
 * $Id$
 */

class wework_xml_helper
{

    public static function to_array($xml)
    {
        // 禁止引用外部xml实体
        libxml_disable_entity_loader(true);

        $arr = json_decode( json_encode( simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA) ) , true);

        return $arr;
    }

}