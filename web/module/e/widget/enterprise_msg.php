<?php
/**
 * alltosun.com  enterprise_msg.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-3-24 下午12:52:16 $
 * $Id$
 */
class enterprise_msg_widget
{
    //企业号corpid
    private $corpid = 'wx1a1fb37c4adad916';
    //企业号secret
    private $secret = 'FPcvdPv9cztiBQ9aNK4MvsjuWLOFdtqrM-cOVfrO1QPINNlM6JUwmCHBXXhvalAs';
    //企业号应用ID
    private $agentid = 1;
    //企业号access_token
    private $access_token = NULL;
    
    //获取access_token的方法
    public function __construct()
    {
        $get_access_token   = curl_get('https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid='.$this->corpid.'&corpsecret='.$this->secret);
        
        $this->access_token = json_decode($get_access_token,true);
    }
    //入口
    public function enter($action = "", $param = array())
    {
        if ($this->check_signature() == 'ok') {
            return array('info' => 'ok');
        } else {
            return array('info' => 'no');
        }
    }
    
    private function send_text() 
    {
        //推送的文本消息
        $msg     = 'guojf测试';
        
        //推送的成员ID   最多支持1000个）。特殊情况：指定为@all 必须  否
        $touser  = explode('|' , e_config::$touser);
        
        //推送的部门的ID 最多支持100个。当touser为@all      必须 否
        $toparty = explode('|' , e_config::$toparty);
        
        $data="{
                    \"touser\"  : \"$touser\",
                    \"msgtype\" : \"text\",
                    \"agentid\" : $this->agentid,
                    \"text\"    : {\"content\":\"$msg\"},
                    \"safe\"    : 0
                }";
        //发送文本消息
        $json_msg = curl_post('https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token='.$this->access_token['access_token'] , $data);
        
        return json_decode($json_msg); //{'errcode' : 0 , 'errmsg' : 'ok' }
    }
}