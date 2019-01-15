<?php
/**
 * alltosun.com  screen_msg_helper.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-1-8 上午11:14:32 $
 * $Id$
 */

class screen_msg_helper
{
    // 后缀生成字符串
    public static $charset = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";

    /**
     * 
     * @param unknown $phone   手机号
     * @param unknown $param1  离线设备数量
     * @return multitype:number string
     */
    public static function send_check_msg($tophone, $param1, $user_number)
    {
        if ( !$tophone || !$param1 || !$user_number ) return false;

        // 离线设备原因
        $param2 = '将无法展示您要求的内容。我们已帮您统计到终端型号，';

        // 是否存在缓存后缀
        $cache_info = _model('screen_redirect_url_cache')->read(
                    array(
                        'user_number' => $user_number,
                        'type'        => 2 // 2是检验
                    )
        );

        if ( $cache_info && $cache_info['url'] && $cache_info['cache'] ) {
            $short_suffix = $cache_info['cache'];

        } else {
            // 单点登录地址
            $single_url = self::get_single_url($user_number, 'screen_dm/device?state=check');
            // 短连接后缀
            $short_suffix = self::short_url_cache($single_url, $user_number, 2);
        }

        if ( !$short_suffix ) return '';

        // 跳转地址
        $url = 'api/redirect/'.$short_suffix;
        // 线上地址
        $param3 = AnUrl($url);

       // 单发和群发  目前都是群发   每次都有我的手机号
        if ( strpos($tophone, '|') ) {
            $tophone = explode("|", $tophone);
            self::foreach_send_msg($tophone, $param1, $param2, $param3);

        } else {
            self::alone_send_msg($tophone, $param1, $param2, $param3);

        }
    }

    /**
     * 单独发短信
     * @param unknown $phone
     * @param unknown $param1
     * @param unknown $param2
     * @param unknown $param3
     * @return boolean|multitype:number string
     */
    public static function alone_send_msg($phone, $param1, $param2, $param3)
    {
        if ( !$phone || !$param1 || !$param2 || !$param3 ) return false;

        $template_id = 91553794;

        //发短信验证码
        $content =  array(
                'param1' => $param1,
                'param2' => $param2,
                'param3' => $param3,
        );

        $params['tel']         = $phone;
        $params['content']     = json_encode($content);
        $params['template_id'] = $template_id;

        $msg_res = _widget('message')->send_message($params, 2);

//         if ($msg_res['info'] == 'error' ) {
            
//         }

        return true;
    }

    /**
     * 群发短信
     * @param unknown $tophone
     * @param unknown $param1
     * @param unknown $param2
     * @param unknown $param3
     * @return boolean
     */
    public static function foreach_send_msg($tophone, $param1, $param2, $param3)
    {
        if ( !$tophone || !$param1 || !$param2 || !$param3 ) return false; 

        foreach ($tophone as $k => $v) {
            self::alone_send_msg($v, $param1, $param2, $param3);
        }

        return true;
    }

    /**
     * 获取单点登录地址
     * @param unknown $account
     * @return string
     */
    public static function get_single_url($account, $module)
    {

        if ( !$account || !$module ) return false;

        $app_id       = 'wifi_dxawifi_j29sod9dawfe29d2';
        $app_key      = '83136817debff9b6ab2e5b0269695137';
        $action       = 'redirect_uri';
        $timestamp    = time();
        $redirect_uri = AnUrl($module);

        $url  = AnUrl('api/member/login');
        $url .= '?timestamp='.$timestamp;
        $url .= '&token='.md5($app_id.'_'.$app_key.'_'.$timestamp);
        $url .= '&account='.$account;
        $url .= '&appid='.$app_id;
        $url .= '&action='.$action;
        $url .= '&redirect_uri='.urlencode($redirect_uri);

        return $url;
    }

    /**
     * 获取后缀
     * @param unknown $url
     * @param unknown $user_number
     * @param unknown $type
     * @return boolean|string|multitype:
     */
    public static function short_url_cache($url, $user_number, $type, $target = 0)
    {
        if ( !$url || !$user_number || !$type ) return false;

        if ( $target ) {
            $merge_url = $url . '&user_number=' . $user_number;
        } else {
            $merge_url = $url;
        }

        $url_record = _model('screen_redirect_url_cache')->read(
                    array(
                        'url'         => $url,
                        'user_number' => $user_number,
                        'type'        => $type
                           )
        );

        if ( $url_record ) {
            return $url_record['cache'];
        }

        // 字符表
        $key      = "alexis";
        $url_hash = md5($key . $merge_url);
        $len      = strlen($url_hash);

        $url_hash_piece = substr($url_hash, 0, $len / 4);
        // 将分段的位与0x3fffffff做位与，0x3fffffff表示二进制数的30个1，即30位以后的加密串都归零
        // 此处需要用到hexdec()将16进制字符串转为10进制数值型，否则运算会不正常
        $hex = hexdec($url_hash_piece) & 0x3fffffff; 

        $short_url_cache = '';
         // 生成6位短连接
        for ( $j = 0; $j < 6; $j ++ ) {
            // 将得到的值与0x0000003d,3d为61，即charset的坐标最大值
            $short_url_cache .= self::$charset[$hex & 0x0000003d];
            // 循环完以后将hex右移5位
            $hex = $hex >> 5;
        }

        $id = _model('screen_redirect_url_cache')->create(
                    array(
                        'cache'       => $short_url_cache,
                        'url'         => $url,
                        'user_number' => $user_number,
                        'type'        => $type
                    )
                );

        if ( $id ) return $short_url_cache;

        return array();
    }


    /**
     * 例子
     * @return string
     */
    public static function short_url_test()
    {
        // 字符表
        $key = "alexis";
        $urlhash = md5($key . $url);
        $len = strlen($urlhash);

        #将加密后的串分成4段，每段4字节，对每段进行计算，一共可以生成四组短连接
        for ($i = 0; $i < 4; $i++) {
            $urlhash_piece = substr($urlhash, $i * $len / 4, $len / 4);
            // 将分段的位与0x3fffffff做位与，0x3fffffff表示二进制数的30个1，即30位以后的加密串都归零
            $hex = hexdec($urlhash_piece) & 0x3fffffff; #此处需要用到hexdec()将16进制字符串转为10进制数值型，否则运算会不正常

            $short_url = AnUrl().'/';
            // 生成6位短连接
            for ($j = 0; $j < 6; $j++) {
                // 将得到的值与0x0000003d,3d为61，即charset的坐标最大值
                $short_url .= self::$charset[$hex & 0x0000003d];
                // 循环完以后将hex右移5位
                $hex = $hex >> 5;
            }

            $short_url_list[] = $short_url;
        }

        return $short_url_list;
    }
}