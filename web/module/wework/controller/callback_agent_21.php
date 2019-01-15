<?php
/**
 * alltosun.com user_callback.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-5-8 上午11:14:09 $
 * $Id$
 */

include MODULE_PATH.'/wework/controller/weixin/WXBizMsgCrypt.php';

class Action
{

    //开发机和正式机的地址
    private $url              = '';

    private $corpId           = "wx1a1fb37c4adad916";

    private $encodingAesKey   = "vFO6rqXNS1eMZhjpWltw09v1fuAlOSjG5aYWI1mBXD2";

    private $agent_id          = 21;
    private $alltosun_agent_id = 27;

    private $token            = "alltosun";

    private $sVerifyMsgSig    = '';
    private $sVerifyTimeStamp = '';
    private $sVerifyNonce     = '';

    private $to_user_name     = '';
    private $msg_encrypt      = '';
    private $timestamp        = '';

    //access_token
    private $access_token     = '';


    //获取开启回调模式的参数
    public function __construct()
    {
        $this -> sVerifyMsgSig    = Request::get('msg_signature', '');
        $this -> sVerifyTimeStamp = Request::get('timestamp', '');
        $this -> sVerifyNonce     = Request::get('nonce', '');
    }


    public function index()
    {
        //首次加载验证
        if (Request::Get('echostr')) {
            $this->valid();
            exit;
        }

        //接口能力返回信息
        $this->parse_request();
    }

    /**
     * 获取参数和返回信息
     */
    public function parse_request()
    {
        if (ONDEV) {
            $request_data = file_get_contents('php://input');
        } else {
            $request_data = (isset($GLOBALS["HTTP_RAW_POST_DATA"]) && !empty($GLOBALS["HTTP_RAW_POST_DATA"])) ? $GLOBALS["HTTP_RAW_POST_DATA"] : '' ;
        }

        if (empty($request_data)) {
            return 'xml请求数据$request_data为空。';
        }

        $request = simplexml_load_string($request_data, 'SimpleXMLElement', LIBXML_NOCDATA);

        //解密
        $msg = '';
        $wxcpt   = new WXBizMsgCrypt($this -> token, $this -> encodingAesKey, $this -> corpId);

        $errCode = $wxcpt->DecryptMsg($this -> sVerifyMsgSig, $this -> sVerifyTimeStamp, $this -> sVerifyNonce, $request_data, $msg);

        if ($errCode == 0) {
            //操作返回的XML数据
            $request = simplexml_load_string($msg);

            //点击菜单拉取消息的事件推送
            $ToUserName   = AnFilter::filter_string(trim($request->ToUserName));
            $FromUserName = AnFilter::filter_string(trim($request->FromUserName));
            $MsgType      = AnFilter::filter_string(trim($request->MsgType));
            $Event        = AnFilter::filter_string(trim($request->Event));
            $AgentID      = AnFilter::filter_string(trim($request->AgentID));
//             $UserID       = AnFilter::filter_string(trim($request->UserID));
//             $EnglishName   = AnFilter::filter_string(trim($request->EnglishName));

            // 消息类型
            if ($MsgType == 'event') {
                // 事件类型，subscribe(订阅)、unsubscribe(取消订阅)
                // 目前关注就创建
                if ( 'subscribe' == $Event ) wework_user_helper::loacal_create('work', $FromUserName);
                if ( 'unsubscribe' == $Event ) wework_user_helper::unsubscribe($FromUserName);
            }

//             _model('wework_test_record')->create(array('content' => $Event));

        } else {
            _model('qydev_test_record')->create(array('content' => $errCode));

            print("ERR: " . $errCode . "\n\n");
        }
    }

    /**
     * 首次开启回调模式的验证
     */
    private  function valid()
    {
        $sEchoStr = '';

        $wxcpt          = new WXBizMsgCrypt($this -> token, $this -> encodingAesKey, $this -> corpId);
        $sVerifyEchoStr = Request::Get("echostr");
        $errCode        = $wxcpt->VerifyURL($this -> sVerifyMsgSig, $this -> sVerifyTimeStamp, $this -> sVerifyNonce, $sVerifyEchoStr, $sEchoStr);

        if ($errCode == 0) {
            // 验证URL成功，将sEchoStr返回
            echo $sEchoStr;
        } else {
            print("ERR: " . $errCode . "\n\n");
        }
    }

}