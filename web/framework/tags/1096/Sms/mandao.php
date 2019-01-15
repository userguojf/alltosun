<?php

/**
 * alltosun.com 漫道科技短信平台 mandao.php
 * ============================================================================
 * 版权所有 (C) 2009-2010 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 宁海新 (ninghx@alltosun.com) $
 * $Date: 2010-11-22 下午12:34:57 $
 * $Id: mandao.php 228 2012-04-12 11:28:03Z ninghx $
*/

/**
 * 漫道科技短信平台操作的实现类
 * @author ninghx@alltosun.com
 * @package AnSms
 */
class mandaoWrapper extends SmsAbstract implements SmsWrapper
{
    /**
     * 构造api的url地址
     * @param string $action 执行的动作 发送短信或者接收短信
     */
    public function apiUrlConstruction($action)
    {
        // url
        $base = $this->config[0];
        // 配置中的账号
        $account = $this->config[1];
        // 配置中的密码
        $password = $this->config[2];
        // 分号
        $this->apiUrl = $base.$action.'.aspx?sn='.$account.'&pwd='.$password;
        if ($action=='z_receive' && isset($this->config[3])) {
            $ext = $this->config[3];
            $this->apiUrl .= '&ext='.$ext;
        }

        return $this->apiUrl;
    }

    /**
     * 获取短消息发送量
     * @return int 短信发送量
     */
    public function getSendCount()
    {

    }

    /**
     * 发送短消息
     * @param string $phone 手机号码
     * @param string $content 短信内容
     * @param string $send_time 发送日期
     * @return array 包含发送状态、手机号码、消息ID（注：返回状态说明:0 没有信息，-2 参数错误 ，1 发送成功，-3 序列号密码错误，-1 发送失败）
     */
    public function sendSms($phone, $content, $send_time = '')
    {
        // 指定action
        $action = 'z_send';
        // 构造url
        $this->apiUrlConstruction($action);
        // 完善url
        $this->apiUrl .= '&mobile='.$phone.'&content='.urlencode($content);
        $tmp_result = $this->curl_get_content($this->apiUrl);
        return $tmp_result;
    }

    /**
     * 获取短信
     * @return array
     */
    public function getSms()
    {
        // 指定action
        $action = 'z_receive';
        // 构造url
        $this->apiUrlConstruction($action);
        $tmp_result = $this->curl_get_content($this->apiUrl);
        return $tmp_result;
    }

    /**
     * 使用curl的方式获取短信接口返回值
     * @param string $apiUrl url
     * @param int $timeout 过期时间
     */
    private function curl_get_content($apiUrl, $timeout = 0)
    {
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL,$apiUrl);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt ($ch, CURLOPT_TIMEOUT, $timeout);
        $handles = curl_exec($ch);
        $error = curl_error($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($error) {
            throw new Exception($error);
        } elseif ($responseCode != 200) {
            throw new Exception("responseCode:".$responseCode);
        }

        return $handles;
    }
}
?>