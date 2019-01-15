<?php

/**
 * alltosun.com 人人接口实现类 renren.php
 * ============================================================================
 * 版权所有 (C) 2009-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 宁海新 (ninghx@alltosun.com) $
 * $Date: 2012-9-18 上午10:36:04 $
 * $Id: renren.php 643 2013-02-07 12:16:41Z anr $
*/

require_once AnPHP::$dir_3rd.'/OpenApi/renren/requires.php';

class renrenWrapper extends AnOpenApiAbstract implements AnOpenApiUserWrapper, AnOpenApiTWrapper, AnOpenApiCommentWrapper
{
    private $client;

    /**
     * 授权方法
     */
    public function authorize()
    {
        require_once 'Authorize.php';
        $auth_instance = new renrenAuthorize();
        return $auth_instance->authorize();
    }

    /**
     * 授权的回调函数
     */
    public function callback($code)
    {
        if (!$code) {
            return false;
        }

        require_once 'Authorize.php';
        $auth_instance = new renrenAuthorize($code);
        return $auth_instance->callback();
    }

    /**
     * 检查是否授权
     */
    public function checkAuthorize()
    {
        $renren_akey = AnOpenApiAbstract::$akey;
        $renren_skey = AnOpenApiAbstract::$skey;

        $openapi_connect_instance = AnOpenApiConnect::connect();
        $access_token = $openapi_connect_instance->getAccessToken();
        if (!$access_token) {
            throw new AnException('acess_token_fail');
        }

        $oauth = new RenRenOauth();
        $key = $oauth->getSessionKey($access_token);
        $this->client = new RenRenClient();
        $this->client->setSessionKey($key['renren_token']['session_key']);
    }

    public function publicTimeLine($page = 1, $count = 50, $base_app = 0)
    {
        return '';
    }

    public function homeTimeLine($page = 1, $count = 50, $since_id = 0, $max_id = 0, $base_app = 0, $feature = 0)
    {
        return '';
    }

    public function friendsTimeLineIds()
    {
        return '';
    }

    public function userTimeLine($uid = NULL , $page = 1 , $count = 50 , $since_id = 0, $max_id = 0, $feature = 0, $trim_user = 0, $base_app = 0)
    {
        return '';
    }

    public function userTimeLineIds()
    {
        return '';
    }

    public function rtTimeLine($sid, $page = 1, $count = 50, $since_id = 0, $max_id = 0, $filter_by_author = 0)
    {
        return '';
    }

    public function rtTimeLineIds()
    {
        return '';
    }

    public function rtByMe($page = 1, $count = 50, $since_id = 0, $max_id = 0, $filter_by_type = 0)
    {
        return '';
    }

    public function mentions($page = 1, $count = 50, $since_id = 0, $max_id = 0, $filter_by_author = 0, $filter_by_source = 0, $filter_by_type = 0)
    {
        return '';
    }

    public function mentionsIds()
    {
        return '';
    }

    public function bilateralTimeLine()
    {
        return '';
    }

    /**
     * 根据ID获取单条微博信息
     * @param int $id 要获取已发表的微博ID, 如ID不存在返回空
     * @return array
     */
    public function show($id)
    {
        $this->checkAuthorize();
        return $this->client->POST('status.get', array($id));
    }

    public function queryMid($id, $type = 1, $is_batch = 0)
    {
        return '';
    }

    public function queryId($mid, $type = 1, $is_batch = 0, $inbox = 0, $isBase62 = 0)
    {
        return '';
    }

    public function hotRepostDaily($count = 20, $base_app = 0, $filter_by_type = 0)
    {
        return '';
    }

    public function hotRepostWeekly($count = 20,  $base_app = 0)
    {
        return '';
    }

    public function hotCommentsDaily($count = 20, $base_app = 0)
    {
        return '';
    }

    public function hotCommentsWeekly($count = 20, $base_app = 0)
    {
        return '';
    }

    public function count()
    {
        return '';
    }


    /**
     * 用户转发状态的操作，支持同时评论给被转发人。
     * @param int $rt_id 被转发的状态id
     * @param string $status 用户更新的状态信息，与转发前的内容加在一起最多240个字符
     * @param int $owner_id 被转发的状态所有者的id
     */
    public function rt($rt_id, $status = '', $owner_id = 0)
    {
        $this->checkAuthorize();
        return $this->client->POST('status.forward', array($rt_id, $status, $owner_id));
    }

    public function delete($t_id)
    {
        return '';
    }

    /**
     * 发微博
     * @param string $status
     * @param string $pic_path
     */
    public function update($status, $pic_path = '', $lat = '', $long = '')
    {
        $this->checkAuthorize();
        return $this->client->POST('status.set', array($status));
    }

    public function uploadUrlText($status, $url, $client_ip = '')
    {
        return '';
    }

    /**
     * 获取表情
     * @param string $type 无用
     * @param string $language 无用
     */
    public function getEmotions($type = 'face', $language = "cnname")
    {
        $this->checkAuthorize();
        return $this->client->POST('status.getEmoticons', array());
    }

    public function getCommentListBySid($sid, $page = 1, $count = 50, $since_id = 0, $max_id = 0, $filter_by_author = 0)
    {
        return '';
    }

    public function commentByMe($page = 1 , $count = 50, $since_id = 0, $max_id = 0,  $filter_by_source = 0)
    {
        return '';
    }

    public function commentToMe($page = 1 , $count = 50, $since_id = 0, $max_id = 0, $filter_by_author = 0, $filter_by_source = 0)
    {
        return '';
    }

    public function commentTimeLine($page = 1, $count = 50, $since_id = 0, $max_id = 0)
    {
        return '';
    }

    public function commentMentions($page = 1, $count = 50, $since_id = 0, $max_id = 0, $filter_by_author = 0, $filter_by_source = 0)
    {
        return '';
    }

    public function commentShowBatch($cids)
    {
        return '';
    }

    /**
     * 发布评论
     * @param int $t_id 需要评论的微博ID。
     * @param string $comment 评论内容
     * @param int $owner_id 。
     */
    public function comment($t_id, $comment, $owner_id = 0)
    {
        $this->checkAuthorize();
        return $this->client->POST('status.addComment', array($t_id, $comment, $owner_id));
    }

    public function deleteComment($comment_id)
    {
        return '';
    }

    public function replyComment($sid, $text, $cid, $without_mention = 0, $comment_ori = 0)
    {
        return '';
    }

    /**
     * 获取用户信息
     * @param string $user_id 多个用户id用逗号分割,如果为空即当前access_token对应的id
     */
    public function getUserInfo($user_id, $fields = '')
    {
        $this->checkAuthorize();
        return $this->client->POST('users.getInfo', array($user_id, $fields));
    }

    /**
     * 根据用户id获取用户信息
     * @param int $uid
     */
    public function getUserInfoById($uid)
    {
        return;
    }

    /**
     * 根据用户昵称获取用户信息
     * @param string $screen_name
     */
    public function getUserInfoByName($screen_name)
    {
        return;
    }

    /**
     * 获取好友列表
     * @param int $start
     * @param int $end
     */
    public function getFriendsList($start = 0, $end = 20)
    {
        $this->checkAuthorize();
        return $this->client->POST('friends.getFriends', array($start, $end));
    }

    public function searchFriends($q)
    {
        $this->checkAuthorize();
        return $this->client->POST('friends.search', array($q));
    }
}
?>