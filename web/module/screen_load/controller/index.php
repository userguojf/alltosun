<?php
/**
 * alltosun.com  index.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-3-15 下午5:51:43 $
 * $Id$
 */

class Action
{
    public function index()
    {
        $province = '内蒙古';

        $privince_info = _model('province')->read(array('name' => $province));

        $business_list = _model('business_hall')->getList(array('province_id' => $privince_info['id']));

        foreach ( $business_list as  $k => $v ) {
            if ( !$v['lat'] || !$v['log'] ) continue;

            $list[$k]['province']    = $privince_info['name'];
            $list[$k]['city']        = screen_helper::by_id_get_field($v['city_id'],'city', 'name');
            $list[$k]['title']       = $v['title'];
            $list[$k]['user_number'] = "\t".$v['user_number'];

            $info = _model('screen_device')->read(array('business_id' => $v['id']));

            if ( !$info ) {
                $list[$k]['instatll'] = ' ';
            } else {
                $list[$k]['instatll'] = '是';
            }

        }

        $head = array('省', '市', '门店名称','渠道视图编码', '安装厅');

        $params['filename'] = $province . '营业厅';
        $params['data']     = $list;
        $params['head']     = $head;

        Csv::getCvsObj($params)->export();
    }
}