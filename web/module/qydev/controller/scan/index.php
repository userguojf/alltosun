<?php

/**
 * 扫一扫
 *
 * @author  wangl
 */

class Action
{
    public function index()
    {
        Response::display('scan/index.html');
    }

    /**
     * 获取js-config的参数
     *
     * @author  wangl
     */
    public function get_param()
    {
        if ( empty($_SERVER['HTTP_REFERER']) ) {
            exit(-1);
        }

        $refere = $_SERVER['HTTP_REFERER'];

        if ( strpos($refere, SITE_URL) === false ) {
            exit(-1);
        }

        if ( !function_exists('wx') ) {
            require __DIR__.'/wx.php';
        }

        $token = _widget('qydev.token')->get_access_token();

        if ( !$token ) {
            return '获取token失败';
        }

        $ticket = wx('ticket')->get($token);

        if ( !$ticket ) {
            return '获取ticket失败';
        }

        $noncestr = '';

        for ($i = 0; $i < 16; $i ++) {
            $noncestr .= chr(rand(65, 90));
        }

        $time = time();

        $ary  = array(
            'noncestr'      =>  $noncestr,
            'jsapi_ticket'  =>  $ticket,
            'timestamp'     =>  $time,
            'url'           =>  $refere
        );
        ksort($ary);

        // 注：js验证时需要当前页面地址，但是http_build_query会把url编码，但是微信那边没有编码造成不一致的情况
        $string     = urldecode(http_build_query($ary));

        $signature  = sha1($string);

        $res = array(
            'appId'     =>  qydev_config::$corp_id,
            'timestamp' =>  $time,
            'nonceStr'  =>  $noncestr,
            'signature' =>  $signature
        );

        // an_dump($ticket, $noncestr, $time, $string, $signature, $ary['url']);
        return array('info' => 'ok', 'param' => $res);
    }
}
