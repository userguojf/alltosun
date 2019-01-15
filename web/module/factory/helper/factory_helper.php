<?php
/**
 * alltosun.com  
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * szy: 宋志宇 (songzy@alltosun.com)
 * 2018年3月7日: 2016-7-26 下午3:05:10
 * Id
 */
class factory_helper
{
   
    
    public static function push_email_info($param,$email_list,$phone_list,$order_code)
    {
        if(empty($param) || empty($email_list) || !$order_code){
            return false;
        }
        $str = $res = $send_content = '';
       
        //获取省市
        $province_name = business_hall_helper::get_info_name('province', $param['province_id'], 'name');
        $city_name     = business_hall_helper::get_info_name('city', $param['city_id'], 'name');
        $area_name     = business_hall_helper::get_info_name('area' , $param['area_id'] ,'name');
        $business_name = business_hall_helper::get_info_name('business_hall', $param['business_id'], 'title');
        
            $str = '恭喜您申请的';
            $res = '设备已发货，单号为 ';
            array_push($email_list,$email);
       
        $send_content = $str .' '.$province_name .' '.$city_name.$area_name.$business_name.$param['num'].'台'.$param['device_type'].$res.$order_code;
    
        //数组去重
        $email_list = array_unique($email_list);
        $res = _widget('email')->send_email($email_list,"发货通知",$send_content);
        return $res;
    
    }
    
    /**
     * 清楚数组空数据
     */
    public static function filter($arr)
    {
        if(!$arr){
            return [];
        }
        $arr = array_filter($arr);
        return $arr;
    }
    
    /**
     * 保存订单扩展字段
     * @param $user_id
     * @param $res_name
     * @param $values
     * @return array
     */
    public static function save_goods_extend($application_id, $res_name, $values,$device_mac_label_id)
    {
        if (!$application_id || !$res_name || !$values || !$device_mac_label_id) {
            return [];
        }
    
        if (!is_array($values)) {
            $values = [$values];
        }
    
        // 第一个元素默认为首个元素
        $first_value = $values[0];
    
        $old_values = _model('goods_contact_extend_relation')->getFields('value', [
                'application_id'   => $application_id,
                'res_name'  => $res_name,
        ], 'order by `view_order` desc');
    
        $res = self::get_filter_old_new_data($old_values, $values, 0, 1);
        if (!$res) {
            return [];
        }
      
        $old_data = $res['old_data'];
        $new_data = $res['new_data'];
    
        
        if ($new_data) {
            foreach ($new_data as &$v) {
                _model('goods_contact_extend_relation')->create([
                        'application_id'   => $application_id,
                        'res_name'  => $res_name,
                        'value'     => $v,
                        'device_mac_label_id'     => $device_mac_label_id,
                ]);
            }
        }
    
        _model('goods_contact_extend_relation')->update([
                'application_id'   => $application_id,
                'res_name'  => $res_name,
                'value !='  => $first_value
        ], [
                'view_order' => 0
        ]);
    
        _model('goods_contact_extend_relation')->update([
                'application_id' => $application_id,
                'res_name'  => $res_name,
                'value'     => $first_value
        ],[
                'view_order'    => 1
        ]);
    
        if (!$new_data && !$old_data) {
            return [];
        }
    
        // 返回更新前后的数据
        return [
                'res_name'  => $res_name,
                'old_data'  => $old_data,
                'new_data'  => $new_data
        ];
    }
    
    
    /**
     * 过滤新旧数据，只返回不同的数据,若无不同，则返回空数组
     * @param $old_data
     * @param $new_data
     * @param int $is_assoc 是否是关联数组，默认是
     * @param int $is_delete_old 是否删除旧数据（准对用户扩展字段）
     * @return array
     */
    public static function get_filter_old_new_data($old_data, $new_data, $is_assoc = 1, $is_delete_old = 0)
    {
        if (!$new_data) {
            return [];
        }
    
        if ($old_data == $new_data) {
            return [];
        }
    
        if ($is_assoc) {
            // 关联数组时，以修改后数组为准，多出来的数据去掉
            $old_data = self::filter_assoc_before_data($old_data, $new_data);
            $old = self::arr_diff_assoc($old_data, $new_data);
            $new = self::arr_diff_assoc($new_data, $old_data);
        } else {
            // 用于扩展字段时，保留所有
            //            $old = array_diff($old_data, $new_data);
            //            $new = array_diff($new_data, $old_data);
            $old = $old_data;
            $new = $new_data;
        }
    
        if ($old_data) {
            // 存在旧数据的为更新
            if (!$new) {
                // 是否删除旧数据（更新时：不删除旧数据，新旧数据比对时：删除旧数据）
                if (!$is_delete_old) {
                    return [];
                }
            }
        }
    
        return [
                'old_data'  => $old,
                'new_data'  => $new
        ];
    }
    
    /**
     * 自定义array_diff_assoc
     * @param $old_data
     * @param $new_data
     * @return array
     */
    public static function arr_diff_assoc($old_data, $new_data)
    {
        if (!$old_data || !$new_data) {
            return [];
        }
    
        $old = [];
        foreach ($old_data as $k => $v) {
            if (isset($new_data[$k])) {
                if (is_numeric($new_data[$k])) {
                    if (intval($new_data[$k] * 100) == intval($v * 100)) {
                        continue;
                    }
                } else {
                    if ($new_data[$k] == $v) {
                        continue;
                    }
                }
            }
            $old[$k] = $v;
        }
    
        return $old;
    }
    
    /**
     * 对于关联数组比对前需过滤修改前数据多余字段
     * @param $before_data
     * @param $after_data
     * @return array
     */
    public static function filter_assoc_before_data($before_data, $after_data)
    {
        $new_before_data = [];
        foreach ($after_data as $k => $v) {
            $new_before_data[$k] = $before_data[$k];
        }
    
        return $new_before_data;
    }
    
}