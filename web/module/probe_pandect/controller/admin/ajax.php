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
        $res_id = Request::post ( 'res_id', 0 );
        $res_name = Request::post ( 'res_name', '' );
        $filter = array(
                'city_id'     => $city_id,
                'province_id' => $province_id,
                'create_time' => $create_time,
                'res_id' => $res_id,
                'res_name' => $res_name,
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
    
    public function change_approve()
    {
        $flag = Request::post ( 'flag', 0 );//0 拒绝 1通过
        $list = htmlspecialchars_decode(tools_helper::post('list',''));
        $factory_account = Request::post('factory_account','');
        $list = json_decode($list,true);
        $city_id = Request::post ( 'city_id', 0 );
        $province_id = Request::post ( 'province_id', 0 );
        $device_type = Request::post ( 'device_type','');
        $num = Request::post ( 'num', 0 );
        $param =array(
                'province_id' => $province_id,
                'city_id' => $city_id,
                'device_type' => $device_type,
                'factory_account' => $factory_account,
                'flag' => $flag,
                'num' => $num,
        );
        $filter = array();
        foreach ($list as $k => $v){
            $filter[] = $v['id'];
        }
        $id = _model('device_application')->update($filter, array('status' => 1));
        //通过订单状态改为待发货 1待发  3已拒绝
        if($flag == 1){
            $res_id =  _model('device_application')->update($filter, array('order_status' => 1));
        }else{
             $res_id = _model('device_application')->update($filter, array('order_status' => 3));
        }
        if(!$id){
            return array('info' => 'fail','msg' =>"网络错误");
        }
         
        /////////////////////////////推送邮箱/////////////////////////////////
            
           $res = probe_pandect_helper::push_email_info($param,$filter);
            if($res){
                return array('info' => 'ok','msg'=> '已发送至邮箱');
            }else{
                return array('info' => 'error','msg'=> '发送至邮箱失败');
            }
        return 'ok';
    }
    
    
    
    public function pop_page()
    {
        $pop_page_no = Request::post ( 'pop_page_no', 1 );
        //查询条件
        $filter = htmlspecialchars_decode(tools_helper::post('filter',''));
        $filter = json_decode($filter,true);
        $max = 6;
        $page  = ($pop_page_no-1)*$max;
        
        $list  = _model('device_application')->getList($filter, ' LIMIT '.$page.','.$max);
        
        foreach ($list as $k => $v){
            $list[$k]['provice']       = business_hall_helper::get_info_name('province', $v['province_id'], 'name');
            $list[$k]['city']          = business_hall_helper::get_info_name('city', $v['city_id'], 'name');
            $list[$k]['area']          = business_hall_helper::get_info_name('area', $v['area_id'], 'name');
            $list[$k]['business_hall'] = business_hall_helper::get_info_name('business_hall', $v['business_id'], 'title');
            $list[$k]['business_level'] = probe_pandect_config::$business_level[$v['business_level']];
        }
        
        return array('info' => 'ok', 'errno' => 0, 'data' => $list);
        
    }
    
    
    
    public function export_send()
    {
        $id = Request::Post('id',0);
        if(!$id){
            return false;
        }
        $list = _model('goods_contact_extend_relation')->getList(array('application_id' => $id));
        p($list);
        $this->export($list);
        
        return array('info' => 'ok');
        exit();
    }
    
    
}
