<?php
/**
  * alltosun.com 客服widget service.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2017年4月18日 下午5:25:54 $
  * $Id$
  */
class service_widget
{
    /**
     * 企业号 Url Api
     */
    public static $qy_api_url = array(

             //发送客服消息
            'send_service'                  => "https://qyapi.weixin.qq.com/cgi-bin/kf/send?access_token={ACCESS_TOKEN}",

            //客服回复消息
            'service_reply'                 => "https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token={ACCESS_TOKEN}",

            //获取access
            'get_access_token'              => "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid={CORPID}&corpsecret={CORPSECRET}",
    );

    /**
     * 客服所使用的Secret
     * @var unknown
     */
    public static $secrets     = array(

            //发送客服消息
            'admin_group'    => 'FPcvdPv9cztiBQ9aNK4MvsjuWLOFdtqrM-cOVfrO1QPINNlM6JUwmCHBXXhvalAs',

            //客服回复消息
            'service'  => 'eh0-dz73FN00G3a79Pb8H4W0nzQXobENEdNQvE7adxxd4IvaNFVx7uAfCIkyyzUy'
    );


    /**
     * 发送客服消息
     * @param unknown $conversation_info
     * @param unknown $content
     */
    public function send_service_msg($conversation_info, $msg_info) {

        if (!$conversation_info) {
            return array(1100, '不存在的会话信息');
        }

        if ($msg_info['msg_type'] == 'text') {
            $send_data = $this->get_send_service_text($conversation_info['qy_user_id'], $conversation_info['qy_service_id'], $msg_info);
        } else if ($msg_info['msg_type'] == 'image'){
            $send_data = $this->get_send_service_image($conversation_info['qy_user_id'], $conversation_info['qy_service_id'], $msg_info);
        }



        //获取access_token 2-发送客服消息
        $access_token = $this->get_access_token(self::$secrets['service']);

        //发送客服消息地址
        $url = str_replace("{ACCESS_TOKEN}", $access_token, self::$qy_api_url['send_service']);

        //发送客服消息
        $result = json_decode(curl_post($url,$send_data), true);

        //记录错误日志
        if ($result['errcode'] != 0) {
            $data = array(
                    'agent_id' => $conversation_info['qy_agent_id'],
                    'result'   => json_encode($result),
            );
            _model('qy_error_logs')->create($data);
            return  array(1100, '客服消息发送失败');
        }
        return $this->save_send_msg($conversation_info, $msg_info);

    }

    /**
     * 客服消息回复
     * @param unknown $conversation_info
     * @param unknown $content
     */
    public function service_reply_msg($conversation_info, $content)
    {
        //获取回复的数据
        $reply_data = $this->get_service_reply_msg($conversation_info['qy_user_id'], $conversation_info['qy_agent_id'], $content);

        //发送消息
        $access_token = $this->get_access_token(self::$secrets['admin_group']);

        $url = str_replace("{ACCESS_TOKEN}", $access_token, self::$qy_api_url['service_reply']);
        $result = json_decode(curl_post($url,$reply_data), true);

        //记录错误日志
        if ($result['errcode'] != 0) {
            $data = array(
                    'agent_id' => $conversation_info['qy_agent_id'],
                    'result'   => json_encode($result),
            );
            _model('qy_error_logs')->create($data);
            return array(1100, $result);
        }

        return $this->save_reply_msg($conversation_info, $content);
    }

    /**
     * 保存已向客服发送的消息
     * @param unknown $conversation
     * @param unknown $msg_info
     * @return unknown[]
     */
    public function save_send_msg($conversation_info, $msg_info)
    {
        $filter = array(
                'conversation_id' => $conversation_info['id'],
                'qy_user_id'      => $conversation_info['qy_user_id'],
                'qy_service_id'   => $conversation_info['qy_service_id'],
                'msg_type'        => $msg_info['msg_type']
        );
        //根据消息类型保存
        if ($msg_info['msg_type'] == 'text') {
            //存入会话消息
            $filter['content'] = $msg_info['msg_info'];
        } else if ($msg_info['msg_type'] == 'image') {
            $filter['pic_url']  = $msg_info['msg_info']['pic_url'];
            $filter['media_id'] = $msg_info['msg_info']['media_id'];
        }

        _model('qy_message')->create($filter);
        return array('ok', $filter);
    }

    /**
     * 保存已向客服发送的消息
     * @param unknown $conversation
     * @param unknown $msg_info
     * @return unknown[]
     */
    public function save_reply_msg($conversation_info, $content)
    {
        //存入会话消息
        $filter = array(
                'conversation_id' => $conversation_info['id'],
                'qy_user_id'      => $conversation_info['qy_user_id'],
                'qy_service_id'   => $conversation_info['qy_service_id'],
                'content'         => htmlspecialchars($content),
                'is_reply'        => 1
        );

        _model('qy_message')->create($filter);
        return array('ok', $filter);
    }

    /**
     * 获取要发送的客服消息数据
     * @param string $user_id
     * @param string $kf_id
     * @param unknown $content
     */
    public function get_send_service_text($user_id, $kf_id, $msg_info)
    {
        //拼接成员发送的数据
        $send_data = array(
                'sender' => array(
                        'type' => 'userid',
                        'id'     => $user_id
                ),
                'receiver' => array(
                        'type' => 'kf',
                        'id'   => $kf_id
                ),
                'msgtype' => 'text',
                'text'    => array(
                        'content' => $msg_info['msg_info']
                )
        );

        //返回json
        return json_encode($send_data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 获取要发送的客服消息数据
     * @param string $user_id
     * @param string $kf_id
     * @param unknown $content
     */
    public function get_send_service_image($user_id, $kf_id, $msg_info)
    {
        //拼接成员发送的数据
        $send_data = array(
                'sender' => array(
                        'type' => 'userid',
                        'id'     => $user_id
                ),
                'receiver' => array(
                        'type' => 'kf',
                        'id'   => $kf_id
                ),
                'msgtype' => 'image',
                'image'    => array(
                        'media_id' => $msg_info['msg_info']['media_id']
                )
        );

        //返回json
        return json_encode($send_data, JSON_UNESCAPED_UNICODE);

    }

    /**
     * 获取客服要回复的消息数据
     * @param string $user_id
     * @param int $kf_id
     * @param unknown $content
     */
    public function get_service_reply_msg($user_id, $agent_id, $content)
    {
        //拼接成员发送的数据
        $reply_data = array(
                'touser' => $user_id,
                'msgtype' => 'text',
                'agentid' => $agent_id,
                'text'    => array(
                        'content' => $content
                )

        );

        //返回json
        return json_encode($reply_data, JSON_UNESCAPED_UNICODE);

    }


    /**
     * 根据secret获取access_token
     * @param unknown $corpSecret
     */
    public function get_access_token($secret)
    {

        //取数据库
        $filter = array(
                'secret' => $secret,
        );
        $token_info = _uri('qy_token', $filter);
        if ($token_info && $token_info['expire_time'] > date('Y-m-d H:i:s')) {
            return $token_info['access_token'];
        }

        //操作类型
        $action = 'update';
        if (!$token_info) {
            $action = 'create';
        }

        //更新token
        $result = $this->update_access_token($secret, $action);
        if (isset($result['errcode'])) {
            //记录错误日志
            $result = json_encode($result);
            _model('qy_error_logs')->create(array('result' => $result));
            return '';
        }

        return $result;

    }

    /**
     * 更新token
     * @param unknown_type $action create | update
     * @return mixed|boolean
     */
    public function update_access_token($secret, $action)
    {
        //因使用到多种access_token  用户向客服发送消息、客服向用户发送消息
        $corpId          = qydev_config::$corp_id;

        //替换完整的url
        $url = str_replace('{CORPID}', $corpId, self::$qy_api_url['get_access_token']);
        $url = str_replace('{CORPSECRET}', $secret, $url);
        ;
        $res = json_decode(curl_get($url), true);

        if (!isset($res['errcode'])) {
            $data['access_token']       = $res['access_token'];
            $data['expire_time'] = date('Y-m-d H:i:s', time() + $res['expires_in']);

            if ($action == 'update') {
                $filter = array(
                        'secret'  => $secret
                );
                //更新token
                _model('qy_token')->update($filter, $data);
            } else {

                $data['secret']    = $secret;
                _model('qy_token')->create($data);
            }
            return $data['access_token'];
        } else {
            return $res;
        }

    }

}