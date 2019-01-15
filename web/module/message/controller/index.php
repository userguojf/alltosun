<?php
/**
 * alltosun.com message index.php
 * ============================================================================
 * 版权所有 (C) 2009-2014 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 石武浩 (shiwh@alltosun.com) $
 * $Date:  2014-6-30 下午12:54:07 $
 * $Id$
*/

class Action
{
    private $user_id;
    private $per_page = 10;

    public function __construct()
    {
        $this->user_id = user_helper::get_user_id();

        if (!$this->user_id) {
            if (!Request::isAjax()) {
                throw new AnMessageException('对不起，请登录！', 'permission', 'user/login');
            }
        }
    }

    /**
     * 获取私信详情
     */
    public function __call($action, $params = array())
    {
        $page_no = Request::getParam('page_no', 1);
        $message_id = (int)$action;

        $params = array(
            'message_id' => $message_id,
            'page_no'    => $page_no
        );

        _widget('message')->get_detail($params);

        if (Request::isAjax()) {

        } else {
            Response::display('detail.html');
        }
    }

    /**
     * 获取私信列表
     */
    public function index()
    {
        $list = _widget('message')->get_list();

        if (Request::isAjax()) {
            // TODO

        } else {
            //an_dump($list);
            Response::display('index.html');
        }
    }

    /**
     * IM 创建聊天时请求数据
     */
    public function IM_get_detail()
    {
        $to_user_id = Request::getParam('user_id', 0);
        if (!$to_user_id) {
            return 'empty';
        }

        // 查询是否有对话信息
        $members = array($to_user_id, $this->user_id);
        natsort($members);
        $filter = array();
        $filter['members like'] = join(',', $members);
        $filter['status']  = 1;
        $message_info = _model('message')->read($filter);
        if (!$message_info) {
            return 'empty';
        }

        $params = array(
            'message_id' => $message_info['id']
        );
        return _widget('message')->get_detail($params);
    }

    /**
     * 获取最新的一条消息
     */
    public function get_new_message()
    {

    }

    /**
     * 轮询判断状态 获取最新未读消息数量
     * @return string|multitype:string number unknown
     */
    public function ajaxUnReadMsg()
    {
        $unreadmsg_info = array();
        $unreadmsg_info = _uri('message_unread', array('user_id'=>$this->user_id));

        if (!$unreadmsg_info) {
            return 'empty';
        }

        $info = array(
            'info'      =>  'ok',
            'comment'   =>  $unreadmsg_info['comment'],
            'message'   =>  $unreadmsg_info['message'],
            'at'        =>  $unreadmsg_info['at'],
            'system'    =>  $unreadmsg_info['system'],
            'app'       =>  $unreadmsg_info['app'],
            'total'     =>  $unreadmsg_info['comment'] + $unreadmsg_info['message'] + $unreadmsg_info['at'] + $unreadmsg_info['system'] + $unreadmsg_info['app']
        );
        return $info;
    }
    /**
     * 库存不足时候提示
     */
    public  function add_tips()
    {
        $sku_id = tools_helper::post('sku_id', 0);
        $type = tools_helper::post('type', 1);
        if(empty($sku_id)) {
            return array('info'=>'faild','msg'=>'请传递sku_id');
        }
        
        //通过sku_id获取到相关信息
        $goods_id = _uri('sku',array('id'=> $sku_id),'goods_id');
        //获取到商家id
        //$store_id = _uri('goods',array('id'=>$goods_id),'store_id');
        
        $params=array(
            'goods_id' => $goods_id,
            'type'     => $type        );
        
        $result = _widget('message')->send_order_message($params);
        
        if(is_string($result)) {
            return array('info'=>'ok','msg'=>'库存记录成功');
        }
        
        return array('info'=>'faild','msg'=>'记录失败');
    }
    
}