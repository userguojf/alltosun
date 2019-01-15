<?php
/**
 * alltosun.com  test.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-12-27 上午11:41:31 $
 * $Id$
 */
class Action
{
    public function create()
    {

        $member_id = _model('member')->create(
                array(
                        'member_user' => 1111111111111,
                        'member_pass' => md5('Awifi@123'),
                        'res_name'    => 'business_hall',
                        'res_id'      => 110736,
                        'ranks'       => 5,
                        'hash'        => uniqid()
                )
        );
        
        if ( $member_id ) {
            _model('group_user')->create(
            array(
            'member_id' => $member_id,
            'group_id'  => 26,
            )
            );
        }
    }

    public function __call($action = '', $param = array())
    {
        $user_number = tools_helper::get('user_number', '');

        if ( !$user_number ) return '请填写渠道码';

        $business_info = _model('business_hall')->read(array('user_number' => $user_number));

        if ( !$business_info ) return '不存在营业厅信息';

        $member_info = _model('member')->read(array('member_user' => $business_info['user_number']));

        if ( $member_info ) {
            p($member_info);
            echo '账号已存在';
            exit();
        }
        $member_id = 0;

        $member_id = _model('member')->create(
                array(
                        'member_user' => $business_info['user_number'],
                        'member_pass' => md5('Awifi@123'),
                        'res_name'    => 'business_hall',
                        'res_id'      => $business_info['id'],
                        'ranks'       => 5,
                        'hash'        => uniqid()
                )
        );

        if ( $member_id ) {
            _model('group_user')->create(
                array(
                    'member_id' => $member_id,
                    'group_id'  => 26,
                )
            );
        }
    }

    public function delete()
    {
        $id = tools_helper::get('id', 0);
        $table = tools_helper::get('t', '');

        $token  = tools_helper::Get('token', '');

        if ($token != 'alltosun') return '验证失败';

        if ( !$id || !$table ) return '参数错误';
        $res = _model($table)->delete(array('id' => $id), " LIMIT 1 ");
        p($res);
    }
}