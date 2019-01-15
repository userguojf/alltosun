<?php
/**
 * alltosun.com Base64 Base64.php
 * ============================================================================
 * 版权所有 (C) 2009-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: Shenxn 申小宁 (shenxn@alltosun.com) $
 * $Date: May 31, 2014 2:30:48 PM $
 * $Id$
 */

class Base64 {

    /**
     * 加密字符串
     * @access static
     * @param string $str 字符串
     * @param string $key 加密key
     * @return string
     */
    public static function encrypt($data,$key) {
        $key    =   md5($key);
        $data   =   base64_encode($data);
        $x=0;
		$len = strlen($data);
		$l = strlen($key);
        for ($i=0;$i< $len;$i++) {
            if ($x== $l) $x=0;
            $char   .=substr($key,$x,1);
            $x++;
        }
        for ($i=0;$i< $len;$i++) {
            $str    .=chr(ord(substr($data,$i,1))+(ord(substr($char,$i,1)))%256);
        }
        return $str;
    }

    /**
     * 解密字符串
     * @access static
     * @param string $str 字符串
     * @param string $key 加密key
     * @return string
     */
    public static function decrypt($data,$key) {
        $key    =   md5($key);
        $x=0;
		$len = strlen($data);
		$l = strlen($key);
        for ($i=0;$i< $len;$i++) {
            if ($x== $l) $x=0;
            $char   .=substr($key,$x,1);
            $x++;
        }
        for ($i=0;$i< $len;$i++) {
            if (ord(substr($data,$i,1))<ord(substr($char,$i,1))) {
                $str    .=chr((ord(substr($data,$i,1))+256)-ord(substr($char,$i,1)));
            }else{
                $str    .=chr(ord(substr($data,$i,1))-ord(substr($char,$i,1)));
            }
        }
        return base64_decode($str);
    }
}