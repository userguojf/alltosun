<?php

// load trait storage
probe_helper::load('storage', 'trait');
probe_helper::load('device', 'interface');

/**
 * 子午线
 *
 * @author  wangl
 */
class glt implements device
{
    // use trait
    use storage;

    /**
     * 探测的mac
     *
     * @var Array
     */
    private $macs = array();

    /**
     * 存储
     *
     * @author  wangl
     */
    public function storage($data = array())
    {
        try {
            // 获取设备提交的数据
            if ( empty($this -> macs) ) {
                $this -> macs = $this -> get($data);
            }

            // 写入数据
            $this->write($this->macs);
        } catch (Exception $e) {
            probe_helper::write_log('meridian', $e -> getMessage());
        }
    }

    public function put_status($data)
    {
        $file_path = SITE_URL.'/images/data/fw-server-7688-559_8_64_7688-yundongli-qiandaoqi-2.1.34.aes.tar';
        $old_file_path = SITE_URL.'/images/data/fw-server-7688-559_8_64_7688-yundongli-qiandaoqi-2.1.34.tar';
        $iv = "a80e151735d95327";
        $aes_key = 'SDJygFYZ';

        $hwid = Request::Get('hwid','');

        if ($hwid) {
            $data['hwid'] = $hwid;
        }

        $ibeacon = array(
            'uuid' => 'FDA50693-A4E2-4FB1-AFCF-C6EB07647825',
            'major' => '10174',
            'minor'=> '33284',
            'uuid2'=> 'FDA50693-A4E2-4FB1-AFCF-C6EB07647825',
            'major2'=> '10174',
            'minor2'=> '33284'
        );

        if ($data['hwid'] == 'ys5a1fc323cf690' || $data['hwid'] == '0qgcgqbku85mccv' || $data['hwid'] == 'v55a1fc323cf746') {
            $ibeacon['deviceNo'] = 'DX180118002';
        } else if ($data['hwid'] == 'wl5a4dbf59ebe20') {
            $ibeacon['major'] = '10173';
            $ibeacon['minor'] = '47042';
            $ibeacon['deviceNo'] = 'DX180515280';
            $ibeacon['major2'] = '10173';
            $ibeacon['minor2'] = '47042';
        } else {
            $ibeacon['deviceNo'] = 'DX1801180024';
        }
//wl5a4dbf59ebe20
        $mac = Request::Get('mac','');

        if ($mac) {
            $data['mac'] = $mac;
        }

        if ($data['mac'] == '18:62:2c:01:47:fc') {
            $ibeacon['major'] = '10018';
            $ibeacon['minor'] = '16082';
            $ibeacon['major2'] = '10018';
            $ibeacon['minor2'] = '16082';
            $ibeacon['deviceNo'] = 'DX050200012';
        }
//b05a4dbf59de330


//         if ($data['mac'] == '18:62:2c:01:47:fc') {
//             $ibeacon['minor'] = '51879';
//             $ibeacon['minor2'] = '51879';
//             $ibeacon['deviceNo'] = 'DX180118002';
//         }


        $result = array(
            'action'  => 'put_status',
            'hwid'    => $data['hwid'],
            'working' => '1',
            'unit'    => '540',
            'interval' => '130',
            'rssi'    => '-80',
            'upgrade' => array(
                'hw_ver'  => $data['hw_ver'],
                'sw_ver'  => '2.1.34',
                'key_size' => strlen($aes_key),
                'aes_key'  => $aes_key,
                'file_size' => '7792640',
                'filename'  => 'fw-server-7688-559_8_64_7688-yundongli-qiandaoqi-2.1.34.tar',
                'url'       => $file_path,
                'md5'      => md5_file($old_file_path)
            ),
            'ibeacon' => $ibeacon,
            'urls'    => array('dataserver_url2' => 'http://mac.pzclub.cn/probe/glt/dataupload','token_url2' => '3whJ7fKgULniiLPC','pass_url2' => 'SDJygFYZ')
        );

        //mz5a4dbf59d97f3 
        if (   $data['hwid'] == '7f5a4dbf59e6427' 
            || $data['hwid'] =='7t5a4dbf59f2813' 
            || $data['hwid'] == 'la59a63ff4026fa' 
            || $data['hwid'] =='mz5a4dbf59f1915' 
            || $data['hwid'] == 'mz5a4dbf59d97f3'
            || $data['hwid'] == 'df5a4dbf59ec49a'
            ) {
            $result = array(
                'action'  => 'put_status',
                'hwid'    => $data['hwid'],
                'working' => '1',
                'unit'    => '540',
                'interval' => '130',
                'rssi'    => '-80',
                'upgrade' => array(
                    'hw_ver'  => $data['hw_ver'],
                    'sw_ver'  => '2.1.38',
                    'key_size' => strlen($aes_key),
                    'aes_key'  => $aes_key,
                    'file_size' => '7792640',
                    'filename'  => 'fw-server-7688-559_8_64_7688-yundongli-qiandaoqi-2.1.38.tar',
                    'url'       => SITE_URL.'/images/data/fw-server-7688-559_8_64_7688-yundongli-qiandaoqi-2.1.38.aes.tar',
                    'md5'      => md5_file(SITE_URL.'/images/data/fw-server-7688-559_8_64_7688-yundongli-qiandaoqi-2.1.38.tar')
                ),
                'ibeacon' => $ibeacon,
                'urls'    => array('dataserver_url2' => 'http://mac.pzclub.cn/probe/glt/dataupload','token_url2' => '3whJ7fKgULniiLPC','pass_url2' => 'SDJygFYZ')
            );
        }

        return $result;
    }

    /**
     * 获取设备提交数据
     *
     * @author  wangl
     */
    public function get($data)
    {
        if (empty($data['device_list'])) {
            throw new Exception('the device submits data is empty.');
        }

        $rows     = $data['device_list'];
        $r_data     = array();

        foreach ($rows as $k => $v) {
            $cols = explode('|', $v);

            if ( empty($cols[0])  || empty($cols[2]) ) {
                continue;
            }

            $r_data[] = array(
                'dev'   =>  $data['hwid'],
                'mac'   =>  $cols[0],
                'rssi'  =>  $cols[1],
                'time'  =>  $cols[2],
            );
        }

        return $r_data;
    }

    /**
     * 设备登录
     *
     * @author  wangl
     */
    public function login()
    {
        // 拿登录参数，注意：设备上传的参数为json格式
        $request = file_get_contents('php://input');

        if ( !$request ) {
            throw new Exception('device login param is empty.');
        }
/*
        // 解析请求
        $ary = json_decode($request, true);

        if ( !$ary ) {
            throw new Exception('parse login param fail.');
        }
*/
        // 记录log，注：如果需要中断脚本用异常，不需要中断脚本用log
        probe_helper::write_log('meridian', 'login param: '.$request);

        $return = array(
            'state' =>  'success',
            'msg'   =>  'success',
        );
        echo json_encode($return);
    }

    /**
     * 握手
     *
     * @author  wangl
     */
    public function trace()
    {
        // 拿握手参数
        $request = file_get_contents('php://input');

        if ( !$request ) {
            throw new Exception('trace param is empty.');
        }
/*
        // 解析请求
        $ary = json_decode($request, true);

        if ( !$ary ) {
            throw new Exception('parse trace param fail.');
        }
*/
        // 记录
        probe_helper::write_log('meridian', 'trace param: '.$request);

        $return = array(
            'state' =>  'success',
            'msg'   =>  'success'
        );
/*
        $url = SITE_URL.'/probe/meridian/report_log';
        $return = array(
            'state' =>  'success',
            'msg'   =>  'readmore', // 注意：msg = 'readmore'为获取设备log信息，上传log地址为data.uval，其他值见文档
            'data'  =>  array(
                'uval'  =>  $url
            )
        );
*/
        echo json_encode($return);
    }

    /**
     * 设备上报log
     *
     * @author  wangl
     */
    public function report_log()
    {
        // 注意：log以文件的形式上传，这里只处理文件的内容，当进程结束后文件自动删除
        if ( empty($_FILES['filedata']) ) {
            throw new Exception('device report log is empty.');
        }

        $content = file_get_contents($_FILES['filedata']['tmp_name']);

        probe_helper::write_log('meridian', 'report log: '.$content);
    }

    /**
     * 设备上报config信息
     *
     * @author  wangl
     */
    public function report_config()
    {
        if ( empty($_FILES['filedata']) ) {
            throw new Exception('report_config no file data');
        }

        $content = file_get_contents($_FILES['filedata']['tmp_name']);

        probe_helper::write_log('meridian', 'report config: '.$content);
    }

    /**
     * 检查更新
     *
     * @author  wangl
     */
    public function checkupdate()
    {
        $request = file_get_contents("php://input");

        if ( !$request ) {
            throw new Exception('checkupdate param is empty.');
        }

        $ary     = json_decode($request, true);

        if ( !$ary ) {
            throw new Exception('parse checkupdate param is fail.');
        }

        probe_helper::write_log('meridian', 'checkupdate param: '.$request);

        $return  = array(
            'code'  =>  0,
            'data'  =>  array()
        );

        $url  = SITE_URL.'/probe/meridian';

        //日志记录的更新版本信息
        $log_version = 0;
        $log_cfg_version = 0;

        //新版本
        $new_version = 1131528;
        $new_cfg_version = 6;

        if ( $ary['FirmwareVer'] <  $new_version) {
            $path = MODULE_PATH."/probe/core/update/{$new_version}.bin";
            $return['data']['FirmwareAdd'] = $url.'/up_version';
            $return['data']['FirmwareMD5'] = md5_file($path);
            $log_version = $new_version;
        }

        if ( $ary['ConfigVer'] < $new_cfg_version ) {
            // 配置文件路径
            $path = MODULE_PATH.'/probe/core/update/my.cfg';

            if ( !$return['data'] ) {
                $return['data']['ConfigureAdd'] = $url .'/up_config';
                $return['data']['ConfigureMD5'] = md5_file($path);
            }

            $log_cfg_version = $new_cfg_version;

        }

        //记录升级日志
        probe_helper::write_update_version_log($ary, $return, $log_version, $log_cfg_version);

        echo json_encode($return);
    }

    /**
     * 更新版本
     *
     * @author  wangl
     */
    public function up_version()
    {
        //新版本
        $new_version = 1131528;

        probe_helper::write_log('meridian', 'update version.');

        $path = MODULE_PATH."/probe/core/update/{$new_version}.bin";

        header("Content-Type:application/octet-stream");
        header("Content-Disposition:attachment;filename=my.bin");
        header("Accept-ranges:bytes");
        header("Accept-Length:".filesize($path));
        readfile($path);
        exit(0);
    }

    /**
     * 更新配置
     *
     * @author  wangl
     */
    public function up_config()
    {
        probe_helper::write_log('meridian', 'update config.');

        $path = MODULE_PATH.'/probe/core/update/my.cfg';

        header("Content-Type:application/octet-stream");
        header("Content-Disposition:attachment;filename=my.cfg");
        header("Accept-ranges:bytes");
        header("Accept-Length:".filesize($path));
        readfile($path);
        exit(0);
    }
}