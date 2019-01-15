<?php

/**
 * alltosun.com  kaixin.php
 * ============================================================================
 * 版权所有 (C) 2009-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 宁海新 (ninghx@alltosun.com) $
 * $Date: 2012-9-18 下午5:42:12 $
 * $Id: kaixin.php 643 2013-02-07 12:16:41Z anr $
*/

require_once AnPHP::$dir_3rd.'/OpenApi/kaixin/kxClient.php';

class kaixinWrapper extends AnOpenApiAbstract implements AnOpenApiUserWrapper, AnOpenApiTWrapper, AnOpenApiCommentWrapper
{
    private $connection;

    /**
     * 授权方法
     */
    public function authorize()
    {
        require_once 'Authorize.php';
        $auth_instance = new kaixinAuthorize();
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
        $auth_instance = new kaixinAuthorize($code);
        return $auth_instance->callback();
    }

    private function checkAuthorize()
    {
        $openapi_connect_instance = AnOpenApiConnect::connect();
        $access_token = $openapi_connect_instance->getAccessToken();
        if (!$access_token) {
            throw new AnException('acess_token_fail');
        }

        $this->connection = new KXClient($access_token, AnOpenApiAbstract::$config);
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

    public function show($id)
    {
        return '';
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

    public function rt($rt_id, $status = '', $owner_id = 0)
    {
        return '';
    }

    public function delete($t_id)
    {
        return '';
    }

    /**
     * 发微博
     * @param string $status
     * @param string $pic_path 图片路径
     */
    public function update($status, $pic_path = '', $lat = '', $long = '')
    {
        $this->checkAuthorize();
        return $this->connection->records_add($status, $pic_path);
    }

    public function uploadUrlText($status, $url, $client_ip = '')
    {
        return '';
    }

    public function getEmotions($type = 'face', $language = "cnname")
    {
        return '';
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

    public function comment($t_id, $comment, $owner_id = 0)
    {
        return '';
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
        if ($user_id) {
            return $this->connection->users_show($user_id, $fields);
        } else {
            return $this->connection->users_me($fields);
        }
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
        return $this->connection->friends_me(NULL, $start, $end);
    }

    public function searchFriends($q)
    {
        return '';
    }
}
?>