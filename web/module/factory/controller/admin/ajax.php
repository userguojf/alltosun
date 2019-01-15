<?php
/**
 * alltosun.com  
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * szy: 宋志宇 (songzy@alltosun.com)
 * 2018年3月7日: 2016-7-26 下午3:05:10
 * Id
 */
class Action
{
    
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
/////////////////////////////// 推送 START ///////////////////////////////
        if ($table == 'screen_cotnent_res') {
            //获取需要推送的注册id
            $registration_ids = _widget('screen_content.put')->get_put_registration_ids($info);

            if ($registration_ids === false) {
                return 'ok';
            }

            //推送
            push_helper::push_msg('2', $registration_ids);
        }
/////////////////////////////// 推送 END ///////////////////////////////
        //push_helper::push_msg(2);

        return 'ok';
    }
    
    public function change_order_status()
    {
        $id            = Request::Post('id', 0);
        $order_status         = Request::Post('order_status', '');
        if (empty($id)) {
            return '请选择您要操作的信息';
        }
        $id = _model('device_application')->update(array('id' => $id),array('order_status' => $order_status));
        
        //差短信
        return 'ok';
    }
}