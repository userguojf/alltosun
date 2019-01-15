<?php

/**
 * alltosun.com AnOpenApi类 AnOpenApiAbstract类 AnOpenApiConnect类 AnOpenApiConnectAbstract类 AnOpenApiConnectWrapper类 OpenApi.php
 * ============================================================================
 * 版权所有 (C) 2009-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 宁海新 (ninghx@alltosun.com) $
 * $Date: 2012-9-16 上午11:01:01 $
 * $Id: OpenApi.php 720 2013-05-05 06:05:45Z liw $
*/

/**
 * 生成单例开放平台链接对象
 * 通过此类可以适配多种开放平台
 * @author ninghx@alltosun.com
 * @package AnOpenApi
 */
class AnOpenApi
{
    /**
     * 开放平台连接的实例
     * @var array
     */
    private static $connections = array();

    /**
     * 连接短信平台
     * @example 连接短信平台格式：connect('vendor', 'type')
     * @example 可配合Config来连接开放平台
     * @throws Exception
     */
    public static function connect()
    {
        $params = func_get_args();

        $driver = array_shift($params);

        if (count($params) == 1) {
            $params = $params[0];
        }

        $key = md5(serialize($params));

        if (!isset(self::$connections[$key])) {
            require_once dirname(__FILE__).'/OpenApi/driver/'.$driver.'/'.$driver.'.php';
            $class = $driver.'Wrapper';
            self::$connections[$key] = new $class($driver, $params);
        }
        return self::$connections[$key];
    }
}

// @TODO 包含整个目录下的所有文件
require_once 'OpenApi/interface/base.php';
require_once 'OpenApi/interface/t.php';
require_once 'OpenApi/interface/comment.php';
require_once 'OpenApi/interface/search.php';
require_once 'OpenApi/interface/user.php';
require_once 'OpenApi/interface/relation.php';

/**
 * 开放平台操作的抽象层，初始化
 * @author nignhx@alltosun.com
 * @package AnOpenApi
 */
abstract class AnOpenApiAbstract
{
    public static $site_name = '';
    public static $config = array();
    public static $akey = '';
    public static $skey = '';
    public static $callback = '';
    public static $storage_type = '';

    /**
     * 初始化配置
     * @param array $config 开放平台配置
     */
    public function __construct($vendor, $config)
    {
        self::$config       = $config;
        self::$site_name    = $vendor;
        self::$akey         = array_shift($config);
        self::$skey         = array_shift($config);
        self::$callback     = array_shift($config);
        self::$storage_type = 'session';
        if ($config) {
            self::$storage_type = array_shift($config);
        }
    }

    /**
     * 更新connect信息 供项目中的开放平台调用
     * @param int $connect_id 开放平台关联id
     * @param int $user_id 用户id
     * @return boolean 成功与否
     */
    public function updateConnect($connect_id, $user_id)
    {
        $connect_id = intval($connect_id);
        $user_id = intval($user_id);
        if (!$user_id || !$connect_id) {
            return false;
        }
        if (in_array(self::$site_name, array('qqweibo', 'qqsns'), true)) {
            $table = 'connect_qq';
        } else {
            $table = 'connect';
        }
        $connect_info = _uri($table, $connect_id);
        if (!$connect_info) {
            return false;
        }
        _model($table)->update($connect_id, array('user_id'=>$user_id));

        return true;
    }
}

/**
 * 生成单例开放平台数据保存对象
 * 通过此类可以适配多种开放平台的数据保存
 * @author ninghx@alltosun.com
 * @package AnOpenApiConnect
 */
class AnOpenApiConnect
{
    /**
     * 平台数据保存连接的实例
     * @var array
     */
    private static $connections = array();

    /**
     * 连接数据保存驱动
     * @author ninghx@alltosun.com
     * @throws Exception
    */
    public static function connect()
    {
        $driver = AnOpenApiAbstract::$storage_type;
        $params = AnOpenApiAbstract::$config;

        $key = md5(serialize($params));

        if (!isset(self::$connections[$key])) {
            require_once dirname(__FILE__).'/OpenApi/connect/'.$driver.'.php';
            $class = 'AnOpenApi'.$driver.'Wrapper';
            self::$connections[$key] = new $class();
        }

        return self::$connections[$key];
    }
}

/**
 * 开放平台数据保存操作的抽象层 初始化
 * @author nignhx@alltosun.com
 * @package AnOpenApiConnect
 */
abstract class AnOpenApiConnectAbstract
{
    protected $site_id = 0;
    protected $site_name = '';
    protected $user_id = 0;
    protected $storage_type = '';

    /**
     * 初始化配置
    */
    public function __construct()
    {
        // 兼容以前老的项目取user_id
        if ( function_exists('get_user_id') ) {
            $this->user_id = get_user_id();
        } else {
            $this->user_id = user_helper::get_user_id();
        }
        $this->site_name = AnOpenApiAbstract::$site_name;
        $this->site_id = _uri('connect_site', array('site_name'=>AnOpenApiAbstract::$site_name), 'id');
        $this->storage_type = AnOpenApiAbstract::$storage_type;
    }
}

/**
 * 开放平台平台数据保存实现的接口类
 * @author nignhx@alltosun.com
 * @package AnOpenApiConnect
 */
interface AnOpenApiConnectWrapper
{
    /**
     * 检查授权
    */
    public function checkAuth();

    /**
     * 保存授权
    */
    public function saveAuth($token);

    /**
     * 获取授权
    */
    public function getAccessToken();
}
?>