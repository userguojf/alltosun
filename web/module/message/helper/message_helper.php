<?php
/**
 * alltosun.com  message_helper.php
 * ============================================================================
 * 版权所有 (C) 2009-2014 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 石武浩 (shiwh@alltosun.com) $
 * $Date:  2014-6-30 上午11:25:00 $
 * $Id$
*/

class message_helper
{
    /**
     * 初始化未读消息数量
     * @param int $user_id
     * @return boolean
     */
    public static function init_message_unread_num($user_id)
    {
        $message_info = _model('message_unread')->read(array('user_id'=>$user_id));
        if ($message_info) {
            return FALSE;
        }

        return !!_model('message_unread')->create(array('user_id'=>$user_id));
    }

    /**
     * 更新未读消息数量
     * @param int $user_id
     * @param string $field (message_num, sys_num, task_num, file_num, work_num, knowledge_num, calendar_num)
     * @param string $action add:增，delete:减，clean:清空
     * @param int $num
     * @return boolean
     */
    public static function update_message_unread_num($user_id, $field, $action, $num)
    {
        if (!user_helper::get_user_info($user_id) || empty($field) || empty($action)) {
            return FALSE;
        }

        if (!in_array($action, array('add', 'delete', 'clean'))) {
            return FALSE;
        }

        message_helper::init_message_unread_num($user_id);
        $message_info = _model('message_unread')->read(array('user_id'=>$user_id));

        if (!$message_info) {
            return FALSE;
        }

        if ($action == 'decrease' && $message_info["{$field}"] <= 0) {
            return FALSE;
        }

        switch ($action) {
            case 'add':
                $value = $message_info["{$field}"] + $num;
                break;
            case 'delete':
                $value = max($message_info["{$field}"] - $num, 0);
                break;
            case 'clean':
                $value = 0;
                break;
            default:
                return FALSE;
        }

        return !!_model('message_unread')->update(array('user_id'=>$user_id), 'SET '.$field.'='.$value);
    }

    /**
     * 取得未读消息数量
     * @param int $user_id
     * @return boolean
     */
    public static function get_unread_message($field, $user_id = 0)
    {
        if (!$user_id) {
            $user_id = user_helper::get_user_id();
        }

        return _uri('message_unread', array('user_id'=>$user_id), $field);
    }

    /**
     * 取两个人的会话ID
     * @param int $to_user_id
     * @param int $user_id
     */
    public static function get_message_id($to_user_id = 0, $user_id = 0)
    {
        if (!$to_user_id) {
            return 0;
        }
        if (!$user_id) {
            $user_id = user_helper::get_user_id();
        }

        $member = array($user_id, $to_user_id);
        natsort($member);

        $members = join(',', $member);

        $filter = array();
        $filter['members like'] = $members;
        $filter['status']      = 1;

        $message_info = _model('message')->read($filter);

        if (!$message_info) {
            return 0;
        }

        return $message_info['id'];
    }

    /**
     * 获取群聊的user_ids
     */
    public static function get_group_user_ids($message_id)
    {
        if (!$message_id) {
            return array();
        }
        $message_info = _model('message')->read(array('id' => $message_id));
        if (!$message_info || $message_info['members']) {
            return array();
        }

        $to_user_ids = _model('message_member')->getFields('to_user_id', array('message_id' => $message_id));
        //$to_user_ids = array_diff($to_user_ids, array(user_helper::get_user_id()));

        return $to_user_ids;
    }


    /**
     * 取得要发送的user_ids
     * @param string $user_ids
     * return array()
     */
    public static function get_to_user_ids($user_ids)
    {
        $user_ids = explode(',', $user_ids);
        $to_user_ids = array();
        if (empty($user_ids)) {
            return array();
        }

        foreach ($user_ids as $k => $v) {
            if (!$v) {
                continue;
            }
            $to_user_ids[$k] = user_helper::get_user_id_by_name($v);
        }

        natsort($to_user_ids);

        return $to_user_ids;
    }
    /**
     * 发送消息提醒
     * @param unknown $res_name
     * @param int $type 1、库存不足 2、用户付款 3、申请退款
     * @param unknown $store_id
     * @return string
     */
    public function send_message($goods_id,$type,$store_id,$user_id)
    {

        if(!isset($goods_id) && !isset($type) && !isset($store_id) && !isset($user_id)){
            return '请传递资源名字或类型';
        }

        $filter =array(
            'goods_id'  => $goods_id,
            'type'      => $type,
            'store_id'  => $store_id,
            'user_id'   => $user_id,
        );

        _model('message')->create($filter);

    }

    public static function pay_succ_message($store_id, $order_info)
    {

    }

    /**
     * 下发退款成功短信通知
     * @param int $phone
     * @param int $order_id
     * @param float $price
     * @return boolean
     */
    public static function send_refund_succ_mess($phone, $order_id, $price)
    {
        if (!$phone || !$order_id || !$price) {
            return false;
        }

        $params['tel']         = $phone;
        $params['content']     = json_encode(array( 'param1' => $order_id, 'param2' => $price));
        $params['template_id'] = 91005145;

        return _widget('message')->send_message($params);
    }

    /**
     * 下发发货短信通知
     * @param int $phone
     * @param int $order_id
     * @param str $logistics_res 例如 ［顺丰优选：875609825］
     * @return boolean
     */
    public static function send_logistics_mess($phone, $order_no, $logistics_res)
    {
        if (!$phone || !$order_no || !$logistics_res) {
            return false;
        }

        $params['tel']         = $phone;
        $params['content']     = json_encode(array( 'param1' => $order_no, 'param2' => $logistics_res));
        $params['template_id'] = 91005144;

        return _widget('message')->send_message($params);
    }

    /**
     * 下发支付成功短信通知
     * @param int $phone
     * @param int $order_id
     * @param str $logistics_res 例如 ［顺丰优选：875609825］
     * @return boolean
     */
    public static function send_pay_mess($phone, $user_name, $order_no)
    {
        if (!$phone || !$order_no || !$user_name) {
            return false;
        }

        $params['tel']         = $phone;
        $params['content']     = json_encode(array( 'param1' => $user_name, 'param2' => $order_no));
        $params['template_id'] = 91005206;

        return _widget('message')->send_message($params);
    }

    /**
     * 下发支付成功短信通知
     * @param int $phone
     * @param int $order_id
     * @param str $logistics_res 例如 ［顺丰优选：875609825］
     * @return boolean
     */
    public static function send_refund_succ_start($phone, $user_name, $type ,$price)
    {
        if (!$phone || !$user_name || !$type) {
            return false;
        }

        $params['tel']         = $phone;
        $params['content']     = json_encode(array( 'param1' => $user_name, 'param2' => $type , 'param3' => $price));
        $params['template_id'] = 91005207;

        return _widget('message')->send_message($params);
    }

    public static function send_pay_user_mess($phone , $order_code, $store_ids)
    {
        //
        if (!$phone || !$order_code || !is_array($store_ids)) {
            return false;
        }

        //
        $store_title_arr = _model('store')->getList($store_ids);

        $store_title = '';
        $length = count($store_title_arr);

        foreach ($store_title_arr as $k => $v) {
            $store_title .= "{$v['title']}";

            if ($v['mobile']) {
                $store_title .= "（{$v['mobile']}）";
            }

            if ($k < $length-1) {
                $store_title .= '、';
            }
        }

        $params['tel']         = $phone;
        $params['content']     = json_encode(array('param1' => $order_code, 'param2' => $store_title));
        $params['template_id'] = 91549232;

         return _widget('message')->send_message($params);
    }
}