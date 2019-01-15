<?php

/**
 * alltosun.com DES加密 csv.php
 * ============================================================================
 * 版权所有 (C) 2009-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: Shenxn 申小宁 (shenxn@alltosun.com) $
 * $Date: Jul 18, 2014 12:49:05 PM $
 * $Id: csv.php 125095 2014-07-18 07:19:33Z shenxn $
 */

class AnDES {

    private static $_instance = NULL;
 
    /**
     * @return AnDES OBJECT
     */
    public static function share() {
        if (is_null(self::$_instance)) {
            self::$_instance = new AnDES();
        }
        return self::$_instance;
    }

    /**
     * 加密
     * @param string $str 要处理的字符串
     * @param string $key 加密Key，为8个字节长度
     * @return string
     */
    public function encode($str, $key) {
        $size = mcrypt_get_block_size(MCRYPT_DES, MCRYPT_MODE_CBC);
        $str = $this->pkcs5Pad($str, $size);

        //php5.5 7弃用
        //$aaa = mcrypt_cbc(MCRYPT_DES, $key, $str, MCRYPT_ENCRYPT, $key);

        $aaa = mcrypt_encrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_CBC, $key);
        $ret = base64_encode($aaa);
        return $ret;
    }

    /**
     * 解密
     * @param string $str 要处理的字符串
     * @param string $key 解密Key，为8个字节长度
     * @return string
     */
    public function decode($str, $key) {
        $strBin = base64_decode($str);
        $str = mcrypt_decrypt(MCRYPT_DES, $key, $strBin, MCRYPT_MODE_CBC, $key);
        $str = $this->pkcs5Unpad($str);
        return $str;
    }

    /**
     * 转2进制
     * @param String $hexData
     * @return string
     */
    public function hex2bin($hexData) {
        $binData = "";

        for ($i = 0; $i < strlen($hexData); $i += 2) {
            $binData .= chr(hexdec(substr($hexData, $i, 2)));
        }

        return $binData;
    }

    /**
     * text转化为Pad
     * @param String $text
     * @param Int $blocksize
     */
    public function pkcs5Pad($text, $blocksize) {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    /**
     * 转化
     * @param String $text
     */
    public  function pkcs5Unpad($text) {
        $pad = ord($text {strlen($text) - 1});
        if ($pad > strlen($text)){
            return false;
        }

        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad){
            return false;
        }

        return substr($text, 0, - 1 * $pad);
    }

}