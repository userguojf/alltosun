<?php
class AnCurl
{
    public static $curlHandle;

    public function __construct() {
        self::init();
    }
    public static function init() {
        if (self::$curlHandle == null) {
            self::$curlHandle = curl_init();
        }
    }

    /**
     * POST方式
     */
    public function post($url, $data)
    {
        if (is_array($data)) {
            $data = http_build_query($data);
        }

        curl_setopt(self::$curlHandle, CURLOPT_URL, $url);
        curl_setopt(self::$curlHandle, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt(self::$curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt(self::$curlHandle, CURLOPT_POST, 1);
        curl_setopt(self::$curlHandle, CURLOPT_POSTFIELDS, $data);
        curl_setopt(self::$curlHandle,  CURLOPT_FOLLOWLOCATION, 1);

        return curl_exec(self::$curlHandle);
    }

    /**
     * get方式
     */
    public function get($url, $data)
    {
        if (is_array($data)) {
            $data = http_build_query($data);
        }

        curl_setopt(self::$curlHandle, CURLOPT_URL, $url);
        curl_setopt(self::$curlHandle, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt(self::$curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt(self::$curlHandle, CURLOPT_HEADER, 0);
        curl_setopt(self::$curlHandle, CURLOPT_HTTPGET, 1);
        curl_setopt(self::$curlHandle,  CURLOPT_FOLLOWLOCATION, 1);

        return curl_exec(self::$curlHandle);
    }

    /**
     * 执行请求
     * @param string $url    请求的url
     * @param string $type   请求类型 支持 get post
     * @param array  $data   传递的参数
     */
    public function exec($url, $type, $data) {
        $type = strtolower($type);
        curl_setopt(self::$curlHandle, CURLOPT_URL, $url);
        $data = $this->encodeData($data);
        if (strtolower($type) == 'get') {
            curl_setopt(self::$curlHandle, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt(self::$curlHandle, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt(self::$curlHandle, CURLOPT_HEADER, 0);
            curl_setopt(self::$curlHandle, CURLOPT_HTTPGET, 1);
            curl_setopt(self::$curlHandle,  CURLOPT_FOLLOWLOCATION, 1);
        } else if ($type == 'post') {
            curl_setopt(self::$curlHandle, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt(self::$curlHandle, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt(self::$curlHandle, CURLOPT_POST, 1);
            curl_setopt(self::$curlHandle, CURLOPT_POSTFIELDS, $data);
            curl_setopt(self::$curlHandle,  CURLOPT_FOLLOWLOCATION, 1);
        } else {
            return false;
        }
        return curl_exec(self::$curlHandle);
    }

    public function encodeData($data) {
        $str = '';
        if (is_array($data)) {
            foreach ($data as $k=>$v) {
                $str =$str."$k=".urlencode($v).'&';
            }
        }
        return $str;
    }

    public function get_curl_error()
    {
        return curl_error(self::$curlHandle);
    }
    public function __destruct(){
//         self::close();
    }

    public static function close() {
        if (self::$curlHandle) {
            curl_close(self::$curlHandle);
        }
    }
}
?>