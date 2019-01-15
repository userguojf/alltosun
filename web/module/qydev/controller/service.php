<?php
/**
  * alltosun.com 企业客服模块 service.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2017年4月18日 下午12:09:20 $
  * $Id$
  */

include_once ROOT_PATH."/helper/qy_weixin/WXBizMsgCrypt.php";
class Action
{
    //企业号应用和客服回调共用的token 和 encodinigAesKey
    public static $token          = "t8ErQdXv4Xe3z";
    public static $encodingAesKey = "eFooY8qoRGXGIWsUBCVdUW1iNlQc8Uhi5qU7pUyYlFY";
    private $sVerifyMsgSig;
    private $sVerifyTimeStamp;
    private $sVerifyNonce;
    private $fromUsername;
    private $toUsername;
    private $msgType;
    private $createTime;
    private $content;
    private $packageId;
    private $agent_id;
    private $pic_url;
    private $media_id;
    private $event;
    private $event_key;
    private $conversation_info;
    public static $wxcpt;
    private $user_key;


    public function __construct()
    {
        //Request::getParam();
        $this->sVerifyMsgSig    = Request::get('msg_signature', '');
        $this->sVerifyTimeStamp = Request::get('timestamp', '');
        $this->sVerifyNonce     = Request::get('nonce', '');
        self::$wxcpt            = new WXBizMsgCryptClass(self::$token, self::$encodingAesKey, qydev_config::$corp_id);
    }

    /**
     * 企业号应用回调
     */
    public function index()
    {
        //首次加载验证
        if (isset($_GET['echostr'])) {
            $this->valid();
            exit;
        }

        $this->parse_request();
    }

    /**
     * 验证Url
     */
    private  function valid()
    {
        $sEchoStr = '';

        $sVerifyEchoStr = Request::Get("echostr");
        $errCode        = self::$wxcpt->VerifyURL($this -> sVerifyMsgSig, $this -> sVerifyTimeStamp, $this -> sVerifyNonce, $sVerifyEchoStr, $sEchoStr);

        if ($errCode == 0) {
            echo $sEchoStr;
        } else {
            // 验证URL失败，
            print("ERR: " . $errCode . "\n\n");
        }
    }

    /**
     * 分析请求
     */
    private function parse_request()
    {

        //获取请求数据
        $request_data = file_get_contents('php://input');
        if (empty($request_data)) {
            return 'xml请求数据$request_data为空。';
        }

        //验证 Url
        $sMsg = '';
        $errCode        = self::$wxcpt->DecryptMsg($this -> sVerifyMsgSig, $this -> sVerifyTimeStamp, $this -> sVerifyNonce, $request_data, $sMsg);
        if ($errCode != 0) {
            // 验证URL失败
            print("ERR: " . $errCode . "\n\n");return ;
        }

        //解析XML
        $xml = new DOMDocument();
        $xml->loadXML($sMsg);
        $this->toUsername       = $xml->getElementsByTagName('ToUserName')->item(0)->nodeValue;
        $this->fromUsername     = $xml->getElementsByTagName('FromUserName')->item(0)->nodeValue;
        $this->createTime       = $xml->getElementsByTagName('CreateTime')->item(0)->nodeValue;
        $this->msgType          = $xml->getElementsByTagName('MsgType')->item(0)->nodeValue;
        $this->agent_id         = $xml->getElementsByTagName('AgentID')->item(0)->nodeValue;
        if ($this->agent_id != 11) {
            return false;
        }

        //获取正在会话的信息
        $this->conversation_info = _widget('qydev.conversation')->get_in_conversation($this->fromUsername);

        //文本消息
        if ($this->msgType == 'text') {
            $this->content          = $xml->getElementsByTagName('Content')->item(0)->nodeValue;
            $this->parse_text();
        //图片消息
        } else if ($this->msgType == 'image') {
            $this->pic_url          = $xml->getElementsByTagName('PicUrl')->item(0)->nodeValue;
            $this->media_id         = $xml->getElementsByTagName('MediaId')->item(0)->nodeValue;
            $this->parse_image();
        //语音消息
        } else if ($this->msgType == 'voice') {
            $this->media_id         = $xml->getElementsByTagName('MediaId')->item(0)->nodeValue;
            $this->format           = $xml->getElementsByTagName('Format')->item(0)->nodeValue;
        //事件消息
        } else if ($this->msgType == 'event') {
            //获取事件类型
            $this->event = $xml->getElementsByTagName('Event')->item(0)->nodeValue;
            //菜单key
            if ($this->event  == 'click'){
                $this->event_key = $xml->getElementsByTagName('EventKey')->item(0)->nodeValue;
                $this->parse_event();
            }

        }

    }

    /**
     * 文本消息
     */
    public function parse_text()
    {
        //global $mc_wr;
        //是否在会话中
        if (!$this->conversation_info) {
            $this->send_error_msg("请点击下方的客服按钮来呼叫客服吧");
        }

        //自动回复
        //$user_key = md5('qy_service'.$this->fromUsername);
        //if ($mc_wr->get($user_key) == 102) {
            if ($this->content == 1) {
                $reply_content = "派单反馈类问题，请点击查看\n http://wifi.pzclub.cn/share/dm/diff_type/?diff_question=1";
            } else if ($this->content == 2) {
                $reply_content = "登录名称及密码，请点击查看\n http://wifi.pzclub.cn/share/dm/diff_type/?diff_question=2";
            } else if ($this->content == 3) {
                $reply_content = "上传地图，请点击查看\n http://wifi.pzclub.cn/share/dm/diff_type/?diff_question=3";
            } else {
                $reply_content = "回复1：派单反馈\n回复2：登录名称及密码\n回复3：上传地图";
            }
            echo $this->generate_xml_text($reply_content);
        //}

        //消息
        $msg_info  = array(
                'msg_type' => $this->msgType,
                'msg_info' => $this->content
        );

        //发送客服消息
        $result = _widget('qydev.service')->send_service_msg($this->conversation_info, $msg_info);
        if ($result[0] != 'ok') {
            $this->send_error_msg($result[1]);
        }

    }

    /**
     * 图片消息
     */
    public function parse_image()
    {

        //是否在会话中
        if (!$this->conversation_info) {
            $this->send_error_msg("请点击下方的客服按钮来呼叫客服吧");
        }

        //消息
        $msg_info  = array(
                'msg_type' => $this->msgType,
                'msg_info' => array(
                        'pic_url' => $this->pic_url,
                        'media_id' => $this->media_id
                )
        );
        //发送客服消息
        $result = _widget('qydev.service')->send_service_msg($this->conversation_info, $msg_info);
        if ($result[0] != 'ok') {
            $this->send_error_msg($result[1]);
        }

    }

    /**
     * 事件
     */
    public function parse_event()
    {
        //排队，ＷＩＦＩ　　　数字地图　，摇得

        //Awifi
        if ($this->event_key == 101) {
            $content = "欢迎咨询Awifi！！请在下方输入您想咨询的问题！";
        //数字地图
        } else if ($this->event_key == 102) {
            $content = "亲，您好，非常高兴为您服务，由于现在咨询人数较多，请先回复下面数字进行自助查询，如问题依然没有解决，我稍后回复您，带来不便请您谅解。\n回复1：派单反馈\n回复2：登录名称及密码\n回复3：上传地图";
        //排队
        } else if ($this->event_key == 103){
            $content = "欢迎咨询排队！！请在下方输入您想咨询的问题！";
        //ibeacon
        } else if ($this->event_key == 104) {
            $content = "欢迎咨询ibeacon！！请在下方输入您想咨询的问题！";
        //摇的
        } else if ($this->event_key == 104) {
            $content = "欢迎咨询摇的！！请在下方输入您想咨询的问题！";
        }

        //查询未结束的会话
        $this->conversation_info = _widget('qydev.conversation')->get_not_end_conversation($this->fromUsername, $this->event_key);

        //创建会话
        if (!$this->conversation_info) {

            //创建会话， 并分配客服
            $conversation_id = _widget('qydev.conversation')->create_conversation($this->fromUsername, $this->event_key, $this->agent_id);

            if (!$conversation_id){
                $this->send_error_msg('会话创建失败');
            }

        } else {
            $conversation_id = $this->conversation_info['id'];
        }

        //切换会话，设置用户的当前会话
        $result = qy_conversation_helper::set_user_curr_conversation($this->event_key, $this->fromUsername, $conversation_id);
        if (isset($result['msg'])) {
            $this->send_error_msg($result['msg']);
        }

        echo $this->generate_xml_text($content);

    }

    /**
     * 发送错误信息
     */
    public function send_error_msg($content)
    {
         echo $this->generate_xml_text($content);
         exit();

    }

    /**
     * 生成xml文本消息
     * @param unknown $content
     * @return boolean|string
     */
    public function generate_xml_text($content)
    {
        $create_time = time();
        $xml_tpl = "<xml>
                       <ToUserName><![CDATA[".$this->fromUsername."]]></ToUserName>
                       <FromUserName><![CDATA[".$this->toUsername."]]></FromUserName>
                       <CreateTime>".$create_time."</CreateTime>
                       <MsgType><![CDATA[text]]></MsgType>
                       <Content><![CDATA[".$content."]]></Content>
                    </xml>";
        $sMsg = '';
        $errCode = self::$wxcpt->EncryptMsg($xml_tpl, $create_time, $this -> sVerifyNonce, $sMsg);
        if ($errCode != 0) {
            $data = array(
                    'agent_id' => $this->agent_id,
                    'result'   => json_encode($errCode),
            );
            _model('qy_error_logs')->create($data);
            return false;
        }
        return  $sMsg;
    }


}