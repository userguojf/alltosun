<?php

/**
 * alltosun.com 开放平台的接口关系类 relation.php
 * ============================================================================
 * 版权所有 (C) 2009-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 宁海新 (ninghx@alltosun.com) $
 * $Date: 2012-9-18 上午10:29:06 $
 * $Id: relation.php 643 2013-02-07 12:16:41Z anr $
*/

/**
 * 开放平台的接口关系类
 * @author nignhx@alltosun.com
 * @package AnOpenApi
 */

interface AnOpenApiRelationWrapper extends AnOpenApiWrapper
{
    /**
     * 获取用户的关注列表 如果没有提供cursor参数，将只返回最前面的5000个关注id
     * @param int $cursor 返回结果的游标，下一页用返回值里的next_cursor，上一页用previous_cursor，默认为0。
     * @param int $count 单页返回的记录条数，默认为50，最大不超过200。
     * @param int $uid  要获取的用户的ID。
     * @return array
     */
    public function followingList($uid, $cursor = 0, $count = 50);

    /**
     * 获取用户的关注列表 如果没有提供cursor参数，将只返回最前面的5000个关注id
     * @param int $cursor 返回结果的游标，下一页用返回值里的next_cursor，上一页用previous_cursor，默认为0。
     * @param int $count 单页返回的记录条数，默认为50，最大不超过200。
     * @param string $screen_name  要获取的用户的 screen_name
     * @return array
     */
    public function followingListByName($screen_name, $cursor = 0, $count = 50);

    /**
     * 获取两个用户之间的共同关注人列表
     * @param int $uid  需要获取共同关注关系的用户UID
     * @param int $suid  需要获取共同关注关系的用户UID，默认为当前登录用户。
     * @param int $count  单页返回的记录条数，默认为50。
     * @param int $page  返回结果的页码，默认为1。
     * @return array
     */
    public function friendsInCommon($uid, $suid = NULL, $page = 1, $count = 50);

    /**
     * 获取用户的双向关注列表，即互粉列表
     * @param int $uid  需要获取双向关注列表的用户UID。
     * @param int $count  单页返回的记录条数，默认为50。
     * @param int $page  返回结果的页码，默认为1。
     * @param int $sort  排序类型，0：按关注时间最近排序，默认为0。
     * @return array
     **/
    public function bilateralFriendList($uid, $page = 1, $count = 50, $sort = 0);

    /**
     * 获取用户的双向关注uid列表
     * @param int $uid  需要获取双向关注列表的用户UID。
     * @param int $count 单页返回的记录条数，默认为50。
     * @param int $page  返回结果的页码，默认为1。
     * @param int $sort  排序类型，0：按关注时间最近排序，默认为0。
     * @return array
     **/
    public function bilateralFriendIds($uid, $page = 1, $count = 50, $sort = 0);

    /**
     * 获取用户的关注列表uid 如果没有提供cursor参数，将只返回最前面的5000个关注id
     * @param int $cursor 返回结果的游标，下一页用返回值里的next_cursor，上一页用previous_cursor，默认为0。
     * @param int $count 每次返回的最大记录数（即页面大小），不大于5000, 默认返回500。
     * @param int $uid 要获取的用户 UID，默认为当前用户
     * @return array
     */
    public function followingIds($uid, $cursor = 0, $count = 500);

    /**
     * 获取用户的关注列表uid 如果没有提供cursor参数，将只返回最前面的5000个关注id
     * @param int $cursor 返回结果的游标，下一页用返回值里的next_cursor，上一页用previous_cursor，默认为0。
     * @param int $count 每次返回的最大记录数（即页面大小），不大于5000, 默认返回500。
     * @param string $screen_name 要获取的用户的 screen_name，默认为当前用户
     * @return array
     */
    public function followingIdsByName($screen_name, $cursor = 0, $count = 500);

    /**
     * 获取用户的粉丝列表
     * @param int $uid  需要查询的用户UID
     * @param int $count 单页返回的记录条数，默认为50，最大不超过200。
     * @param int $cursor false 返回结果的游标，下一页用返回值里的next_cursor，上一页用previous_cursor，默认为0。
     * @return array
     **/
    public function fansList($uid, $cursor = 0, $count = 50, $mode = 0, $filter_by_sex = 0);

    /**
     * 获取用户的粉丝列表
     * @param string $screen_name  需要查询的用户的昵称
     * @param int  $count 单页返回的记录条数，默认为50，最大不超过200。
     * @param int  $cursor false 返回结果的游标，下一页用返回值里的next_cursor，上一页用previous_cursor，默认为0。
     * @return array
     **/
    public function fansListByName($screen_name, $cursor = 0, $count = 50);

    /**
     * 获取用户的粉丝列表uid
     * @param int $uid 需要查询的用户UID
     * @param int $count 单页返回的记录条数，默认为50，最大不超过200。
     * @param int $cursor 返回结果的游标，下一页用返回值里的next_cursor，上一页用previous_cursor，默认为0。
     * @return array
     **/
    public function fansIds($uid, $cursor = 0, $count = 50);

    /**
     * 获取用户的粉丝列表uid
     * @param string $screen_name 需要查询的用户screen_name
     * @param int $count 单页返回的记录条数，默认为50，最大不超过200。
     * @param int $cursor 返回结果的游标，下一页用返回值里的next_cursor，上一页用previous_cursor，默认为0。
     * @return array
     **/
    public function fansIdsByName($screen_name, $cursor = 0, $count = 50);

    /**
     * 获取优质粉丝
     * @param int $uid 需要查询的用户UID。
     * @param int $count 返回的记录条数，默认为20，最大不超过200。
     * @return array
     **/
    public function fansActive($uid, $count = 20);

    /**
     * 获取当前登录用户的关注人中又关注了指定用户的用户列表
     * @param int $uid 指定的关注目标用户UID。
     * @param int $count 单页返回的记录条数，默认为50。
     * @param int $page 返回结果的页码，默认为1。
     * @return array
     **/
    public function friendsChain($uid, $page = 1, $count = 50);

    /**
     * 返回两个用户关系的详细情况
     * @param mixed $target_id 目标用户UID
     * @param mixed $source_id 源用户UID，可选，默认为当前的用户
     * @return array
     */
    public function isFollowed($target_id, $source_id = NULL);

    /**
     * 返回两个用户关系的详细情况 如果源用户或目的用户不存在，将返回http的400错误
     * @param mixed $target_name 目标用户的微博昵称
     * @param mixed $source_name 源用户的微博昵称，可选，默认为当前的用户
     * @return array
     */
    public function isFollowedByName($target_name, $source_name = NULL);

    /**
     * 根据用户UID批量关注用户
     * @param string $uids 要关注的用户UID，用半角逗号分隔，最多不超过20个。
     * @return array
     */
    public function batchFollow($uids);

    /**
     * 根据用户id关注某人
     * @param int $following_id
     */
    public function followById($following_id);

    /**
     * 根据昵称关注某人
     * @param string $name
     */
    public function followByName($name);

    /**
     * 根据id取消关注默认
     * @param int $following_id
     */
    public function unfollowById($following_id);

    /**
     * 根据昵称取消关注某人
     * @param string $name
     */
    public function unfollowByName($name);
}
?>