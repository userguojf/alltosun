<?php
/**
  * alltosun.com 个推探针 glt.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 申小宁 (shenxn@alltosun.com) $
  * $Date: 2017年9月19日 上午11:22:05 $
  * $Id: glt.php 378246 2017-11-02 11:30:58Z shenxn $
  */

// probe_helper::load('func');

/**
 * 加载storage
 */
// probe_helper::load('base', 'probe_storage');

class Action
{
    private $probe_storage = NULL;
    private $key = 'SDJygFYZ';

    public function __construct()
    {
        
    }

    public function bstr2bin($input){
        // 
        if (!is_string($input)) return null; // Sanity check

        $value = unpack('H*', $input);

        $value = str_split($value[1], 1);

        $bin = '';
        foreach ($value as $v){
            $b = str_pad(base_convert($v, 16, 2), 4, '0', STR_PAD_LEFT);
            $bin .= $b;
        }

        return $bin;
    }

    function BinToStr($str){
        $arr = explode(' ', $str);
        foreach($arr as &$v){
            $v = pack("H".strlen(base_convert($v, 2, 16)), base_convert($v, 2, 16));
        }
    
        return join('', $arr);
    }
    function hextostr($hex)
    {
        return preg_replace_callback('/\\\x([0-9a-fA-F]{2})/', function($matches) {
            return chr(hexdec($matches[1]));
        }, $hex);
    }

    function gzdecode ($data) {
        $flags = ord(substr($data, 3, 1));
        $headerlen = 10;
        $extralen = 0;
        $filenamelen = 0;
        if ($flags & 4) {
            $extralen = unpack('v' ,substr($data, 10, 2));
            $extralen = $extralen[1];
            $headerlen += 2 + $extralen;
        }
        if ($flags & 8) // Filename
            $headerlen = strpos($data, chr(0), $headerlen) + 1;
            if ($flags & 16) // Comment
                $headerlen = strpos($data, chr(0), $headerlen) + 1;
                if ($flags & 2) // CRC at end of file
                    $headerlen += 2;
                    $unpacked = @gzinflate(substr($data, $headerlen));
                    if ($unpacked === FALSE)
                        $unpacked = $data;
                        return $unpacked;
    }
    
    /**
     * 数据上传
     */
    public function dataupload()
    {
        $data = file_get_contents("php://input");
        $data = $this->gzdecode($data);//二进制转成16进制
        $data = str_replace('data=', '', $data);
        $data = Security::decrypt($data, $this->key);

        $content = $data['device_list'];

        //处理 存储
        $new_data = array();
        foreach ($content as $v) {
            $info = explode('|',$v);

            try {


            } catch ( Exception $e ) {

            }

        }

        echo '{"action":"put_device_data","result":10000,"des":"成功"}';
        exit();
    }

    /**
     * 设备升级版本
     *
     * @return  String
     */
    public function up_version()
    {
        $data = Request::Post('data', '');

        //开发机暂时不用des加密
        if (!ONDEV) {
            $data = AnDES::share()->decode($data, $this->key);
        }

        //效验数据
        if (!$data) {
            $this->return_fail_message(10001, 'input error');
        }

        $content = json_decode($data,true);

        $mac        = $content['mac'];
        $version    = $content['version'];
        $time       = $content['time'];
        $hash       = $content['hash'];

        if (!$mac || !$version || !$time || !$hash) {
            $this->return_fail_message(10001, 'input error');
        }

        $path = "http://201512awifi.alltosun.net/images/test.bin";
        $hash = md5_file($path);

        $data = array(
            "version" => "1.1.2",
            "hash"=> $hash,
            "path"=> $path
        );

        return $this->return_success_message(10000,'success',1,$data);
    }

    /**
     * 设备升级配置文件
     *
     * @return  String
     */
    public function up_config()
    {
        
        $data = file_get_contents('php://input');
        $data = trim(Request::Post('data', ''));

        $data = str_replace(' ', '+', $data);

        //效验数据
        if (!$data) {
            $this->return_fail_message(10001, 'input error');
        }

//         $data = urldecode($data);

        $data = Security::decrypt($data, $this->key);

        //效验数据
        if (!$data) {
            $this->return_fail_message(10002, 'des error');
        }

        $content = json_decode($data,true);

        $mac        = $content['mac'];
        $version    = $content['version'];
        $time       = $content['time'];

        if (!$mac || !$version || !$time) {
            $this->return_fail_message(10003, 'params error');
        }

        $data = array(
            "tz_server_url" => "http://201512awifi.alltosun.net/probe/lierda/dataupload",
            "tz_server_port"=> 3600,
            "tz_upload_frequency"=> 5,
            "tz_sweep_spacing"=> 200,
            "tz_rssi_threshold"=> 120
        );

        return $this->return_success_message(10000,'success',1,$data);
    }

    private function return_fail_message($code = 0, $message = '')
    {
        echo json_encode(array(
            'code'     => $code,
            'message'    => $message
        ));
        exit;
    }

    private function return_success_message($code = 0, $message = '',$is_upload = 0, $data = array())
    {
        echo json_encode(array(
            'code'       => $code,
            'message'    => $message,
            'is_upload'  => $is_upload,
            'data'       => $data
        ),JSON_UNESCAPED_SLASHES);
        exit;
    }

    public function test()
    {
        $str = 'cGZ+UiED/q0G6x8O2aTb4kzHOz8xuBJshKPiTepJMlmXHd0fZMeLXo3CZEinbAeJiP/vJKMs5Tec3y+5yAUYk0SKQzRLzrjd2HaV1kKUvCUiSEBcM+nJdv3qdYmhQwm20MaJQNaYc0CMG08kwcTswzQWa4bLCalDIU6BvX3yC29yS0htUYJu3eVuvWf6nPY775ItCWOzcDluqPdL6RFNebBWclhtEx1wuDydWxhdKiIl8iI8xwK7/vVqL2fe7wVjkOUkZ/QiWGaY1ydXxhVueAdhqWYzx8CLuuQ7MzTojhlWqmhcUuvkfBILRi7GlHPU3Ljh3Ho/OnwEa732N2INW7vE3cyQfSD7v/oBQU/K0buOHJukgcnWFQImxzhg0oTv+5FJM8euftb5HRPE1dMX4nYVCSTw3Num1lzWBpjTv9B2R2zvqZ/86wFRuYXf1hSQxM1xoHVLM/YBHH9+Iu5d/FrWKBdSAf6r/iZRtuukSG2vqsa6FHrAh9WBqHRTnXIw';
        p(Security::decrypt($str, $this->key));
    }
}