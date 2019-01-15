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
 * 2017年12月28日
 * Id
 */

class screen_price_stat_widget
{
   /**
    * 定时写入价格统计
    */
    public function screen_price_stat()
    {   

        
            set_time_limit ( 0 );
        
            // 今天取昨天的
            $old_date = date ( 'Ymd',time() - 3600*24 );
            $date = date ( 'Ymd',time());
            $table = 'screen_device_price_record';
            
            $filter = array(
                    'add_time >=' => $old_date,
                    'add_time <=' => $date,
            );
            
            
            $list = _model($table)->getList($filter, 'GROUP BY `device_unique_id` ORDER BY `add_time` desc');
            
            if(!$list){
                return false;
            }
            $stat_list = array();
            foreach ($list as $k => $v){
                $this->price_num_record($v['device_unique_id']);
            }
            echo '完成';
    }
    
    
    /**
     * 统计表定时更新
     * @param unknown $device_unique_id
     */
    public function price_num_record($device_unique_id)
    {
        if(!$device_unique_id){
            return false;
        }
        $filter =array(
                'device_unique_id' => $device_unique_id
        );
        $change_num  = _model('screen_device_price_record')->getTotal($filter);
        if(!$change_num){
            $change_num = 1;
        }
        $list = _model('screen_device_price_record')->read($filter);
        $stat_list =array();
        $stat_list['date']             = date('Ymd',time($list['add_time']));
        $stat_list['add_time']         = $list['add_time'];
        $stat_list['city_id']          = $list['city_id'];
        $stat_list['area_id']          = $list['area_id'];
        $stat_list['change_num']       = $change_num;
        $stat_list['content_id']       = $list['content_id'];
        $stat_list['province_id']      = $list['province_id'];
        $stat_list['business_hall_id'] = $list['business_hall_id'];
        $stat_list['device_unique_id'] = $list['device_unique_id'];
        //查询统计表是否有数据
        $flag_stat = _model('screen_device_price_stat')->read(array('device_unique_id' => $device_unique_id));
        if ( !$flag_stat ) {
            _model('screen_device_price_stat')->create($stat_list);
        } else {
            _model('screen_device_price_stat')->update( $flag_stat['id'],
                    " SET `change_num` = $change_num + 1 "
                    );
        }
    }

}
