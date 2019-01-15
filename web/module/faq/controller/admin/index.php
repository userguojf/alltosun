<?php
/**
 * alltosun.com  index.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-11-25 下午8:01:20 $
 * $Id$
 */
class Action
{
    private $diff_project = 5;

    private $per_page     = 20;
    private $member_id    = 0;
    private $member_info  = array();

    public function __construct()
    {
        $this->member_id    = member_helper::get_member_id();
        $this->member_info  = member_helper::get_member_info($this->member_id);

        Response::assign('member_info',$this->member_info);
        Response::assign('project',$this->diff_project);
    }


    public function __call($action='',$param=array())
    {
        $search_filter = Request::get('search_filter', array());
        $order         = " ORDER BY `view_order` ASC , `id`  DESC ";
        $page          = tools_helper::get('page_no', 1);

        $filter = $list = array();
        //搜索
        if(isset($search_filter['title']) && !empty($search_filter['title'])) {
            $filter['`question`  LIKE '] ='%'.$search_filter['title'].'%';
        }

        if(!$filter) {
            $filter=array('1'=>1);
        }

        $filter['diff_project'] = $this->diff_project;

        $count = _model('faq_record')->getTotal($filter);

        if ($count) {
            $pager  = new Pager($this->per_page);
            $list   = _model('faq_record')->getList($filter ,$order.$pager->getLimit($page));

            if ($pager->generate($count,$page)) {
                Response::assign('pager', $pager);
            }
        }

        Response::assign('count', $count);
        Response::assign('list' , $list );
        Response::assign('search_filter',$search_filter);
        Response::assign('page' , $page);

        Response::display('admin/index.html');
    }

    public function add()
    {
        $id = Request::get('id',0);
        if ($id) {
            $faq_info = _model('faq_record')->read(array('id' => $id));
            Response::assign('faq_info',$faq_info);
        } else {
            Response::assign('add','add');
        }

        Response::display('admin/add.html');
    }

    public function save()
    {
        $id   = Request::post('id' , 0);

        $info = Request::post('info' , array());

        //标题不为空
        if (!isset($info['question']) || empty($info['question'])) return '标题不能为空';

        //问题分类不为空
        if (!isset($info['diff_question']) || empty($info['diff_question'])) return '问题分类不能为空';

        //判断
        if ($info['site_type'] ==1) {
            if (!isset($info['answer']) || empty($info['answer'])) return '问题描述不能为空';
        } else {
            if (!isset($info['link']) || empty($info['link'])) return '外部链接不能为空';
        }

        //判断修改、创建
        if (!$id) {
            //创建
            _model('faq_record')->create($info);
            
            return array('操作成功', 'success', AnUrl("faq/admin"));
        }

        //修改
        if ($info['site_type']==1) {
            $info['link']   = '';
        } else {
            $info['answer'] = '';
        }

        //修改
        _model('faq_record')->update(array('id' => $id),$info);

        return array('操作成功', 'success', AnUrl("faq/admin"));

        Response::display('admin/add.html');
    }

    //删除
    public function delete()
    {
        $id = Request::getParam('id',0);

        if (!$id) {
            return array('info'=>'请选择删除的数据');
        }

        $info = _model('faq_record')->read(array('id' => $id));

        if ($info['status']) {
            return '请下线再删除';
        }

        _model('faq_record')->delete(array('id' => $id));

        return array('info' => 'ok');
    
    }
}