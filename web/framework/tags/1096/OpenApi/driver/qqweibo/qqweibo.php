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
 * $Id: qqweibo.php 643 2013-02-07 12:16:41Z anr $
*/

require_once AnPHP::$dir_3rd.'/OpenApi/qqweibo/Tencent.php';

class qqweiboWrapper extends AnOpenApiAbstract implements AnOpenApiTWrapper, AnOpenApiUserWrapper, AnOpenApiRelationWrapper, AnOpenApiSearchWrapper, AnOpenApiCommentWrapper
{
    /**
     * 授权方法
     */
    public function authorize()
    {
        require_once 'Authorize.php';
        $auth_instance = new qqweiboAuthorize();
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
        $auth_instance = new qqweiboAuthorize($code);
        return $auth_instance->callback();
    }

    /**
     * 检查授权
     */
    public function checkWeibo()
    {
        $sinaweibo_akey = AnOpenApiAbstract::$akey;
        $sinaweibo_skey = AnOpenApiAbstract::$skey;
        if (isset($_SESSION['t_access_token']) && !$_SESSION['t_access_token']) {
            throw new AnException('acess_token_fail');
        }
        OAuth::init($sinaweibo_akey, $sinaweibo_skey);
    }

    /**
     * 获取最新的公共微博
     * @param int $pos 开始的位置，默认为0。
     * @param int $reqnum 返回结果的数量，默认为50。
     * @param int $base_app 无用
     * @return array
     */
    public function publicTimeLine($start = 0, $count = 50, $base_app = 0)
    {
        $this->checkWeibo();

        $params = array('pos'=>$start, 'reqnum'=>$count);
        $r = Tencent::api('statuses/public_timeline', $params);
        return json_decode($r, true);
    }

    /**
     * 获取当前登录用户及其所关注用户的最新微博
     * @param int $page 分页标识（0：第一页，1：向下翻页，2：向上翻页）
     * @param int $count 每次请求记录的条数（1-70条）
     * @param int $pagetime 本页起始时间（第一页：填0，向上翻页：填上一次请求返回的第一条记录时间，向下翻页：填上一次请求返回的最后一条记录时间）
     * @param int $feature 拉取类型（需填写十进制数字）0x1 原创发表 0x2 转载 如需拉取多个类型请使用|，如(0x1|0x2)得到3，则type=3即可，填零表示拉取所有类型
     * @param string $filter_by_type 内容过滤。0-表示所有类型，1-带文本，2-带链接，4-带图片，8-带视频，0x10-带音频 建议不使用contenttype为1的类型，如果要拉取只有文本的微博，建议使用0x80
     * @param int $base_app 无用
     * @return array
     */
    public function homeTimeLine($page = 1, $count = 50, $pagetime = 0, $feature = 0, $filter_by_type = 0, $base_app = 0)
    {
        $this->checkWeibo();

        $params = array(
                    'pos'         => $page,
                    'reqnum'      => $count,
                    'pagetime'    => $pagetime,
                    'type'        => $feature,
                    'contenttype' => $filter_by_type
                  );
        $r = Tencent::api('statuses/home_timeline', $params);
        return json_decode($r, true);
    }

    public function friendsTimeLineIds()
    {
        return '';
    }

    /**
     * 获取用户发布的微博
     * @param int $uid 你需要读取的用户的openid（可选）name和fopenid至少选一个，若同时存在则以name值为主
     * @param string $name 你需要读取的用户的用户名（可选）
     * @param int $page 分页标识（0：第一页，1：向下翻页，2：向上翻页）
     * @param int $count 每次请求记录的条数（1-70条）
     * @param int $page_time 本页起始时间（第一页：填0，向上翻页：填上一次请求返回的第一条记录时间，向下翻页：填上一次请求返回的最后一条记录时间）
     * @param int $last_id 用于翻页，和pagetime配合使用（第一页：填0，向上翻页：填上一次请求返回的第一条记录id，向下翻页：填上一次请求返回的最后一条记录id）
     * @param int $type 拉取类型（需填写十进制数字）
                     0x1 原创发表
                     0x2 转载
                     0x8 回复
                     0x10 空回
                     0x20 提及
                     0x40 点评
                    如需拉取多个类型请使用|，如(0x1|0x2)得到3，则type=3即可，填零表示拉取所有类型
     * @param string $filter_by_type 内容过滤。0-表示所有类型，1-带文本，2-带链接，4-带图片，8-带视频，0x10-带音频 建议不使用contenttype为1的类型，如果要拉取只有文本的微博，建议使用0x80
     * @return array
     */
    public function userTimeLine($uid = NULL , $name = '' , $page = 0 , $count = 50, $page_time = 0, $last_id = 0, $type = 0, $filter_by_type = 0)
    {
        $this->checkWeibo();

        $params = array(
                'fopenid'      => $uid,
                'name'         => $name,
                'pageflag'     => $page,
                'reqnum'       => $count,
                'pagetime'     => $page_time,
                'lastid'       => $last_id,
                'type'         => $type,
                'contenttype'  => $filter_by_type,
        );
        $r = Tencent::api('statuses/user_timeline', $params);
        return json_decode($r, true);
    }

    public function userTimeLineIds()
    {
        return '';
    }

    /**
     * 返回一条原创微博的最新转发微博
     * @param int $sid 微博id，与pageflag、pagetime共同使用，实现翻页功能（第1页填0，继续向下翻页，填上一次请求返回的最后一条记录id）
     * @param int $page 分页标识，用于翻页（0：第一页，1：向下翻页，2：向上翻页）
     * @param int $count 每次请求记录的条数（1-100条）
     * @param int $page_time 本页起始时间，与pageflag、twitterid共同使用，实现翻页功能（第一页：填0，向上翻页：填上一次请求返回的第一条记录时间，向下翻页：填上一次请求返回的最后一条记录时间）
     * @param int $root_id 转发或回复的微博根结点id（源微博id）
     * @param int $flag 类型标识。0－转播列表，1－点评列表，2－点评与转播列表
     * @return array
     */
    public function rtTimeLine($sid, $page = 0, $count = 50, $page_time = 0, $root_id = 0, $flag = 0)
    {
        $this->checkWeibo();

        $params = array(
                'twitterid'  => $sid,
                'pageflag'   => $page,
                'reqnum'     => $count,
                'pagetime'   => $page_time,
                'rootid'     => $root_id,
                'flag'       => $flag
        );
        $r = Tencent::api('t/re_list', $params);

        return json_decode($r, true);
    }

    public function rtTimeLineIds()
    {
        return '';
    }

    /**
     * 返回用户转发的最新微博
     * @param int $page 分页标识（0：第一页，1：向下翻页，2：向上翻页）
     * @param int $count 每次请求记录的条数（1-300条）
     * @param int $page_time 本页起始时间（第一页：填0，向上翻页：填上一次请求返回的第一条记录时间，向下翻页：填上一次请求返回的最后一条记录时间）
     * @param int $last_id 和pagetime配合使用（第一页：填0，向上翻页：填上一次请求返回的第一条记录id，向下翻页：填上一次请求返回的最后一条记录id）
     * @param string $filter_by_type 内容过滤。0-表示所有类型，1-带文本，2-带链接，4-带图片，8-带视频，0x10-带音频 建议不使用contenttype为1的类型，如果要拉取只有文本的微博，建议使用0x80
     * @return array
     */
    public function rtByMe($page = 1, $count = 50, $page_time = 0, $last_id = 0, $filter_by_type = 0)
    {
        $this->checkWeibo();

        $params = array(
                'pageflag'    => $page,
                'reqnum'      => $count,
                'pagetime'    => $page_time,
                'lastid'      => $last_id,
                'contenttype' => $filter_by_type,
                'type'        => '0x2'
        );
        $r = Tencent::api('broadcast_timeline_ids', $params);

        return json_decode($r, true);
    }

    /**
     * 获取@当前用户的最新微博
     * @param int $page 分页标识（0：第一页，1：向下翻页，2：向上翻页）
     * @param int $count 每次请求记录的条数（1-300条）
     * @param int $page_time 本页起始时间（第一页：填0，向上翻页：填上一次请求返回的第一条记录时间，向下翻页：填上一次请求返回的最后一条记录时间）
     * @param int $last_id 和pagetime配合使用（第一页：填0，向上翻页：填上一次请求返回的第一条记录id，向下翻页：填上一次请求返回的最后一条记录id）
     * @param string $filter_by_type 内容过滤。0-表示所有类型，1-带文本，2-带链接，4-带图片，8-带视频，0x10-带音频
     * @param int $filter_by_author 无用
     * @param int $filter_by_source 无用
     * @return mixed
     */
    public function mentions($page = 1, $count = 50, $page_time = 0, $last_id = 0, $filter_by_type = 0, $filter_by_author = 0, $filter_by_source = 0)
    {
        $this->checkWeibo();

        $params = array(
                'pageflag'    => $page,
                'reqnum'      => $count,
                'pagetime'    => $page_time,
                'lastid'      => $last_id,
                'type'        => '0x20',
                'contenttype' => $filter_by_type
        );
        $r = Tencent::api('statuses/mentions_timeline', $params);

        return json_decode($r, true);
    }

    public function mentionsIds($page = 1, $count = 50, $page_time = 0, $last_id = 0, $filter_by_type = 0, $filter_by_author = 0, $filter_by_source = 0)
    {
        $this->checkWeibo();

        $params = array(
                'pageflag'    => $page,
                'reqnum'      => $count,
                'pagetime'    => $page_time,
                'lastid'      => $last_id,
                'type'        => '0x20',
                'contenttype' => $filter_by_type
        );
        $r = Tencent::api('statuses/mentions_timeline_ids', $params);

        return json_decode($r, true);
    }

    public function bilateralTimeLine()
    {
        return '';
    }

    /**
     * 查看微博详情
     * @param int $id
     */
    public function show($id)
    {
        $this->checkWeibo();
        $r = Tencent::api('t/show', array('id'=>$id), 'GET');
        return json_decode($r, true);
    }

    public function queryMid($id, $type = 1, $is_batch = 0)
    {
        return '';
    }

    public function queryId($mid, $type = 1, $is_batch = 0, $inbox = 0, $isBase62 = 0)
    {
        return '';
    }

    /**
     * 返回热门转发榜
     * @param int $page 翻页标识
     * @param int $count 每次请求记录的条数（1-100条）
     * @param string $filter_by_type 微博消息类型 0x1-带文本 0x2-带链接 0x4-带图片 0x8-带视频 如需拉取多个类型请使用|，如(0x1|0x2)得到3，此时type=3即可，填零表示拉取所有类型
     * @return mixed
     */
    public function hotRepostDaily($page = 1, $count = 20, $filter_by_type = 0)
    {
        $this->checkWeibo();

        $params = array(
                'pos'         => $page,
                'reqnum'      => $count,
                'type'        => $filter_by_type
        );
        $r = Tencent::api('trends/t', $params);

        return json_decode($r, true);
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
     * 转发微博
     * @param int $rt_id 转播父节点微博id
     * @param string $status 微博内容（若在此处@好友，需正确填写好友的微博账号，而非昵称），不超过140字
     * @param string $client_ip  用户ip（必须正确填写用户侧真实ip，不能为内网ip及以127或255开头的ip，以分析用户所在地）
     * @return mixed
     */
    public function rt($rt_id, $status = '', $client_ip = '')
    {
        $this->checkWeibo();
        $params = array(
                'reid'         => $rt_id,
                'content'      => $status,
                'clientip'     => $client_ip
        );
        $r = Tencent::api('t/re_add', $params, 'POST');

        return json_decode($r, true);
    }

    /**
     * 删除某条微博
     * @param int $t_id
     */
    public function delete($t_id)
    {
        $this->checkWeibo();

        $params = array(
                'id' => $t_id
        );

        $r = Tencent::api('t/del', $params, 'POST');

        return json_decode($r, true);
    }

    /**
     * 发送微博
     * @param string $status  微博内容（若在此处@好友，需正确填写好友的微博账号，而非昵称），不超过140字
     * @param string $pic_path  文件域表单名。本字段不要放在签名的参数中，不然请求时会出现签名错误，图片大小限制在4M。
     * @param string $lat 纬度，为实数，如22.354231（最多支持10位有效数字，可以填空）
     * @param string $long 经度，为实数，如113.421234（最多支持10位有效数字，可以填空）
     * @param string $client_ip 用户ip（必须正确填写用户侧真实ip，不能为内网ip及以127或255开头的ip，以分析用户所在地）
     * @return mixed
     */
    public function update($status, $pic_path = '', $lat = '', $long = '', $client_ip = '')
    {
        $this->checkWeibo();
        if ($pic_path) {
            $params = array(
                    'content'      => $status,
                    'pic'          => $pic_path,
                    'longitude'    => $long,
                    'latitude'     => $lat,
                    'clientip'     => $client_ip
            );

            $r = Tencent::api('t/add_pic', $params, 'POST');

            return json_decode($r, true);
        } else {
            $params = array(
                    'content'      => $status,
                    'longitude'    => $long,
                    'latitude'     => $lat,
                    'clientip'     => $client_ip
            );

            $r = Tencent::api('t/add', $params, 'POST');

            return json_decode($r, true);
        }
    }

    /**
     * 发布一条微博同时指定上传的图片或图片url
     * @param string $status 微博内容（若在此处@好友，需正确填写好友的微博账号，而非昵称），不超过140字
     * @param string $url 图片的URL地址（URL最长不能超过1024字节）
     * @param string $client_ip  用户ip（必须正确填写用户侧真实ip，不能为内网ip及以127或255开头的ip，以分析用户所在地）
     * @return mixed
     */
    public function uploadUrlText($status, $url, $client_ip = '')
    {
        $this->checkWeibo();
        $params = array(
                'content'      => $status,
                'pic_url'      => $url,
                'clientip'     => $client_ip
        );

        $r = Tencent::api('t/add_pic_url', $params, 'POST');

        return json_decode($r, true);
    }

    /**
     * 获取表情
     */
    public function getEmotions($type = '', $language = "")
    {
        $this->checkWeibo();
        $r = Tencent::api('other/get_emotions');
        return json_decode($r, true);
    }

    /**
     * 返回一条原创微博的评论列表
     * @param int $sid 微博id，与pageflag、pagetime共同使用，实现翻页功能（第1页填0，继续向下翻页，填上一次请求返回的最后一条记录id）
     * @param int $page 分页标识，用于翻页（0：第一页，1：向下翻页，2：向上翻页）
     * @param int $count 每次请求记录的条数（1-100条）
     * @param int $page_time 本页起始时间，与pageflag、twitterid共同使用，实现翻页功能（第一页：填0，向上翻页：填上一次请求返回的第一条记录时间，向下翻页：填上一次请求返回的最后一条记录时间）
     * @param int $root_id 转发或回复的微博根结点id（源微博id）
     * @param int $filter_by_author 无用
     * @return array
     */
    public function getCommentListBySid($sid, $page = 0, $count = 50, $page_time = 0, $root_id = 0, $filter_by_author = 0)
    {
        return $this->rtTimeLine($sid, $page, $count, $page_time, $root_id, 1);
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

    /**
     * 获取最新的提到当前登录用户的评论，即@我的评论
     * @param int $page 分页标识（0：第一页，1：向下翻页，2：向上翻页）
     * @param int $count 每次请求记录的条数（1-300条）
     * @param int $page_time 本页起始时间（第一页：填0，向上翻页：填上一次请求返回的第一条记录时间，向下翻页：填上一次请求返回的最后一条记录时间）
     * @param int $last_id 和pagetime配合使用（第一页：填0，向上翻页：填上一次请求返回的第一条记录id，向下翻页：填上一次请求返回的最后一条记录id）
     * @param string $filter_by_type 内容过滤。0-表示所有类型，1-带文本，2-带链接，4-带图片，8-带视频，0x10-带音频
     * @param int $filter_by_author 无用
     * @param int $filter_by_source 无用
     * @return mixed
     */
    public function commentMentions($page = 1, $count = 50, $page_time = 0, $last_id = 0, $filter_by_type = 0, $filter_by_author = 0, $filter_by_source = 0)
    {
        $this->checkWeibo();

        $params = array(
                'pageflag'    => $page,
                'reqnum'      => $count,
                'pagetime'    => $page_time,
                'lastid'      => $last_id,
                'type'        => '0x40',
                'contenttype' => $filter_by_type
        );
        $r = Tencent::api('statuses/mentions_timeline', $params);

        return json_decode($r, true);
    }

    public function commentShowBatch($cids)
    {
        return '';
    }

    /**
     * 发布评论
     * @param int $t_id 点评父节点微博id
     * @param string $comment 微博内容（若在此处@好友，需正确填写好友的微博账号，而非昵称），不超过140字
     * @param string $client_ip 用户ip（必须正确填写用户侧真实ip，不能为内网ip及以127或255开头的ip，以分析用户所在地）
     * @return mixed
     */
    public function comment($t_id, $comment, $client_ip = '')
    {
        $this->checkWeibo();

        $params = array(
                'reid'        => $t_id,
                'content'     => $comment,
                'clientip'    => clientip
        );
        $r = Tencent::api('t/comment', $params);

        return json_decode($r, true);
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
     * 获取当前登录用户的信息
     * @param string $info
     * @param 获取用户信息的字段 $fields  name fopenid
     */
    public function getUserInfo($info, $fields = '')
    {
        $this->checkWeibo();

        if (!$info) {
            $r = Tencent::api('user/info');
        } else {
            $params = array(
                $fields => $info
            );
            $r = Tencent::api('user/other_info');
        }
        return json_decode($r, true);
    }

    public function getUserInfoById($uid)
    {
        return '';
    }

    public function getUserInfoByName($name)
    {
        return '';
    }

    /**
     * 获取用户的关注列表
     * @param int $page 起始位置（第一页:填0，继续向下翻页：填【reqnum*（page-1）】）
     * @param int $count 请求个数(1-30)
     * @param int $base_app 过滤安装应用好友（可选）0-不考虑该参数，1-获取已安装应用好友，2-获取未安装应用好友
     * @return mixed
     */
    public function followingList($page, $count = 30, $base_app = 0)
    {
        $this->checkWeibo();

        if (!$page) {
            $page = 0;
        }

        $params = array(
                'reqnum'      => $count,
                'startindex'  => $page,
                'install'     => $base_app
        );

        $r = Tencent::api('friends/idollist', $params);

        return json_decode($r, true);
    }

    public function followingListByName($screen_name, $cursor = 0, $count = 50)
    {
        return '';
    }

    public function friendsInCommon($uid, $suid = NULL, $page = 1, $count = 50)
    {
        return '';
    }

    /**
     * 获取用户的双向关注列表，即互粉列表
     * @param int $uid 用户openid（可选）name和fopenid至少选一个，若同时存在则以name值为主
     * @param string $name  用户帐户名（可选）
     * @param int $page 起始位置（第一页:填0，继续向下翻页：填【reqnum*（page-1）】）
     * @param int $count 请求个数(1-30)
     * @return array
     */
    public function bilateralFriendList($uid, $name = '', $page = 0, $count = 30)
    {
        $params = array(
                'fopenid'     => $uid,
                'name'        => $name,
                'reqnum'      => $count,
                'startindex'  => $page
        );

        $r = Tencent::api('friends/mutual_list', $params);

        return json_decode($r, true);
    }

    public function bilateralFriendIds($uid, $page = 1, $count = 50, $sort = 0)
    {
        return '';
    }

    public function followingIds($uid, $cursor = 0, $count = 500)
    {
        return '';
    }

    public function followingIdsByName($screen_name, $cursor = 0, $count = 500)
    {
        return '';
    }

    /**
     * 获取用户的粉丝列表
     * @param int $uid  需要查询的用户UID
     * @param int $count 单页返回的记录条数，默认为50，最大不超过200。
     * @param int $cursor false 返回结果的游标，下一页用返回值里的next_cursor，上一页用previous_cursor，默认为0。
     * @return array
     **/
    public function fansList($page, $count = 50, $filter_by_sex = 0, $mode = 0, $base_app = 0)
    {
        $this->checkWeibo();

        $params = array(
                'reqnum'      => $count,
                'startindex'  => $page,
                'install'     => $base_app,
                'sex'         => $filter_by_sex,
                'mode'        => $mode
        );

        $r = Tencent::api('friends/fanslist', $params);

        return json_decode($r, true);
    }

    public function fansListByName($screen_name, $cursor = 0, $count = 50)
    {
        return '';
    }

    public function fansIds($uid, $cursor = 0, $count = 50)
    {
        return '';
    }

    public function fansIdsByName($screen_name, $cursor = 0, $count = 50)
    {
        return '';
    }

    public function fansActive($uid, $count = 20)
    {
        return '';
    }

    public function friendsChain($uid, $page = 1, $count = 50)
    {
        return '';
    }

    public function isFollowed($target_id, $source_id = NULL)
    {
        return '';
    }

    public function isFollowedByName($target_name, $source_name = NULL)
    {
        return '';
    }

    /**
     * 批量关注
     * @param string $name
     * @return string
     */
    public function batchFollow($name)
    {
        $this->followByName($name);
    }

    /**
     * 根据用户id关注某人
     * @param int $following_id
     */
    public function followById($following_id)
    {
        $this->checkWeibo();

        $params = array(
                'fopenids' => $following_id
        );

        $r = Tencent::api('friends/add', $params);

        return json_decode($r, true);
    }

    public function followByName($name)
    {
        $this->checkWeibo();

        $params = array(
                'name' => $name
        );

        $r = Tencent::api('friends/add', $params);

        return json_decode($r, true);
    }

    /**
     * 根据id取消关注默认
     * @param int $following_id
     */
    public function unfollowById($following_id)
    {
        $this->checkWeibo();

        $params = array(
                'fopenid' => $following_id
        );

        $r = Tencent::api('friends/del', $params, 'POST');

        return json_decode($r, true);
    }

    /**
     * 根据id取消关注默认
     * @param int $name
     */
    public function unfollowByName($name)
    {
        $this->checkWeibo();

        $params = array(
                'name' => $name
        );

        $r = Tencent::api('friends/del', $params, 'POST');

        return json_decode($r, true);
    }

    /**
     * @提示联系
     * @param string $q 关键字
     * @param int $count 返回记录的条数
     * @param int $type 联想类型 允许的资源为users， statuses， companies
     * @param int $range 联想范围
     */
    public function suggestion($q, $page = 0, $count = 10)
    {
        $this->checkWeibo();

        $params = array(
                'keyword'  => $q,
                'pagesize' => $count,
                'page'     => $page
        );

        $r = Tencent::api('search/user', $params);

        return json_decode($r, true);
    }

    public function suggestionSchool($q, $count = 10, $type = 1)
    {
        return '';
    }

    /**
     * ＠用户时的联想建议
     * @param string $q 搜索的关键字。必填
     * @param int $count 返回的记录条数，默认为10。
     * @param int 无用
     * @param int 无用
     * @return array
     */
    public function suggestionAtUser($q, $count = 10, $type = 0, $range = 2)
    {
        $this->checkWeibo();

        $params = array(
                'match'  => $q,
                'reqnum' => $count
        );

        $r = Tencent::api('match_nick_tips', $params);

        return json_decode($r, true);
    }

    /**
     * 搜索与指定的一个或多个条件相匹配的微博
     *
     */
    public function searchStatusHigh($params)
    {
        $this->checkWeibo();

        $r = Tencent::api('search/t', $params);

        return json_decode($r, true);
    }

    public function searchUserKeywords($query)
    {
        return false;
    }
}
?>