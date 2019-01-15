<?php

/**
 * alltosun.com 异常类 Exception.php
 * ============================================================================
 * 版权所有 (C) 2009-2011 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 高竞竞 (gaojj@alltosun.com) $
 * $Date: 2011-1-7 下午07:06:21 $
*/

/**
 * An Exception Handler
 * @param $e Exception
 */
function an_exception_handler($e)
{
    if ($e instanceof AnMessageException) {
        AnMessage::show($e->getMessageArr());
    } else {
        echo $e->getMessage();
    }
}

// @TODO
//set_exception_handler('an_exception_handler');

/**
 * AnForm Exception
 */
class AnFormGenerateException extends Exception {}

class AnFormRuleException extends Exception {}

class AnFormParseException extends Exception {}

class AnFormFilterException extends Exception {}

/**
 * AnMessage Exception
 */
class AnMessageException extends Exception
{
    protected $type = '';
    protected $code_type = array(
                  'info'    => 0,
                  'success' => 1,
                  'error'   => 2,
                  'notice'  => 3
              );
    protected $jumpurl = '';

    public function __construct($message, $type = '', $jumpurl = '')
    {
        $this->jumpurl = $jumpurl;
        $this->type    = $type;

        $code = isset($this->code_type[$type]) ? $this->code_type[$type] : 0;
        parent::__construct($message, $code);
    }

    public function getMessageExt()
    {
        return $this->type ? array($this->message, $this->type, $this->jumpurl) : $this->message;
    }
}
?>