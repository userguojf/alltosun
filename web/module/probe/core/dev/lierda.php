<?php

// load trait storage
probe_helper::load('storage', 'trait');

/**
 * 利尔达
 *
 * @author wangl
 */
class lierda implements device
{
    // use trait
    use storage;

    /**
     * 探测到的mac地址
     *
     * @var Array
     */
    private $macs = array();

    /**
     * 存储mac
     *
     * @author  wangl
     */
    public function storage($macs=array())
    {
        if ($macs && !$this->macs) {
            $this->macs = $macs;
        }

        try {
            // 获取设备提交的数据
            if ( empty($this -> macs) ) {
                $this -> macs = $this -> get();
            }

            // 存储
            $this->write($this->macs);
        } catch (Exception $e) {
            probe_helper::write_log('lierda', $e -> getMessage());
        }
    }

    /**
     * 获取利尔达设备数据
     *
     * @author  wangl
     */
    public function get()
    {
        $data = Request::Post('data', '');

        if ( !$data ) {
            throw new Exception('device submits data is empty.');
        }

        // 最终返回的数据
        $res = array();
        // 解析提交数据
        $ary = json_decode($data, true);

        if ( !$ary ) {
            // 注：提交上来的数据可能会产生少个[导致无法解析的情况，如果解析错误手动拼上一个[再次尝试解析
            $data = '['.$data;
            $ary  = json_decode($data, true);

            if ( !$ary ) {
                throw new Exception('parse data is fail.');
            }
        }

        foreach ($ary as $k => $v) {
            if ( empty($v[2][0]) ) {
                continue;
            }

            $info = $v[2][0];

            if ( !isset($info[0]) || !isset($info[1]) ) {
                continue;
            }

            $res[] = array(
                'dev'   =>  isset($v[1]) ? $v[1] : '',
                'mac'   =>  $info[0],
                'rssi'  =>  $info[1],
                'time'  =>  time()
            );
        }

        return $res;
    }
}