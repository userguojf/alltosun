<?php

class Action
{
    private $local_lat  = '';
    private $local_lng  = '';
    private $distance   = 20;

    public function __construct()
    {
    }

    public function index()
    {
        $filter = array(
            'add_time >' => '2016-09-27 00:00:00'
        );

        $list = _model('business_hall')->getList($filter);
p($list);exit();
        foreach ($list as $v) {
            $info = _model('old_business')->read(array('customerId' => $v['wifi_res_id']));

            $area_info = _model('area')->read(array('wifi_res_id' => $info['countyId']));

            p($info);

            if (!$area_info) {
                _model('business_hall')->delete(array('id' => $v['id']));
            } else {
echo $info;
            }

//             _model('business_hall')->update(
//                     $v['id'],
//                     array(
//                         'province_id' => $area_info['province_id'],
//                         'city_id' => $area_info['city_id'],
//                         'area_id' => $area_info['id']
//                     )
//             );
        }
    }

    public function get_city_list()
    {
        $province_id = Request::post('province_id');

        if (!$province_id) {
            return array('msg'=>'no');
        }

        $city_info = _model('city')->getList(array('province_id'=>$province_id));

        if (!$city_info) {
            return array('msg' => 'no');
        }

        return array('msg'=>'ok' , 'city_list'=> $city_info);
    }

    public function get_area_list()
    {
        $city_id = Request::post('city_id');

        if (!$city_id) {
            return array('msg'=>'no');
        }

        $area_info = _model('area')->getList(array('city_id'=>$city_id));

        if (!$area_info) {
            return array('msg' => 'no');
        }

        return array('msg'=>'ok' , 'area_list'=> $area_info);
    }
}
?>