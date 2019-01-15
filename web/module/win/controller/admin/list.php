<?php
/**
 * alltosun.com 主页面 list.php
 * ============================================================================
 * 版权所有 (C) 2009-2018 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 赵高举 (zhaogj@alltosun.com) $
 * $Date: 2018/4/25 17:28 $
 * $Id$
 */

class Action
{

    private $per_page = 20;

    public function __construct()
    {
        $this->time = date('Y-m-d H:i:s', time());
        $this->member_id = member_helper::get_member_id();

        $member_info = member_helper::get_member_info($this->member_id);

        if ($member_info) {
            $this->member_res_name = $member_info['res_name'];
            $this->member_res_id = $member_info['res_id'];
            $this->ranks = $member_info['ranks'];
        }

        Response::assign('curr_member_ranks', $this->ranks);
    }

    public function __call($action = '', $params = array())
    {
        Response::display("list/index.html");
    }


    public function comment_list()
    {

        $page = tools_helper::get('page_no', 1);

        $comment_list = array();

        $filter = array(1 => 1);
        $order = ' ORDER BY `add_time` DESC ';

        $count = _model('comment')->getTotal($filter);
        if ($count) {
            $pager = new Pager($this->per_page);
            if ($pager->generate($count)) {
                Response::assign('pager', $pager);
            }
            $comment_list = _model('comment')->getList($filter, ' ' . $order . ' ' . $pager->getLimit($page));
        }
        Response::assign('comment_list', $comment_list);
        Response::display('list/comment_list.html');
    }


    public function add()
    {
        $id = tools_helper::get('id', 0);
        if (!$id) {
            return false;
        }
        $info = _model('comment')->read(['id' => $id]);
        Response::assign('info', $info);
        Response::display('list/add.html');
    }

    public function save()
    {
        $id = tools_helper::post('infoid', 0);
        $content = trim(tools_helper::post('content', ''));
        if (!$id) {
            return false;
        }
        $res = _model('comment')->update(['id' => $id], ['content' => $content]);

        if ($res) {
            echo '<script>Win10_child.close();</script>';
        } else {
            echo 222;
        }
    }

    public function ajax_delete()
    {
        $id = tools_helper::post('infoid', 0);
        if (!$id) {
            return json_encode(['msg' => '请求错误', 'code' => 0]);
        }
        $res = _model('comment')->update(['id' => $id], ['is_del' => 1]);
        if ($res) {
            return json_encode(['msg' => '删除成功', 'code' => 1]);
        } else {
            return json_encode(['msg' => '请求错误', 'code' => 0]);
        }
    }
}