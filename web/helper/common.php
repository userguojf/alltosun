<?php

/**
 * alltosun.com 公共函数 common.php
 * ============================================================================
 * 版权所有 (C) 2009-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: Shenxn 申小宁 (shenxn@alltosun.com) $
 * $Date: Jan 22, 2014 2:49:35 PM $
 * $Id: common.php 106433 2014-02-14 08:12:35Z shenxn $
 */

/**
 * 倒计时
 * @param $end_time
 */
function time_count_down($end_time) {
    $time = strtotime($end_time) - time();
    if ($time > 0) {
        $day = $time / 60 * 60 * 24;
        $hour = $time / 60 * 60;
        $min  = $time / 60;
        return "{$day}天{$hour}时{$min}分";
    } else {
        return '00天00时00分';
    }
}

/**
 * 设置$search_filter 数组设置默认值
 * @param 默认值规则 &$default_search_filter
 * @return array $search_filter
 * @example
 * $default_value  = array('page_no'=>1, 'is_over'=>-1, 'start_time'=>'order_desc', 'id'=>'order_desc');
 */
function set_search_filter_default_value($search_filter, $default_search_filter)
{
    foreach ($default_search_filter as $k => $v) {
        if (!isset($search_filter[$k])) {
            $search_filter[$k] = $v;
        }
    }

    return $search_filter;
}


/**
 * 封装分页操作，后台获取数据常用,自动分配分页
 * @param string $table_name 表名
 * @param array $filter 条件数组
 * @param string $order 排序
 * @param int $page_no  页码
 * @param int $per_page 每页数量
 * @return array
 */
function get_data_list($table_name, $filter = array( 1 => 1), $order = ' ORDER BY `id` DESC ', $page_no = 1, $per_page = 30)
{
    $count = _model($table_name)->getTotal($filter, $order);

    if ($count) {
        $pager = new Pager($per_page);
        if ($pager->generate($count)) {
            Response::assign('pager', $pager);
        }

        Response::assign('count', $count);
        $list = _model($table_name)->getList($filter, ' '.$order.' '.$pager->getLimit($page_no));
        return $list;
    }

    return array();
}

/**
 * 组装url地址
 * @param unknown $search_filter
 * @param string $key
 * @param string $value
 * @param string $url
 * @return string
 */
function compile_url($search_filter = array(),$key = '',$value = '',$url ='')
{

    if ($key) {
        $search_filter[$key] = $value;
    }

    $return_url = '';
    foreach ($search_filter as $k=>$v) {
        $return_url .= '&'."search_filter[{$k}]".'='.$v;
    }

    return AnUrl($url.'?'.$return_url);
}

/**
 * 根据res_name 和 res_id获取信息
 * @param string $res_name
 * @param int $res_id
 * @param int $field
 * @return array or string
 */
function get_resource_info($res_name, $res_id, $field='')
{
    $res_info = _uri($res_name, $res_id);
    if (empty($res_info)) return $res_info;

    if (!$field) return $res_info;

    return $res_info[$field];
}

/**
 * 等长截取字符串
 * @param int 1 str 被截取的字符串
 * @param int 2 str 剩余的字符串
 * @param int 3 str 截取的长度
 * @param int 0 array
 * @return string|int|array
 * @author shenxn
 */
function sub_str($str, $len = 10 , $type = 1 ,$fix = '')
{
    $mystr = array();

    //字符串长度小于截取长度
    if (strlen($str) <= $len) {
        $mystr['sub'] =  $str;
        $mystr['str'] =  0;
        $mystr['num'] = strlen($str);

        if ($type == 1) {
            return $mystr['sub'];
        } elseif ($type == 2) {
            return 0;
        } elseif ($type == 3) {
            return strlen($str);
        } else {
            return $mystr;
        }
    }

    $returnStr = '';
    $i = 0;
    $n = 0;
    $mystr = array();

    //进入遍历  计算个数
    while (($n < $len) && ($i <= strlen($str))) {
        $tempStr = substr($str, $i, 1);
        $ascnum = ord($tempStr);//得到字符串中第$i位字符的ascii码

        //如果ASCII位高与224，
        if ($ascnum >= 224) {
            $returnStr .= substr($str, $i, 3); //根据UTF-8编码规范，将3个连续的字符计为单个字符
            $i += 3; //实际Byte计为3
            $n++; //字串长度计1

            //如果ASCII位高与192，
        } elseif ($ascnum >= 192) {
            $returnStr .= substr($str, $i, 2); //根据UTF-8编码规范，将2个连续的字符计为单个字符
            $i += 2; //实际Byte计为2
            $n++; //字串长度计1

            //如果是大写字母，
        } elseif ($ascnum >= 65 && $ascnum <= 90) {
            $returnStr .= substr($str, $i, 1);
            $i++; //实际的Byte数仍计1个
            $n++; //但考虑整体美观，大写字母计成一个高位字符

            //其他情况下，包括小写字母和半角标点符号，
        } else {
            $returnStr .= substr($str, $i, 1);
            $i++; //实际的Byte数计1个
            $n += 0.5; //小写字母和半角标点等与半个高位字符宽...
        }
    }

    $str = substr($str, $i);
    $mystr['sub'] = $returnStr;
    $mystr['str'] = $str;
    $mystr['num'] = $i;

    //判断是否有后缀
    if ($n < $len) {
        $fix ="";
    } else {
        $fix = $fix;
    }

    //返回值
    if ($type == 1) {
        return $returnStr.$fix;
    } elseif ($type == 2) {
        return $str;
    } elseif ($type == 3) {
        return $i;
    } else {
        return $mystr;
    }
}

/**
 * 是否微信端
 */
function is_weixin()
{
    if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
        return true;
    }

    return false;
}

/**
 * 是否在移动端上
 * @return boolean
 */
function is_mobile()
{
    $agent = $_SERVER['HTTP_USER_AGENT'];
    if (preg_match("/(iPhone|iPod|Android|ios|iPad|mobile)/i", $agent)) {
        return TRUE;
    }

    return FALSE;
}

/**
 *调试
 *@author shenxn
 */
function p(){
    $args=func_get_args();  //获取多个参数

    echo '<div style="width:100%;text-align:left"><pre>';
    //多个参数循环输出
    foreach($args as $arg) {
        if (is_array($arg)) {
            print_r($arg);
            echo '<br>';
        } else if(is_string($arg)) {
            echo $arg.'<br>';
        } else {
            var_dump($arg);
            echo '<br>';
        }
    }
    echo '</pre></div>';
}
/**
 * 获取文件的附件类型（用于`attachment`.`type`）
 * @param $file_path 文件路径
 * @return int 附件类型（1-图片；2-视频）
 * @author gaojj@alltosun.com
 */
function get_attachment_type($file_path)
{
    $path_info = pathinfo($file_path);
    $attachment_type = 0;
    if (in_array(strtolower($path_info['extension']), Config::get('allow_image_type'))) $attachment_type = 1;
    if (in_array(strtolower($path_info['extension']), Config::get('allow_flash_type'))) $attachment_type = 2;

    //var_dump($path_info, $attachment_type);
    return $attachment_type;
}
/**
 * 字符串截取，支持中文和其他编码
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断显示字符
 * @return string
 * @author shenxn or thinkPHP
 */
function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true) {
    if(function_exists("mb_substr"))
        $slice = mb_substr($str, $start, $length, $charset);
    elseif(function_exists('iconv_substr')) {
        $slice = iconv_substr($str,$start,$length,$charset);
        if(false === $slice) {
            $slice = '';
        }
    } else {
        $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("",array_slice($match[0], $start, $length));
    }
    return $suffix ? $slice.'...' : $slice;
}


/**
 * 获取某一个字段的值
 * @param str $table
 * @param array|int $filter
 * @param str $field
 * @return multitype:
 * @todo 用于查询不常用的table，常用模块请在helper自行实现
 */
function get_field_info($table,$filter,$field = '')
{
    if (!$table || !$filter) return false;

    !is_array($filter) ? $filter = array('id' => $filter) :'';

    if ($field) {
        return _uri($table,$filter,$field);
    } else {
        return _uri($table,$filter);
    }
}

/**
 * 使用GET方式请求接口，适用于sso方式登录
 * @param string $url
 */
function curl_get($url,$params = array())
{
    if ($params) {
        $par = http_build_query($params);
        if (strpos($url, '?')) {
            $par = '&'.$par;
        } else {
            $par = '?'.$par;
        }

        $url = $url.$par;
    }
    $ch = curl_init();

    if (Request::Get('test','') == 'shenxn') {
        echo $url;
    }

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

/**
 * curl_post请求
 * @param string $url
 * @param string $postdata
 * @param unknown $options
 * @return unknown
 */
function curl_post($url='', $postdata='', $options=array()){
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    if (!empty($options)){
        curl_setopt_array($ch, $options);
    }

    $data = curl_exec($ch);

    curl_close($ch);
    return $data;
}

/**
 * 浏览器友好的变量输出
 * @param mixed $var 变量
 * @param boolean $echo 是否输出 默认为True 如果为false 则返回输出字符串
 * @param string $label 标签 默认为空
 * @param boolean $strict 是否严谨 默认为true
 * @return void|string
 */
function dump($var, $echo=true, $label=null, $strict=true) {
    $label = ($label === null) ? '' : rtrim($label) . ' ';
    if (!$strict) {
        if (ini_get('html_errors')) {
            $output = print_r($var, true);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        } else {
            $output = $label . print_r($var, true);
        }
    } else {
        ob_start();
        var_dump($var);
        $output = ob_get_clean();
        if (!extension_loaded('xdebug')) {
            $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        }
    }
    if ($echo) {
        echo($output);
        return null;
    }else
        return $output;
}

/**
 * 标记转标签
 * @param str $string
 * @param int $indent
 * @return string
 */
function smarty_modifier_tags($string,$fix = '',$tag = 'dd',$num = 0)
{
    if($string=="") return "";

    $fix = $fix?$fix:chr(10);

    if ($num) {
        $arr = explode($fix, trim($string,$fix),$num);
    } else {
        $arr = explode($fix, trim($string,$fix));
    }

    $html = '';
    foreach ($arr as $k=>$v) {
        if ($v) {
            $html .= '<'.$tag.'>'.$v.'</'.$tag.'>';
        }

        if ($num && $k == $num) {
            break;
        }
    }

    return $html;
}

function get_extension($file)
{
    return substr($file, strrpos($file, '.')+1);
}

function substr_utf8($string,$start,$length) {

    $chars = $string;
    $i=0;
    $m=0;
    $n=0;

    do{
        if (preg_match("/[0-9a-zA-Z]/", $chars[$i])){//纯英文
            $m++;
        } else {
            $n++;
        }

        $k = $n/3+$m/2;
        $l = $n/3+$m;
        $i++;
    } while($k < $length);

    $str1 = mb_substr($string,$start,$l,'utf-8');//保证不会出现乱码

    return $str1;
}

function cut_str($sourcestr, $cutlength = 80, $etc = '...')
{
    $returnstr = '';
    $i = 0;
    $n = 0.0;
    $str_length = strlen($sourcestr); //字符串的字节数

    while ( ($n<$cutlength) and ($i<$str_length) ) {
        $temp_str = substr($sourcestr, $i, 1);
        $ascnum = ord($temp_str); //得到字符串中第$i位字符的ASCII码
        if ( $ascnum >= 252) {
            $returnstr = $returnstr . substr($sourcestr, $i, 6); //根据UTF-8编码规范，将6个连续的字符计为单个字符
            $i = $i + 6; //实际Byte计为6
            $n++; //字串长度计1
        } elseif ( $ascnum >= 248 ) {
            $returnstr = $returnstr . substr($sourcestr, $i, 5); //根据UTF-8编码规范，将5个连续的字符计为单个字符
            $i = $i + 5; //实际Byte计为5
            $n++; //字串长度计1
        } elseif ( $ascnum >= 240 ) {
            $returnstr = $returnstr . substr($sourcestr, $i, 4); //根据UTF-8编码规范，将4个连续的字符计为单个字符
            $i = $i + 4; //实际Byte计为4
            $n++; //字串长度计1
        } elseif ( $ascnum >= 224 ) {
            $returnstr = $returnstr . substr($sourcestr, $i, 3); //根据UTF-8编码规范，将3个连续的字符计为单个字符
            $i = $i + 3 ; //实际Byte计为3
            $n++; //字串长度计1
        } elseif ( $ascnum >= 192 ) {
            $returnstr = $returnstr . substr($sourcestr, $i, 2); //根据UTF-8编码规范，将2个连续的字符计为单个字符
            $i = $i + 2; //实际Byte计为2
            $n++; //字串长度计1
        } elseif ( $ascnum>=65 and $ascnum<=90 and $ascnum!=73) {
            $returnstr = $returnstr . substr($sourcestr, $i, 1);
            $i = $i + 1; //实际的Byte数仍计1个
            $n++; //但考虑整体美观，大写字母计成一个高位字符
        } elseif ( !(array_search($ascnum, array(37, 38, 64, 109 ,119)) === FALSE) ) {
            $returnstr = $returnstr . substr($sourcestr, $i, 1);
            $i = $i + 1; //实际的Byte数仍计1个
            $n++; //但考虑整体美观，这些字条计成一个高位字符
        } else {
            $returnstr = $returnstr . substr($sourcestr, $i, 1);
            $i = $i + 1; //实际的Byte数计1个
            $n = $n + 0.5; //其余的小写字母和半角标点等与半个高位字符宽...
        }
    }

    if ( $i < $str_length ) {
        $returnstr = $returnstr . $etc; //超过长度时在尾处加上省略号
    }
    return $returnstr;
}

/**
 * 格式化数字
 */
function format_number($num)
{

    if (strpos($num, '.') == false) {
        return $num;
    }

    return round($num, 2);
}

function array_unset($arr,$str){

    if (!is_array($arr) || empty($arr) || !$str) {
        return array();
    }

    foreach ($arr as $k=>$v){
        if($v == $str){
            unset($arr[$k]);
        }
    }

    return $arr;
}

/**
 * 二维数组合并
 * @param  $array  array(array(1,2),array(2,3))
 * @return array(1,2,3)
 * @author shenxn
 */
function  array_or_merge($array)
{
    $list = array();

    foreach ($array as $k => $v) {
        if (empty($v)) {
            unset($array[$k]);
        } else {
            $list = array_merge($list, $v);
        }
    }

    array_unique($list);
    return $list;
}

function replace_link($link)
{
    //mphone=###&cusid=###
    if (strpos($link, '###') === false) {
        return $link;
    }

    //jiac
    $user_phone       = user_helper::get_user_info('', 'phone');
    $business_hall_id = business_hall_helper::get_business_hall_id();

    if ($user_phone) {
        $link = str_replace('mphone=###', 'mphone='.base64_encode($user_phone), $link);

        $open_id = 'wifi_'.$user_phone;
        $link = str_replace('openid=###', 'openid='.$open_id, $link);
    }


    if ($business_hall_id) {
        $link = str_replace('cusid=###', 'cusid='.base64_encode($business_hall_id), $link);
    }

    //

    return $link;
}

function des($str , $key)
{
    $size = mcrypt_get_block_size ( MCRYPT_DES, MCRYPT_MODE_CBC );

    $pad = $size - (strlen ( $str ) % $size);
    $str =  $str . str_repeat ( chr ( $pad ), $pad );

    $data = @mcrypt_cbc(MCRYPT_DES, $key, $str, MCRYPT_ENCRYPT, 1);

    return base64_encode($data);
}

/**
 * 替换手机号的中间四位为****
 * @param string $phone_number
 * @return boolean
 */
function replace_phone_number($phone_number, $length=11)
{
    if (!$phone_number) {
        return false;
    }

    //判断手机号长度
    if (strlen($phone_number) != $length){
        return false;
    }
    //替换
    return substr_replace($phone_number, '****', 3,4);
}
/**
 * 验证是否授权  add guojf
 */
function check_qydev_auth($return_url)
{

    $member_id      = member_helper::get_member_id();

    if (is_weixin()) {
        if (!$member_id){
            Response::redirect(AnUrl("qydev/auth?return_url=".urlencode($return_url)));
            Response::flush();
            exit;
        }
    }
}

/**
 * 调试模式打印， 为节省资源调用， 使用后非必须的情况下必须删除调用  add wangjf
 * @param unknown $var
 */
function debug_p($var, $is_exit=false)
{
    $debug = tools_helper::Get('debug', 0);
    $action = tools_helper::Get('action', '');

    if ($debug && $action == 'dump') {
        p($var);

        if ($is_exit) {
            exit();
        }

    }
}

function _mongo($db = 'screen' , $collection)
{
    if (!$collection) {
        throw new AnException('mongo Error.', "mongo collection name '{$collection}' is invalid!");
    }

    //获取mogondb的配置集合
    $mongo_conf = Config::get('mongo_conf');

    $mongo_op = !empty($mongo_conf[$db])?$mongo_conf[$db]:'mongo';

    $mongo_link = Config::get($mongo_op);

    if (!$mongo_link) {
        throw new AnException('mongo Error.', "mongo link is invalid!");
    }

    try {
        $mongodb_client = (new MongoDB\Client($mongo_link[0]));
    } catch (AnException $e) {
       echo $e;
    }

    return $mongodb_client->$db->$collection;
}

/**
 * mongodb拼装条件
 * @param array $filter
 * return array
 */

function get_mongodb_filter($filter)
{
    $new_filter = array();

    if (isset($filter[1])) {
        $filter = array();
    }

    foreach ($filter as $k => $v) {
        if (is_array($v)) {

            $new_filter[trim($k)] = array('$in' => $v);

        } else {

            if (strpos($v, 'e') === false) {
                $v = is_numeric($v) ? (int)$v : $v;
            }

            if ( strpos($k, '<=') ) {
                $new_filter[trim(str_replace('<=', '', $k))]['$lte'] = $v;
            } else if ( strpos($k, '>=') ) {
                $new_filter[trim(str_replace('>=', '', $k))]['$gte'] = $v;
            } else if (strpos($k, '<')) {
                $new_filter[trim(str_replace('<', '', $k))]['$lt'] = $v;
            } else if (strpos($k, '>')) {
                $new_filter[trim(str_replace('>', '', $k))]['$gt'] = $v;
            } else {
                $new_filter[trim($k)] = $v;
            }
        }

    }

    return $new_filter;
}

function get_mongodb_last_id($mongo_obj, $filter=array())
{
    $id = $mongo_obj->findOne($filter, ['projection'=>['id'=>1] , 'sort'=>['_id'=>-1]]);

    return $id['id'];
}

/**
 * php解压缩
 * @param 二进制数据包 $data
 * @return 字符串
 */
function AnGzdecode ($data) {
    $flags = ord(substr($data, 3, 1));
    $headerlen = 10;
    $extralen = 0;
    $filenamelen = 0;
    if ($flags & 4) {
        $extralen = unpack('v' ,substr($data, 10, 2));
        $extralen = $extralen[1];
        $headerlen += 2 + $extralen;
    }

    if ($flags & 8) {
        $headerlen = strpos($data, chr(0), $headerlen) + 1;
        if ($flags & 16) {
            $headerlen = strpos($data, chr(0), $headerlen) + 1;

            if ($flags & 2){
                $headerlen += 2;
                $unpacked = @gzinflate(substr($data, $headerlen));
                if ($unpacked === FALSE) {
                    $unpacked = $data;
                    return $unpacked;
                }
            }
        }
    }
}

/**
 * AES CBC加密
 * @param $key
 * @param $str
 * @param null $iv
 * @return string
 */
function aes_encode($key, $str, $iv = null)
{
    if (empty($iv) || strlen($iv) < 16) {
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), MCRYPT_RAND);
    }

    //php5.6前使用
    //return mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $str, MCRYPT_MODE_CBC, $iv);
    return  openssl_encrypt($str, "AES-128-CBC", $key, OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING, $iv);
}

/**
 * 数组条件转换where语句
 * @param unknown $filter
 * @return string
 */
function to_where_sql($filter)
{
    if (!$filter) {
        return '';
    }

    $where = '';

    if (is_array($filter)) {

        foreach ($filter as $k => $v) {

            if ( !$where ) {
                $where = " WHERE ";
            }

            if ( strpos($k, '<') || strpos($k, '>') ) {
                $where .= " {$k}{$v} AND";
            } else {
                $where .= " {$k}={$v} AND";
            }

        }

        $where = rtrim($where, 'AND');
    } else {

        if ( !$where ) {
            $where = " WHERE ";
        }

        $where .= "id={$filter} ";
    }

    return $where;
}

/**
 * 获取用户IP
 * @return unknown
 */
function get_user_ip(){
    //获取用户IP
    if(!empty($_SERVER['HTTP_CLIENT_IP'])){

        //用户IP
        $ip = $_SERVER['HTTP_CLIENT_IP'];

    } else if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {

        //代理IP
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];

    } else {

        //服务器IP
        $ip = !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    }
    return $ip;
}

/**
 * 根据给定的坐标列表获取坐标中心位置
 * @param unknowtype
 * @return return_type
 * @author 王敬飞 (wangjf@alltosun.com)
 * @date 2018年4月24日上午11:11:45
 */
function get_center_coord($coord_list)
{
    if (!$coord_list) {
        return false;
    }

    $total = count($coord_list);
    $x1 = 0;
    $y1 = 0;
    $z1 = 0;

    foreach ($coord_list as $k => $v)
    {
        $pi = 3.1415;
        $lat = $v['lat'] * $pi / 180;
        $lng = $v['lng'] * $pi / 180;
        $x = cos($lat) * cos($lng);
        $y = cos($lat) * sin($lng);
        $z = sin($lat);
        $x1 += $x;
        $y1 += $y;
        $z1 += $z;
    }
    $x1 = $x1 / $total;
    $y1 = $y1 / $total;
    $z1 = $z1 / $total;

    $lng = atan2($y1, $x1);
    $hyp = sqrt($x1 * $x1 + $y1 * $y1);
    $lat = atan2($z1, $hyp);

    $lat = $lat * 180 / $pi;
    $lng = $lng * 180 / $pi;

    return array('lat' => $lat, 'lng' => $lng);
}
?>