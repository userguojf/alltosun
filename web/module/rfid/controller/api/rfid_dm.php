<?php
/**
 * alltosun.com  rfid_dm.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-5-11 下午5:29:30 $
 * $Id$
 */
/**
 * 
 * @author 郭剑峰
 *
 *数字地图取体验详情的接口
 */
class Action
{
    private $config = array(
            'appid'    =>  'szdt',
    );

    public function index()
    {
        $app_id        = Request::Post('app_id', '');
        $label_id      = Request::Post('label_id', '');
        $business_code = Request::post('business_code' , '');

        $date          = Request::Post('date', date('Ymd'));

        //数据存在返回的数组
        $data = array();

        if ($this->config['appid'] != $app_id) {
            $this->api_return('app_id不正确');
        }

        if (!$label_id) {
            $this->api_return('标签ID不存在');
        }

        if (!$business_code) {
            $this->api_return('渠道码不存在');
        }

        $business_hall_info = _model('business_hall')->read(array('user_number' => $business_code));

        if (!$business_hall_info) {
            $this->api_return('渠道码不正确');
        }

        $rfid_info = _model('rfid_label')->read(array('label_id' => $label_id));

        if ( !$rfid_info ) {
            $this->api_return('标签ID不存在后台数据');
        }

        //手机信息
        $data['phone_brand']   = $rfid_info['name'];
        $data['phone_version'] = $rfid_info['version'];

        //设备编号 IMEI末六位
        $data['IMEI']          = $rfid_info['imei'];

        $info = _model('rfid_record_detail')->getFields( 'remain_time',
                array(
                    'business_id'     => $business_hall_info['id'],
                    'label_id'        => $label_id,
                    'date'            => $date,
                    'end_timestamp >' => 0,
                    'status'          => 1
                    )
                );

        if ($info) {
            //体验信息
            $data['action_num']      = count($info);
            $data['experience_time'] = array_sum($info);
        } else {
            //体验信息为0
            $data['action_num']      = 0;
            $data['experience_time'] = 0;
        }

        $this->api_return($data);
    }

    /**
     * 返回数据方法
     */
    private function api_return( $data )
    {
        if ( is_array($data) ) {
            $array = array(
                    'status'    =>  1,
                    'msg'       =>  'ok',
                    'data'      =>  $data
            );
        } else {
            $array = array(
                    'status'   =>  0,
                    'msg'      =>  $data,
                    'data'     =>  array(),
            );
        }

        echo json_encode($array);
        exit(0);
    }

    public function test()
    {
        require ROOT_PATH.'/helper/AnCurl.php';

        $curl = new AnCurl();

        $data = array(
                'app_id'        => 'szdt',
                'label_id'      => '03003C57',
                'business_code' => '1101021002051',
        );

        $res  = $curl -> post('http://wifi.pzclub.cn/rfid/api/rfid_dm', $data);

        an_dump($res);
        an_dump(json_decode($res , true));
    }
}