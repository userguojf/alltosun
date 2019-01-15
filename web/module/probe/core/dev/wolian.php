<?php 

// load trait storage
probe_helper::load('storage', 'trait');

/**
 * 沃联设备类
 *
 * @author  wangl
 */
class wolian implements device
{
    // use trait
    use storage;

    /**
     * list，用来存储解析后的信息
     *
     * @var Array
     */
    private $macs = array();

    /**
     * 设备提交的原始数据
     *
     * @var String
     */
    private $param = '';

    /**
     * 构造函数
     *
     * @author  wangl
     */
    public function __construct()
    {
        // 获取设备提交的数据
        $this -> param = file_get_contents('php://input');
    }

    /**
     * 存储数据
     *
     * @author  wangl
     */
    public function storage()
    {
        try {
            // 如果mac不存在，则获取
            if ( empty($this -> macs) ) {
                $this -> macs = $this -> get();
            }

            $this->write($this->macs);
        } catch (Exception $e) {
            probe_helper::write_log('wolian', $e -> getMessage());
        }
    }

    /**
     * 获取设备提交数据
     *
     * @author  wangl
     */
    public function get()
    {
        $param = $this -> param;

        if ( !$param ) {
            throw new Exception('the device submits data is empty.');
        }

        $data = array();
        $len  = 0;

        // 注：str_split将字符串变为数组，例如：str_spit(abc)，返回array(a, b, c)
        foreach (str_split($param) as $chr) {
            // 将字符的ASCII码值转成16进制输出
            $data[$len ++] = sprintf("%02X", ord($chr));
        }

        // 探针设备编号
        if ( !isset($data[2]) || !isset($data[5]) ) {
            throw new Exception('the device submits data incomplete.');
        }

        // 设备编号
        $dev = $data[2].$data[3].$data[4].$data[5];

        // 路由数据，暂丢弃
        if ( $data[14] == 11 ) {
            return array();
        }

        $sum  = 0;

        for($i = 0; $i < $len - 1; $i ++){
            $sum += hexdec($data[$i]);
        }

        // 将10进制的数字转成16进制
        $sum = base_convert((string)$sum, 10, 16);  

        // 对比计算出的和的最后两位
        if(strtolower($data[$len - 1]) != strtolower(substr($sum, -2) ) ){
            throw new Exception('Data validation error. '.$param);
        }

        $mac   = "";
        $list  = array();

        for ($i = 1; $i < $len - 23; $i ++) {
            if ($i % 7 == 0) {
                $list[] = array(
                    'dev'   =>  $dev,                       // 探测设备
                    'mac'   =>  hexdec($mac),               // mac地址
                    'rssi'  =>  hexdec($data[$i+22]) - 255, // 信号
                    'time'  =>  time(),                     // 探测时间
                );
                // 下一组mac地址重新组合
                $mac = "";
            } else {
                // 一组mac地址组合
                $mac .= $data[$i+22];
            }
        }

        return $list;
    }

    /**
     * 数据转发
     * 注：由于沃联设备提交地址难改，而且又是提交的线下地址，所以在线下往线上转发一份
     *
     * @author  wangl
     */
    public function transmit()
    {
        $param = $this -> param;

        if ( !$param ) {
            throw new Exception('transmit: the device submits data is empty.');
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://wifi.pzclub.cn/probe');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param);

        $res = curl_exec($ch);
        curl_close($ch);
    }
}