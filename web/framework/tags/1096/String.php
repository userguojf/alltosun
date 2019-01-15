<?php

/**
 * alltosun.com 字符串处理类 AnString.php
 * ============================================================================
 * 版权所有 (C) 2009-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址：http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 钱有明 (qianym@alltosun.com) $
 * $Date: 2012-6-25 下午06:00:55 $
 * $Id: String.php 731 2013-07-30 08:21:25Z anr $
*/

class AnString
{
    /**
     * 截取字符串
     * @param string $str
     * @param int $len 截取的长度
     * @param string $fix 补充的省略符
     * @return string
     */
    public static function cutStr($str, $len, $fix = '...')
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
            if ($ascnum >= 224) {
                $returnStr .= substr($str, $i, 3); //根据UTF-8编码规范，将3个连续的字符计为单个字符
                $i += 3; //实际Byte计为3
                $n++; //字串长度计1

             //如果ASCII位高与192，
            } elseif ($ascnum >= 192) {
                $returnStr .= substr($str, $i, 2); //根据UTF-8编码规范，将2个连续的字符计为单个字符
                $i += 2; //实际Byte计为2
                $n++; //字串长度计1

            //如果是大写字母，
            } elseif ($ascnum >= 65 && $ascnum <= 90) {
                $returnStr .= substr($str, $i, 1);
                $i++; //实际的Byte数仍计1个
                $n++; //但考虑整体美观，大写字母计成一个高位字符

            //其他情况下，包括小写字母和半角标点符号，
            } else {
                $returnStr .= substr($str, $i, 1);
                $i++; //实际的Byte数计1个
                $n += 0.5; //小写字母和半角标点等与半个高位字符宽...
            }
        }

        if (strlen($returnStr) != strlen($str)) {
            $returnStr .= $fix;
        }

        return $returnStr;
    }

    /**
     * 截取html文本，同时保留换行
     * @param string $html
     * @param int $len
     * @param string $replace
     * @return string
     * @author qianym@alltosun.com
     */
    public static function stripHtml($html, $len = 150, $fix = '...')
    {
        $replace = '。...';
        $returnHtml = str_replace('</p>', '<br>', $html);
        $returnHtml = strip_tags(str_replace(array('<br>', '<br/>', '<br />'), $replace, $returnHtml));
        $returnHtml = AnString::cutStr($returnHtml, $len, $fix);

        if (strlen($html) != strlen($returnHtml) && mb_strlen($html, 'utf-8') > $len) {
            $returnHtml .= $fix;
        }

        return AnString::stripEmptyLines(trim(str_replace($replace, '<br>', $returnHtml), '<br>'));
    }

    /**
     * 换行转换
     * @param string $str
     * @param string $replace 将换行转成字符
     * @return string
     * @author gaojj@alltosun.com
     */
    public static function stripBr($str, $replace='')
    {
        if (is_array($str)) {
            foreach ($str as $k=>$v) {
                $str[$k] = self::stripBr($v, $replace);
            }
            return $str;
        } else {
            $order   = array("\r\n", "\n", "\r");
            return str_replace($order, $replace, $str);
        }
    }

    /**
     * 清除字符串中指定的符串
     * @param string $str
     * @param array $replace
     * @return string
     * @author qianym@alltosun.com
     * @example $str = AnString::stripStr($str, array("\r\n", "\r", "\n", "\t", "&nbsp;"))
     */
    public static function stripStr($str, $replace = array())
    {
        return str_ireplace($replace, "", $str);
    }

	/**
     * 清除、空格、回车换行符的函数
     * @param string $str
     * @return string
     * @author liww@alltosun.com
     */
    public static function stripSpace($str)
    {
        $str = str_ireplace(array("\t", " ", "&nbsp;"), "", $str);
        return trim($str);
    }

    /**
     * 去除空行 （即：多个连续的换行符只保留一个）
     * @param string $str
     * @author gaojj@alltosun.com
     */
    public static function stripEmptyLines($str)
    {
        while (stripos($str, '<br><br>') !== false || stripos($str, '<br /><br />') !== false ) {
            $str = str_ireplace(array('<br><br>','<br /><br />'), '<br>', $str);
        }
        return $str;
    }

    /**
     * 使用HTMLPurifier 防止xss（默认过滤掉script）
     * @param string  $html
     * @param string  $tags  'b,i,pre'
     * @return string
     * @example $content = AnString::html_purifer($content, 'b,i,pre')
     */
    public static function html_purifier($html, $tags = '')
    {
        $html_config = HTMLPurifier_Config::createDefault();
        if ($tags) {
            $html_config->set('HTML', 'ForbiddenElements', $tags);
        }
        $purifier = new HTMLPurifier($html_config);

        return $purifier->purify($html);
    }

    /*
     * 区配手机号
     * @param int $mobile_num 手机号码
     * @return bool
     * 因目前我国的手机号中
     * 2G的手机号差不多都是13开头
     * 3G的号以150、151、158、159开头，还有就是186, 189开头，所以代码如下。
     */
    public static function checkMobile($mobile_num)
    {
        $reg = "/^(1[35][0-9]{9})$|(18[69][0-9]{8})$/";
        return !!preg_match($reg, $mobile_num);
    }

    /**
     * 将多行的字符串转换为数组（暂未用到，可以用于自定义属性默认值的处理）
     * @param string $line
     * @author gaojj@alltosun.com
     */
    public static function lineToArray($line)
    {
        return explode("\n", str_replace(array("\r\n", "\r"), "\n", $line));
    }

    /**
     * 多字节的字符串反序列化（比如原先是gbk的数据序列化后，在utf-8的环境下反序列化）
     * @param $serialStr
     *
     */
    public static function mbUnserialize($serialStr)
    {
        $out = preg_replace('!s:(\d+):"(.*?)";!se', "'s:'.strlen('$2').':\"$2\";'", $serialStr);
        return unserialize($out);
    }

    /**
     * 截取开始与结束标记字串之间的内容
     * 如果 $cutEnd 为空，返回 $cutStart 字串后的至结尾的内容
     * 如果 $cutStart 或 $cutEnd 不存在于内容中，返回为空字串
     * @param string $info     要截取的字符串
     * @param string $cutStart 起始字符串
     * @param string $cutEnd   结束字符串
     * @param intval $offset   偏移，$cutStart 偏移的位置
     * @example AnString::cut('start abcdkdkekd end', 'start', 'end');
     * @example $f = file_get_contents('http://tech.sina.com.cn/');
     *          echo AnString::cut($f, '<div class="blk01">', "\n            </div>");
     */
    public static function cut($info, $cutStart, $cutEnd = '', $offset = 0)
    {
        $start = strpos($info, $cutStart, $offset) + strlen($cutStart);
        if (false === $start) return '';

        if (!$cutEnd) return substr($info, $start);

        $end = strpos($info, $cutEnd, $start + 1);
        if (false === $end) return '';
        else return substr($info, $start, $end - $start);
    }

    /**
     * 依次截取全部开始与结束标记字串之间的内容
     * @param string $info     要截取的字符串
     * @param string $cutStart 起始字符串
     * @param string $cutEnd   结束字符串
     * @param intval $offset   偏移，$cutStart 偏移的位置
     * @return array()
     */
    public static function cutAll($info, $cutStart, $cutEnd = '', $offset = 0)
    {
        static $t_array = array();
        $tem = self::cut($info, $cutStart, $cutEnd, $offset);
        if ($tem) {
            $t_array[] = $tem;
            $offset += strlen($cutStart) + strlen($cutEnd) + strlen($tem);
            self::cutAll($info, $cutStart, $cutEnd, $offset);
        }
        return $t_array;
    }
}
?>