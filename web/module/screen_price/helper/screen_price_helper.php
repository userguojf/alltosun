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
 * 2017年12月15日: 2016-7-26 下午3:05:10
 * Id
 */

class screen_price_helper
{
    /**
     * 获取营业厅名
     * @param int $id
     * @return boolean|mixed
     */
    public static function get_business_hall_info($id)
    {   
        if (!$id){
            return false;
        }
        $filter = array(
                'id' => $id
        );
        
        $info = _model('business_hall')->read($filter);
        return $info['title'];
    }
    
    /**
     * 获取省市区名
     * @param int $id
     * @param string $table
     * @return boolean|mixed
     */
    public static function get_region_info($id,$table)
    {
        if(!$id || !$table){
            return false;
        }
        $filter =array(
                'id' => $id
        );
        
        $info = _model($table)->read($filter);
        return $info['name'];
    }
    
    
    public static function get_content_info($content_id)
    {
        if(!$content_id){
            return false;
        }
        $content_info = _uri('screen_content',$content_id);
        return $content_info;
    }
    
    
    /**
     * 修改价格记录
     * @param unknown $device_unique_id
     * @param unknown $price
     * @param unknown $content_id
     */
    public  static function record($device_info, $price, $content_id)
    {

        if(!$device_info || !$price || !$content_id) return false;

        $date  = date('Ymd');

        _model( 'screen_device_price_record' )->create( array(
                'business_hall_id' => $device_info['business_id'],
                'province_id'      => $device_info['province_id'],
                'city_id'          => $device_info['city_id'],
                'area_id'          => $device_info['area_id'],
                'device_unique_id' => $device_info['device_unique_id'],
                'content_id'       => $content_id,
                'price'            => $price,
                'date'             => $date
        ) );

       
        return true;
    }

}
?>