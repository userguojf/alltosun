<?php
/**
 * alltosun.com 帮助与反馈应用开发使用 help_feekback_callback.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-12-20 上午11:25:03 $
 * $Id$
 */
include MODULE_PATH.'/qydev/php/WXBizMsgCrypt.php';

class Action
{
    //开发机和正式机的地址
    private $url               = '';

    private $corpId           = "wx1a1fb37c4adad916";
    private $secret           = 'FPcvdPv9cztiBQ9aNK4MvsjuWLOFdtqrM-cOVfrO1QPINNlM6JUwmCHBXXhvalAs';

    // 应用ID
    private $agent_id         = 28;

    // 后台随机生成
    private $encodingAesKey = "kjoifiu27z6REh318FQwe15fqPxoQ6k3kFBgQ2OzjTf";
    private $token          = "alltosun";

    // 文档需要的参数
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
        //首次加载验证
        if ( tools_helper::get('echostr', '') ) {
            $this->check();
            exit;
        }

        global $mc_wr;
        $mc_wr->set('start','start',60);

        $this->parse_request();
        $sEchoStr = '';

    }

    /**
     * 解析应用的请求
     */
    public function parse_request()
    {

        // global $mc_wr;

        if (ONDEV) {
            $request_data = file_get_contents('php://input');
        } else {
            $request_data = (isset($GLOBALS["HTTP_RAW_POST_DATA"]) && !empty($GLOBALS["HTTP_RAW_POST_DATA"])) ? $GLOBALS["HTTP_RAW_POST_DATA"] : '' ;
        }

        if (empty($request_data)) {
            return 'xml请求数据$request_data为空。';
        }

        // $mc_wr->set('qydev1',$request_data,60);

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

            $AgentID      = AnFilter::filter_string(trim($request->AgentID));
            $Event        = AnFilter::filter_string(trim($request->Event));
            $EventKey     = AnFilter::filter_string(trim($request->EventKey));

            if ($MsgType == 'event' && $Event == 'click') {

                if ( $EventKey == 'novice_video' ) {

                    $sRespData = "<xml>
                                   <ToUserName><![CDATA[".$FromUserName."]]></ToUserName>
                                   <FromUserName><![CDATA[".$AgentID."]]></FromUserName>
                                   <CreateTime>".time()."</CreateTime>
                                   <MsgType><![CDATA[news]]></MsgType>
                                   <ArticleCount>1</ArticleCount>
                                   <Articles>
                                       <item>
                                           <Title><![CDATA[数字地图厅店侧操作指引(一)]]></Title>
                                           <Description><![CDATA[一看就会！！首次登陆数字地图的操作指引视频，供大家快速学习使用。]]></Description>
                                           <PicUrl><![CDATA[http://wifi.pzclub.cn/upload/2017/12/20/20171220152336000000_1_15196_38.jpg]]></PicUrl>
                                           <Url><![CDATA[https://qy.weixin.qq.com/cgi-bin/show?uin=NTA2MDEzNzUz&videoid=1014_12e46e2d674b49f1a0f1470d8fb31d54]]></Url>
                                       </item>
                                   </Articles>
                                </xml>";

                    $sEncryptMsg = ""; //xml格式的密文$sReqTimeStamp, $sReqNonce, $sEncryptMsg
                    $errCode = $wxcpt->EncryptMsg($sRespData,  $this -> sVerifyTimeStamp, $this -> sVerifyNonce ,$sEncryptMsg);

                    if ($errCode == 0) {
                        print($sEncryptMsg);
                        // TODO:
                        // 加密成功，企业需要将加密之后的sEncryptMsg返回
                        // HttpUtils.SetResponce($sEncryptMsg);  //回复加密之后的密文
                    } else {
                        print("ERR: " . $errCode . "\n\n");
                        // exit(-1);
                    }

                } 
            }
//             echo $msg;

        } else {
            print("ERR: " . $errCode . "\n\n");
        }
    }
    /*
     * 验证回调
     */
    public function check()
    {
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