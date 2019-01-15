<?php
/**
 * alltosun.com 更新标签绑定helper secret_helper.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 王敬飞 (wangjf@alltosun.com) $
 * $Date: 2017年5月12日 下午5:59:53 $
 * $Id$
 */

class secret_helper
{
    public static $secret_key = 'phone_';

    /**
     * 更新secret
     * @param unknown $params
     * @return number[]|string[]|boolean
     */
    public static function update_secret($label_id)
    {
        if (!$label_id) {
            return array('errno' => 1001, 'error' => '标签id不能为空');
        }

        $redis = RedisCache::content();


        $key = self::$secret_key . $label_id;

        $redis->delete($key);

        return true;
    }

    /**
     * 获取营业厅在线标签数量
     * @param unknown $business_id
     */
    public static function get_label_online_count($business_id, $date = 0)
    {
        if (!$date) {
            $date = date('Ymd');
        }

        $redis = RedisCache::content();

        $key = 'onlineStatus|' . $date . '|' . $business_id;

        $count = $redis->redis->ssize($key);

        if ($count) {
            return $count;
        }

        return 0;
    }

    /**
     * 根据读写器状态获取营业厅id
     * @param unknown $business_id
     */
    public static function get_label_online_business_hall_id($rwtool_status, $date = 0)
    {
        if (!$date) {
            $date = date('Ymd');
        }

        $redis = RedisCache::content();

        $key = 'onlineStatus|' . $date . '|*';

        $key_list = $redis->redis->keys($key);

        $business_ids = array();

        //全部标签离线(读写器异常)
        if ($rwtool_status == 6) {
            //查询所有读写器的营业厅
            $count_business_ids = _model('rfid_rwtool')->getFields('business_id', array(1 => 1));
            $online_business_ids = array();
            foreach ($key_list as $v) {

                list($res_name, $date, $business_id) = explode('|', $v);
                if (!$business_id) {
                    continue;
                }

                $online_business_ids[] = $business_id;
            }

            if (!$online_business_ids) {
                return $count_business_ids;
            }

            return array_diff($count_business_ids, $online_business_ids);
        }

        foreach ($key_list as $v) {

            list($res_name, $date, $business_id) = explode('|', $v);
            if (!$business_id) {
                continue;
            }

            //获取读写器标签总数
            $rwtool_info = _model('rfid_rwtool')->read(array('business_id' => $business_id));

            if (!$rwtool_info) {
                continue;
            }

            //查询在线标签数
            $num = self::get_label_online_count($business_id, $date);

            $status = rfid_helper::get_rwtool_status_code($rwtool_info['label_num'], $num);

            if ($status != 0 && $status == $rwtool_status) {
                $business_ids[] = $business_id;
            }
        }

        return $business_ids;
    }

}