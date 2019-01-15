<?php

/**
 * alltosun.com qq微博接口实现类 qqweibo.php
 * ============================================================================
 * 版权所有 (C) 2009-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 宁海新 (ninghx@alltosun.com) $
 * $Date: 2012-9-18 上午10:35:21 $
 * $Id: qqsns.php 643 2013-02-07 12:16:41Z anr $
*/

class qqsnsWrapper extends AnOpenApiAbstract implements AnOpenApiUserWrapper, AnOpenApiTWrapper, AnOpenApiCommentWrapper
{
    /**
     * 授权方法
     */
    public function authorize()
    {
        require_once 'Authorize.php';
        $auth_instance = new qqsnsAuthorize();
        return $auth_instance->authorize();
    }

    /**
     * 授权回调函数
     */
    public function callback($code)
    {
        if (!$code) {
            return false;
        }

        require_once 'Authorize.php';
        $auth_instance = new qqsnsAuthorize($code);
        return $auth_instance->callback();
    }

    /**
     * 检查授权
     */
    public function checkAuth()
    {
        if (isset($_SESSION['t_access_token']) && !$_SESSION['t_access_token']) {
            throw new AnException('acess_token_fail');
        }
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
        return '';
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
    public function getUserInfo($user_id = '', $fields = '')
    {
        $get_user_info_url = "https://graph.qq.com/user/get_user_info?"
                . "access_token=" . $_SESSION['access_token']
                . "&oauth_consumer_key=" . $_SESSION["appid"]
                . "&openid=" . $_SESSION["openid"]
                . "&format=json";

        $info = file_get_contents($get_user_info_url);
        $arr = json_decode($info, true);

        return $arr;
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

    public function getFriendsList($start = 0, $end = 20)
    {
        return '';
    }

    public function searchFriends($q)
    {
        return '';
    }
}
?>