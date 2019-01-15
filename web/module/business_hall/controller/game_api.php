<?php

class Action
{
    private $local_lat  = '';
    private $local_lng  = '';
    private $distance   = 20;

    public function __construct()
    {
//             $this->local_lat = get_cookie('localLat');
//             $this->local_lng = get_cookie('localLng');

//             $localLatShare = get_cookie('localLatShare');
//             $localLngShare = get_cookie('localLngShare');

//             if ($localLatShare && $localLngShare) {
//                 $this->local_lat = $localLatShare;
//                 $this->local_lng = $localLngShare;
//             }
    }

    public function get_business_list()
    {
        $area_id     = tools_helper::post('area_id', 0);
        $lat      = tools_helper::post('lat', 0);
        $lng      = tools_helper::post('lng', 0);
        $data     = array();
        $distance = $this->distance;

        if (!$area_id) {
            $city = event_helper::get_address_by_ip();
            if (!$city) {
                //首次加载
                $province_id = 1;
                $city_id    = 17;
                $area_id    = 121;
            } else {
                $city = mb_substr($city, 0, (mb_strlen($city)-1), 'UTF-8');
                $filter = array(
                        'name LIKE' => $city.'%',                );
                $city_info = _model('city')->read($filter);
                if (!$city_info) {
                    return '无此市信息';
                }

                $city_id = $city_info['id'];
                $province_id = $city_info['province_id'];
                $area_id       = 0;
            }
            $data['province_id'] = $province_id;
            $data['city_id'] = $city_id;
            $data['area_id'] = $area_id;
            $data['province_list'] = _model('province')->getList(array('1' => 1));
            $data['city_list'] = _model('city')->getList(array('province_id' => $province_id));
            $data['area_list'] = _model('area')->getList(array('city_id' => $city_id));
        }
        if($area_id && _uri('area', $area_id)) {
            $area_info = _uri('area', $area_id);
            $data['game_address'] = _model('map')->getList(array('district LIKE' => $area_info['name'].'%'));
        } else {
            $data['game_address'] = _model('map')->getList(array('city_id' => $city_id));
        }
        return array('msg' => 'ok','data' => $data);
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