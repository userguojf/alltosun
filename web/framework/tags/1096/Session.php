<?php

/**
 * session函数注册类
 * ============================================================================
 * 版权所有 (C) 2007-2009 北京共创阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: anr (anr@alltosun.com) $
 * $Date: 2009-04-13 23:37:57 +0800 $
 * $Id: Session.php 143 2012-02-02 17:25:16Z gaojj $
 * @link http://wiki.alltosun.com/index.php?title=Framework:Session.php
*/

/**
 *
 * @author anr
 * @link http://wiki.alltosun.com/index.php?title=Framework:class:session
 *
 */
class Session
{
    /**
     * 目前只支持memcache
     * @param mixed $handler
     */
    public static function start($handler)
    {
        if (is_object($handler)) {
            SessionMemcache::start($handler);
        } else {
            throw new Exception("start session exception!");
        }
    }
}

/**
 *
 * @author anr@alltosun.com
 * @link http://wiki.alltosun.com/index.php?title=Framework:class:abstract_session
 *
 */
abstract class SessionAbstract
{
    protected static $ua = null;
    protected static $ip = null;
    protected static $lifetime = null;
    protected static $time = null;
    protected static $flash = false;

    protected static function initialization()
    {
        self::$ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        self::$ip = !empty($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] :
                    (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] :
                    (!empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown'));
        // 判断是否为合法ip
        filter_var(self::$ip, FILTER_VALIDATE_IP) === false && self::$ip = 'unknown';
        self::$lifetime = ini_get('session.gc_maxlifetime');
        self::$time = $_SERVER['REQUEST_TIME'];

        $session_name = ini_get('session.name');
        // fix for swf ie.swfupload
        if (isset($_POST[$session_name])) {
            self::$flash = true;
            session_id($_POST[$session_name]);
        }
    }
}


/**
 *
 * @author anr@alltosun.com
 * @link http://wiki.alltosun.com/index.php?title=Framework:class:Session_Memcache
 *
 */
class SessionMemcache extends SessionAbstract
{
    const NS = 'session_';
    protected static $mc = null;

    public static function start($memcache)
    {
        self::$mc = clone $memcache;
        parent::initialization();

        session_set_save_handler(
            array(__CLASS__, 'open'),
            array(__CLASS__, 'close'),
            array(__CLASS__, 'read'),
            array(__CLASS__, 'write'),
            array(__CLASS__, 'destroy'),
            array(__CLASS__, 'gc')
        );
        session_start();
    }

    private static function open($path, $name)
    {
        return true;
    }

    public static function close()
    {
        return true;
    }

    private static function read($PHPSESSID)
    {
        $out = self::$mc->get(self::session_key($PHPSESSID));
        if ($out === false || $out === null) {
            return '';
        }

        return $out;
    }

    public static function write($PHPSESSID, $data)
    {
        if (self::$mc instanceof Memcache) {
            // 直接使用memcache对象
            return self::$mc->set(self::session_key($PHPSESSID), $data, MEMCACHE_COMPRESSED, parent::$lifetime);
        } else {
            // MemcacheWrapper
            return self::$mc->set(self::session_key($PHPSESSID), $data, parent::$lifetime);
        }
    }

    public static function destroy($PHPSESSID)
    {
        return self::$mc->delete(self::session_key($PHPSESSID));
    }

    private static function gc($lifetime)
    {
        return true;
    }

    /**
     * 用于组成$PHPSESSID在memcache里的key
     * @param $PHPSESSID
     */
    private static function session_key($PHPSESSID)
    {
        $session_key = '';
        if (defined('PROJECT_NS')) {
            $session_key .= PROJECT_NS;
        }
        $session_key .= self::NS . $PHPSESSID;

        return $session_key;
    }
}
?>