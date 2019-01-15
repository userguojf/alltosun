<?php
/**
  * alltosun.com 极光推送类 JiGuangPush.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2018年3月5日 下午12:02:00 $
  * $Id$
  */
use JMessage\IM\Admin;

require MODULE_PATH.'/push/jpush/autoload.php';
use JPush\Client as JPush;

class JiGuangPush
{
    //极光推送类
    private static $push;
    private $key;
    private $secret;
    private $platfrom;
    public $client;

    /**
     * 初始化
     * @param unknown $key 极光推key
     * @param unknown $secret 极光推secret
     * @param string $platfrom 推送时设置的平台形式
     */
    function __construct($key, $secret, $platfrom='android')
    {

        $this->key          = $key;
        $this->secret       = $secret;
        $this->platfrom     = $platfrom;

        $this->client = new JPush($this->key, $this->secret);

        self::$push = $this->client->push();
        self::$push->setPlatform($platfrom);
    }

    /**
     * 初始化参数
     * @param unknown $param 参数
     */
    public function init_param($param)
    {
        //注：目前只有推送版本的时候，params为数组，其他则都为数字
        if (!is_array($param)) {
            $param =  array('title' => $param);
        }

        if (empty($param['title'])) {
            $param['title'] = '2';
        }

        $param['msg'] = (string)$param['title'];

        if (empty($param['extras'])) {
            $param['extras'] = array(
                    'time' => time()
            );
        }

        return $param;
    }

    /**
     * 获取key
     * @return unknown
     */
    public function get_key()
    {
        return $this->key;
    }

    /**
     * 获取secret
     * @return unknown
     */
    public function get_secret()
    {
        return $this->secret;
    }

    /**
     * 获取设置的平台
     * @return string
     */
    public function get_platfrom()
    {
        return $this->platfrom;
    }

    /**
     * 根据标签推送
     */
    public function push_tag($tags, $param)
    {
        //初始化参数
        $param = $this->init_param($param);

        self::$push->addTag($tags);

        return $this->send($param);
    }

    /**
     * 根据同时存在于指定标签的设备推送
     */
    public function push_tag_and($tags, $param)
    {
        //初始化参数
        $param = $this->init_param($param);

        self::$push->addTagAnd($tags);

        return $this->send($param);
    }

    /**
     * 根据不存在于指定标签的设备推送
     */
    public function push_tag_not($tags, $param)
    {
        //初始化参数
        $param = $this->init_param($param);

        self::$push->addTagNot($tags);

        return $this->send($param);
    }

    /**
     * 全部推送
     */
    public function push_all($param)
    {
        //初始化参数
        $param = $this->init_param($param);

        self::$push->addAllAudience();

        return $this->send($param);
    }


    /**
     * 根据注册id推送
     */
    public function push_registration_id($registration_ids, $param)
    {
        //初始化参数
        $param = $this->init_param($param);

        self::$push->addRegistrationId($registration_ids);

        return $this->send($param);
    }

    /**
     * 实施推送
     * @param unknown $param
     * @return boolean|unknown[]|mixed[]
     */
    public function send($param)
    {
        //推送
        self::$push->message($param['msg'],[
                'title' => $param['title'] ,
                'content_type' => 'text' ,
                'extras' => $param['extras']
        ]);

        return self::$push->send();

    }
}
?>