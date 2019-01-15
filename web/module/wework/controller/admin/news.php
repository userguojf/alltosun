<?php
/**
 * alltosun.com 消息后台模板 news.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-4-18 上午10:06:23 $
 * $Id$
 */

class Action
{
    private $table = 'qydev_news';
    private $per_page = 20;

    public function __call( $action = '', $param = array() )
    {
        $page = Request::get('page_no' , 1) ;

        $search_filter = Request::get('search_filter' , array());

        $list = $filter = array();

        if (!$filter ) {
            $filter = array( 1 => 1 );
        }

        $count = _model($this->table)->getTotal($filter);

        if ($count) {
            $pager = new Pager($this->per_page);
            $list  = _model($this->table)->getList($filter , $pager->getLimit($page));

            if ($pager->generate($count,$page)) {
                Response::assign('pager', $pager);
            }
        }

        Response::assign('list' , $list);
        Response::assign('count' , $count);

        Response::assign('page' , $page);
        Response::assign('search_filter' , $search_filter );

        Response::display('admin/news_list.html');
    }


    //添加数据
    public function add()
    {
        $id = Request::get('id' , 0);

        if ( $id ) {
            $news_info = _uri($this->table , array('id'=>$id));

            Response::assign('info' , $news_info);
        }

        Response::display('admin/add_news.html');
    }

    public function save()
    {
        $info = tools_helper::post('info', array());
        $id   = tools_helper::post('id', 0);

        if ( !isset($info['title']) ||  !$info['title'] ) {
            return '请填写标题';
        }

        // 单个封面上传
        if (isset($_FILES['cover_link']['name']) && $_FILES['cover_link']['name']) {
            $info['cover_link'] = upload_file($_FILES['cover_link'], false, 'focus');
        } else {
            if ( !$id  ) {
                return '请上传封面';
            }
        }

        if ( !isset($info['content']) ||  !$info['content'] ) {
            return '请填写正文';
        }

        if ( $id ) {
            _model($this->table)->update($id, $info);
        } else {
            _model($this->table)->create($info);
        }
        return array('操作成功', 'success', AnUrl('wework/admin/news'));
    }

    public function delete()
    {
        $id  = Request::Post('id', 0);

        if (!$id) {
            return array('info' => '信息错误');
        }

        $info = _uri($this->table, $id);

        if (!$info) {
            return array('info' => '通讯录信息不存在');
        }
// p($info);exit();
        _model($this->table)->delete(array('id' => $id), " LIMIT 1 ");

        return array('info' => 'ok');
    }

    public function send()
    {
        $id  = Request::Post('id', 0);

        if (!$id) {
            return array('info' => '信息错误');
        }

        $info = _uri($this->table, $id);

        if (!$info) {
            return array('info' => '通讯录信息不存在');
        }

        if ( $info['status'] ) return '不能重复发送';

        _model($this->table)->update(array('id' => $id), array('date' => date("Y-m-d"), 'status' => 1));

        $touser = '1101021002051_13';
        $this->send_msg($info, $touser);

        return array('info' => 'ok');
    }

    public function send_msg($info, $touser)
    {
        $agent_id = 27;

        $params = '{
            "touser": "'. $touser .'",
            "msgtype": "news",
            "agentid": '.$agent_id.',
            "news": {
                 "articles":[
                {
               "title": "' . $info['title'] . '",
               "description": "'.$info['summary'].'",
               "url": "'. SITE_URL . '/wework/news?id='. $info['id'] .'",
               "picurl": "'.  SITE_URL .'/upload/' .$info['cover_link'] .'"
                }
            ]
            }
        }';

        $info = _widget ( 'qydev.send_msg' )->send_message ($touser, $params, $agent_id);

        if ( isset($info['errmsg']) && $info['errmsg'] == 'ok' ) {
            //$this->record('install', $business_hall_id, $touser);
        }

        return true;
    }

    public function share()
    {
        $news_id = tools_helper::get('news_id', 0);

         if ( $news_id ) {
            $news_share_info = _uri('qydev_news_share' , array('news_id' => $news_id));

            Response::assign('news_id' , $news_id);
            Response::assign('info' , $news_share_info);
        }

        Response::display('admin/share.html');
    }

    public function share_save()
    {
        $info = tools_helper::post('info', array());
        $id   = tools_helper::post('id', 0);

        if ( !isset($info['news_id']) ||  !$info['news_id'] ) {
            return '请先选择一条图文消息';
        }

        if ( !$id ) {
            $news_share_info = _uri('qydev_news_share' , array('news_id' => $info['news_id']));

            if ( $news_share_info ) return '该图文消息已经存在分享内容';
        }

        if ( !isset($info['title']) ||  !$info['title'] ) {
            return '请填写标题';
        }

        // 单个封面上传
        if (isset($_FILES['img_link']['name']) && $_FILES['img_link']['name']) {
            $info['img_link'] = upload_file($_FILES['img_link'], false, 'focus');
        } else {
            if ( !$id  ) {
                return '请上传封面';
            }
        }

        if ( !isset($info['summary'])) {
            return '非法操作';
        }

        if ( $id ) {
            _model('qydev_news_share')->update($id, $info);
        } else {
            _model('qydev_news_share')->create($info);
        }
        return array('操作成功', 'success', AnUrl('wework/admin/news'));
    }
}