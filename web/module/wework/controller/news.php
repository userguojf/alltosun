<?php
/**
 * alltosun.com  news.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-4-23 下午12:26:56 $
 * $Id$
 */

class Action
{
    private $table = 'qydev_news';
    private $member_id = 0;
    private $member_info = array();
    private $agent_id    = 21;
    private $alltosun_agent_id = 27;

    /**
     * 构造
     */
    public function __construct()
    {

        $this->member_id = member_helper::get_member_id();
        $this->member_info = member_helper::get_member_info($this->member_id);

        Response::assign('member_id', $this->member_id);
    }

    public function __call($action = '' , $param = array())
    {
        $id   = tools_helper::get('id', 0);
        $type = tools_helper::get('type', 0);
        $auth = tools_helper::get('auth', 0);

        if ( !$id ) return '素材下架';

        $content_auth = isset($_SESSION['wework_user_id']) && $_SESSION['wework_user_id']
        ? $_SESSION['wework_user_id'] : '';

        $auth_target = isset($_SESSION['wework_auth_id']) && $_SESSION['wework_auth_id'] ? $_SESSION['wework_auth_id'] : false;

        if ( ONDEV ) {
            $agent_id = $this->alltosun_agent_id;
        } else {
            $agent_id = $this->agent_id;
        }

        if ( is_weixin() && !$auth_target ) {
            // 授权标识
            $_SESSION['wework_auth_id'] = true;

            $return_url = AnUrl("wework/news?id={$id}");
            wework_helper::check_wework_auth($return_url, $agent_id);

//             $url = AnUrl("qydev/auth?return_url=" . urlencode($return_url));
            qydev_helper::redirect($url);
        }

        $filter = $content_list = array();

        $filter['id']   = $id;

        $info = _model('qydev_news')->read($filter);

        if ( !$info )  return '素材已经失效';

        $news_share_info = _uri('qydev_news_share' , array('news_id' => $id));

        // 阅读数、点赞数  都可以

        // 授权成功的使用唯一ID  失败 用户的 IP 
        $user_ip       = $_SERVER ['REMOTE_ADDR'];
        $qydev_user_id = isset($_SESSION['wework_user_id']) && $_SESSION['wework_user_id'] 
                        ? $_SESSION['wework_user_id'] : $user_ip;

        $user_agent  = $_SERVER['HTTP_USER_AGENT'];
        $user_number = $this->member_info && isset( $this->member_info['member_user'] ) 
                        ? $this->member_info['member_user'] : $user_agent;
        // 阅读数
        $news_read_info = _model('qydev_news_operate_record')->read(
                array(
                    'news_id'     => $info['id'],
                    'user_number' => $user_number,
                    'unique_id'   => $qydev_user_id,
                    'type'        => 1
                ));

        if ( !$news_read_info ) {
            _model($this->table)->update($info['id'], " SET `reading_num` = `reading_num` + 1 ");

            _model('qydev_news_operate_record')->create(
                array(
                    'news_id'     => $info['id'],
                    'user_number' => $user_number,
                    'unique_id'   => $qydev_user_id,
                    'type'        => 1
                )
            );
        }

        // 点赞
        $news_zan_info = _model('qydev_news_operate_record')->read(
                array(
                        'news_id'     => $info['id'],
                        'user_number' => $user_number,
                        'unique_id'   => $qydev_user_id,
                        'type'        => 2,
                ));

        if ( $news_zan_info ) {
            Response::assign('zan' , $news_zan_info);
        }

        // 必须是企业号内部人员才能评论
        if ( is_weixin() && $this->member_info ) {
            $content_list = _model('qydev_news_content')->getList(array('news_id' => $id), " ORDER  BY `id` DESC ");
        }

        Response::assign('content_auth', $content_auth);
        Response::assign('news_share_info', $news_share_info);
        Response::assign('member_id', $this->member_id);
        Response::assign('id' , $info['id']);
        Response::assign('info' , $info);
        Response::assign('content_list' , $content_list);

//         exit();
        Response::display('news.html');
    }

    public function content()
    {

        $id   = tools_helper::get('id', 0);

        if ( !$id ) return '素材下架';

        $filter =  array();
        $filter['id']   = $id;

        $info = _model('qydev_news')->read($filter);

        if ( !$info )  return '素材已经失效';

        Response::assign('info' , $info);
        Response::display('content.html');
    }

    public function write()
    {
        $id      = tools_helper::post('id', 0);
        $content = tools_helper::post('content', '');

        if ( !$id || !$content ) return array('info' => 'no', 'msg' => 'no');

        $filter = $user_info = array();

        $filter['id']     = $id;

        $info = _model('qydev_news')->getList($filter);

        if ( !$info )  return array('info' => 'no', 'msg' => 'no');

        if ( isset($_SESSION['wework_user_id']) && $_SESSION['wework_user_id'] ) {
            $user_info = _uri('wework_user', array('user_id' => $_SESSION['wework_user_id']));
        }

        $info = _model('qydev_news_content')->create(
                array(
                        'news_id' => $id,
                        'content' => $content,
                        'user_name' => $user_info ? $user_info['name'] : $this->member_info['member_user'],
                        'avatar'    => $user_info ? $user_info['avatar'] : SITE_URL . "/images/share.jpg"
                )
            );

        _model('qydev_news')-> update( $id, " SET `content_num` = `content_num` + 1 " );

        return array('info' => 'ok', 'msg' => 'ok');
    }
    
    public function share_save()
    {
        $id = tools_helper::post('id', 0);
        $type = tools_helper::post('type', 0);

        if ( !$id || !$type ) {
            return '';
        }

        $news_info = _uri('qydev_news', array('id' => $id));

        if ( !$news_info ) {
            return '';
        } else {
            _model('qydev_news')->update(array('id' => $id)," SET `share_num` = `share_num` + 1 " );
        }

        _model('qydev_share_record')->create(array(
                'res_id'   => $id,
                'res_name' => 'news',
                'type'     => $type
            )
        );

        return true;
    }

}