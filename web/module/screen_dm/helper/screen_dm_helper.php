<?php
/**
 * alltosun.com  screen__dm_helper.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-12-7 上午10:47:49 $
 * $Id: screen_dm_helper.php 414411 2018-03-28 10:55:14Z songzy $
 */
class screen_dm_helper 
{
    /**
     *
     * @param int $business_id
     * @param string $phone_name
     * @param string $phone_version
     * @return boolean
     */
    public static function is_show_price($business_id, $phone_name, $phone_version)
    {
        if ( !$business_id || !$phone_name || !$phone_version ) return false;
        return _widget ( 'screen' )->get_type4_content_by_device ( $business_id, $phone_name, $phone_version );
    }
    
    /**
     * 获取内容发布手机型号
     * @param unknown $content_id
     * @return boolean|unknown
     */
    public static function get_content_phone_version($content_id)
    {
        if(!$content_id){
            return false;
        }
     
        $phone_version_list = _model('screen_content_res')->getFields('phone_version',array('content_id' => $content_id ));
        
        if(!empty($phone_version_list)){
            $phone_version = $phone_version_list[0];
        }else{
            $phone_version = '';
        }
        $all = _model('screen_content_res')->getList(array('content_id' =>$content_id,'phone_version' =>'','phone_name' =>''));
        if(!empty($all)){
            $phone_version='';
        }
        return $phone_version;
    }
    
    /**
     * 获取内容发布手机型号数量
     * @param unknown $content_id
     * @return boolean|unknown
     */
    public static function get_content_phone_version_num($content_id)
    {
        if(!$content_id){
            return false;
        }
        $version_list =array();
        $edit_phone_name_list = _model('screen_content_res')->getList(array('content_id' => $content_id ));
        
        //如果投放
        if(!empty($edit_phone_name_list)){
            foreach ($edit_phone_name_list as $k => $v){
                //该型号全部机型
                if($v['phone_name'] && !$v['phone_version']){
                    $checked_phone = self::get_version_list($v['phone_name']);
                }else{
                    $checked_phone_tmp = self::get_version_list_by_version($v['phone_name'], $v['phone_version']);
                    $checked_phone2[] = $checked_phone_tmp;
                }
            }
        
            if($checked_phone && $checked_phone2){
                foreach ($checked_phone2 as $k => $v){
                    array_push($checked_phone,$v);
                }
            }
        
            if(!$checked_phone && $checked_phone2){
                foreach ($checked_phone2 as $k => $v){
                    $checked_phone[] = $v;
                }
            }
            //获取所有选择型号
            foreach($checked_phone as $k => $v){
                $version_list[] = $v['phone_version'];
            }
        }
        if(!empty($version_list)){
            $version_list = array_unique($version_list);
            sort($version_list);
        }
        $num = count($version_list);
        
        $all = _model('screen_content_res')->getList(array('content_id' =>$content_id,'phone_version' =>'','phone_name' =>''));
        if(!empty($all)){
            $num = 1;
        }
        return $num;
    }
    
    public static function get_content_put_version($id,$phone_version)
    {
        if(!$phone_version && !$id){
            return false;
        }
        $edit_phone_version_list = _model('screen_content_res')->getList(array('content_id' =>$id));
        
        $num = _model('screen_content_res')->getTotal(array('content_id' => $content_id ));
        return $num;
    }
    
    
    /**
     * 判断是否为最近三天安装
     * @param unknown $id
     * @return boolean
     */
    public static function get_new_device($id)
    {
        if(!$id){
            return false;
        }
        $old_time =(int)date('Ymd',time()-60*60*24*3);
        
       $list = _model('screen_device')->read(array('id' =>$id));
       
       $day = (int)$list['day'];
       if ($day >= $old_time)
       {
           // 执行相应操作
           return true;
       }
        return false;
    }
    public static function get_content_price($content_res_id){
        if(!$content_res_id){
            return false;
        }
        $contend_id = _model('screen_content_res')->getFields('content_id',array('id' => $content_res_id));
        $content_info = _model('screen_content')->read($contend_id);
        return $content_info['price'];
    }
    
    /**
     * 获取权限下所有厅店
     */
    public static function get_business_hall($res_name,$res_id)
    {
        //初始化默认条件
        $filter = array();
        if (in_array($res_name, array('province', 'city', 'area'))) {
            $filter[$res_name.'_id'] = $res_id;
        } else if ($res_name == 'business_hall') {
            $filter['id'] = $res_id;
        } else if ($res_name == 'group') {
            $filter['1'] = 1;
        }
        //为了兼容后续有详情页，先把所有有设备营业厅id取出
        $business_hall_ids = _model('business_hall')->getFields('id', $filter, ' GROUP BY `id` ');
        
        $num = count($business_hall_ids);
        return $num;
    }
    
    /**
     * 获取版本列表
     */
    public static function get_version_list($phone_name)
    {
        $filter = array(
                'phone_name' => $phone_name,
                'status'     => 1
        );
        
        $version_list = _model('screen_device')->getList($filter, " GROUP BY `phone_version`");
        return $version_list;
    }
    
    
    /**
     * 获取版本列表
     */
    public static function get_version_list_by_version($phone_name,$phone_version)
    {
        $filter = array(
                'phone_name' => $phone_name,
                'phone_version' => $phone_version,
                'status'     => 1
        );
        $version_list = _model('screen_device')->read($filter, " GROUP BY `phone_version`");
        if(empty($version_list)){
            //从昵称表里取
            $nickname_info = _model('screen_device_nickname')->read(array('name_nickname' => $phone_name,'version_nickname'=>$phone_version));
            $filter=array(
                    'phone_name' => $nickname_info['phone_name'],
                    'phone_version' => $nickname_info['phone_version'],
                    'status'     => 1
            );
            $version_list = _model('screen_device')->read($filter, " GROUP BY `phone_version`");
        }
        return $version_list;
    }
    
    public static function  get_thumb($link)
    {
        if(!$link){
            return false;
        }
        $arr = explode("/",$link);
        //缩略图路径
        $path_temp =  "middle_".end($arr);
        //弹出最后一个
        array_pop($arr);
        //压入拼接好的缩略图
        array_push($arr,$path_temp);
        //拼接完整路径
        $path = implode('/', $arr);
        return $path;
    }
}
?>