<?php
/**
 * alltosun.com  weibo.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 王德康 (wangdk@alltosun.com) $
 * $Date: 2014-2-7 上午10:57:44 $
 * $Id$
 */

class weibo_widget
{
    /**
     * 发送微博
     * @param string $status 微博内容
     * @param string $image  图片路径
     * @return array;
     */
    public function send_weibo($status, $image = '')
    {
        if (ONDEV) return true;

        if($image) {
            $image = file_get_contents($image);
            $result = AnSinaWeibo::update(trim($status), $image);
        } else {
            $result = AnSinaWeibo::update(trim($status));
        }

        if (isset($result['error'])) {
            return  array('info'=>$result['error'], 'code'=>$result['error_code']);
        }

        return array('info'=>'ok', 'weibo_id'=>$result['id']);
    }

    /**
     * 转发微博
     * @param int $rt_id  转发的微博ID
     * @param string $status 转发微博带的文案
     * @param int $is_comment 是否发布到评论
     * @return boolean|Ambigous <multitype:, mixed>
     */
    public function rt_weibo($rt_id,  $status, $is_comment=0)
    {
        if (ONDEV) return true;

        $result = AnSinaWeibo::rt($rt_id, $status, $is_comment);

        if (isset($result['error'])) {
            return $result;
        }

        if (isset($result['error'])) {
            return  array('info'=>$result['error'], 'code'=>$result['error_code']);
        }

        return array('info'=>'ok', 'weibo_id'=>$result['id']);
    }

    /**
     * 微博ID转为微博地址字符串
     * @param int $mid 微博ID
     * @return string
     */
    public function midToStr($mid) {
        settype($mid, 'string');
        $mid_length = strlen($mid);
        $url = '';
        $str = strrev($mid);
        $str = str_split($str, 7);

        foreach ($str as $v) {
            $char = intTo62(strrev($v));
            $char = str_pad($char, 4, "0");
            $url .= $char;
        }

        $url_str = strrev($url);

        return ltrim($url_str, '0');
    }

    /**
     * 整形转62进制
     * @param string int
     * @return string
     */
    private function intTo62($int10) {
        $s62 = '';
        $r = 0;
        while ($int10 != 0) {
            $r = $int10 % 62;
            $s62 .= str62keys_int_62($r);
            $int10 = floor($int10 / 62);
        }
        return $s62;
    }

    /**
     * 62进制字典
     * @param string  $key
     * @return string
     */
    private function str62keys_int_62($key) //62进制字典
    {
        $str62keys = array (
                "0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q",
                "r","s","t","u","v","w","x","y","z","A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q",
                "R","S","T","U","V","W","X","Y","Z"
        );
        return $str62keys[$key];
    }
}
?>