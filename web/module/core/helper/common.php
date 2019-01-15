<?php

/**
 * alltosun.com 公用函数库 common.php
 * ============================================================================
 * 版权所有 (C) 2009-2011 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 高竞竞 (gaojj@alltosun.com) $
 * $Date: 2010-12-28 下午03:21:58 $
*/

require_once MODULE_CORE.'/helper/Exception.php';
require_once MODULE_CORE.'/helper/Rule.php';
require_once MODULE_CORE.'/helper/Router.php';
require_once MODULE_CORE.'/helper/Url.php';
require_once MODULE_CORE.'/helper/Module.php';
require_once MODULE_CORE.'/helper/Message.php';
require_once MODULE_CORE.'/helper/Form.php';
require_once MODULE_CORE.'/helper/Filter.php';
require_once MODULE_CORE.'/helper/smarty.php';

// /**
//  * 计算时差返回天数
//  */
// function date_diff($start_time, $end_time)
// {
//     $time = strtotime($end_time) - strtotime($start_time);
//     $day_count = $time / ( 60 * 60 * 24);
//     if ($day_count) {
//         return $day_count;
//     }
//     return 1;
// }

//10进制转62进制
function midToStr($mid) {
    settype($mid, 'string');
    $mid_length = strlen($mid);
    $url = '';
    $str = strrev($mid);
    $str = str_split($str, 7);

    foreach ($str as $v) {
        $char = intTo62(strrev($v));
        $char = str_pad($char, 4, "0");
        $url .= $char;
    }

    $url_str = strrev($url);

    return ltrim($url_str, '0');
}

function str62keys_int_62($key) //62进制字典
{
    $str62keys = array (
            "0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q",
            "r","s","t","u","v","w","x","y","z","A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q",
            "R","S","T","U","V","W","X","Y","Z"
    );
    return $str62keys[$key];
}

/* url 10 进制 转62进制*/

function intTo62($int10) {
    $s62 = '';
    $r = 0;
    while ($int10 != 0) {
        $r = $int10 % 62;
        $s62 .= str62keys_int_62($r);
        $int10 = floor($int10 / 62);
    }

    return $s62;
}


/**
 * 按utf-8编码进行截取字符串
 * @param string $string
 * @param int $len 截取的长度
 * @param string $fix 补充的省略符
 * @return string
 */
function sub_arrstr($string, $len = 11, $fix = '...')
{
    $t = mb_substr($string, 0, $len, 'utf-8');
    if (strlen($t) != strlen($string)) {
        $t .= $fix;
    }
    return $t;
}

/**
 * size的换算
 * @param $number
 * @return string
 */
function conversion($number)
{
    $num = strlen($number);
    if ($num > 9) {
        $num = $num - 9;
        return  substr($number,0,$num)."GB";
    } elseif ($num > 6) {
        $num = $num - 6;
        return substr($number,0,$num)."MB";
    } elseif ($num > 3) {
        $num = $num -3;
        return substr($number,0,$num)."KB";
    } else {
        return $number."B";
    }
}

/**
 * 验证文件上传状态
 * @param array $file_info 上传的文件信息，$_FILES['name']
 * @return string 错误信息failed_msg
 * @author gaojj@alltosun.com
 */
function check_upload($file_info = null, $max_size = 0)
{
    if (!isset($file_info)) {
        return '没有找到上传的文件';
    }
    if (isset($file_info['error']) && $file_info['error'] != 0) {
        $error_mapping = array(
            0   =>  "上传成功！",
            1   =>  "服务器限制的上传文件大小为".ini_get('upload_max_filesize'),
            2   =>  "上传文件大小超过了表单中MAX_FILE_SIZE的限制！",
            3   =>  "只有部分文件被上传了，请重试！",
            4   =>  "没有选择要上传的文件。",
            6   =>  "服务器上传临时目录不存在，请联系系统管理员。",
            7   =>  "文件无法写入磁盘，请联系系统管理员。",
            8   =>  "某个PHP扩展导致上传失败，请联系系统管理员。"
        );

        $error_id = $file_info['error'];

        return '上传失败，'.$error_mapping[$error_id];
    }

    if (empty($file_info['size'])) {
        return '选中的文件大小为空';
    }
    $image_max_size = $max_size ? $max_size : Config::get('image_max_size');
    $file_extension_name = pathinfo($file_info['name'], PATHINFO_EXTENSION);
    if ($image_max_size && $file_info['size'] > $image_max_size && in_array($file_extension_name, Config::get('allow_image_type'))) {
        return '上传文件的大小不得超过'.conversion($image_max_size);
    }

    if (empty($file_info['name'])) {
        return '选中的文件没有文件名';
    }

    if (empty($file_info['tmp_name'])) {
        return '上传到服务器临时目录失败';
    }

    return '';
}

/**
 * 验证多文件上传状态（以path[]数组形式作为file的name上传的文件组）
 * @param $file_info 上传的文件信息，$_FILES['name']
 * @return array 对应索引的错误信息数组array(0=>'failed_msg')
 * @author gaojj@alltosun.com
 */
function check_multiple_upload($files_info)
{
    $failed_msg_arr = array();
    foreach ($files_info['name'] as $k=>$v) {
        if (empty($v)) continue;

        $file_info = array(
            'name'      => $files_info['name'][$k],
            'tmp_name'  => $files_info['tmp_name'][$k],
            'size'      => $files_info['size'][$k],
            'type'      => $files_info['type'][$k],
            'error'     => $files_info['error'][$k]
        );

        $failed_msg = check_upload($file_info);
        if (!empty($failed_msg)) {
            $failed_msg_arr[$k] = '第'.($k+1).'个文件上传失败：'.$failed_msg;
        }
    }
    return $failed_msg_arr;
}

function time_format($time, $format="Y-m-d H:i")
{
    return date($format, strtotime($time));
}
/**
 * curl
 * @param string $url
 * @param string $data
 * @param int $is_json 1:是json数据 0:非json
 * @param int $json_deep 1:需要json_decode时带上参数true 0:不需要带true
 * @return mixed
 * @example header参数示例：$headers = array(
 'Content-Type:application/json',
 //'Content-Type:application/x-www-form-urlencoded'
 );
 */
function an_curl($url, $data = null, $is_json = 1, $json_deep = 1, $is_debug = 0, $headers = array())
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    if (!empty($data)){
        curl_setopt($curl, CURLOPT_POST, 1);
        if (is_array($data)) {
            $data = http_build_query($data);
        }
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    if (!empty($headers)) {
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    }
    $output = curl_exec($curl);

    if ($is_debug == 1) {
        an_dump(curl_error($curl));
    }

    curl_close($curl);

    if (1 == $is_json) {
        if ($json_deep) {
            $output = json_decode($output, true);
        } else {
            $output = json_decode($output);
        }
    }

    return $output;
}

/**
 * 显示距离当前时间的字符串
 * @param $time int 时间戳
 * @return string
 * @author gaojj@alltosun.com
 */
function time_past($time)
{
    $now        = time();
    $time_past  = $now - strtotime($time);

    // 如果小于1分钟（60s），则显示"刚刚"
    if ($time_past < 60) {
        return '1分钟前';
    }

    $time_mapping = array(
        '分钟' => '60',
        '小时' => '24',
        '天'   => '7',
        '周'   => '4',
        '月'   => '12',
        '年'   => '100'
    );

    $time_past = floor($time_past/60);

    foreach($time_mapping as $k=>$v) {
        if ($time_past < $v) return floor($time_past).$k.'前';
        $time_past = $time_past/$v;
    }

    // 如果小于1小时（60*60s），则显示N分钟前
    // 如果小于24个小时（60*60*24s），则显示N小时前
    // 如果大于24个小时（60*60*24s），则显示N天前
}

/**
 * 换行转换
 * @param string $str
 * @param string $replace 将换行转成字符
 * @return string
 * @author gaojj@alltosun.com
 */
function strip_br($str, $replace='')
{
    if (is_array($str)) {
        foreach ($str as $k=>$v) {
            $str[$k] = strip_br($v, $replace);
        }
        return $str;
    } else {
        $order   = array("\r\n", "\n", "\r");
        return str_replace($order, $replace, $str);
    }
}

/**
 * 获取符合系统设置的url地址
 * @param int|string $res_type 资源类型/资源名称，为空则返回网站首页地址
 * @param int|string $res_id 资源id/自定义名称，为空则返回资源首页地址
 * @example 获取资源首页：AnUrl('product')，结果为res_name.html
 * @example 获取资源列表页：AnUrl('category', 1)，结果为res_name/category.html
 * @example 获取资源详情页：AnUrl('product', 1)，结果为product/1.html(格式为res_name/res_id.html)
 *                           如果该资源在url表中有自定义url，则结果为res_name/custom_url.html
 * @example 其他使用方式：AnUrl('product/add')，结果为product/add.html
 *                         AnUrl('product/edit?id=1')，结果为product/edit.html?id=1
 * @return string
 */
function AnUrl($res_name = '', $res_id = 0)
{
    if (!$res_name || $res_name == 'index') {
        return SITE_URL;
    }

    static $anurl_static = array();
    if (isset($anurl_static[$res_name.'_'.$res_id])) {
        return $anurl_static[$res_name.'_'.$res_id];
    }

    if (is_numeric($res_name)) {
        $res_name = get_resource($res_name, 'name');
    }

    // @TODO nba项目临时手动替换讨论区首页的链接，需要改成按照路由规则替换
    $match_rule = 'thread\/team\/?(&|\?)forum_id=(\d+)';
    $alias = 'team/{$forum_alias}';
    $matches = array();

    if (preg_match("/{$match_rule}/", $res_name, $matches)) {
        // 匹配上
        $match_url = $matches[0];
        $forum_id = $matches[2];
        $forum_alias = _uri('forum', $forum_id, 'en_name');
        if ($forum_alias == 'sportswear' || $forum_alias == 'card') {
            $new_url = $forum_alias;
        } else {
            $new_url = str_replace('{$forum_alias}', $forum_alias, $alias);
        }
        $res_name = str_replace($match_url, $new_url, $res_name);
    }

    $location_hash = $query_string = '';

    // location_hash
    if (strpos($res_name, '#') !== false) {
        list($res_name, $location_hash) = explode('#', $res_name, 2);
    }

    // query string
    if (strpos($res_name, '?') !== false) {
        list($res_name, $query_string) = explode('?', $res_name, 2);
    } elseif (strpos($res_name, '&') !== false) {
        // 有直接写&k=v的
        list($res_name, $query_string) = explode('&', $res_name, 2);
    }

    if (strncasecmp($res_name, 'http://', 7) != 0) {
        $url = SITE_URL;
    }

    // url rewrite on
    //$url .= '/app/case';
    $url .= Config::get('rewrite_on') ? '/' : '/index.php?url=';

    // 分类获取分类对应的资源
    if ($res_name == 'category') {
        $res_name = _uri('category', $res_id, 'res_name');
        if (is_numeric($res_name)) {
            $res_name = get_resource($res_name, 'name');
        }
        $url .= "$res_name/category";
    } else {
        $url .= $res_name;
    }

    if ($res_id) {
        // custom url
        //$filter = array('res_name'=>$res_name, 'res_id'=>$res_id);
        //$custom_url = _uri('url', $filter, 'url');
        //if ($custom_url) {
        //    $url .= "/$custom_url";
        //} else {
            $url .= "/$res_id";
        //}
    }

    $cid = Request::Get('cid', 0);
    if ($cid) {
        $url .= '&cid='.$cid;
    }


    // html static on
    if (Config::get('html_static')) {
        $url .= '.html';
    }

    if (!empty($query_string)) {
        $connector = strpos($url, '?') !== false ? '&' : '?';
        $url .= $connector.$query_string;
    }

    if (!empty($location_hash)) {
        $url .= '#'.$location_hash;
    }

    $anurl_static[$res_name.'_'.$res_id] = $url;

    return $url;
}
/**
 * 组装存储文件名
 * @param unknown $file
 * @param unknown $ext
 * @return string
 */
function build_target_name($ext)
{
    $time = time();
    $path = date('/Y/m/d', $time);
    //$folder = UPLOAD_PATH.'/2014/03/31';
    $folder = UPLOAD_PATH;

    $tmp_arr = explode('/', ltrim($path, '/'));
    foreach($tmp_arr as $k=>$v) {
        $folder .= '/'.$v;

        if (!file_exists($folder)) {
            @mkdir($folder, 0777, true);
        }
    }

    //var_dump($tmp_arr, $folder);
    //exit;

    static $count = 1;
    // u为microseconds，> PHP 5.2.2
    if (version_compare(PHP_VERSION, '5.2.2') >= 0) {
        $current_time = date('YmdHisu', $time);
    } else {
        $current_time = date('YmdHis', $time);
    }

    $random = mt_rand(0, 100);

    $target = $folder.'/'.$current_time.'_'.$count.'_'.'_'.$random.'.'.$ext;

    $target = trim($target, '.');
    $count++;

    return $target;
}
/**
 * 上传文件
 * @param string $file
 * @param string $ext
 * @return string
 */
function an_upload($file, $ext)
{
    $time = time();
    $folder = UPLOAD_PATH.date('/Y/m/d', $time);

    if (!file_exists($folder) && !@mkdir($folder, 0777, true)) {
        return '';
    }

    // 如果是图片的话，则按当前时间+文件大小+随机数（用于区分同一秒多个进程上传不同文件）存放文件
    $target = build_target_name($ext);
    if (!file_exists($target)) {
        if (!rename($file, $target)) {
            return '';
        }
    }

    return substr($target, strlen(UPLOAD_PATH));
}
/**
 * 转换时间为固定格式,如果为当天则显示时间(15:00),否则显示日期(12月1日)
 * @param $date (2010-06-22 16:00:00)
 * @return string
 * @author gaojj@alltosun.com
 */
function track_date($date)
{
    //获取当前时间
    $now = date('Y-m-d');
    //获取要转换的时间
    list($date, $time) = explode(' ', $date);

    if ($now == $date) {
        return substr($time, 0, 5);
    } else {
        list($y, $m, $d) = explode('-', $date);
        return $m.'月'.$d.'日';
    }
}

/**
 * 将多行的字符串转换为数组（暂未用到，可以用于自定义属性默认值的处理）
 * @param string $line
 * @author gaojj@alltosun.com
 */
function line_to_array($line)
{
    return explode("\n", str_replace(array("\r\n", "\r"), "\n", $line));
}

/**
 * 模板中的select
 * @param $array array 二维数组
 * @param string 显示的字段名称
 * @return array 一维数组
 * @author ninghx@alltosun.com
 */
function array_to_option($array, $val = 'title')
{
    $result = array();
    if (empty($array)) {
        return $result;
    }
    foreach ($array as $v) {
        if ($v) {
            $result[$v['id']] = $v[$val];
        }
    }
    return $result;
}

/**
 * 多字节的字符串反序列化（比如原先是gbk的数据序列化后，在utf-8的环境下反序列化）
 * @param $serial_str
 *
 */
function mb_unserialize($serial_str)
{
    $out = preg_replace('!s:(\d+):"(.*?)";!se', "'s:'.strlen('$2').':\"$2\";'", $serial_str);
    return unserialize($out);
}

/**
 * 自定义error_handler
 * @param int $errno
 * @param str $errstr
 * @param str $errfile
 * @param str $errline
 */
function an_error_handler($errno, $errstr, $errfile, $errline)
{
    if (stripos($errstr, 'memcache') !== false) {
        $log = "Error$errno:$errstr in $errfile on $errline";
        error_log($log, 1, 'root@alltosun.com');
    }
    return FALSE;
}

/*
 * PHP 清除HTML代码、空格、回车换行符的函数
 * @author liww@alltosun.com
 */
function strip_space($str)
{
    $str = str_ireplace(array("\t", "\r\n", "\r", "\n", " ", "&nbsp;"), "", $str);
    return trim($str);
}

/**
 * 去除空行
 * @param $str
 * @author gaojj@alltosun.com
 */
function strip_empty_lines($str)
{
    while (stripos($str, '<br><br>') !== false || stripos($str, '<br /><br />') !== false ) {
        $str = str_ireplace(array('<br><br>','<br /><br />'), '<br>', $str);
    }
    return $str;
}

/**
 * 对数组的所有key, value进行编码转换
 * @param string $in_charset
 * @param string $out_charset
 * @param string|array $array
 * @return string|array
 */
function iconv_array($in_charset, $out_charset, $array)
{
    if (is_array($array)) {
        foreach ($array as $k=>$v) {
            $k = iconv($in_charset, $out_charset, $k);
            $array[$k] = iconv_array($in_charset, $out_charset, $v);
        }
    } else {
        $array = iconv($in_charset, $out_charset, $array);
    }

    return $array;
}

if (!function_exists('_')) {
    /**
     * 多国语言方法
     * @param string $lang
     * @TODO
     */
    function _($lang)
    {
        // 测试用
        return true;
    }
}

/**
 * 获取指定路径图片的缩略图
 * @param string $path 图片路径
 * @param string $prefix 缩略图前缀名称：''-原始图片（默认）；small-小图； middle-中图；big-大图……
 * @param string @res_name 图片所属资源（用来显示指定资源的默认图）
 * @return string 图片的完整路径
 * @author gaojj@alltosun.com
 */
function _image($path, $prefix = '', $res_name = '')
{
    // @FIXME general.gif默认图片，如果视频、文章等默认图片不同的话则存在问题
    if (!$path) return STATIC_URL.'/images/default/general.gif';

    // 外站图片的缩略图直接返回源图
    if (strncasecmp($path, 'http://', 7) == 0) {
        if (stripos($path, 'sinaimg.cn/') !== false) {
            // 'http://ss'.rand(1, 4).'sinaimg.cn/large/xxxxxx';
            return $prefix ? str_replace('large', $prefix, $path) : $path;
        }

        if (stripos($path, SITE_URL) === false && stripos($path, STATIC_URL) === false) {
            return $path;
        }

        // 注：已http://开头的图片都直接返回
        return $path;
    }

    $path_info = pathinfo($path);
    $file_path = '';

    //@FIXME 改为读取对应资源的缩略图配置，但是需要函数传入资源类型，待考虑
    if (!empty($prefix)) {
        $file_path = $path_info['dirname'].'/'.$prefix.'_'.$path_info['basename'];

    } else {
        $file_path = $path;
    }
    //如果传入的路径没有标明上传文件夹的话，则补全路径
    if (substr($file_path, 0, strlen(UPLOAD_FOLDER)) != UPLOAD_FOLDER) $file_path = UPLOAD_FOLDER.$file_path;
    if (!file_exists(ROOT_PATH.'/'.$file_path)) {
        // wangjf add 2018-03-28 如果不存在缩略图，取原图
        if (!empty($prefix)) {
            return _image($path);
        }
        return STATIC_URL.'/images/default/general.gif';
    }
    return STATIC_URL.'/'.$file_path;
}

/**
 * 生成缩略图
 * @param $file_path 原图路径
 * @param $res_type 资源类型
 * @param $category_id 对应分类id
 * @return bool
 * @author gaojj@alltosun.com
 */
function make_thumb($file_path, $res_name, $category_id = 0, $prefix='')
{
    if (empty($res_name) || empty($file_path)) return false;
    // 从配置文件读取缩略图配置
    $thumb_set = get_res_thumb($res_name, $category_id);

    if (empty($thumb_set)) return false;

    $file_path = UPLOAD_PATH.$file_path;
    $path_info = pathinfo($file_path);

    // 缩略图模式，默认为切图
    $thumb_mode = 'cut';
    if (isset($thumb_set['mode'])) {
        $thumb_mode = $thumb_set['mode'];
        unset($thumb_set['mode']);
    }

    // 生成缩略图
    foreach ($thumb_set as $k=>$v) {
        if (!empty($prefix) && $k != $prefix) {
            continue;
        }
        // 缩略图路径
        $thumb_path = $path_info['dirname'].'/'.$k.'_'.$path_info['basename'];

        $new_width  = $v[0];

        if (isset($v[1]) && $v[1]) {
            $new_height = $v[1];
        } else {
            $new_height = null;
        }

        $new_height = $v[1];

        // 按照最大宽度/最大高度进行等比缩放
        if ($thumb_mode == 'max') {
            $gd = new Gd($file_path);
            // 如果原图的宽度小于指定缩放的宽度，则宽度不参与缩放
            if (empty($new_width) || $gd->width < $new_width) {
                $new_width = null;
            }
            // 如果原图的高度小于指定缩放的高度，则高度不参与缩放
            if (empty($new_height) || $gd->height < $new_height) {
                $new_height = null;
            }
            $gd->scale($new_width, $new_height);
            $gd->saveAs($thumb_path);
        } elseif ($thumb_mode == 'merge') {
            // 融图的方案
            $gd = Gd::create($new_width, $new_height, MERGE_BG);
            $gd2 = new Gd($file_path);
            $gd2->scale($new_width, $new_height);
            $gd->merge_auto($gd2);
            $gd->saveAs($thumb_path);
        } elseif ($thumb_mode == 'cut') {
            // 切图的方案，按照指定的宽高比进行缩放到指定的比例，再切去多余的图
            $gd = new Gd($file_path);
            $gd->scale_fill($new_width, $new_height);
            $gd->saveAs($thumb_path);
        }
    }
    return true;
}

/**
 * 从配置文件读取缩略图配置
 * @param mixed $res_type
 * @param $category_id 对应分类id
 * @return array
 * @author gaojj@alltosun.com
 */
function get_res_thumb($res_name, $category_id = 0)
{
    $res_thumb = Config::get('res_thumb');

    $res_type = $res_name;
    if (is_numeric($res_type)) {
        $res_name = get_resource($res_type, 'name');
    }

    $thumb_set = isset($res_thumb[$res_name]) ? $res_thumb[$res_name] : array();

    // 支持资源的不同分类生成不同的缩略图
    if (isset($thumb_set['category'])) {
        // 如果传入category_id，则采用对应分类的缩略图设置
        if (!empty($category_id) && isset($thumb_set['category'][$category_id])) {
            return $thumb_set['category'][$category_id];
        }
        // 未定义该分类的缩略图设置，或者未传入category_id，则采用默认的缩略图设置
        return $thumb_set['category'][0];
    }

    return $thumb_set;
}

/**
 * 对ip进行处理
 * @param $ip
 * @return string
 * @author gaojj@alltosun.com
 * @FIXME
 */
function ip_treat($ip)
{
    if (empty($ip)) return '127.0.0.1';
    list($a, $b, $c, $d) = explode('.', $ip);
    return $a.'.'.$b.'.'.$c.'.'.'*';
}

/**
 * @TODO
 * @param $sUBB
 */
function ubb2html($sUBB)
{
    $sHtml=$sUBB;

    global $emotPath,$cnum,$arrcode,$bUbb2htmlFunctionInit;$cnum=0;$arrcode=array();
    $emotPath='/js/xheditor/xheditor_emot/';//表情根路径

    if(!$bUbb2htmlFunctionInit){
        function saveCodeArea($match)
        {
            global $cnum,$arrcode;
            $cnum++;$arrcode[$cnum]=$match[0];
            return "[\tubbcodeplace_".$cnum."\t]";
        }}
        $sHtml=preg_replace_callback('/\[code\s*(?:=\s*((?:(?!")[\s\S])+?)(?:"[\s\S]*?)?)?\]([\s\S]*?)\[\/code\]/i','saveCodeArea',$sHtml);

        $sHtml=preg_replace("/&/",'&amp;',$sHtml);
        $sHtml=preg_replace("/</",'&lt;',$sHtml);
        $sHtml=preg_replace("/>/",'&gt;',$sHtml);
        $sHtml=preg_replace("/\r?\n/",'<br />',$sHtml);

        $sHtml=preg_replace("/\[(\/?)(b|u|i|s|sup|sub)\]/i",'<$1$2>',$sHtml);
        $sHtml=preg_replace('/\[color\s*=\s*([^\]"]+?)(?:"[^\]]*?)?\s*\]/i','<span style="color:$1;">',$sHtml);
        if(!$bUbb2htmlFunctionInit){
            function getSizeName($match)
            {
                $arrSize=array('8pt','10pt','12pt','14pt','18pt','24pt','36pt');
                return '<span style="font-size:'.$arrSize[$match[1]-1].';">';
            }}
            $sHtml=preg_replace_callback("/\[size\s*=\s*(\d+?)\s*\]/i",'getSizeName',$sHtml);
            $sHtml=preg_replace('/\[font\s*=\s*([^\]"]+?)(?:"[^\]]*?)?\s*\]/i','<span style="font-family:$1;">',$sHtml);
            $sHtml=preg_replace('/\[back\s*=\s*([^\]"]+?)(?:"[^\]]*?)?\s*\]/i','<span style="background-color:$1;">',$sHtml);
            $sHtml=preg_replace("/\[\/(color|size|font|back)\]/i",'</span>',$sHtml);

            for($i=0;$i<3;$i++)$sHtml=preg_replace('/\[align\s*=\s*([^\]"]+?)(?:"[^\]]*?)?\s*\](((?!\[align(?:\s+[^\]]+)?\])[\s\S])*?)\[\/align\]/','<p align="$1">$2</p>',$sHtml);
            $sHtml=preg_replace('/\[img\]\s*(((?!")[\s\S])+?)(?:"[\s\S]*?)?\s*\[\/img\]/i','<img src="$1" alt="" />',$sHtml);
            if(!$bUbb2htmlFunctionInit){
                function getImg($match)
                {
                    $alt=$match[1];$p1=$match[2];$p2=$match[3];$p3=$match[4];$src=$match[5];
                    $a=$p3?$p3:(!is_numeric($p1)?$p1:'');
                    return '<img src="'.$src.'" alt="'.$alt.'"'.(is_numeric($p1)?' width="'.$p1.'"':'').(is_numeric($p2)?' height="'.$p2.'"':'').($a?' align="'.$a.'"':'').' />';
                }}
                $sHtml=preg_replace_callback('/\[img\s*=([^,\]]*)(?:\s*,\s*(\d*%?)\s*,\s*(\d*%?)\s*)?(?:,?\s*(\w+))?\s*\]\s*(((?!")[\s\S])+?)(?:"[\s\S]*)?\s*\[\/img\]/i','getImg',$sHtml);
                if(!$bUbb2htmlFunctionInit){
                    function getEmot($match)
                    {
                        global $emotPath;
                        $arr=split(',',$match[1]);
                        if(!isset($arr[1])){$arr[1]=$arr[0];$arr[0]='default';}
                        $path=$emotPath.$arr[0].'/'.$arr[1].'.gif';
                        return '<img src="'.$path.'" alt="'.$arr[1].'" />';
                    }}
                    $sHtml=preg_replace_callback('/\[emot\s*=\s*([^\]"]+?)(?:"[^\]]*?)?\s*\/\]/i','getEmot',$sHtml);
                    $sHtml=preg_replace('/\[url\]\s*(((?!")[\s\S])*?)(?:"[\s\S]*?)?\s*\[\/url\]/i','<a href="$1">$1</a>',$sHtml);
                    $sHtml=preg_replace('/\[url\s*=\s*([^\]"]+?)(?:"[^\]]*?)?\s*\]\s*([\s\S]*?)\s*\[\/url\]/i','<a href="$1">$2</a>',$sHtml);
                    $sHtml=preg_replace('/\[email\]\s*(((?!")[\s\S])+?)(?:"[\s\S]*?)?\s*\[\/email\]/i','<a href="mailto:$1">$1</a>',$sHtml);
                    $sHtml=preg_replace('/\[email\s*=\s*([^\]"]+?)(?:"[^\]]*?)?\s*\]\s*([\s\S]+?)\s*\[\/email\]/i','<a href="mailto:$1">$2</a>',$sHtml);
                    $sHtml=preg_replace("/\[quote\]([\s\S]*?)\[\/quote\]/i",'<blockquote>$1</blockquote>',$sHtml);
                    if(!$bUbb2htmlFunctionInit){
                        function getFlash($match)
                        {
                            $w=$match[1];$h=$match[2];$url=$match[3];
                            if(!$w)$w=480;if(!$h)$h=400;
                            return '<embed type="application/x-shockwave-flash" src="'.$url.'" wmode="opaque" quality="high" bgcolor="#ffffff" menu="false" play="true" loop="true" width="'.$w.'" height="'.$h.'" />';
                        }}
                        $sHtml=preg_replace_callback('/\[flash\s*(?:=\s*(\d+)\s*,\s*(\d+)\s*)?\]\s*(((?!")[\s\S])+?)(?:"[\s\S]*?)?\s*\[\/flash\]/i','getFlash',$sHtml);
                        if(!$bUbb2htmlFunctionInit){
                            function getMedia($match)
                            {
                                $w=$match[1];$h=$match[2];$play=$match[3];$url=$match[4];
                                if(!$w)$w=480;if(!$h)$h=400;
                                return '<embed type="application/x-mplayer2" src="'.$url.'" enablecontextmenu="false" autostart="'.($play=='1'?'true':'false').'" width="'.$w.'" height="'.$h.'" />';
                            }}
                            $sHtml=preg_replace_callback('/\[media\s*(?:=\s*(\d+)\s*,\s*(\d+)\s*(?:,\s*(\d+)\s*)?)?\]\s*(((?!")[\s\S])+?)(?:"[\s\S]*?)?\s*\[\/media\]/i','getMedia',$sHtml);
                            if(!$bUbb2htmlFunctionInit){
                                function getTable($match)
                                {
                                    return '<table'.(isset($match[1])?' width="'.$match[1].'"':'').(isset($match[2])?' bgcolor="'.$match[2].'"':'').'>';
                                }}
                                $sHtml=preg_replace_callback('/\[table\s*(?:=(\d{1,4}%?)\s*(?:,\s*([^\]"]+)(?:"[^\]]*?)?)?)?\s*\]/i','getTable',$sHtml);
                                if(!$bUbb2htmlFunctionInit){
                                    function getTR($match){return '<tr'.(isset($match[1])?' bgcolor="'.$match[1].'"':'').'>';}}
                                    $sHtml=preg_replace_callback('/\[tr\s*(?:=(\s*[^\]"]+))?(?:"[^\]]*?)?\s*\]/i','getTR',$sHtml);
                                    if(!$bUbb2htmlFunctionInit){
                                        function getTD($match){
                                            $col=isset($match[1])?$match[1]:0;$row=isset($match[2])?$match[2]:0;$w=isset($match[3])?$match[3]:null;
                                            return '<td'.($col>1?' colspan="'.$col.'"':'').($row>1?' rowspan="'.$row.'"':'').($w?' width="'.$w.'"':'').'>';
                                        }}
                                        $sHtml=preg_replace_callback("/\[td\s*(?:=\s*(\d{1,2})\s*,\s*(\d{1,2})\s*(?:,\s*(\d{1,4}%?))?)?\s*\]/i",'getTD',$sHtml);
                                        $sHtml=preg_replace("/\[\/(table|tr|td)\]/i",'</$1>',$sHtml);
                                        $sHtml=preg_replace("/\[\*\]((?:(?!\[\*\]|\[\/list\]|\[list\s*(?:=[^\]]+)?\])[\s\S])+)/i",'<li>$1</li>',$sHtml);
                                        if(!$bUbb2htmlFunctionInit){
                                            function getUL($match)
                                            {
                                                $str='<ul';
                                                if(isset($match[1]))$str.=' type="'.$match[1].'"';
                                                return $str.'>';
                                            }}
                                            $sHtml=preg_replace_callback('/\[list\s*(?:=\s*([^\]"]+))?(?:"[^\]]*?)?\s*\]/i','getUL',$sHtml);
                                            $sHtml=preg_replace("/\[\/list\]/i",'</ul>',$sHtml);

                                            for($i=1;$i<=$cnum;$i++)$sHtml=str_replace("[\tubbcodeplace_".$i."\t]", $arrcode[$i],$sHtml);

                                            if(!$bUbb2htmlFunctionInit){
                                                function fixText($match)
                                                {
                                                    $text=$match[2];
                                                    $text=preg_replace("/\t/",'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',$text);
                                                    $text=preg_replace("/ /",'&nbsp;',$text);
                                                    return $match[1].$text;
                                                }}
                                                $sHtml=preg_replace_callback('/(^|<\/?\w+(?:\s+[^>]*?)?>)([^<$]+)/i','fixText',$sHtml);

                                                $bUbb2htmlFunctionInit=true;

                                                return $sHtml;
}

/**
 * 清除字符串中的ubb标签
 * @param string $str
 * @return string
 * @author gaojj@alltosun.com
 */
function clean_ubb($str)
{
    return preg_replace("/^\[\S+\]$/U","",$str);
}

/**
 * 图片缩放
 * @param $path 图片路径
 * @param $max_width 缩放最大宽度
 * @param $max_height 缩放最大高度
 * @param $key
 * @return array 与getimagesize()返回的数据结构相同
 */
function image_scale($path, $max_width = 0, $max_height = 0, $key = null)
{
    $path = ROOT_PATH . '/' . _image($path);
    $size = getimagesize($path);
    if (!$size) return false;
    $width  = $size[0];
    $height = $size[1];

    if ($max_width && $width > $max_width) {
        $height = $max_width / $width * $height;
        $width  = $max_width;
    }

    if ($max_height && $height > $max_height) {
        $width  = $max_height / $height * $width;
        $height = $max_height;
    }

    $array = array($width, $height, $size[2], " width=\"$width\" height=\"$height\"");

    if (isset($key)) return $array[$key];
    return $array;
}

/**
 * 敏感词过滤
 * @param string $subject 原字符串
 * @param string $separator 分隔符 默认空格
 * @param string $replace 替换字符串 无->不允许发布带有敏感词的微博
 */
function filter_words($subject, $separator=' ', $replace='')
{
    // 获取关键词 从setting表
    //    $filter_words = _uri('setting', array('field'=>'filter_words'), 'value');
    $filter_words = Config::get('filter_words');

    if (!$filter_words) return $subject;

    $filter_arr = explode($separator, $filter_words);

    $subject_replace = str_replace($filter_arr, $replace, $subject);

    if ($replace) return $subject_replace;

    $cmp = strcmp($subject , $subject_replace);

    if ($cmp == 0) return $subject;

    return '您发表的内容中含有不良词语！';
}

/**
 * 数据过滤
 * @param array  $arr 要处理的数据字段和类型
 * @param array  $data FORM传来的数据
 */
function filter_data($arr, $data)
{
    $filter = array();
    foreach ($arr as $k=>$v) {
        $type = is_array($v) ? $v[0] : $v;
        switch ($type) {
            case 'bool':
                $filter[$k] = empty($data[$k]) ? 0 : 1;
                break;
            case 'int':
                $filter[$k] = empty($data[$k]) ? (empty($v[1]) ? 0 : $v[1]) : intval($data[$k]);
                break;
            case 'text':
                $filter[$k] = empty($data[$k]) ? '' : htmlspecialchars($data[$k]);
                break;
            case 'date':
                $filter[$k] = date("Y-m-d H:i:s");
                break;
            case 'arr':
                $filter[$k] = $v[1];
                break;
            default :
                $filter[$k] = '';
        }
    }
    return $filter;
}

/**
 * 授权
 * return int （1为授权）
 */
function auth()
{
    $need_auth = false;
    $expires_time_auth = false;

    if (isset($_SESSION['user_id'])) {
        $session_user_id = $_SESSION['user_id'];
        if (isset($_SESSION['sub_appkey']) || !empty($_SESSION['sub_appkey'])) {
            $sub_appkey = $_SESSION['sub_appkey'];
            // 判断用户过期时间
            $expires_time = _uri('connect', array('connect_user_id'=>$session_user_id, 'appkey'=>$sub_appkey), 'expires_time');
            if ($expires_time > time()) {
                $expires_time_auth = true;
            }
        }
    } else {
        $session_user_id = false;
    }

    if (empty($session_user_id) || empty($expires_time_auth)) {
        if (!empty($_REQUEST['tokenString'])) {
            list($string1, $string2) = explode('.', $_REQUEST['tokenString']);
            $token_string = json_decode(base64_decode($string2), true);
            if (isset($token_string['oauth_token']) && !empty($token_string['oauth_token'])) {
                $_SESSION['sinaweibo']['token']['access_token'] = $token_string['oauth_token'];
                //$_SESSION['user_id'] = $token_string['user_id'];
                // 获取用户信息
                $sub_appkey = $_SESSION['sub_appkey'];

                $get_user_info = AnSinaWeibo::getUserInfo($token_string['user_id']);
                $user_info = _uri('user', array('weibo_id'=>$token_string['user_id']));
                $connect_info = _uri('connect', array('connect_site_id'=>1, 'connect_user_id'=>$token_string['user_id'], 'appkey'=>$sub_appkey, 'status'=>1));
                //$connect_info =  _model('connect')->read(array('connect_site_id'=>1, 'connect_user_id'=>$get_uid['uid'], 'appkey'=>Config::get('sinaweibo_skey'), 'status'=>1));
                // 更新用户信息
                if (isset($get_user_info['id']) && !empty($get_user_info['id'])) {
                    $info = array(
                        'user_name'=>$get_user_info['screen_name'],
                        'avatar' =>$get_user_info['profile_image_url'],
                        'gender' => $get_user_info['gender'],
                        'hash'   => user_helper::random_hash(),
                        //'weibo_id' => $get_user_info['uid'],
                        'fans_count' => $get_user_info['followers_count'],
                        'follow_count' => $get_user_info['friends_count'],
                        'weibo_count'=> $get_user_info['statuses_count']
                    );
                    if (!$user_info) {
                        $info['weibo_id'] = $get_user_info['id'];
                        $user_id = _model('user')->create($info);
                    } else {
                        _model('user')->update(array('weibo_id'=>$get_user_info['id']), $info);
                        $user_id = $user_info['id'];
                    }
                    if (!$connect_info) {
                        $connect_filter = array(
                            'user_id'         => $user_id,
                            'connect_user_id' => $get_user_info['id'],
                            'connect_site_id' => 1,
                            'user_name'       => $get_user_info['screen_name'],
                            'access_token'    => $token_string['oauth_token'],
                            'appkey'          => $sub_appkey,
                            //                    'refresh_token' => $token['refresh_token'],
                            'expires_time'    => $token_string['expires']+time(),
                            'auth_type' => 3
                        );
                        $connect_id = _model('connect')->create($connect_filter);

                        // 发送邮件
                        $now_date = date('Y-m-d H:i');
                        $send_content = "用户昵称：{$get_user_info['screen_name']}<br> 用户id：{$get_user_info['id']}<br> 安装时间：{$now_date}<br> sub_appkey:$sub_appkey<br>微博地址：http://e.weibo.com/{$get_user_info['id']}";
                        _widget('mail')->send_email('sateam@alltosun.com', "恭喜 {$get_user_info['screen_name']} 安装了微助理", $send_content, '微助理');

                    } else {
                        $connect_filter = array(
                            'connect_site_id' => 1,
                            'user_name'       => $get_user_info['screen_name'],
                            'access_token'    => $token_string['oauth_token'],
                            'expires_time' => $token_string['expires']+time()
                        );
                        $connect_id = _model('connect')->update($connect_info['id'], $connect_filter);
                    }
                    $cid = Request::Get('cid');
                    if (!$cid || $cid != $get_user_info['id']) {
                        exit('非法操作');
                    }
                    $sub_appkey_info = _uri('sub_appkey', array('cid'=>$get_user_info['id']));
                    if (empty($sub_appkey_info)) {
                        _model('sub_appkey')->create(array('cid'=>$get_user_info['id'], 'sub_appkey'=>$sub_appkey, 'app_id'=>100));
                    } else {
                        _model('sub_appkey')->update(array('cid'=>$get_user_info['id']), array('sub_appkey'=>$sub_appkey, 'app_id'=>100));
                    }

                    define("WB_AKEY", $sub_appkey);
                    define("WB_SKEY", 'cf5b98cb3b6303a639c397f3eb637ee4');
                    $_SESSION['user_id'] = $user_id;
                    //$_SESSION['sub_appkey'] = $sub_appkey;
                    $_SESSION['cid'] = $cid;

                }
            } else {
                $need_auth = 1;
            }
        } else {
            $need_auth = 1;
        }
    }

    return $need_auth;
}

/**
 * 上传文件
 * @param $file_info
 * @param $res_name 生成缩略图
 * @param $is_attachment 是否插入附件表
 *
 */
function upload_file($file_info, $is_attachment = false, $res_name = '')
{
    // php.ini限制的post大小
    $POST_MAX_SIZE = ini_get('post_max_size');
    $unit = strtoupper(substr($POST_MAX_SIZE, -1));
    $multiplier = ($unit == 'M' ? 1048576 : ($unit == 'K' ? 1024 : ($unit == 'G' ? 1073741824 : 1)));

    if ((int)$_SERVER['CONTENT_LENGTH'] > $multiplier * (int)$POST_MAX_SIZE && $POST_MAX_SIZE) {
        header("HTTP/1.1 500 Internal Server Error"); // This will trigger an uploadError event in SWFUpload
        throw new Exception('对不起，上传文件超过服务器限制大小。');
    }

    $allow_type  = array_merge(Config::get('allow_image_type'), Config::get('allow_flash_type'));
//     p(Config::get('allow_image_type'));
//     p(Config::get('allow_flash_type'));
    $upload_path = UPLOAD_PATH;

    // 上传验证
    $failed_msg = check_upload($file_info, 8000000);
    if (!empty($failed_msg)) {
        throw new Exception($failed_msg);
    }

    // 上传
    if (SAE) {
        require_once MODULE_CORE . '/helper/Sae_uploader.php';
        $uploader = new Sae_uploader(Config::get('storage_domains'));
    } else {
        $uploader = new Uploadr($upload_path, $allow_type);
    }
    try {
        $file_path = $uploader->uploadFile($file_info['tmp_name']);
    } catch (Exception $e) {
        throw new Exception($e->getMessage());
    }

    if (empty($file_path)) {
        throw new Exception('图片保存失败，请重试');
    }

    if ( !$is_attachment ) {
        if ( $res_name ) make_thumb($file_path, $res_name);
        return $file_path;
    }

    // 插入数据库
    $attachment_id = 0;
    $attachment_info = _model('file')->read(array('path'=>$file_path));
    if (!empty($attachment_info)) {
        // 如果同一张图片在不同模型中使用，这样处理会导致返回的缩略图比例只是第1个模型产生的
        // 可采用解决方案：图片存放名称不用md5唯一值，改为当前时间+计数，已在Uploadr.php采用该方案
        $attachment_id = $attachment_info['id'];
    } else {
        // 附件类型
        $attachment_type = get_attachment_type($file_path);

        $attachment_info = array(
                'member_id' => member_helper::get_member_id(),
                'title'     => htmlspecialchars($file_info['name'], ENT_NOQUOTES),
                'path'      => $file_path,
                'type'      => $attachment_type,
                'size'      => $file_info['size']
        );

        $attachment_id = _model('file')->create($attachment_info);

        // 缩略图
        // *requires res_name 前台上传工具必须传入res_name
        if ($attachment_type == 1) make_thumb($file_path, $res_name);
    }

    if (empty($attachment_id)) {
        throw new Exception('数据库插入失败，请重试');
    }
    return array('id'=>$attachment_id, 'file_path'=>$file_path);
}

function an_dump($info)
{
    $arr = func_get_args();

    echo '<pre>';
    foreach ($arr as $k => $v) {
        var_dump($v);
    }
    echo '</pre>';
}

/**
 * 加载php文件
 * @param string $module
 * @param string $file
 * @param string $file_name
 * @return boolean
 */
function load_file($module, $file, $file_name)
{
    if ( !$file || !$module || !$file_name) {
        return false;
    }

    require MODULE_PATH.'/'.$module.'/'.$file.'/'.$file_name.'.php';
}

/**
 * 封装分页操作，app获取数据常用,自动分配分页
 * @param string $table_name 表名
 * @param array $filter 条件数组
 * @param string $order 排序
 * @param int $page_no  页码
 * @param int $per_page 每页数量
 * @return array
 */
function get_app_data_list($table_name, $filter = array( 1 => 1), $order = ' ORDER BY `id` DESC ', $page_no = 1, $per_page = 30)
{
    $page_arr = $data = array();
    $count = _model($table_name)->getTotal($filter, $order);

    if ($count) {
        $pager = new Pager($per_page);

        $pager->generate($count);
        $page_arr['page_no'] = (int)$page_no;
        $page_arr['pages']   = (int)$pager->getPages();


        $list = _model($table_name)->getList($filter, ' '.$order.' '.$pager->getLimit($page_no));

        $data['count'] = $count;
        $data['page'] = $page_arr;
        $data['data'] = $list;

        return $data;
    }

    return array();
}
?>
