<?php
/**
 * alltosun.com 调用微信JS-SDK，返回固定参数   get_param.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-4-19 下午2:45:23 $
 * $Id$
 */

class Action
{
    
    private $jsapi_ticket = '';

    public function __construct()
    {
        $this->jsapi_ticket = _widget('wework.ticket')->get_jsapi_ticket('work');
        if ( !$this->jsapi_ticket ) exit();
    }

    public function __call($action = '', $param = array())
    {
        $url = tools_helper::post('url', '');

        if ( !$url ) return false;


        $noncestr = '';

        for ($i = 0; $i < 16; $i ++) {
            $noncestr .= chr(rand(65, 90));
        }

        $time = time();

        $arr  = array(
                'noncestr'      =>  $noncestr,
                'jsapi_ticket'  =>  $this->jsapi_ticket,
                'timestamp'     =>  $time,
                'url'           =>  $url
        );
// p($arr);exit();
        ksort($arr);

        // 注：js验证时需要当前页面地址，但是http_build_query会把url编码，但是微信那边没有编码造成不一致的情况
        $string     = urldecode(http_build_query($arr));

        $signature  = sha1($string);

        $res = array(
                'appId'     =>  qydev_config::$corp_id,
                'timestamp' =>  $time,
                'nonceStr'  =>  $noncestr,
                'signature' =>  $signature
        );

        return $res;
    }
}