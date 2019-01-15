<?php

/**
 * alltosun.com Sms类、SmsAbstract类、SmsWrapper类 Sms.php
 * ============================================================================
 * 版权所有 (C) 2007-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 宁海新 (ninghx@alltosun.com) $
 * $Date: 2010-7-3 上午10:15:57 $
 * $Id: Sms.php 228 2012-04-12 11:28:03Z ninghx $
*/

/**
 * 生成单例短信平台链接对象
 * 通过此类可以适配多种短信平台
 * @author ninghx@alltosun.com
 * @package AnSms
 */
class Sms
{
    /**
     * 短信平台连接的实例
     * @var array
     */
    private static $connections = array();

    /**
     * 连接短信平台
     * @example 连接短信平台格式：connect('vendor', 'api_uri', 'user', 'password', 'ext')
     * @example 可配合Config来连接短信平台，其中短信平台的配置通过Config::get('sms')获取
     * @throws Exception
     */
    public static function connect()
    {
        $params = func_get_args();
        if (count($params) == 1) {
            $params = $params[0];
        }

        $key = md5(serialize($params));

        if (!isset(self::$connections[$key])) {
            $driver = array_shift($params);
            require_once dirname(__FILE__).'/Sms/'.$driver.'.php';
            $class = $driver.'Wrapper';
            self::$connections[$key] = new $class($params);
        }
        return self::$connections[$key];
    }
}

/**
 * 短信平台操作的抽象层，封装短信平台的基本操作
 * @example $sms = DB::connect(array(), array());
 * @example $sms->send('mobile', 'content'); // 发送短信
 * @example $sms->receive(); // 接收短信
 * @author nignhx@alltosun.com
 * @package AnSms
 */
abstract class SmsAbstract
{
    /**
     * 短信平台配置
     * @var array
     */
    protected $config = array();
    
    /**
     * 短信平台uri
     * @var string
     */
    protected $apiUrl = null;

    /**
     * 初始化配置
     * @param array $config 短信平台配置
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * 获取总数
     */
    public function getSendTotal()
    {
        return $this->getSendCount();
    }

    /**
     * 发送短信
     * @param string $phone 手机号码
     * @param string $content 短信内容
     * @param string $send_time 发送时间
     */
    public function send($phone, $content, $send_time = null)
    {
        return $this->sendSms($phone, $content, $send_time = null);
    }

    /**
     * 接收上行短信
     */
    public function receive()
    {
        return $this->getSms();
    }
}

/**
 * 短信平台实现的接口类
 * @author nignhx@alltosun.com
 * @package AnSms
 */
interface SmsWrapper
{
    /**
     * 构造url链接
     */
    public function apiUrlConstruction($action);
    
    /**
     * 发送短信
     */
    public function sendSms($phone, $content, $send_time = null);
    
    /**
     * 接收短信
     */
    public function getSms();
    
    /**
     * 获取短信发送总数
     */
    public function getSendCount();
}
?>