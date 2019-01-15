<?php
/**
 * alltosun.com  字符串相关工具类库 string.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 王德康 (wangdk@alltosun.com) $
 * $Date: 2014-2-7 上午10:28:42 $
 * $Id$
 */

class string_widget
{
    /**
     * 匹配图片添加超链接
     * @param string $string
     * @return string;
     */
    public static function auto_img_link($string)
    {
        $reg = "/<[img|IMG].*?src=([\'|\"](.*?)[\'|\"]).*?[\/]?\>/";
        $replace_to = '<a href="${2}" target="_blank" > ${0}</a>';

        return preg_replace($reg, $replace_to, $string);
    }

    /**
     * 字符串截取，支持中文和其他编码
     * @param string $str 需要转换的字符串
     * @param string $start 开始位置
     * @param string $length 截取长度
     * @param string $charset 编码格式
     * @param string $suffix 截断显示字符
     * @return string
     */
    public function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true) {
        if(function_exists("mb_substr"))
            $slice = mb_substr($str, $start, $length, $charset);
        elseif(function_exists('iconv_substr')) {
            $slice = iconv_substr($str,$start,$length,$charset);
            if(false === $slice) {
                $slice = '';
            }
        } else {
            $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
            $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
            $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
            $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
            preg_match_all($re[$charset], $str, $match);
            $slice = join("",array_slice($match[0], $start, $length));
        }
        return $suffix ? $slice.'...' : $slice;
    }

    /**
     * 中英文字符长度
     * @param string $str
     * @return number
     */
    public function str_cn_len($str) {
        $len = 0;
        for ($i = 0; $i < strlen($str); $i++) {
            $temp_str = substr($str, $i, 1);
            $asc_num  = ord($temp_str);
            if ($asc_num > 192) {
                $len = $len + 2;
                $i = $i + 2;
            } else {
                $len++;
            }
        }

        return $len;
    }

    /**
     * 截取字符串(支持中英文混合)
     * @param $str 原字符串
     * @param $len 截取的长度
     * @param $fix 扩展字符(例如：是否截取之后有...)
     * @return;
     */
    public function sub_arrstr($str, $len = 11, $fix = '...')
    {
        if (strlen($str) - strlen($fix) <= $len) {
            return $str;
        }

        $returnStr = '';
        $i = 0;
        $n = 0;
        while (($n < $len) && ($i <= strlen($str))) {
            $tempStr = substr($str, $i, 1);
            $ascnum = ord($tempStr);//得到字符串中第$i位字符的ascii码

            //如果ASCII位高与224，
            if ($ascnum >= 192) {
                $returnStr .= substr($str, $i, 3); //根据UTF-8编码规范，将2个连续的字符计为单个字符
                $i += 3; //实际Byte计为2
                $n = $n + 2; //字串长度计2

                //如果ASCII位高与192，
            } else {
                $returnStr .= substr($str, $i, 1);
                $i++; //实际的Byte数计1个
                $n += 1; //小写字母和半角标点等与半个高位字符宽...
            }
        }

        if (strlen($returnStr) != strlen($str)) {
            $returnStr .= $fix;
        }

        return $returnStr;
    }
}
?>