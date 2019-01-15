<?php
/**
 * alltosun.com 函数库 function.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 王敬飞 (wangjf@alltosun.com) $
 * $Date: 2017年6月17日 下午12:00:37 $
 * $Id$
 */
/**
 * curl_post操作
 * @param unknown $url
 * @param unknown $data
 */
function curl_post($url, $data)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    $res = curl_exec($ch);
    curl_close($ch);

    return $res;
}

/**
 * 根据间隔时长取指定时间戳的模数
 * @param unknown $time 指定时间戳
 * @param unknown $interval 间隔时间
 * @return number
 */
function get_modulo_by_interval($time, $interval)
{
    //计算间隔字符串长度
    $len = strlen($interval);
    //截取上次请求的等长字符串
    $s = substr($time, strlen($time) - $len, $len);

    return (int)$s % $interval;

}

/**
 * 加载文件
 * @param unknown $dir
 * @param unknown $name
 */
function loadfile($dir, $name)
{
    if (!$dir || !$name) {
        return NULL;
    }

    require_once ROOT_PATH . '/' . $dir . '/' . $name . '.php';
}
