<?php

/**
 * alltosun.com 异常类 Exception.php
 * ============================================================================
 * 版权所有 (C) 2009-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 安然 (gaojj@alltosun.com) $
 * $Date: 2012-8-7 上午06:06:21 $
 * $Id: Exception.php 430 2012-08-10 10:01:37Z anr $
*/

/**
 * AnMessage Exception
 * @author anr@alltosun.com
 * @package AnException
 */
class AnException extends Exception
{

    public $err = array();
    public $DebugMsg = '';

    /**
     * 多添加第2个参数，在调试状态才显示的信息
     */
    public function __construct($message = '', $DebugMsg = '', $code = 0)
    {
        parent::__construct($message, $code);
        $this->DebugMsg = $DebugMsg;
    }

    /**
     * 显示错误信息
     * 在开发状态与debug状态显示
     * @param $e Exception 传进来的错误对象
     * @return bool true
     */
    public static function echoMsg($e)
    {
        if ((defined('ONDEV') && ONDEV) || (defined('D_BUG') && D_BUG)) {
            echo $e->getMessage();
            echo "\n<pre>\n";
            if(!empty($e->DebugMsg)) echo $e->DebugMsg . "\n";
            echo $e;
        } else {
//             echo "<img src='http://mac.pzclub.cn/upload/2017/09/30/20170930141628000000_1_143437_71.jpg'>";
            echo $e->getMessage();
            echo "请添加DEBUG调试！";
        }

        return true;
    }
}
?>