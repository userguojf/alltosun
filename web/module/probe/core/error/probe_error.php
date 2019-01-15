<?php

/**
 * 自定义错误处理
 *
 * @author  wangl
 */

class probe_error
{
    /**
     * 自定义错误处理
     *
     * @author  wangl
     */
    public function error($errno, $errstr, $errfile = '', $errline = 0)
    {
        // 注：线上连接数据库时会报异常
        if ( strpos($errstr, 'mysql_connect') !== false ) {
            return true;
        }

        $str = $errstr;

        if ( $errfile ) {
            $str .= ' in '.$errfile;
        }

        if ( $errline ) {
            $str .= ':'.$errline;
        }

        probe_helper::write_log('error', $str);

        // 级别位error或用户自定义error时，结束运行。其他级别照常运行
        if ( $errno == E_ERROR || $errno == E_USER_ERROR ) {
            exit(-1);
        }

        return true;
    }
}

$err = new probe_error;

// 设置自定义错误处理
set_error_handler(array($err, 'error'));