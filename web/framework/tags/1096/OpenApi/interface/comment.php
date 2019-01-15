<?php

/**
 * alltosun.com 开放平台的接口评论类 comment.php
 * ============================================================================
 * 版权所有 (C) 2009-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 宁海新 (ninghx@alltosun.com) $
 * $Date: 2012-9-18 上午10:29:19 $
 * $Id: comment.php 643 2013-02-07 12:16:41Z anr $
*/

/**
 * 开放平台的接口评论类
 * @author nignhx@alltosun.com
 * @package AnOpenApi
 */

interface AnOpenApiCommentWrapper extends AnOpenApiWrapper
{
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
    public function getCommentListBySid($sid, $page = 1, $count = 50, $since_id = 0, $max_id = 0, $filter_by_author = 0);

    /**
     * 获取当前登录用户所发出的评论列表
     * @param int $since_id 若指定此参数，则返回ID比since_id大的评论（即比since_id时间晚的评论），默认为0。
     * @param int $max_id 若指定此参数，则返回ID小于或等于max_id的评论，默认为0。
     * @param int $count  单页返回的记录条数，默认为50。
     * @param int $page 返回结果的页码，默认为1。
     * @param int $filter_by_source 来源筛选类型，0：全部、1：来自微博的评论、2：来自微群的评论，默认为0。
     * @return array
     */
    public function commentByMe($page = 1 , $count = 50, $since_id = 0, $max_id = 0,  $filter_by_source = 0);

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
    public function commentToMe($page = 1 , $count = 50, $since_id = 0, $max_id = 0, $filter_by_author = 0, $filter_by_source = 0);

    /**
     * 最新评论(按时间)返回最新n条发送及收到的评论。
     * @access public
     * @param int $page 页码
     * @param int $count 每次返回的最大记录数，最多返回200条，默认50。
     * @param int $since_id 若指定此参数，则只返回ID比since_id大的评论（比since_id发表时间晚）。可选。
     * @param int $max_id 若指定此参数，则返回ID小于或等于max_id的评论。可选。
     * @return array
     */
    public function commentTimeLine($page = 1, $count = 50, $since_id = 0, $max_id = 0);

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
    public function commentMentions($page = 1, $count = 50, $since_id = 0, $max_id = 0, $filter_by_author = 0, $filter_by_source = 0);

    /**
     * 根据评论ID批量返回评论信息
     * @param string $cids 需要查询的批量评论ID，用半角逗号分隔，最大50
     * @return array
     */
    public function commentShowBatch($cids);

    /**
     * 发布评论
     * @param int $t_id 需要评论的微博ID。
     * @param string $comment 评论内容
     * @param int $comment_or 当评论转发微博时，是否评论给原微博，0：否、1：是，默认为0。
     */
    public function comment($t_id, $comment, $comment_or = 0);

    /**
     * 删除一条评论
     * @param int $comment_id
     */
    public function deleteComment($comment_id);

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
    public function replyComment($sid, $text, $cid, $without_mention = 0, $comment_ori = 0);
}
?>