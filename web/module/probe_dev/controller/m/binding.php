<?php
/**
  * alltosun.com  binding.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2017年11月17日 下午5:26:03 $
  * $Id: binding.php 382270 2017-11-23 08:36:12Z songzy $
  */
class Action
{
    /**
     * 绑定页面
     */
//     public function index()
//     {

//        // $device      = Request::getParam('device','');
//         Response::display('m/hand_bind.html');
//     }

    public function __call($action = '', $params = array())
    {
        $title       = Request::get('title','');
        $flag        = Request::get('flag',0);
        $user_number = Request::get('user_number','');
        if($action){
            $action_temp = str_replace(':','',$action);
            if(strlen($action_temp)!=12){
                $action = "";
            }
        }
        Response::assign('title',$title);
        Response::assign('flag',$flag);
        Response::assign('action',$action);
        Response::assign('user_number',$user_number);
        Response::display('m/binding.html');
    }

    /**
     * 进入查找页面
     */
    public function search_title()
    {

        $mac = tools_helper::Get('mac', '');
        Response::assign('mac', $mac);

        Response::display('m/binding_search.html');
    }

    public function bind_business()
    {
        $time =date('Y-m-d H:i:s',time());
        $title       = Request::getParam('title','');
        $device      = Request::getParam('device','');
        $user_number = Request::getParam('user_number','');
        //mac地址要求有效12位
        if($device){
            $temp_device = str_replace(':','',$device);
            if(strlen($temp_device)!=12){
                return false;
            }
        }
        //获取营业厅信息
        $where = array(
                'title' => $title
        );
        $business_info = business_hall_helper::get_business_hall_info($where);
        $bind_info['device']      = $device;
        $bind_info['city_id']     = $business_info['city_id'];
        $bind_info['area_id']     = $business_info['area_id'];
        $bind_info['add_time']    = $time;
        $bind_info['province_id'] = $business_info['province_id'];
        $bind_info['business_id'] = $business_info['id'];

        //绑定
        $device_id=_model('probe_device')->create($bind_info);

        Response::assign('device_id',$device_id);
        Response::assign('device',$device);
        Response::display('m/binding_success.html');

    }


    public function details()
    {
        $id     = Request::get('id',0);
        $device = Request::get('device','');

        $bussiness_id = _uri('probe_device',$id,'business_id');

        $business_info = business_hall_helper::get_business_hall_info($bussiness_id);
        $title         = $business_info['title'];
        $user_number   = $business_info['user_number'];

        Response::assign('title',$title);
        Response::assign('device',$device);
        Response::assign('user_number',$user_number);
        Response::display('m/binding_details.html');

    }
}
