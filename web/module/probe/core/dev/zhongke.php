<?php

// load trait storage
probe_helper::load('storage', 'trait');

/**
 * 中科
 *
 * @author wangl
 */
class zhongke implements device
{
    // use trait
    use storage;

    /**
     * 设备探测mac
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

            $this->write($this->macs);
        } catch (Exception $e) {
            probe_helper::write_log('zhongke', $e -> getMessage());
        }
    }

    /**
     * 获取中科爱讯设备数据
     *
     * @author  wangl
     */
    public function get()
    {
        $data = file_get_contents('php://input', 'r');

        if ( !$data ) {
            throw new Exception('the device submits data is empty.');
        }

        $list = explode("\n", $data);
        // 返回数据
        $res  = array();
        // 提交时间
        $time = time();

        foreach ($list as $k => $v) {
            $row = explode('|', $v);

            if ( !isset($row[0]) || !isset($row[1]) || !isset($row[3]) ) {
                continue;
            }

            $mac  = $row[1];
            $dev  = $row[0];
            $rssi = $row[3];

            $res[] = array(
                'dev'   =>  $dev,
                'mac'   =>  $mac,
                'rssi'  =>  $rssi,
                'time'  =>  $time,
            );
        }

        return $res;
    }
}