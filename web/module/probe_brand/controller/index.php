<?php

/**
 * 设备品牌
 *
 * @author  wangl
 */

class Action
{
    /**
     * 跟新本地品牌库
     *
     * @author  wangl
     */
    public function up_brand()
    {
        // 国际标准组织oui数据库
        $url = 'http://standards-oui.ieee.org/oui.txt';

        $ch  = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);
        curl_close($ch);

        $preg = "/[\w]+     \(base 16\)(.*)+/";

        preg_match_all($preg, $res, $ary);

        foreach ( $ary[0] as $k => $v ) {
            $str = str_replace('(base 16)', '', $v);
            $arr = explode('  ', $str);

            if ( empty($arr[0]) || empty($arr[2]) ) {
                echo 'continue '.$v;
                continue;
            }

            $mac = $arr[0];
            $inc = trim($arr[2]);

            $info = _model('t_oui')->read(array('id' => $mac));

            if ( $info ) {
                // _model('t_oui')->update(array('id' => $mac), array('company' => $inc));
            } else {
                _model('t_oui')->create(array('id' => $mac, 'company' => $inc));
            }
        }

        echo 'ok';
    }

    public function test()
    {
        $str = '40A3CC     (base 16)		Intel Corporate';

        $str = str_replace('(base 16)', '', $str);

        $arr = explode('  ', $str);

        $mac = $arr[0];

        $inc = trim($arr[2]);

        an_dump($mac, $inc);
    }
}
