<?php
/**
  * alltosun.com 企业客服会话 service.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2017年4月21日 下午2:25:45 $
  * $Id$
  */
class Action
{
    public $member_id;
    public $member_name;
    public $service_id = '';
    public $type = 0;
    public function __construct()
    {
        $this->member_id   = member_helper::get_member_id();
        if (!$this->member_id) {
            return '请登录';
        }
        $member_info = member_helper::get_member_info($this->member_id);
        $this->member_name = $member_info['member_user'];
        //service_ 长度为8 ， 客服member_name都以service_开头
        if (strlen($this->member_name) < 8 && ($this->member_name != 'admin' || substr($this->member_name, 0, 8) != 'service_' )) {
            return '您无此权限';
        }

        //获取type
        if ($this->member_name != 'admin') {
            $this->service_id = substr($this->member_name, 8, strlen($this->member_name));
            if (!$this->service_id) return '您无此权限';
            foreach (qy_conversation_config::$service_list as $k => $v) {
                if (in_array($this->service_id, $v)) {
                    $this->type = $k;
                }
            }

            if (!$this->type && ONDEV) {
                foreach (qy_conversation_config::$service_list_ondev as $k => $v) {
                    if (in_array($this->service_id, $v)) {
                        $this->type = $k;
                    }
                }
            }
            if (!$this->type) return '您无此权限';
        }

        Response::assign('conversation_type', $this->type);
        Response::assign('service_id', $this->service_id);

    }
    public function __call($action='', $params=array())
    {
        $filter = array(
                'status' => 1
        );

        //会话业务线类型，20170705废除此条件，因存在一个客服服务多条业务线的问题
//         if ($this->type) {
//             $filter['type'] = $this->type;
//         }

        if ($this->service_id) {
            $filter['qy_service_id'] = $this->service_id;
        }

        //获取会话列表
        $conversation_list = _widget('qy_conversation')->get_conversation_list($filter);
        $message_list      = array();
        if (isset($conversation_list[0])) {
            $message_list = _widget('qy_message')->get_message_list(array('conversation_id' => $conversation_list[0]['id']));
        }

        //表情
        $emotions = json_decode(emotion_config::$weixin_emotions, true);
        $face_list = array();
        foreach ($emotions['emotions'][0] as $k => $v) {
            $face_list[$k] = '<img style="display: inline;" data-phrase="'.$v['phrase'].'" src="'.$v['url'].'" />';
        }

        //获取快速回复列表
        $reply_list = qy_quick_reply_helper::get_reply_list();
        $json_conversation_list = json_encode($conversation_list);
        $json_message_list      = json_encode($message_list);


        Response::assign('json_conversation_list', $json_conversation_list);
        Response::assign('message_list', $message_list);
        Response::assign('face_list', $face_list);
        Response::assign('reply_list', $reply_list);
        Response::assign('json_message_list', $json_message_list);
        Response::assign('conversation_list', $conversation_list);
        Response::display('admin/service/online.html');
    }

    //导出会话信息
    public function export_msg()
    {
        $conversation_id = tools_helper::get('conversation_id', 0);
        if (!$conversation_id) {
            return false;
        }

        //消息列表
        $message_list = _widget('qy_message')->get_message_list(array('conversation_id' => $conversation_id));

        //会话详情
        $conversation_info = qy_conversation_helper::completion_conversation_user_info(array(), $conversation_id);

        //获取客服名称
        $service_name = _uri('public_contact_user', array('unique_id' => $conversation_info['qy_service_id']), 'user_name');
        $service_name = $service_name ? $service_name : $conversation_info['qy_service_id'];

        $list = array();
        foreach ($message_list as $k => $v) {
            $tmp_list = array();
            if ($v['is_reply'] == 1) {
                $tmp_list[] = '';
                $tmp_list[] = '';
                $tmp_list[] = '';
                $tmp_list[] = '';
                $tmp_list[] = '';
                $tmp_list[] = $v['content'];
                $tmp_list[] = $service_name;

            } else {
                $tmp_list[] = $conversation_info['user_name'];
                $tmp_list[] = $conversation_info['user_number'];
                $tmp_list[] = $conversation_info['hall_name'];
                $tmp_list[] = $conversation_info['user_phone'];
                $tmp_list[] = $v['content'];
                $tmp_list[] = '';
                $tmp_list[] = '';
            }

            $list[] = $tmp_list;
        }

        $params['data'] = $list;
        $params['head'] = array('咨询用户','编码','营业厅','联系电话' , '咨询内容' , '回复内容', '客服');
        Csv::getCvsObj($params)->export();
    }
}