<?php

/**
 * alltosun.com  comment_help.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 赵高举 (zhaogj@alltosun.com) $
 * $Date: 2018-4-3 下午5:35:51 $
 * $Id$
 */
class like_helper
{
    /**
     * 根据宣传素材id获取标题
     * @param $table
     * @param $resid
     * @return mixed
     */
    static function get_res_title_by_type_id($table, $resid)
    {
        $data['title'] = '';
        $data = _model($table)->field('title')->read(array('id' => $resid));
        return $data['title'];
    }


    /**
     * 根据文章id获取文章赞数
     * @param $resid
     * @return mixed
     */
    static function get_res_like_count_by_resid($resid)
    {
        $res_name = tools_helper::get('search_filter', array());
        if (!isset($res_name['module'])) {
            $res_name['module'] = 'qydev_news';
        }
        $filter = array(
            'type' => "1",
            "type_id" => $resid,
            "status" => 1,
            "res_name" => $res_name['module'],
        );
        $like_count = _model('like')->getTotal($filter);
        return $like_count;
    }

    /**
     * 根据文章id获取文章评论数
     * @param $res_name
     * @param $resid
     * @return mixed
     */
    static function get_res_comment_count_by_resid($res_name, $resid)
    {
        $res_name = tools_helper::get('search_filter', array());
        if (!isset($res_name['module'])) {
            $res_name['module'] = 'qydev_news';
        }

        $filter = array(
            'is_del' => 0,
            'pid' => 0,
            'res_name' => $res_name['module'],
            'res_id' => $resid,
        );

        $comment_count = _model('comment')->getTotal($filter);
        return $comment_count;
    }

    /**
     * 根据文章id获取文章最后一次点赞时间
     * @param $res_name
     * @param $resid
     * @return mixed
     */
    static function get_last_like_by_resid($res_name, $resid)
    {
        $res_name = tools_helper::get('search_filter', array());
        if (!isset($res_name['module'])) {
            $res_name['module'] = 'qydev_news';
        }

        $order = ' ORDER BY `add_time` DESC ';
        $filter = array(
            'type' => "1",
            "type_id" => $resid,
            "status" => 1,
            "res_name" => $res_name['module'],
        );
        $last_time = _model('like')->field('add_time')->read($filter, ' ' . $order);
        return $last_time['add_time'];
    }

    /**
     * 根据文章id获取文章最后一次评论时间
     * @param $res_name
     * @param $resid
     * @return mixed
     */
    static function get_last_comment_by_resid($res_name, $resid)
    {
        $res_name = tools_helper::get('search_filter', array());
        if (!isset($res_name['module'])) {
            $res_name['module'] = 'qydev_news';
        }

        $order = ' ORDER BY `add_time` DESC ';
        $filter = array(
            'pid' => "0",
            "res_id" => $resid,
            "res_name" => $res_name['module'],
            "is_del" => 0
        );
        $last_time = _model('comment')->field('add_time')->read($filter, ' ' . $order);
        return $last_time['add_time'];
    }

    /**
     * 根据评论id获取评论赞数
     * @param $commid
     * @return mixed
     */
    static function get_comment_like_count($commid)
    {
        $filter = array(
            'type' => "2",
            "type_id" => $commid,
            "status" => 1,
        );
        $like_count = _model('like')->getTotal($filter);
        if ($like_count > 0) {
            return $like_count;
        } else {
            return '';
        }
    }

    /**
     * 根据评论id获取后台回复赞数
     * @param $commid
     * @return mixed
     */
    static function get_reply_like_count($commid)
    {
        $comm = _model('comment')->field('id')->read(array('pid' => $commid));
        $filter = array(
            'type' => "2",
            "type_id" => $comm['id'],
            "status" => 1,
        );
        $count = _model('like')->getTotal($filter);
        if ($count > 0) {
            return $count;
        } else {
            return '';
        }
    }

    /**
     * 根据pid查后台回复的消息的
     * @param $pid
     * @return mixed
     */
    static function get_content_by_pid($pid)
    {
        $data['content'] = '';
        $data = _model('comment')->field('content')->read(array('pid' => $pid));
        return $data['content'];
    }

    /**
     * 根据宣传素材id获取标题
     * @param $table
     * @param $resid
     * @return mixed
     */

    static function get_res_title_by_res_id($table, $resid)
    {
        $data = _model($table)->field('title')->read(array('id' => $resid));
        return $data['title'];
    }

    /**
     * 截取过长字符串
     * @param $str
     * @param $len
     * @param string $suffix
     * @return string
     */
    static function cut_str($str, $len = 10, $suffix = "...")
    {
        if (function_exists('mb_substr')) {
            if (strlen($str) > $len) {
                $str = mb_substr($str, 0, $len) . $suffix;
            }
            return $str;
        } else {
            if (strlen($str) > $len) {
                $str = substr($str, 0, $len) . $suffix;
            }
            return $str;
        }
    }
}
