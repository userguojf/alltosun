<?php
/**
 * alltosun.com  ajax.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-5-9 下午3:56:54 $
 * $Id$
 */
class Action
{
    /**
     *选择厅店
     */
    public function get_business_title()
    {
//         $province_id = Request::post('province_id' , 0);
//         $city_id     = Request::post('city_id' , 0);
        $area_id     = Request::post('area_id' , 0);

//         if (!$province_id || !$city_id || !$area_id) {
        if (!$area_id) {
            return array('msg'=>'no');
        }

        $business_hall_info = _model('business_hall')->getList(
                                        array(
//                                             'province_id' => $province_id,
//                                             'city_id'  => $city_id,
                                            'area_id'  => $area_id,
                                        )
                                );

        if (!$business_hall_info) {
            return array('msg' => 'no');
        }

        return array('msg'=>'ok' , 'business_hall_info' => $business_hall_info);

    }

    /**
     * 自动完成功能使用， 和本文件中的get_business_title方法的区别在于：多返回id字段
     */
    public function get_info_by_title()
    {
        $key_word = Request::Get('term','');

        if (!$key_word) {
            return '数据不存在';
        }

        $business_list = _model('business_hall')->getList(
                array(
                        'title LIKE' => "{$key_word}%"
                )
                );

        $list2=array();
        foreach ($business_list as $k=> $v)
        {
            $arr=array(
                    'id'=>$v['id'],
                    'label'=>$v['title']

            );
            $list2[] =$arr;
        }
        if ($list2) {
            exit(json_encode($list2));
        }
    }

    /**
     * 选择探针设备
     * @return array
     */
    public function get_probe_device()
    {
        $business_hall_id = Request::post('business_id' , 0);

        $info = array('probe_msg' => 'no','shoppe_msg' => 'no','probe_info' => '', 'shoppe_info' => '');

        if (!$business_hall_id) {
            return $info;
        }

        $probe_info  = probe_helper::rfid_get_devs($business_hall_id);

        if ($probe_info) {
            $info['probe_msg']  = 'ok';
            $info['probe_info'] = $probe_info;
        }

        $shoppe_info = rfid_helper::get_shoppe_info($business_hall_id);

        if ($shoppe_info) {
            $info['shoppe_msg']  = 'ok';
            $info['shoppe_info'] = $shoppe_info;
        }

        return $info;
    }


    /**
     *选择手机型号
     */
    public function get_phone_version()
    {
        $phone_name = Request::post('phone_name' , '');

        if (!$phone_name ) {
            return array('msg'=>'no');
        }

        $phone_version_info = _model('rfid_phone')->getFields('version' , array('name' => $phone_name));

        if (!$phone_version_info) {
            return array('msg' => 'no');
        }

        $result = array();

        foreach ($phone_version_info as $v) {
            if (in_array($v, $result)) {
                continue;
            } else {
                array_push($result, $v);
            }
        }

        return array('msg'=>'ok' , 'version_info'=> $result);
    }

    /**
     *选择手机的颜色
     */
    public function get_phone_color()
    {
        $phone_name    = Request::post('phone_name' , '');
        $phone_version = Request::post('phone_version' , '');

        if (!$phone_name || !$phone_version) {
            return array('msg'=>'no');
        }

        $phone_color_info = _model('rfid_phone')->getFields('color' , array('name' => $phone_name , 'version' => $phone_version));

        if (!$phone_color_info) {
            return array('msg' => 'no');
        }

        $result = array_unique($phone_color_info);

        return array('msg'=>'ok' , 'color_info'=> $result);
    }

    //请求数子地图接口失败再请求
    public function send_dm_data()
    {
        $id = Request::post('id' , 0);

        $param = array();

        if (!$id) {
            return array('code' => 400 , 'info' => '由于网络原因，请刷新页面');
        }

        $rfid_info = _model('rfid_label')->read(array('id' => $id));

        if (!$rfid_info) {
            return array('code' => 400 , 'info' => '数据已经不存在，可能被其他操作删除');
        }

        $user_number = _uri('business_hall' , array('id' => $rfid_info['business_hall_id']) , 'user_number');

        if (!$user_number) {
            return array('code' => 400 , 'info' => '营业厅渠道码未找到');
        }

        $param = array(
            'type'        => 'create',
            'user_number' => $user_number,
            'label_id'    => $rfid_info['label_id']
        );

        $response_json = rfid_helper::send_dm_data($param);

        $response_info = json_decode($response_json , true);

        if (200 == $response_info['httpStatus']) {
            _model('rfid_label')->update($id , array('response_code' => 200 , 'response_body' => $response_info['message']));

            return array('code' => 200);
        }

        if (400 == $response_info['httpStatus']) {
            return array('code' => 400 , 'info' => $response_info['message']);
        }
    }
}