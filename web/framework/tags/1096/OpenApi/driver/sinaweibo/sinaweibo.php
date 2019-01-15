<?php

/**
 * alltosun.com 新浪微博接口实现类 sinaweibo.php
 * ============================================================================
 * 版权所有 (C) 2009-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 宁海新 (ninghx@alltosun.com) $
 * $Date: 2012-9-15 下午4:21:31 $
 * $Id: sinaweibo.php 788 2014-07-06 03:40:21Z qianym $
*/

require_once AnPHP::$dir_3rd.'/OpenApi/sinaweibo/saetv2.ex.class.php';

class sinaweiboWrapper extends AnOpenApiAbstract implements AnOpenApiTWrapper, AnOpenApiCommentWrapper, AnOpenApiUserWrapper, AnOpenApiRelationWrapper, AnOpenApiSearchWrapper
{
    protected $wb;

    /**
     * 授权方法
     */
    public function authorize()
    {
        require_once 'Authorize.php';
        $auth_instance = new sinaweiboAuthorize();
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
        $auth_instance = new sinaweiboAuthorize($code);
        return $auth_instance->callback();
    }

    /**
     * 检查授权
     */
    public function checkWeibo()
    {
        $sinaweibo_akey = AnOpenApiAbstract::$akey;
        $sinaweibo_skey = AnOpenApiAbstract::$skey;

        $openapi_connect_instance = AnOpenApiConnect::connect();
        $access_token = $openapi_connect_instance->getAccessToken();
        if (!$access_token) {
            throw new AnException('acess_token_fail');
        }
        $this->wb = new SaeTClientV2($sinaweibo_akey, $sinaweibo_skey, $access_token);
    }

    /**
     * 获取最新的公共微博
     * @param int $count 单页返回的记录条数，默认为50。
     * @param int $page 返回结果的页码，默认为1。
     * @param int $base_app 是否只获取当前应用的数据。0为否（所有数据），1为是（仅当前应用），默认为0。
     * @return array
     */
    public function publicTimeLine($page = 1, $count = 50, $base_app = 0)
    {
        $this->checkWeibo();
        return $this->wb->public_timeline($page, $count, $base_app);
    }

    /**
     * 获取当前登录用户及其所关注用户的最新微博
     * @param int $page 指定返回结果的页码。根据当前登录用户所关注的用户数及这些被关注用户发表的微博数，翻页功能最多能查看的总记录数会有所不同，通常最多能查看1000条左右。默认值1。可选。
	 * @param int $count 每次返回的记录数。缺省值50，最大值200。可选。
	 * @param int $since_id 若指定此参数，则只返回ID比since_id大的微博消息（即比since_id发表时间晚的微博消息）。可选。
	 * @param int $max_id 若指定此参数，则返回ID小于或等于max_id的微博消息。可选。
	 * @param int $base_app 是否只获取当前应用的数据。0为否（所有数据），1为是（仅当前应用），默认为0。
	 * @param int $feature 过滤类型ID，0：全部、1：原创、2：图片、3：视频、4：音乐，默认为0。
	 * @return array
     */
    public function homeTimeLine($page = 1, $count = 50, $since_id = 0, $max_id = 0, $base_app = 0, $feature = 0)
    {
        $this->checkWeibo();
        return $this->wb->home_timeline($page, $count, $since_id, $max_id, $base_app, $feature);
    }

    public function friendsTimeLineIds()
    {
        return '';
    }

    /**
     * 获取用户发布的微博
     * @param int $page 页码
     * @param int $count 每次返回的最大记录数，最多返回200条，默认50。
     * @param mixed $uid 指定用户UID或微博昵称
     * @param int $since_id 若指定此参数，则只返回ID比since_id大的微博消息（即比since_id发表时间晚的微博消息）。可选。
     * @param int $max_id 若指定此参数，则返回ID小于或等于max_id的提到当前登录用户微博消息。可选。
     * @param int $base_app 是否基于当前应用来获取数据。1为限制本应用微博，0为不做限制。默认为0。
     * @param int $feature 过滤类型ID，0：全部、1：原创、2：图片、3：视频、4：音乐，默认为0。
     * @param int $trim_user 返回值中user信息开关，0：返回完整的user信息、1：user字段仅返回uid，默认为0。
     * @return array
     */
    public function userTimeLine($uid = NULL , $page = 1 , $count = 50 , $since_id = 0, $max_id = 0, $feature = 0, $trim_user = 0, $base_app = 0)
    {
        $this->checkWeibo();
        return $this->wb->user_timeline_by_id($uid, $page, $count, $since_id, $max_id, $feature, $trim_user, $base_app);
    }

    public function userTimeLineIds()
    {
        return '';
    }

    /**
     * 返回一条原创微博的最新转发微博
     * @param int $sid 要获取转发微博列表的原创微博ID。
     * @param int $page 返回结果的页码。
     * @param int $count 单页返回的最大记录数，最多返回200条，默认50。可选。
     * @param int $since_id 若指定此参数，则只返回ID比since_id大的记录（比since_id发表时间晚）。可选。
     * @param int $max_id 若指定此参数，则返回ID小于或等于max_id的记录。可选。
     * @param int $filter_by_author 作者筛选类型，0：全部、1：我关注的人、2：陌生人，默认为0。
     * @return array
     */
    public function rtTimeLine($sid, $page = 1, $count = 50, $since_id = 0, $max_id = 0, $filter_by_author = 0)
    {
        $this->checkWeibo();
        return $this->wb->user_timeline_by_id($sid, $page, $count, $since_id, $max_id, $filter_by_author);
    }

    public function rtTimeLineIds()
    {
        return '';
    }

    /**
     * 返回用户转发的最新微博
     * @param int $page 返回结果的页码。
     * @param int $count  每次返回的最大记录数，最多返回200条，默认50。可选。
     * @param int $since_id 若指定此参数，则只返回ID比since_id大的记录（比since_id发表时间晚）。可选。
     * @param int $max_id  若指定此参数，则返回ID小于或等于max_id的记录。可选。
     * @return array
     */
    public function rtByMe($page = 1, $count = 50, $since_id = 0, $max_id = 0, $filter_by_type = 0)
    {
        $this->checkWeibo();
        return $this->wb->repost_by_me($page, $count, $since_id, $max_id);
    }

    /**
     * 获取@当前用户的最新微博
     * @param int $page 返回结果的页序号。
     * @param int $count 每次返回的最大记录数（即页面大小），不大于200，默认为50。
     * @param int $since_id 若指定此参数，则只返回ID比since_id大的微博消息（即比since_id发表时间晚的微博消息）。可选。
     * @param int $max_id 若指定此参数，则返回ID小于或等于max_id的提到当前登录用户微博消息。可选。
     * @param int $filter_by_author 作者筛选类型，0：全部、1：我关注的人、2：陌生人，默认为0。
     * @param int $filter_by_source 来源筛选类型，0：全部、1：来自微博、2：来自微群，默认为0。
     * @param int $filter_by_type 原创筛选类型，0：全部微博、1：原创的微博，默认为0。
     * @return array
     */
    public function mentions($page = 1, $count = 50, $since_id = 0, $max_id = 0, $filter_by_author = 0, $filter_by_source = 0, $filter_by_type = 0)
    {
        $this->checkWeibo();
        return $this->wb->mentions($page, $count, $since_id, $max_id, $filter_by_author, $filter_by_source, $filter_by_type);
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
        $this->checkWeibo();
        return $this->wb->show_status($id);
    }

    /**
     * 通过id获取mid
     * @param int|string $id  需要查询的微博（评论、私信）ID，批量模式下，用半角逗号分隔，最多不超过20个。
     * @param int $type  获取类型，1：微博、2：评论、3：私信，默认为1。
     * @param int $is_batch 是否使用批量模式，0：否、1：是，默认为0。
     * @return array
     */
    public function queryMid($id, $type = 1, $is_batch = 0)
    {
        $this->checkWeibo();
        return $this->wb->querymid($id, $type, $is_batch);
    }

    /**
     * 通过mid获取id
     * @param int|string $mid  需要查询的微博（评论、私信）MID，批量模式下，用半角逗号分隔，最多不超过20个。
     * @param int $type  获取类型，1：微博、2：评论、3：私信，默认为1。
     * @param int $is_batch 是否使用批量模式，0：否、1：是，默认为0。
     * @param int $inbox  仅对私信有效，当MID类型为私信时用此参数，0：发件箱、1：收件箱，默认为0 。
     * @param int $isBase62 MID是否是base62编码，0：否、1：是，默认为0。
     * @return array
     */
    public function queryId($mid, $type = 1, $is_batch = 0, $inbox = 0, $isBase62 = 0)
    {
        $this->checkWeibo();
        return $this->wb->queryid($mid, $type, $is_batch, $inbox, $isBase62);
    }

    /**
     * 按天返回热门转发榜
     * @param int $count 返回的记录条数，最大不超过50，默认为20。
     * @param int $base_app 是否只获取当前应用的数据。0为否（所有数据），1为是（仅当前应用），默认为0。
     * @return array
     */
    public function hotRepostDaily($count = 20, $base_app = 0, $filter_by_type = 0)
    {
        $this->checkWeibo();
        return $this->wb->repost_daily($count, $base_app);
    }

    /**
     * 按周返回热门转发榜
     * @param int $count 返回的记录条数，最大不超过50，默认为20。
     * @param int $base_app 是否只获取当前应用的数据。0为否（所有数据），1为是（仅当前应用），默认为0。
     * @return array
     */
    public function hotRepostWeekly($count = 20,  $base_app = 0)
    {
        $this->checkWeibo();
        return $this->wb->repost_weekly($count, $base_app);
    }

    /**
     * 按天返回当前用户关注人的热门微博评论榜
     * @param int $count 返回的记录条数，最大不超过50，默认为20。
     * @param int $base_app 是否只获取当前应用的数据。0为否（所有数据），1为是（仅当前应用），默认为0。
     * @return array
     */
    public function hotCommentsDaily($count = 20, $base_app = 0)
    {
        $this->checkWeibo();
        return $this->wb->comment_daily($count, $base_app);
    }

    /**
     * 按周返回热门评论榜
     * @param int $count 返回的记录条数，最大不超过50，默认为20。
     * @param int $base_app 是否只获取当前应用的数据。0为否（所有数据），1为是（仅当前应用），默认为0。
     * @return array
    */
    public function hotCommentsWeekly($count = 20, $base_app = 0)
    {
        $this->checkWeibo();
        return $this->wb->comment_weekly($count, $base_app);
    }

    public function count()
    {
        return '';
    }

    /**
     * 转发微博
     * @param int $rt_id 要转发的id
     * @param string $status 添加的转发文本，内容不超过140个汉字，不填则默认为“转发微博”。
     * @param int $is_comment 是否在转发的同时发表评论，0：否、1：评论给当前微博、2：评论给原微博、3：都评论，默认为0 。
     */
    public function rt($rt_id, $status = '', $is_comment = 0)
    {
        $this->checkWeibo();
        return $this->wb->repost($rt_id, $status, $is_comment);
    }

    /**
     * 删除某条微博
     * @param int $t_id
     */
    public function delete($t_id)
    {
        $this->checkWeibo();
        return $this->wb->delete($t_id);
    }

    /**
     * 发微博
     * @param string $status
     * @param string $pic_path
     */
    public function update($status, $pic_path = '', $lat = '', $long = '')
    {
        $this->checkWeibo();
        if ($pic_path) {
            return $this->wb->upload($status, $pic_path);
        } else {
            return $this->wb->update($status, $lat, $long);
        }
    }

    /**
     * 发布一条微博同时指定上传的图片或图片url
     * @param string $status  要发布的微博文本内容，内容不超过140个汉字。
     * @param string $url    图片的URL地址，必须以http开头。
     * @return array
     */
    public function uploadUrlText($status, $url, $client_ip = '')
    {
        $this->checkWeibo();
        return $this->wb->upload_url_text($status, $url);
    }

    /**
     * 获取表情
     * @param string $type 表情类别。"face":普通表情，"ani"：魔法表情，"cartoon"：动漫表情。默认为"face"。
     * @param unknown_type $language $language 语言类别，"cnname"简体，"twname"繁体。默认为"cnname"。可选
     */
    public function getEmotions($type = 'face', $language = "cnname")
    {
        $this->checkWeibo();
        return $this->wb->emotions($type, $language);
    }

    /**
     * 根据微博ID返回某条微博的评论列表
     * @param int $sid 需要查询的微博ID。
     * @param int $page 返回结果的页码，默认为1。
     * @param int $count 单页返回的记录条数，默认为50。
     * @param int $since_id 若指定此参数，则返回ID比since_id大的评论（即比since_id时间晚的评论），默认为0。
     * @param int $max_id  若指定此参数，则返回ID小于或等于max_id的评论，默认为0。
     * @param int $filter_by_author 作者筛选类型，0：全部、1：我关注的人、2：陌生人，默认为0。
     * @return array
     */
    public function getCommentListBySid($sid, $page = 1, $count = 50, $since_id = 0, $max_id = 0, $filter_by_author = 0)
    {
        $this->checkWeibo();
        return $this->wb->get_comments_by_sid($sid, $page, $count, $since_id, $max_id, $filter_by_author);
    }

    /**
     * 获取当前登录用户所发出的评论列表
     * @param int $since_id 若指定此参数，则返回ID比since_id大的评论（即比since_id时间晚的评论），默认为0。
     * @param int $max_id 若指定此参数，则返回ID小于或等于max_id的评论，默认为0。
     * @param int $count  单页返回的记录条数，默认为50。
     * @param int $page 返回结果的页码，默认为1。
     * @param int $filter_by_source 来源筛选类型，0：全部、1：来自微博的评论、2：来自微群的评论，默认为0。
     * @return array
     */
    public function commentByMe($page = 1 , $count = 50, $since_id = 0, $max_id = 0,  $filter_by_source = 0)
    {
        $this->checkWeibo();
        return $this->wb->comments_by_me($page, $count, $since_id, $max_id, $filter_by_source);
    }

    /**
     * 获取当前登录用户所接收到的评论列表
     * @param int $since_id 若指定此参数，则返回ID比since_id大的评论（即比since_id时间晚的评论），默认为0。
     * @param int $max_id  若指定此参数，则返回ID小于或等于max_id的评论，默认为0。
     * @param int $count 单页返回的记录条数，默认为50。
     * @param int $page 返回结果的页码，默认为1。
     * @param int $filter_by_author 作者筛选类型，0：全部、1：我关注的人、2：陌生人，默认为0。
     * @param int $filter_by_source 来源筛选类型，0：全部、1：来自微博的评论、2：来自微群的评论，默认为0。
     * @return array
     */
    public function commentToMe($page = 1 , $count = 50, $since_id = 0, $max_id = 0, $filter_by_author = 0, $filter_by_source = 0)
    {
        $this->checkWeibo();
        return $this->wb->comments_to_me($page, $count, $since_id, $max_id, $filter_by_author, $filter_by_source);
    }

    /**
     * 最新评论(按时间)返回最新n条发送及收到的评论。
     * @access public
     * @param int $page 页码
     * @param int $count 每次返回的最大记录数，最多返回200条，默认50。
     * @param int $since_id 若指定此参数，则只返回ID比since_id大的评论（比since_id发表时间晚）。可选。
     * @param int $max_id 若指定此参数，则返回ID小于或等于max_id的评论。可选。
     * @return array
     */
    public function commentTimeLine($page = 1, $count = 50, $since_id = 0, $max_id = 0)
    {
        $this->checkWeibo();
        return $this->wb->comments_timeline($page, $count, $since_id, $max_id);
    }

    /**
     * 获取最新的提到当前登录用户的评论，即@我的评论
     * @param int $since_id 若指定此参数，则返回ID比since_id大的评论（即比since_id时间晚的评论），默认为0。
     * @param int $max_id  若指定此参数，则返回ID小于或等于max_id的评论，默认为0。
     * @param int $count 单页返回的记录条数，默认为50。
     * @param int $page 返回结果的页码，默认为1。
     * @param int $filter_by_author  作者筛选类型，0：全部、1：我关注的人、2：陌生人，默认为0。
     * @param int $filter_by_source 来源筛选类型，0：全部、1：来自微博的评论、2：来自微群的评论，默认为0。
     * @return array
     */
    public function commentMentions($page = 1, $count = 50, $since_id = 0, $max_id = 0, $filter_by_author = 0, $filter_by_source = 0)
    {
        $this->checkWeibo();
        return $this->wb->comments_mentions($page, $count, $since_id, $max_id, $filter_by_author, $filter_by_source);
    }

    /**
     * 根据评论ID批量返回评论信息
     * @param string $cids 需要查询的批量评论ID，用半角逗号分隔，最大50
     * @return array
     */
    public function commentShowBatch($cids)
    {
        $this->checkWeibo();
        return $this->wb->comments_show_batch($cids);
    }

    /**
     * 发布评论
     * @param int $t_id 需要评论的微博ID。
     * @param string $comment 评论内容
     * @param int $comment_or 当评论转发微博时，是否评论给原微博，0：否、1：是，默认为0。
     */
    public function comment($t_id, $comment, $comment_or = 0)
    {
        $this->checkWeibo();
        return $this->wb->send_comment($t_id, $comment, $comment_or);
    }

    /**
     * 删除一条评论
     * @param int $comment_id
     */
    public function deleteComment($comment_id)
    {
        $this->checkWeibo();
        return $this->wb->comment_destroy($comment_id);
    }

    /**
     * 回复一条评论
     * 为防止重复，发布的信息与最后一条评论/回复信息一样话，将会被忽略。
     * @param int $sid 微博id
     * @param string $text 评论内容。
     * @param int $cid 评论id
     * @param int $without_mention 1：回复中不自动加入“回复@用户名”，0：回复中自动加入“回复@用户名”.默认为0.
     * @param int $comment_ori	  当评论转发微博时，是否评论给原微博，0：否、1：是，默认为0。
     * @return array
     */
    public function replyComment($sid, $text, $cid, $without_mention = 0, $comment_ori = 0)
    {
        $this->checkWeibo();
        return $this->wb->comment_reply($sid, $text, $cid, $without_mention, $comment_ori);
    }

    /**
     * 获取用户信息
     * @param string $info
     * @param string $fields
     */
    public function getUserInfo($info, $fields = 'id')
    {
        $this->checkWeibo();
        if ($fields == 'id') {
            return $this->getUserInfoById($info);
        } elseif ($fields == 'screen_name') {
            return $this->getUserInfoByName($info);
        }
    }

    /**
     * 根据用户id获取用户信息
     * @param int $uid
     */
    public function getUserInfoById($uid)
    {
        $this->checkWeibo();
        return $this->wb->show_user_by_id($uid);
    }

    /**
     * 根据用户昵称获取用户信息
     * @param string $screen_name
     */
    public function getUserInfoByName($screen_name)
    {
        $this->checkWeibo();
        return $this->wb->show_user_by_name($screen_name);
    }

    /**
     * 获取用户的关注列表 如果没有提供cursor参数，将只返回最前面的5000个关注id
     * @param int $cursor 返回结果的游标，下一页用返回值里的next_cursor，上一页用previous_cursor，默认为0。
     * @param int $count 单页返回的记录条数，默认为50，最大不超过200。
     * @param int $uid  要获取的用户的ID。
     * @return array
     */
    public function followingList($uid, $cursor = 0, $count = 50)
    {
        $this->checkWeibo();
        return $this->wb->friends_by_id($uid, $cursor, $count);
    }

    /**
     * 获取用户的关注列表 如果没有提供cursor参数，将只返回最前面的5000个关注id
     * @param int $cursor 返回结果的游标，下一页用返回值里的next_cursor，上一页用previous_cursor，默认为0。
     * @param int $count 单页返回的记录条数，默认为50，最大不超过200。
     * @param string $screen_name  要获取的用户的 screen_name
     * @return array
    */
    public function followingListByName($screen_name, $cursor = 0, $count = 50)
    {
        $this->checkWeibo();
        return $this->wb->friends_by_name($screen_name, $cursor, $count);
    }

    /**
     * 获取两个用户之间的共同关注人列表
     * @param int $uid  需要获取共同关注关系的用户UID
     * @param int $suid  需要获取共同关注关系的用户UID，默认为当前登录用户。
     * @param int $count  单页返回的记录条数，默认为50。
     * @param int $page  返回结果的页码，默认为1。
     * @return array
    */
    public function friendsInCommon($uid, $suid = NULL, $page = 1, $count = 50)
    {
        $this->checkWeibo();
        return $this->wb->friends_in_common($uid, $suid, $page, $count);
    }

    /**
     * 获取用户的双向关注列表，即互粉列表
     * @param int $uid  需要获取双向关注列表的用户UID。
     * @param int $count  单页返回的记录条数，默认为50。
     * @param int $page  返回结果的页码，默认为1。
     * @param int $sort  排序类型，0：按关注时间最近排序，默认为0。
     * @return array
    **/
    public function bilateralFriendList($uid, $page = 1, $count = 50, $sort = 0)
    {
        $this->checkWeibo();
        return $this->wb->bilateral($uid, $page, $count, $sort);
    }

    /**
     * 获取用户的双向关注uid列表
     * @param int $uid  需要获取双向关注列表的用户UID。
     * @param int $count 单页返回的记录条数，默认为50。
     * @param int $page  返回结果的页码，默认为1。
     * @param int $sort  排序类型，0：按关注时间最近排序，默认为0。
     * @return array
    **/
    public function bilateralFriendIds($uid, $page = 1, $count = 50, $sort = 0)
    {
        $this->checkWeibo();
        return $this->wb->bilateral_ids($uid, $page, $count, $sort);
    }

    /**
     * 获取用户的关注列表uid 如果没有提供cursor参数，将只返回最前面的5000个关注id
     * @param int $cursor 返回结果的游标，下一页用返回值里的next_cursor，上一页用previous_cursor，默认为0。
     * @param int $count 每次返回的最大记录数（即页面大小），不大于5000, 默认返回500。
     * @param int $uid 要获取的用户 UID，默认为当前用户
     * @return array
    */
    public function followingIds($uid, $cursor = 0, $count = 500)
    {
        $this->checkWeibo();
        return $this->wb->friends_ids_by_id($uid, $cursor, $count);
    }

    /**
     * 获取用户的关注列表uid 如果没有提供cursor参数，将只返回最前面的5000个关注id
     * @param int $cursor 返回结果的游标，下一页用返回值里的next_cursor，上一页用previous_cursor，默认为0。
     * @param int $count 每次返回的最大记录数（即页面大小），不大于5000, 默认返回500。
     * @param string $screen_name 要获取的用户的 screen_name，默认为当前用户
     * @return array
    */
    public function followingIdsByName($screen_name, $cursor = 0, $count = 500)
    {
        $this->checkWeibo();
        return $this->wb->friends_ids_by_name($screen_name, $cursor, $count);
    }

    /**
     * 获取用户的粉丝列表
     * @param int $uid  需要查询的用户UID
     * @param int $count 单页返回的记录条数，默认为50，最大不超过200。
     * @param int $cursor false 返回结果的游标，下一页用返回值里的next_cursor，上一页用previous_cursor，默认为0。
     * @return array
    **/
    public function fansList($uid, $cursor = 0, $count = 50, $mode = 0, $filter_by_sex = 0)
    {
        $this->checkWeibo();
        return $this->wb->followers_by_id($uid, $cursor, $count);
    }

    /**
     * 获取用户的粉丝列表
     * @param string $screen_name  需要查询的用户的昵称
     * @param int  $count 单页返回的记录条数，默认为50，最大不超过200。
     * @param int  $cursor false 返回结果的游标，下一页用返回值里的next_cursor，上一页用previous_cursor，默认为0。
     * @return array
    **/
    public function fansListByName($screen_name, $cursor = 0, $count = 50)
    {
        $this->checkWeibo();
        return $this->wb->followers_by_name($screen_name, $cursor, $count);
    }

    /**
     * 获取用户的粉丝列表uid
     * @param int $uid 需要查询的用户UID
     * @param int $count 单页返回的记录条数，默认为50，最大不超过200。
     * @param int $cursor 返回结果的游标，下一页用返回值里的next_cursor，上一页用previous_cursor，默认为0。
     * @return array
    **/
    public function fansIds($uid, $cursor = 0, $count = 50)
    {
        $this->checkWeibo();
        return $this->wb->followers_ids_by_id($uid, $cursor, $count);
    }

    /**
     * 获取用户的粉丝列表uid
     * @param string $screen_name 需要查询的用户screen_name
     * @param int $count 单页返回的记录条数，默认为50，最大不超过200。
     * @param int $cursor 返回结果的游标，下一页用返回值里的next_cursor，上一页用previous_cursor，默认为0。
     * @return array
    **/
    public function fansIdsByName($screen_name, $cursor = 0, $count = 50)
    {
        $this->checkWeibo();
        return $this->wb->followers_ids_by_name($screen_name, $cursor, $count);
    }

    /**
     * 获取优质粉丝
     * @param int $uid 需要查询的用户UID。
     * @param int $count 返回的记录条数，默认为20，最大不超过200。
     * @return array
    **/
    public function fansActive($uid, $count = 20)
    {
        $this->checkWeibo();
        return $this->wb->followers_active($uid, $count);
    }

    /**
     * 获取当前登录用户的关注人中又关注了指定用户的用户列表
     * @param int $uid 指定的关注目标用户UID。
     * @param int $count 单页返回的记录条数，默认为50。
     * @param int $page 返回结果的页码，默认为1。
     * @return array
    **/
    public function friendsChain($uid, $page = 1, $count = 50)
    {
        $this->checkWeibo();
        return $this->wb->friends_chain_followers($uid, $page, $count);
    }

    /**
     * 返回两个用户关系的详细情况
     * @param mixed $target_id 目标用户UID
     * @param mixed $source_id 源用户UID，可选，默认为当前的用户
     * @return array
    */
    public function isFollowed($target_id, $source_id = NULL)
    {
        $this->checkWeibo();
        return $this->wb->is_followed_by_id($target_id, $source_id);
    }

    /**
     * 返回两个用户关系的详细情况 如果源用户或目的用户不存在，将返回http的400错误
     * @param mixed $target_name 目标用户的微博昵称
     * @param mixed $source_name 源用户的微博昵称，可选，默认为当前的用户
     * @return array
    */
    public function isFollowedByName($target_name, $source_name = NULL)
    {
        $this->checkWeibo();
        return $this->wb->is_followed_by_name($target_name, $source_name);
    }

    /**
     * 根据用户UID批量关注用户
     * @param string $uids 要关注的用户UID，用半角逗号分隔，最多不超过20个。
     * @return array
    */
    public function batchFollow($uids)
    {
        $this->checkWeibo();
        return $this->wb->follow_create_batch($uids);
    }

    /**
     * 根据用户id关注某人
     * @param int $following_id
     */
    public function followById($following_id)
    {
        $this->checkWeibo();
        return $this->wb->follow_by_id($following_id);
    }

    /**
     * 根据昵称关注某人
     * @param string $name
     */
    public function followByName($name)
    {
        $this->checkWeibo();
        return $this->wb->follow_by_name($name);
    }

    /**
     * 根据id取消关注默认
     * @param int $following_id
     */
    public function unfollowById($following_id)
    {
        $this->checkWeibo();
        return $this->wb->unfollow_by_id($following_id);
    }

    /**
     * 根据昵称取消关注某人
     * @param string $name
     */
    public function unfollowByName($name)
    {
        $this->checkWeibo();
        return $this->wb->unfollow_by_name($name);
    }

    /**
     * @提示联系
     * @param string $q 关键字
     * @param int $count 返回记录的条数
     * @param int $type 联想类型 允许的资源为users， statuses， companies
     * @param int $range 联想范围
     */
    public function suggestion($q, $type = 'users', $count = 10)
    {
        $this->checkWeibo();

        $api_func = 'search_'.$type;
        return $this->wb->$api_func($q, $count);
    }

    /**
     * 搜索学校时的联想搜索建议
     * @param string $q 搜索的关键字，必须做URLencoding。必填
     * @param int $count 返回的记录条数，默认为10。
     * @param int type 学校类型，0：全部、1：大学、2：高中、3：中专技校、4：初中、5：小学，默认为0。选填
     * @return array
     */
    public function suggestionSchool($q, $count = 10, $type = 1)
    {
        $this->checkWeibo();
        return $this->wb->search_schools($q, $count, $type);
    }

    /**
     * ＠用户时的联想建议
     * @param string $q 搜索的关键字，必须做URLencoding。必填
     * @param int $count 返回的记录条数，默认为10。
     * @param int $type 联想类型，0：关注、1：粉丝。必填
     * @param int $range 联想范围，0：只联想关注人、1：只联想关注人的备注、2：全部，默认为2。选填
     * @return array
    */
    public function suggestionAtUser($q, $count = 10, $type = 0, $range = 2)
    {
        $this->checkWeibo();
        return $this->wb->search_at_users($q, $count, $type, $range);
    }

    /**
     * 搜索与指定的一个或多个条件相匹配的微博
     * @param array $query 搜索选项。格式：array('key0'=>'value0', 'key1'=>'value1', ....)。支持的key:
     *  - q				string	搜索的关键字，必须进行URLencode。
     *  - filter_ori	int		过滤器，是否为原创，0：全部、1：原创、2：转发，默认为0。
     *  - filter_pic	int		过滤器。是否包含图片，0：全部、1：包含、2：不包含，默认为0。
     *  - fuid			int		搜索的微博作者的用户UID。
     *  - province		int		搜索的省份范围，省份ID。
     *  - city			int		搜索的城市范围，城市ID。
     *  - starttime		int		开始时间，Unix时间戳。
     *  - endtime		int		结束时间，Unix时间戳。
     *  - count			int		单页返回的记录条数，默认为10。
     *  - page			int		返回结果的页码，默认为1。
     *  - needcount		boolean	返回结果中是否包含返回记录数，true：返回、false：不返回，默认为false。
     *  - base_app		int		是否只获取当前应用的数据。0为否（所有数据），1为是（仅当前应用），默认为0。
     * needcount参数不同，会导致相应的返回值结构不同
     * 以上参数全部选填
     * @return array
    */
    public function searchStatusHigh($query)
    {
        $this->checkWeibo();
        return $this->wb->search_statuses_high($query);
    }

    /**
     * 通过关键词搜索用户
     * @param array $query 搜索选项。格式：array('key0'=>'value0', 'key1'=>'value1', ....)。支持的key:
     *  - q			string	搜索的关键字，必须进行URLencode。
     *  - snick		int		搜索范围是否包含昵称，0：不包含、1：包含。
     *  - sdomain	int		搜索范围是否包含个性域名，0：不包含、1：包含。
     *  - sintro	int		搜索范围是否包含简介，0：不包含、1：包含。
     *  - stag		int		搜索范围是否包含标签，0：不包含、1：包含。
     *  - province	int		搜索的省份范围，省份ID。
     *  - city		int		搜索的城市范围，城市ID。
     *  - gender	string	搜索的性别范围，m：男、f：女。
     *  - comorsch	string	搜索的公司学校名称。
     *  - sort		int		排序方式，1：按更新时间、2：按粉丝数，默认为1。
     *  - count		int		单页返回的记录条数，默认为10。
     *  - page		int		返回结果的页码，默认为1。
     *  - base_app	int		是否只获取当前应用的数据。0为否（所有数据），1为是（仅当前应用），默认为0。
     * 以上所有参数全部选填
     * @return array
    */
    public function searchUserKeywords($query)
    {
        $this->checkWeibo();
        return $this->wb->search_users_keywords($query);
    }
}
?>