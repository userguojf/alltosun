<?php
/**
 * alltosun.com  win10
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * szy: 宋志宇 (songzy@alltosun.com)
 * 2018年3月31日: 2016-7-26 下午3:05:10
 * Id
 */

class Action
{
    
    public function __construct()
    {
        $this->time        = date('Y-m-d H:i:s',time());
        $this->member_id   = member_helper::get_member_id();

        $member_info = member_helper::get_member_info($this->member_id);

        if ($member_info) {
            $this->member_res_name = $member_info['res_name'];
            $this->member_res_id   = $member_info['res_id'];
            $this->ranks           = $member_info['ranks'];
        }

        Response::assign('curr_member_ranks', $this->ranks);
    }

    public function __call($action = '', $params = array())
    {
        Response::display("admin/index.html");
    }
    
    
    public function  message_list()
    {        
       $list = _model('vbot_message')->getList(array('1'=>1));
        Response::assign('list',$list);
        Response::display("admin/list.html");
    }
    
    
    public function add()
    {
      
        Response::display("admin/add.html");
    }
    
    public function edit()
    {
        $id = Request::Get('id', 0);
        if (!$id) {
            return '请选择您要操作的信息';
        }
        
        $info = _uri('vbot_message', $id);
        
        if (!$info ) {
            return '您操作的信息不存在';
        }
        Response::assign('info',$info);
        Response::display("admin/add.html");
    }
    
    public function save()
    {
        $id          = Request::getParam('id', 0);
        $info        = Request::getParam('info', array());
        if ($id) {
            _model('vbot_message')->update($id, $info);
        } else {
            $id = _model('vbot_message')->create($info);
        }
        
        Response::redirect(AnUrl('win/admin/index/message_list'));
    }
    
    /**
     * 删除
     */
    public function delete()
    {
        $id = Request::getParam('id');
        if (!$id) {
            return '对不起，请选择您要删除的信息！';
        }
    
        $ids = explode(',', trim($id, ','));
        foreach ($ids as $v) {
            $info = _uri('vbot_message', $v);
            if (!$info) {
                continue;
            }
    
            _model('vbot_message')->delete($v);
        }
    
        Response::redirect(AnUrl('win/admin/index/message_list'));
    }
}
?>