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
        exit();
        $filter = array(
                'title'       => '测试-增值业务运营中心',
                'type'        => 4,
                'province_id' => 1,
                'city_id'     => 17,
                'area_id'     => 120,
                'contact'     => '陈飞',
                'contact_way' => '13301163776',
                'user_number' => 'zzywyyzx00001',
                'log'         => '116.230319890',
                'lat'         => '39.9576453126578',
                'address'     => '北京市海淀区杏石口路99号中国电信增值业务运营中心测试厅店',
                'store_level' => 1
        );

//         $res = _model('business_hall')->create($filter);
//         p($res);

    }

    public function member()
    {
        exit();
        $id = 110534;
        $business_info = _model('business_hall')->read(array('id' => $id));

        $member_info = _model('member')->read(
                array('res_name'=> 'business_hall',
                        'res_id'      => $business_info['id']
                )
        );

        if ($member_info) {
            return '账号存在';;
        }

        $member_id = _model('member')->create(
                array(
                        'member_user' => $business_info['user_number'],
                        'member_pass' => 'fa2a00984485f9438f24f38af18cb8e4',
                        'res_name'    => 'business_hall',
                        'res_id'      => $business_info['id'],
                        'ranks'       => 5,
                        'hash'       => uniqid()
                )
        );

        _model('group_user')->create( array(
                'member_id'  => $member_id,
                'group_id'   => 26,
            )
        );

        return 'ok';
    }

    public function update()
    {
        $id = 110534;
        $res1 = _model('business_hall')->update(array('id' => $id), array('user_number' => '1111111111000'));

        $res2 = _model('member')->update(array('member_user' => 'zzywyyzx00001'), array('member_user' => '1111111111000'));

        p($res1.'/'.$res2);
    }
}