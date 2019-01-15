<?php
/**
 * alltosun.com  zimo.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-4-8 下午3:45:58 $
 * $Id$
 */

class Action
{
    public function index()
    {
        $privince_info = _model('province')->read(array('name' => '山西'));

        if ( !$privince_info ) exit('error');

        $business_list = _model('business_hall')->getList(array('province_id' => $privince_info['id']));

        foreach ( $business_list as  $k => $v ) {
//             if ( !$v['lat'] || !$v['log'] ) continue;

            $list[$k]['title']       = $v['title'];
            $list[$k]['user_number'] = "\t".$v['user_number'];
            $list[$k]['province'] = '山西';//screen_helper::by_id_get_field($v['province_id'], 'province', 'name');
            $list[$k]['city']     = screen_helper::by_id_get_field($v['city_id'],'city', 'name');
            $list[$k]['area']     = screen_helper::by_id_get_field($v['area_id'],'area', 'name');

            $list[$k]['contact']     = $v['contact'];
            $list[$k]['contact_way'] = $v['contact_way'];
            $list[$k]['address'] = $v['address'];
        }

        $head = array( '门店名称', '渠道视图编码', '省', '市', '区', '地址');

        $params['filename'] = '山西门店详情';

        $params['data']     = $list;
        $params['head']     = $head;

        Csv::getCvsObj($params)->export();
    }
}