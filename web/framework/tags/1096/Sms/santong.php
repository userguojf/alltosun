<?php

/**
 * alltosun.com 大汉三通 santong.php
 * ============================================================================
 * 版权所有 (C) 2009-2010 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 宁海新 (ninghx@alltosun.com) $
 * $Date: 2010-7-3 上午10:24:47 $
 * $Id: santong.php 228 2012-04-12 11:28:03Z ninghx $
*/

/**
 * 大汉三通短信平台操作的实现类
 * @author ninghx@alltosun.com
 * @package AnSms
 */
class santongWrapper extends SmsAbstract implements SmsWrapper
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

        $this->apiUrl = $base.$action.'?Account='.$account.'&Password='.$password;
        return $this->apiUrl;
    }

    /**
     * 获取短消息发送量
     */
    public function getSendCount()
    {
        // 指定action
        $action = 'GetBalance';
        // 构造url
        $this->apiUrlConstruction($action);

        $tmp_result = file_get_contents($this->apiUrl);
        // 解析xml
        $xml = simplexml_load_string($tmp_result);
        foreach ($xml as $k=>$v) {
            $name = $v->getName();
            $result[$name] = (string)$v;
        }

        return $result;
    }

    /**
     * 发送短消息
     * @param string $phone 手机号码
     * @param string $content 短信内容
     * @param string $send_time 发送日期
     * @return array 包含发送状态、手机号码、消息ID（注：返回状态说明-1：帐号登陆失败、-3：此帐号被禁用、-8：缺少请求参数、大于0：提交成功的短信条数）
     */
    public function sendSms($phone, $content, $send_time = null)
    {
        // 指定action
        $action = 'SendSms';
        // 构造url
        $this->apiUrlConstruction($action);
        // 完善url
        $this->apiUrl .= '&Phone='.$phone.'&Content='.urlencode($content);
        if ($send_time) {
            $this->apiUrl .= '&SendTime='.$send_time;
        }
        $tmp_result = file_get_contents($this->apiUrl);
        // 解析xml
        $xml = simplexml_load_string($tmp_result);
        foreach ($xml as $k=>$v) {
            if(!$v->children()) {
                $name = $v->getName();
                $result[$name] = (string)$v;
            } else {
                foreach ($v as $k1=>$v1) {
                    $name = $v1->getName();
                    $result[$name] = (string)$v1;
                }
            }
        }

        return $result;
    }

    /**
     * 接受短信息
     * @return array
     */
    public function getSms()
    {
        // 指定action
        $action = 'GetSms';
        // 构造url
        $this->apiUrlConstruction($action);
        $tmp_result = file_get_contents($this->apiUrl);
        // 解析xml
        $xml = simplexml_load_string($tmp_result);
        foreach ($xml as $k=>$v) {
            if(!$v->children()) {
                $name = $v->getName();
                $result[$name] = (string)$v;
            } else {
                foreach ($v as $k1=>$v1) {
                    $name = $v1->getName();
                    $sms[$name] = (string)$v1;
                    if ($k1 === 'sendTime') {
                        $result['sms'][] = $sms;
                    }
                }
            }
        }

        return $result;
    }
}
?>