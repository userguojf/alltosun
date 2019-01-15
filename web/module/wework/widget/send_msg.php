<?php
/**
 * alltosun.com  send_msg.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-4-17 下午4:19:38 $
 * $Id$
 */
class send_msg_widget
{
    /**
     * 发消息方法
     * 传参数 一定遵守企业号开发文档说明
     * @param  $params
     * @return boolean
     */
    public function send_message( $user_id, $params, $agent_id)
    {
        if ( !$user_id || !$params ) {
            return false;
        }

        $access_token = _widget('qydev.token')->get_access_token($agent_id);

//         if (!$access_token) {
//             return false;
//         }

        $url = qydev_config::$send_msg_url."access_token=".$access_token;
        $json_info = curl_post($url , $params);

        //记录日
        $log['user_id'] = $user_id;
        $log['content'] = $params;
        $log['result']  = $json_info;

        $info = json_decode($json_info,true);

        if ( isset($info['errcode']) ) {
            $log['err_code']  = $info['errcode'];
        }

        $message_id = _model('qydev_msg_log')->create($log);

        return $info;
    }
}