<?php
/**
 * alltosun.com  ajax.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 申小宁 (shenxn@alltosun.com) $
 * $Date: 2015-12-22 下午2:41:43 $
 * $Id$
 */

class Action
{
    /**
     * 添加一条发布关联
     */
    public function add_res()
    {

        $content_id = tools_helper::post('content_id', 0);

        if ( !$content_id ) {
            return '发布内容不存在';
        }

        //发布内容
        $result = _widget('screen.put')->put_content();

        if ($result != 'ok') {
            return $result;
        }

        //更新发布状态 (已在 widget:screen.put 中推送 )
        _model('screen_content')->update($content_id, array('status' => 1));

        return 'ok';
    }

    /** 通用删除方法，应用于推送put页面删除数据  【transitional过渡页使用、coupon推送删除页使用】
     * @return string
     * @throws AnException
     */
    public function delete_res() {
        $table         = Request::Post('table', '');
        $id            = Request::Post('id', 0);

        if (empty($id)) {
            return '请选择您要操作的信息';
        }

        $info = _uri($table, $id);
        if (!$info) {
            return '您要删除的信息不存在';
        }

        _model($table)->delete($id);

        if ($table == 'screen_cotnent_res') {
            //获取需要推送的注册id
            $registration_ids = _widget('screen.put')->get_put_registration_ids($info);

            if (!$registration_id || $registration_id == 'all') {
                //全局推
                push_helper::push_msg('2', array());

                return 'ok';
            }

            //推送
            push_helper::push_msg('2', $registration_ids);
        }


        return 'ok';
    }



    public function update_res_status()
    {
        $content_id = Request::Post('id', 0);
        $status = Request::Post('status', 0);

        if (!$content_id) {
            return '信息错误';
        }

        $info = _uri('screen_content',$content_id);

        if (!$info) {
            return '内容不存在';
        }

        //设备宣传图发布，则验证是否发布到设备
        if ($info['type'] == 4 && $status == 1) {
            $info = _model('screen_content_res')->read(array('content_id' => $info['id'], 'phone_name != ' => ''));
            if (!$info) {
                return '发布失败，请在编辑页投放到指定设备';
            }
        }

        _model('screen_content')->update($content_id,array('status' => $status));

//         if ($status == 1 || $status == 0) {

//         }
        //查询此内容所有的发布
        $res_list = _model('screen_content_res')->getList(array('content_id' => $info['id']));

        $registration_ids = array();

        foreach ( $res_list as $k => $v ){
            //获取需要推送的注册id
            $registration_id = _widget('screen.put')->get_put_registration_ids($v);

            if (!$registration_id || $registration_id == 'all') {
                //全局推
                push_helper::push_msg('2', array());
                return 'ok';
            }

            $registration_ids = array_merge($registration_ids, $registration_id);

        }

        if ($registration_ids) {
            //推送
            push_helper::push_msg('2', $registration_ids);
        }

        return 'ok';
    }


    /**
     * 获取版本列表
     */
    public function get_version_list()
    {
        $phone_name = tools_helper::post('phone_name', '');
        if (!$phone_name) {
            return array('info' => 'ok', 'result' => array());
        }

        $filter = array(
                'phone_name' => $phone_name,
                'status'     => 1
        );

        $version_list = _model('screen_device')->getList($filter, " GROUP BY `phone_version`");

        return array('info' => 'ok', 'result' => $version_list);
    }

}