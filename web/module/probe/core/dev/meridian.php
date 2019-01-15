<?php

// load trait storage
probe_helper::load('storage', 'trait');
//wangjf add 引入设备接口
probe_helper::load('device', 'interface');

/**
 * 子午线
 *
 * @author  wangl
 */
class meridian implements device
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
    public function storage()
    {
        try {
            // 获取设备提交的数据
            if ( empty($this -> macs) ) {

                $this -> macs = $this -> get();
            }
            //probe_helper::write_log('test', 'success');
            // 写入数据
            $this->write($this->macs);
        } catch (Exception $e) {
            probe_helper::write_log('meridian', $e -> getMessage());
        }
    }

    /**
     * 获取设备提交数据
     *
     * @author  wangl
     */
    public function get()
    {
        // 注意：子午线设备以文件上传的形式提交数据
        if ( empty($_FILES['filedata']) ) {
            throw new Exception('the filedata is empty.');
        }

        $path     = $_FILES['filedata']['tmp_name'];
        // 注意：这里不处理上传的文件，只处理上传文件的内容，当进程结束后，文件自动删除，如需保留文件，则另处理
        $contents = file_get_contents($path);

        if ( !$contents ) {
            throw new Exception('the device submits data is empty.');
        }

        $rows     = explode("\n", $contents);
        $data     = array();

        foreach ($rows as $k => $v) {
            $cols = explode(',', $v);

            if ( !$cols ) {
                continue;
            }

            if ( empty($cols[1]) || empty($cols[14]) ) {
                continue;
            }

            $mac  = $cols[1];
            $rssi = $cols[3];
            $dev  = $cols[14];

            $data[] = array(
                'dev'   =>  $dev,
                'mac'   =>  $mac,
                'rssi'  =>  $rssi,
                'time'  =>  $cols[10],
            );
        }

        return $data;
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