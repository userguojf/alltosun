<?php
/**
 * alltosun.com  ajax.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * szy: 宋志宇 (songzy@alltosun.com)
 * 2017年12月25日: 2016-7-26 下午3:05:10
 * Id
 */
class Action
{
    
    public function update_ststus() 
    {
        $status = Request::post ( 'status', 0 );
        $city_id = Request::post ( 'city_id', '' );
        $province_id = Request::post ( 'province_id', '' );
        $create_time = Request::post ( 'create_time', '' );
        $filter = array(
                'city_id'     => $city_id,
                'province_id' => $province_id,
                'create_time' => $create_time
        );
        
        if ($status == '0') {
            //取消申请状态为2
            $id = _model('device_application')->update($filter, array('status' => 2));
        }elseif($status == '2'){
            //重新申请 改为 0
            $id = _model('device_application')->update($filter, array('status' => 0));
        }
        
        if(!$id){
            return array('info' => 'fail','msg' =>"网络错误");
        }
       
        return 'ok';
    }
}
