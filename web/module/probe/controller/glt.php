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
probe_helper::load('func');

class Action
{
    private $probe_storage = NULL;
    private $key           = 'SDJygFYZ';
    private $token         = '3whJ7fKgULniiLPC';

    public function __construct()
    {

    }

    public function gzdecode ($data) {
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
        //接收参数 主要线上环境需要解压缩
        if (ONDEV) {
            $data = Request::Post('data','');
        } else {
            $data = file_get_contents("php://input");
            $data = $this->gzdecode($data);//二进制转成16进制
            $data = str_replace('data=', '', $data);
        }

        if (Request::Get('test', 0)) {
            $data = Request::Post('data','');
        }

        //des解密 data上传接口 key是会变化的
        $data = Security::decrypt($data, $this->key);

        $data = trim($data,"\0");

        file_put_contents('/data/log/php/test'.date('Ymd').'aa.log', $data."\n", FILE_APPEND);

        $data = json_decode($data,true);

        if (empty($data['action'])) {
            $this->return_message(10001, 'action不存在');
        }

        if ($data['action'] == 'put_device_data') {
            if (empty($data['action']) || ($this->token != $data['access_token'])) {
                $this->return_message(10002,'access_token error', $data['action']);
            }

            try {
                // 存储 mz5a4dbf59d97f3 mz5a4dbf59f1915 b05a4dbf59de330
                $probe_list = ['mz5a4dbf59f1915','mz5a4dbf59d97f3','b05a4dbf59de330','7j5a4dbf59ec87e','oo5a4dbf59db427'];

                if (in_array($data['hwid'], $probe_list)) {
                    _model('probe_log_res')->create(['hwid' => $data['hwid'], 'data' => json_encode($data)]);
                }

                //记录指定设备的上报信息到数据库
                if (in_array($data['hwid'], ['df5a4dbf59ec49a'])) {
                    $this->record_report($data);
                }

                device('glt')->storage($data);
            } catch (Exception $e) {
                probe_helper::write_log('glt', $e -> getMessage());
            }

            $this->return_message(10000,'成功',$data['action']);

        } else if ($data['action'] == 'put_status') {
            try {
                // 存储
                $result = device('glt')->put_status($data);
            } catch (Exception $e) {
                probe_helper::write_log('glt', $e -> getMessage());
            }

            $data = Security::encrypt(json_encode($result,JSON_UNESCAPED_SLASHES), $this->key);
            $a =   gzencode($data);

            if (Request::Get('test', 0)) {
                p($result);
                exit();
            }

            echo $a;
            exit();
        } else {
            $this->return_message(10001, 'action不存在');
        }
    }

    public function put_status()
    {

    }

    /**
     * 设备升级版本
     *
     * @return  String
     */
    public function up_version()
    {

    }

    /**
     * 设备升级配置文件
     *
     * @return  String
     */
    public function up_config()
    {
    }

    private function return_message($code = 0, $message = '', $action = '')
    {
        echo '{"action":"'.$action.'","result":'.$code.',"des":"'.$message.'"}';
        exit();
    }

    public function test()
    {
        $str = array('data' =>'cGZ+UiED/q0G6x8O2aTb4lZNOF+IcxRu6MrsUpTKB4t9jirRMkrz7/BbN5oUSYpz4F+qti+D2Z3CjWBwg4u0ZGmjAWrXnSaDe/ondm42t3WTc5rL1zmeMPyLldfojmHiKKC6MYZyFnBxamCUcMJ3xGZYj7pgk84by5SuIAFXho6Gwdox+saM0keBrlew6Pk0c3hev/vqHRgCWI9Y357kZqIS/6cjhAN8WKuVPI/X8Z6oV4fL85jjTZi4ZifEyd8RSxLa5D0yj83i0C+7KUm2BkWJseSVBEBx4wKgI31jCbD5LxFrHl9TWWcW+3M8fcDimzqaDX4nMdKZmSqHrkgnN+MpNhxxr4mwZZ/cetpKLm/2V47999J7OzCgcqnUums3W8thbVNnI8Rv6GMgQUPYpHU9u2cvNHURJq6PyFxBJHB1wSsa+1169tDTUWpbxCwxhaMaT0n6nCHH+GqVV4o/gqV4CoxmspxXMoP1eE2fruiR6ywogRGcYIvWAv7mriXjVsxqDI8KUm7fsDNWPitg0R9Im71yTAnrjblOX9cGs1QTkaQrcfnZ03nUgZCYDAlJ///twtTBfA9kG52Lsq8nsuwByrjRRuCB+FX6TsghQu5bIOsBlWB0ZQaTc3ZfE5XEdG0uUy9Oy4vPqgAWCN+HSdZH6cMfSlP0zdUowEU2u3E=');
$hwid = Request::Get('hwid', '');
$mac = Request::Get('mac', '');
        $result = curl_post(SITE_URL.'/probe/glt/dataupload?test=1&hwid='.$hwid.'&mac='.$mac, $str);

        print_r($result);
    }

    /**
     * 生成aes后的文件
     * 生成后的文件大小与源文件一致
     */
    public function generate_aes_file()
    {
        $v = Request::Get('v', '2.1.38');

        $file_path = SITE_URL.'/images/data/fw-server-7688-559_8_64_7688-yundongli-qiandaoqi-'.$v.'.tar' ;
        $iv = "a80e151735d95327";
        $aes_key = 'SDJygFYZ';

        $a = aes_encode($aes_key, file_get_contents($file_path),$iv);

        $r = file_put_contents('/data/www/wifi/web/images/data/fw-server-7688-559_8_64_7688-yundongli-qiandaoqi-'.$v.'.aes.tar', $a);

        echo '新版本生成成功<br />版本加密后大小为'. $r.'字节';
        exit();
    }

    /**
     * 记录数据上报
     */
    private function record_report($data)
    {
        $new_data = array(
                'dev' => $data['hwid'],
                'source_data' => json_encode($data),
                'probe_data' => '',
                'report_time'   => date('Y-m-d H:i:s'),
        );

        if (empty($data['device_list'])) {
            $rows = array();
        } else {
            $rows     = $data['device_list'];
        }

        $probe_data = '';
        foreach ($rows as $k => $v) {
            $cols = explode('|', $v);

            if ( empty($cols[0])  || empty($cols[2]) ) {
                continue;
            } else {
                $probe_data .= 'mac:'.$cols[0].',rssi:'.$cols[1].',time:'.$cols[2].';';
            }
        }

        if ($probe_data) {
            $new_data['probe_data'] = trim($probe_data, ';');
        }

        _model('probe_report')->create($new_data);
    }

    /**
     * 记录上报
     */
    public function record_report_test()
    {
        $data = array(
                'hwid' => 'df5a4dbf59ec49a',
                'device_list' => array(
                        'mac123|-333|'.time(),
                        'mac345|-334|'.time(),
                ),
        );
        $this->record_report($data);
    }
}