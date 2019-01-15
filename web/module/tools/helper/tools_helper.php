<?php
/**
 * alltosun.com  小工具函数 helper.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 王德康 (wangdk@alltosun.com) $
 * $Date: 2014-2-7 上午10:23:07 $
 * $Id$
 */

class tools_helper
{
    /**
     * 获取客户端IP地址，因为是代理机器 Request::getclient() 失效
     */
    public static function get_client_ip()
    {
        if (isset($_SERVER['HTTP_X_CLIENTIP'])) {
           return $_SERVER['HTTP_X_CLIENTIP'];
        }

        return '';
    }

    /**
     * 获取url的http code
     * @param unknown_type $url
     */
    public static function get_http_code($url)
    {
        $info = get_headers($url);
        if (!$info) {
            return false;
        }

        if (isset($info[0]) && $info[0]) {
            return substr($info[0], 9, 3);
        }

        return false;
    }

    /**
     * 验证图片的安全性
     * 通过隐藏iframe上传的图片，一般是通过ajax上传图片地址，在这个时候要对图片进行合法性验证
     * 1、验证图片的来源
     * 2、验证图片的真实性
     */
    public static function check_url_image($url)
    {
        // 验证图片来源, host
        if (!self::check_host($url)) {
            return false;
        }

        // 验证图片格式
        if (!self::check_image($url)) {
            return false;
        }

        return true;
    }

    /**
     * 验证host 合法性
     * @param unknown_type $url
     * @return boolean
     */
    public static function check_host($url)
    {
        // 允许的host地址
        $host_arr =  array(
                'live189.alltosun.net'
            );

       // 获取url host部分
      $url_host = parse_url($url, PHP_URL_HOST);

      return in_array($url_host, $host_arr);
    }

    /**
     * 判断远程图片格式是否在允许的范围内
     * @param unknown_type $url
     */
    public static function check_image($url)
    {
        $mime_config = array('image/gif','image/jpeg','image/jpeg','image/jpeg','image/png');
        $image_info = getimagesize($url);

        if (isset($image_info['mime'])) {
            return in_array($image_info['mime'], $mime_config);
        }

        return false;
    }

    /**
     * 获取数据中指定的字段，返回数组
     */
    public static function get_list_ids($list, $field = 'id')
    {
        $ids = array();
        if (is_array($list)) {

            foreach ($list as $k => $v) {
                if ($v[$field]) $ids[] = $v[$field];
            }
            return $ids;
        }

        return false;
    }
    /**
     * 格式化输出，跨域用到
     * @param array $data
     */
    public static function echo_callback_json($data) {
        $callback = tools_helper::Get('callback', '');
        if ($callback) {
            echo $callback."(".json_encode($data).")";
            exit();
        } else {
            exit(json_encode($data));
        }
    }

    /**
     * 输出JSON并中断
     * @param unknown_type $array
     */
    public function echo_json($array)
    {
        echo json_decode($array);
        exit;
    }

    /**
     * Request::get($key, $default);
     * 扩展此方法，增加了默认过滤字符串函数
     * @param string  $key
     * @param unknown_type $default 默认值
     * @param 是否开启过滤字符串函数
     */
    public static function get($key, $default, $flag = 1)
    {
        $value = Request::get($key, $default);

        if (is_string($default) && $flag) {
            $value = AnFilter::filter_string($value);
        }

        return $value;
    }


    /**
     * Request::post($key, $default);
     * 扩展此方法，增加默认过滤字符串函数
     * @param unknown_type $key
     * @param unknown_type $default
     * @param unknown_type $flag
     * @return Ambigous <string, mixed, number, array>
     */
    public static function post($key, $default, $flag = 1) {
        $value = Request::Post($key, $default);

        if (is_string($default) && $flag) {
            $value = AnFilter::filter_string($value);
        }

        return $value;
    }

    /**
     * 判断是否安全referer ajax
     * @return boolean
     */
    public static function is_safe($is_ajax = false)
    {
        if(self::is_ref() !== true) {
            return false;
        }

        if($is_ajax) {
            if(self::is_ajax() !== true) {
                return false;
            }
        }

        return true;
    }

    /**
     * 验证来源
     * @return string|boolean
     */
    private static function is_ref()
    {
        $rf = Request::getRf();
//         暂时不验证
//         if (stripos($rf, '201512awifiprobe.alltosun.net') === false && stripos($rf, 'mac.pzclub.cn') === false) {
//             return '您访问的页面不存在!';
//         }

        return true;
    }

    /**
     * 验证是否ajax请求
     */
    private static function is_ajax()
    {
        if (!Request::isAjax()) {
            return '非法请求!';
        }

        return true;
    }


    /**
     * 判断是否手机访问
     * @return boolean
     */
    public static function is_mobile() {

        if (!isset($_SERVER['HTTP_USER_AGENT'])) {
            return false;
        }

        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $mobile_agents = Array("240x320","acer","acoon","acs-","abacho","ahong","airness","alcatel","amoi","android","anywhereyougo.com","applewebkit/525","applewebkit/532","asus","audio","au-mic","avantogo","becker","benq","bilbo","bird","blackberry","blazer","bleu","cdm-","compal","coolpad","danger","dbtel","dopod","elaine","eric","etouch","fly ","fly_","fly-","go.web","goodaccess","gradiente","grundig","haier","hedy","hitachi","htc","huawei","hutchison","inno","ipad","ipaq","ipod","jbrowser","kddi","kgt","kwc","lenovo","lg ","lg2","lg3","lg4","lg5","lg7","lg8","lg9","lg-","lge-","lge9","longcos","maemo","mercator","meridian","micromax","midp","mini","mitsu","mmm","mmp","mobi","mot-","moto","nec-","netfront","newgen","nexian","nf-browser","nintendo","nitro","nokia","nook","novarra","obigo","palm","panasonic","pantech","philips","phone","pg-","playstation","pocket","pt-","qc-","qtek","rover","sagem","sama","samu","sanyo","samsung","sch-","scooter","sec-","sendo","sgh-","sharp","siemens","sie-","softbank","sony","spice","sprint","spv","symbian","tablet","talkabout","tcl-","teleca","telit","tianyu","tim-","toshiba","tsm","up.browser","utec","utstar","verykool","virgin","vk-","voda","voxtel","vx","wap","wellco","wig browser","wii","windows ce","wireless","xda","xde","zte");
        $is_mobile = false;
        foreach ($mobile_agents as $device) {
            if (stristr($user_agent, $device)) {
                $is_mobile = true;
                break;
            }
        }
        return $is_mobile;
    }



    /**
     * 输出Javascript
     * @param string $str  提示字符串
     * @param bool $flag   错误提示和是正确提示
     */
    public static function  echo_script($str, $flag) {
        if ($flag == 1) {
            $script = <<<SCRIPT
<!doctype html>
<html>
    <head>
        <meta charset="utf-8" />
    </head>
    <body>
        <script>
          parent.window.location.reload();
          parent.window.alert('$str');
        </script>
    </body>
</html>
SCRIPT;
        } else {
            $script = <<<SCRIPT
<!doctype html>
<html>
    <head>
        <meta charset="utf-8" />
    </head>
    <body>
        <script>
          parent.window.alert('$str');
        </script>
    </body>
</html>
SCRIPT;
        }

        echo $script;
        exit;
    }

    /**
     * 获取微博秀的验证码，从而可以实现动态生成组件
     */
    public static function getWeiboShowVerifier($uid)
    {
        $url ="http://api.sina.com.cn/weibo/wb/user_verifier.json?uid=$uid";
        $opts = array(
                'http'=>array(
                        'method'=>"GET",
                        'timeout'=>60,
                )
        );

        $context = stream_context_create($opts);
        $r = file_get_contents($url, 1, $context);

        $r = json_decode($r, true);
        if ($r['result']['status']['code'] == 0) {
            return $r['result']['data']['verifier'];
        } else {
            return '';
        }
    }

    /**
     * 距离单位
     *
     */
    public static function get_distance($distance)
    {
        if ($distance < 10) {
            $result = '10';
            $unit   = '米';
        } else if ($distance < 1500) {
            $distance = (int)$distance;
            $result   = $distance;
            $unit     = '米';
        } else {
            $distance = (int)($distance / 1000);
            $result   = $distance;
            $unit     = '公里';
        }

        return array('distance'=>$result, 'unit'=>$unit);
    }

    /**
     * 打印数组数据
     */
    public static function echo_all($array)
    {
        echo '<pre>';
        print_r($array);
        echo '</pre>';
    }

    /**
     * 获取客户端ip
     * @return string
     */
    public static function get_cli_ip()
    {
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $onlineip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $onlineip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_X_CLIENTIP'])) {
            $onlineip = $_SERVER['HTTP_X_CLIENTIP'];
        } else {
            return '';
        }

        if (strpos($onlineip, ',')) {
            $onlineip_arr = explode(',', $onlineip);
            $onlineip     = $onlineip_arr[0];
        }

        return filter_var($onlineip, FILTER_VALIDATE_IP) !== false ? $onlineip : '';
    }

    /**
     * 保存二进制数据到图片
     * @param unknown_type $image_name
     * @param unknown_type $data
     * @return string|boolean
     */
    public static  function save_binary_image($image_name, $data)
    {
        set_time_limit(0);
        $file_dir  = date('/Y/m/d/');

        $file_name = time().mt_rand(1, 10000);
        $ext_name  = substr($image_name, -4);
        $file_path  = $file_dir.$file_name. $ext_name;

        // 生成目录,擦，上级目录权限也要搞
        // data/www/youhui.live.189.cn/upload/2014/10/14
        if (!file_exists(UPLOAD_PATH.$file_dir)) {
            // 防止命令行调用生成root用户目录
            $old_umask = umask(0);
            mkdir(UPLOAD_PATH.$file_dir, 0755, true);
            umask($old_umask);
        }

        if (file_put_contents(UPLOAD_PATH.$file_path, $data) !== false) {
            return $file_path;
        }

        return false;
    }
}
?>