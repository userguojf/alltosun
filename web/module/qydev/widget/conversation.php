<?php
/**
  * alltosun.com 会话widget conversation.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2017年4月18日 下午4:27:16 $
  * $Id$
  */


class conversation_widget
{
    /**
     * 是否存在未完成的会话
     * @param unknown $user_id
     */
    public function exists_not_finished($user_id)
    {
        if (!$user_id) {
            return true;
        }

        $filter = array(
                'qy_user_id' => $user_id,
                'status'     => 1
        );
        $info = _model('qy_conversation')->read($filter);
        if (!$info) {
            return false;
        } else {
            return true;
        }

    }


    /**
     * 创建会话
     * @param unknown $user_id 企业用户
     * @param unknown $type    类型
     * @param unknown $agent_id 应用id
     * @return unknown
     */
    public function create_conversation($user_id, $type, $agent_id){
        //查询是否正在会话中
        $conversation = _uri('qy_conversation', array('qy_user_id' => $user_id, 'status' => 1, 'type' => $type));
        if ($conversation) {
            return $conversation['id'];
        }

        $qy_service_id = $this->assign_service($type);
        $filter = array(
                'qy_user_id'        => $user_id,
                'qy_service_id'     => $qy_service_id,
                'qy_agent_id'       => $agent_id,
                'type'              => $type
        );

        return _model('qy_conversation')->create($filter);
    }

    /**
     * 获取正在会话中的会话信息
     * @param unknown $user_id
     */
    public function get_in_conversation($user_id){
        if (!$user_id) {
            return false;
        }

        //获取正在会话中的会话
        $cvs_id = qy_conversation_helper::get_user_curr_conversation($user_id);

        $filter = array(
                'qy_user_id' => $user_id,
                'status'     => 1,
        );

        if ($cvs_id) {
            return _uri('qy_conversation', $cvs_id);
        }  else {
            return false;
        }

    }

    /**
     * 获取未结束的会话信息
     * @param unknown $user_id
     */
    public function get_not_end_conversation($from_user, $event_key=''){
        if (!$user_id) {
            return false;
        }

        $filter = array(
                'qy_user_id' => $user_id,
                'status'     => 1,
        );

        if ($event_key && isset(qy_conversation_config::$conversation_type[$event_key])) {
            $filter['type'] = $event_key;
        }

        return _model('qy_conversation')->read($filter, ' ORDER BY `id` DESC LIMIT 1 ');

    }

    /**
     * 分配客服
     * @param unknown $type
     * @return unknown
     */
    public function assign_service($type)
    {

        //根据类型获取客服
        $service_list = ONDEV?qy_conversation_config::$service_list_ondev[$type]:qy_conversation_config::$service_list[$type];
        //$service_list = qy_conversation_config::$service_list[$type];

        if (count($service_list) == 1) {
            return $service_list[0];
        }

        //取会话最少的
        $count_arr = array();
        foreach ($service_list as $k => $v) {
            $filter = array(
                    'status'            => 1,
                    'qy_service_id'     => $v
            );
            $count = _model('qy_conversation')->getTotal($filter);

            if ($count == 0) {
                return $v;
            }
            $count_arr[$v] = $count;
        }

        return array_search(min($count_arr), $count_arr);

    }

}