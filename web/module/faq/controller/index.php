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
 * $Date: 2017-11-26 下午3:52:15 $
 * $Id$
 */

/**
 * 备注：diff_project  字段     1
 * share_dm          id = 1
 * @author 郭剑峰
 *
 */

class Action
{
    private $diff_project = 5;

    public function __call($action = '' , $param = array())
    {
        $filter = array();

        $question  = Request::get('question' , '');
        $is_search = Request::get('search' , '');

        $filter['status']       = 1;
        $filter['diff_project'] = $this->diff_project;

        $order  = ' ORDER BY `view_order` ASC , `id` DESC';

        if (!empty($is_search) && $is_search == 'is_search') {
            Response::assign('is_search' , $is_search);
        }

        if (!empty($is_search) && $is_search == 'is_search' && !empty($question)) {

            $filter['question LIKE'] = '%'.$question.'%';

            Response::assign('is_having' , true);
        }

        $faq_info = _model('faq_record')->getList($filter,$order);

        Response::assign('faq_info' , $faq_info);
        Response::assign('question' , $question);

        Response::display('index.html');
    }

    public function diff_type()
    {
        $diff_question = Request::get('diff_question' , '');

        if (!$diff_question) {
            return '由于网络原因，请刷新重新';
        }

        $filter = array();

        $filter = array('status' => 1);

        $filter['diff_project']  = $this->diff_project;
        $filter['diff_question'] = $diff_question;

        $order  = ' ORDER BY `view_order` ASC , `id` DESC';

        $diff_info = _model('faq_record')->getList($filter,$order);

        Response::assign('diff_info' , $diff_info);
        Response::display('type.html');
    }


    public function detail()
    {
        $id     = Request::get('id' , 0);

        $filter = array();

        $filter['id']     = $id;
        $filter['status'] = 1;
        $filter['diff_project'] = $this->diff_project;

        $faq_info = _model('faq_record')->getList($filter);

        Response::assign('faq_info' , $faq_info);
        Response::display('detail.html');
    }
}