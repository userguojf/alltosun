<?php
/**
  * alltosun.com  ajax.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2017年11月20日 下午12:20:00 $
  * $Id: ajax.php 382292 2017-11-23 08:48:02Z songzy $
  */
class Action
{
    public function get_title()
    {

        $lat = tools_helper::post('lat', 0.0);
        $lng = tools_helper::post('lng', 0.0);
        if(!$lat || !$lng){
            return array('info' =>'error');
        }
        $business_list = _model('business_hall')->getAll(
            'SELECT id, title, user_number, user_number,address, blng, blat,
                    (ACOS(SIN(('.$lat.' * 3.1415) / 180 ) *SIN((blat * 3.1415) / 180 )
                    + COS(('.$lat.' * 3.1415) / 180 ) * COS((blat * 3.1415) / 180 )
                    *COS(('.$lng.' * 3.1415) / 180 - (blng * 3.1415) / 180 ) ) * 6380) as dis
                    FROM business_hall ORDER BY dis ASC limit 10');
       
        foreach ($business_list as $k => $v){
             $title_list[]       = $v['title'];
             $user_number_list[] = $v['user_number'];
        }
        $list = array();
        $list['title'] =$title_list;
        $list['user_number'] =$user_number_list;
        
        return array('info' => 'ok' ,array('title' =>$title_list,'user_number' => $user_number_list));   
    }
    
    //获取营业厅
    public function get_info_by_title()
    {
        $key_word = Request::Get('term','');
    
        if (!$key_word) {
            return '数据不存在';
        }
    
        $title_list = _model('business_hall')->getList(
                array(
                        'title LIKE' => "%{$key_word}%"
                )
                );
    
        $list=array();
        foreach ($title_list as $k=> $v)
        {
            $arr=array(
                    'id'=>$v['id'],
                    'user_number'=>$v['user_number'],
                    'label'=>$v['title']
    
            );
            $list[] =$arr;
        }
        //p($list);
        if ($list) {
            exit(json_encode($list));
        }
    }
    

}