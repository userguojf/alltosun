<?php

/**
 * alltosun.com  index.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 赵高举 (zhaogj@alltosun.com) $
 * $Date: 2018-4-9 下午5:35:51 $
 * $Id$
 */
class Action
{
    private $per_page = 20;
    private $member_id = 0;
    private $res_id = 0;
    private $res_name = '';
    private $member_user = '';
    private $member_info = array();

    public function __construct()
    {
        $this->member_id = member_helper::get_member_id();
        $this->member_info = member_helper::get_member_info($this->member_id);

        $this->module_name = '点赞管理';

        if (!$this->member_info) {
            return '请登录';
        }

        $this->res_id = $this->member_info['res_id'];
        $this->res_name = $this->member_info['res_name'];
        $this->member_user = $this->member_info['member_user'];
        Response::assign('member_info', $this->member_info);
        Response::assign('module_name', $this->module_name);
    }

    /**
     * 模块下的文章列表
     * @param string $action
     * @param array $param
     */
    public function __call($action = '', $param = array())
    {

        // 搜索条件
        $search_filter = tools_helper::get('search_filter', array());
        // 排序
        $order = ' ORDER BY `add_time` DESC ';
        $page = tools_helper::get('page_no', 1);
        // 点赞和文章 status 已发布1  已下线0
        $filter = array(1 => 1);
        if (!isset($search_filter['module'])) {
            $search_filter['module'] = 'qydev_news';
        }

        if (!isset($search_filter['search_type'])) {
            $search_filter['search_type'] = 1;
        }

        //搜索
        if (isset($search_filter['title']) && !empty($search_filter['title'])) {
            $filter['`title`  LIKE '] = '%' . $search_filter['title'] . '%';
        }
        // 筛选条件
        if (isset($search_filter['search_type']) && $search_filter['search_type']) {
            if ($search_filter['search_type'] == 1) {
                $filter['status'] = 1;
            } elseif ($search_filter['search_type'] == 2) {
                $filter['status'] = 0;
            }
        }

        // 权限
       /* if (isset($search_filter['put']) && $search_filter['put'] == 1) {
            // 下级投放
            if ($this->res_name == 'group') {
                $filter['res_name'] = 'province';
            } else if ($this->res_name == 'province') {
                $filter['res_name'] = 'city';
            } else if ($this->res_name == 'city') {
                $filter['res_name'] = 'area';
            } else if ($this->res_name == 'area') {
                $filter['res_name'] = 'business_hall';
            } else if ($this->res_name == 'business_hall') {
                $filter['res_id'] = $this->res_id;
            }

        } else {
            // 自身投放
            $filter['res_name'] = $this->res_name;
            $filter['res_id'] = $this->res_id;
        }*/


        $list = array();
        // 数据条数
        $count = _model($search_filter['module'])->getTotal($filter);
        if ($count) {
            $pager = new Pager($this->per_page);
            if ($pager->generate($count)) {
                Response::assign('pager', $pager);
            }
            $list = _model($search_filter['module'])->getList($filter, ' ' . $order . ' ' . $pager->getLimit($page));
        }

        Response::assign('search_filter', $search_filter);
        Response::assign('count', $count);
        Response::assign('list', $list);
        Response::display('admin/index.html');
    }

    /**
     * 某个文章的点赞列表
     * @return string
     */
    public function like_list()
    {
        $id = Request::getParam('id', 0);
        $module = Request::getParam('module', '');
        $page = tools_helper::get('page_no', 1);
        if (!$id) {
            return '请选择文章';
        }
        // 搜索条件
        $filter = array(
            'type_id' => $id,
            'res_name' => $module,
            'type' => 1,
            'status' => 1,
        );
        // 排序
        $order = ' ORDER BY `add_time` DESC ';

        $like_list = array();
        // 点赞列表数量
        $count = _model('like')->getTotal($filter);
        if ($count) {
            $pager = new Pager($this->per_page);
            if ($pager->generate($count)) {
                Response::assign('pager', $pager);
            }
            $like_list = _model('like')->field('user_name,add_time')->getList($filter, ' ' . $order . ' ' . $pager->getLimit($page));
        }
        // 获取标题
        $nav_title = like_helper::cut_str(_uri($module,$id,'title'));
        Response::assign('nav_title', $nav_title);
        Response::assign('count', $count);
        Response::assign('like_list', $like_list);
        Response::assign('module', $module);
        Response::display('admin/like_list.html');
    }

    /**
     * 某个文章的评论列表
     */
    public function comment_list()
    {
        $search_filter = tools_helper::get('search_filter', array());
        $page = tools_helper::get('page_no', 1);

        $order = ' ORDER BY `add_time` DESC ';
        $filter = array(
            'is_del' => 0,
            'pid' => 0,
            'res_name' => $search_filter['module'],
            'res_id' => $search_filter['id']
        );
        // 筛选
        if (isset($search_filter['search_type']) && $search_filter['search_type']) {
            if ($search_filter['search_type'] == 1) {
                $filter['examine'] = 1;
            } elseif ($search_filter['search_type'] == 2) {
                $filter['examine'] = 0;
            } elseif ($search_filter['search_type'] == 3) {
                $filter['status'] = 1;
                $filter['examine'] = 1;
            } elseif ($search_filter['search_type'] == 4) {
                $filter['examine'] = 1;
                $filter['status'] = 0;
            }
        }

        if (isset($search_filter['content']) && $search_filter['content']) {
            $filter['content LIKE'] = "%{$search_filter['content']}%";
        }

        $comment_list = array();

        $count = _model('comment')->getTotal($filter);
        if ($count) {
            $pager = new Pager($this->per_page);
            if ($pager->generate($count)) {
                Response::assign('pager', $pager);
            }
            $comment_list = _model('comment')->getList($filter, ' ' . $order . ' ' . $pager->getLimit($page));
        }
        $nav_title = like_helper::cut_str(_uri($search_filter['module'],$search_filter['id'],'title'));

        Response::assign('search_filter', $search_filter);
        Response::assign('nav_title', $nav_title);
        Response::assign('count', $count);
        Response::assign('comment_list', $comment_list);
        Response::display('admin/comment_list.html');
    }

    /**
     * 评论的回复页面
     * @return string
     */
    public function comment_reply()
    {
        $comm_id = Request::getParam('comm_id', 0);
        $search_filter = tools_helper::get('search_filter', array());
        if (!$comm_id) {
            return '请选择要回复的评论';
        }
        $info = _uri('comment', $comm_id);
        if (!$info) {
            return '该评论信息不存在';
        }
        Response::assign('info', $info);
        Response::assign('search_filter', $search_filter);
        Response::display('admin/comm_reply.html');
    }


    /**
     * 评论回复保存
     */
    public function comment_reply_save()
    {
        $id = Request::getParam('id', 0);
        // 获取回复内容
        $reply_content = trim(Request::Post('reply_content', ''));
        // 获取筛选条件
        $module = tools_helper::Post('module', '');
        $typeid = tools_helper::Post('typeid', 0);
        if (!$reply_content) {
            return '回复内容不得为空';
        }

        // 该条评论的数据内容
        $comment_info = _model('comment')->read(array('id' => $id));

        $info = array(
            'res_name' => $comment_info['res_name'],
            'res_id' => $comment_info['res_id'],
            'name' => $comment_info['name'],
            'avatar' => $comment_info['avatar'],
            'content' => $reply_content,
            'status' => '1',
            'pid' => $id,
        );

        // 查询表里是否已经有回复
        $reply_info = _model('comment')->read(array('pid' => $id));
        // 如果已经有回复，就去更新表里数据，否则创建一条新数据
        if (!$reply_info) {
            $res = _model('comment')->create($info);
        } else {
            $content = array(
                'content' => $reply_content,
            );
            $res = _model('comment')->update(array('pid' => $id), $content);
        }

        if (!$res) {
            return '回复失败';
        } else {
            // 回复成功则直接让该条评论数据变成已审核和已回复状态
            _model('comment')->update(array('id' => $id), array('examine' => 1, 'status' => 1));
            return array('回复成功', 'success', AnUrl("like/admin/comment_list?search_filter[module]=$module&search_filter[id]=$typeid"));
        }
    }
}