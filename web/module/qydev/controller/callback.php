<?php
/**
 * alltosun.com 靓亮应用开启回调模式 callback.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-10-17 下午12:17:44 $
 * $Id$
 */
include MODULE_PATH.'/qydev/php/WXBizMsgCrypt.php';

class Action
{
    //后台随机生成
    private $encodingAesKey = "dogKXxw8nQaoeK9QxKoaeEho65nJEPhDCU32LhvbOiP";
    private $token          = "alltosun";

    //文档需要的参数
    private $sVerifyMsgSig    = '';
    private $sVerifyTimeStamp = '';
    private $sVerifyNonce     = '';

    //请求是地址自带参数
    public function __construct()
    {
        $this -> sVerifyMsgSig    = Request::get('msg_signature', '');
        $this -> sVerifyTimeStamp = Request::get('timestamp', '');
        $this -> sVerifyNonce     = Request::get('nonce', '');
    }

    //验证回调模式
    public function index()
    {
        $sEchoStr = '';

        $wxcpt          = new WXBizMsgCrypt($this -> token, $this -> encodingAesKey, qydev_config::$corp_id);
        $sVerifyEchoStr = Request::Get("echostr");
        $errCode        = $wxcpt->VerifyURL($this -> sVerifyMsgSig, $this -> sVerifyTimeStamp, $this -> sVerifyNonce, $sVerifyEchoStr, $sEchoStr);

        if ($errCode == 0) {
            // 验证URL成功，将sEchoStr返回 即回调模式开启
            echo $sEchoStr;
        } else {
            print("ERR: " . $errCode . "\n\n");
        }
    }
}