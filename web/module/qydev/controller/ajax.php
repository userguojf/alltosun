<?php
/**
 * alltosun.com  ajax.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-4-24 下午5:41:30 $
 * $Id$
 */

class Action
{
    // 图文消息两种的点赞
    public function zan()
    {
        $res_id  = tools_helper::post('res_id', 0);
        $type     = tools_helper::post('type', 0);
        $opereate = tools_helper::post('opereate', '');

        if ( !$res_id || !in_array($type, array(1 , 2, 3)) || !in_array($opereate,  array('add', 'cut')) ) {
            return array('info' => 'no', 'msg' => '参数传输出现问题');
        }


        $member_id = member_helper::get_member_id();
        $member_info = member_helper::get_member_info($member_id);


        // 授权成功的使用唯一ID  失败 用户的 IP
        $user_ip       = $_SERVER ['REMOTE_ADDR'];
        $qydev_user_id = isset($_SESSION['qydev_user_id']) && $_SESSION['qydev_user_id']
                        ? $_SESSION['qydev_user_id'] : $user_ip;

        $user_agent  = $_SERVER['HTTP_USER_AGENT'];
        $user_number = $member_info && isset( $member_info['member_user'] )
                        ? $member_info['member_user'] : $user_agent;

        if ( $type == 1 ) {
            $news_info = _uri('qydev_news', array('id' => $res_id));

            if ( !$news_info ) {
                return array('info' => 'no', 'msg' => '数据已经失效');
            }

            // 点赞
            if ( $opereate == 'add' ) {
                $news_zan_info = _model('qydev_news_operate_record')->create(
                    array(
                            'news_id'     => $res_id,
                            'user_number' => $user_number,
                            'unique_id'   => $qydev_user_id,
                            'type'        => 2
                    ));

                $sql = " SET `zan_num` = `zan_num` + 1 ";
            } else {
                $news_zan_info = _model('qydev_news_operate_record')->delete(
                        array(
                                'news_id'     => $res_id,
                                'user_number' => $user_number,
                                'unique_id'   => $qydev_user_id,
                                'type'        => 2
                        ), " LIMIT 1 ");

                if ( $news_zan_info ) {
                    $sql = " SET `zan_num` = `zan_num` - 1 ";
                } else {
                    $sql = '';
                }
            }

            if ( $sql ) {
                _model('qydev_news')-> update( $res_id, $sql );
            }

            return array('info' => 'ok', 'msg' => 'ok');

        }

        if ( $type == 2 ) {
            $content_info = _uri('qydev_news_content', array('id' => $res_id));

            if ( !$content_info ) {
                return array('info' => 'no', 'msg' => '数据已经失效');
            }
        }

        if ( $type == 3 ) {
            $answer_info = _uri('qydev_news_content_answer', array('id' => $res_id));

            if ( !$answer_info ) {
                return array('info' => 'no', 'msg' => '数据已经失效');
            }
        }

        // 点赞
        if ( $opereate == 'add' ) {
            $news_zan_info = _model('qydev_news_content_zan_record')->create(
                    array(
                            'res_id'     => $res_id,
                            'user_number' => $user_number,
                            'unique_id'   => $qydev_user_id,
                            'type'        => $type
                    ));
            $sql = " SET `zan_num` = `zan_num` + 1 ";
        } else {
            $news_zan_info = _model('qydev_news_content_zan_record')->delete(
                    array(
                            'res_id'     => $res_id,
                            'user_number' => $user_number,
                            'unique_id'   => $qydev_user_id,
                            'type'        => $type
                    ), " LIMIT 1 ");
            $sql = " SET `zan_num` = `zan_num` - 1 ";
        }

        if ( $type == 2 ) {
            _model('qydev_news_content')-> update( $res_id, $sql );
        }

        if ( $type == 3 ){
            _model('qydev_news_content_answer')-> update( $news_id, $sql );
        }

        return array('info' => 'ok', 'msg' => 'ok');
    }
}