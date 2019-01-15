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
    public function index()
    {
        $res = _model('public_contact_user')->delate(array('id' => array(54731,54732)),' LIMIT 2 ');
        p($res);
        exit();
        $res = _model('public_contact_user')->create( 
                array(
                    'type' => 4,
                    'user_number' => '1101001902792',
                    'user_name' => '15011317143',
                    'user_phone' => 15011317143,
//                     'from_id' => ,
                    'unique_id' => '3706831037179_01',
                    'analog_id' => '1101021002051',
                    'an_id' => '1101001902792',
                )
        );

        $user_phone = array('18009592047');

        $res = _model('public_contact_user')->update(
                    array('user_phone' => $user_phone),
                    array('user_number' => '1101001902792')
        );
        p($res);
    }
    
    public function get_department()
    {
        $params['id'] = 1;
        $list = _widget('qydev.department')->get_department_list();
        p('电信智慧门店运营组织架构部门数量：'.count($list).';已达上限');
    }
}