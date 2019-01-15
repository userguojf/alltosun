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
 * 2017年12月12日: 2016-7-26 下午3:05:10
 * Id
 */
class Action {
    /**
     * 更新设备昵称
     */
    public function update_nickname() {
        $type = tools_helper::post ( 'type', '' );
        $new_nickname = tools_helper::post ( 'new_nickname', '' );
        $phone_name = tools_helper::post ( 'phone_name', '' );
        $new_nickname = trim($new_nickname);
        
        $nickname_table = "screen_device_nickname";

        if (! $type || ! in_array ( $type, array (
                'name',
                'version'
        ) )) {
            return array (
                    'info' => 'fail',
                    'msg' => '非法设备信息'
            );
        }

        if (! $new_nickname) {
            return array (
                    'info' => 'fail',
                    'msg' => '输入的信息不合符'
            );
        }

        $info = _uri ( 'screen_device', array (
                "phone_{$type}" => $phone_name
        ) );
        if (! $info) {
            
            if($type == 'name'){
            _model ( $nickname_table )->update ( array ("phone_{$type}" => $phone_name), array('name_nickname' => $new_nickname) );
            }
            if($type == 'version'){
                _model ( $nickname_table )->update ( array ("phone_{$type}" => $phone_name), array('version_nickname' => $new_nickname) );
            }
            return 'ok';
        }
         $device_field = "phone_{$type}_nickname";

        // 更新条件
        $filter = array (
                'phone_version' => $info ['phone_version'],
                'phone_name' => $info ['phone_name']
        );

        if ($type == 'name') {

            // 新昵称
            $new_name = array (
                    'name_nickname' => $new_nickname
            );

            $info_name = _uri ( $nickname_table, $filter );
            if ($info_name) {
                _model ( $nickname_table )->update ( array ('phone_name' => $phone_name), $new_name );
            } else {
                $new_data = $filter;
                $new_data ['name_nickname'] = $new_nickname;
                _model ( $nickname_table )->create ( $new_data );
            }
            // 更新设备表
//             $id = _model ( 'screen_device' )->update ( array (
//                     'phone_name' => $phone_name
//             ), array (
//                     $device_field => $new_nickname
//             ) );
//             if (! $id) {
//                 return "网络错误";
//             }
        }

        if ($type == 'version') {

            // 新型号昵称
            $new_version = array (
                    'version_nickname' => $new_nickname
            );

            $info_version = _uri ( $nickname_table, $filter );

            if ($info_version) {
                _model ( $nickname_table )->update (array('phone_version' => $phone_name), $new_version );
            } else {
                $new_data = $filter;
                $new_data ['version_nickname'] = $new_nickname;
                _model ( $nickname_table )->create ( $new_data );
            }
            // 更新设备表
//             $id = _model ( 'screen_device' )->update (
//                     array ('phone_name' => $info['phone_name'],
//                            'phone_version' => $phone_name
//             ), array (
//                     $device_field => $new_nickname
//             ) );
//             if (! $id) {
//                 return "网络错误";
//             }
        }

        return 'ok';
    }

    /**
     * 更新状态
     * @return string[]|string
     */
    public function update_ststus() {
        $status = Request::post ( 'status', 0 );
        $id = Request::post ( 'id', '' );
        $info = _model ( 'screen_device_nickname')->read($id);
        
        if (!$info) {
            return array('info' => 'fail', 'msg' => '机型信息不存在');
        }
        //更新设备表条件
        $filter =array(
                'phone_name'    => $info['phone_name'],
                'phone_version' => $info['phone_version'],
        );
        
        //查看设备表是否有数据
        $flag_list = _model('screen_device') -> read($filter);
        
        if(!$flag_list){
            return array('info' => 'fail', 'msg' => '没有此设备');
        }
        
        if ($status) {

           if (empty ( $info ['name_nickname'] ) || empty ( $info ['version_nickname'] )) {
                return array('info' => 'fail', 'msg' => '未设置昵称');
            }
            $status = 1;
         
         //更新昵称表
        _model ( 'screen_device_nickname' )->update ( $info['id'], array ( 'status' => $status) );
        //更新设备表
        _model ( 'screen_device' )->update ( $filter, array ( 
                'phone_name_nickname'    => $info['name_nickname'],
                'phone_version_nickname' => $info['version_nickname']
        ) );
        
        } else {
            $status = 0;
            //更新昵称表状态
            _model ( 'screen_device_nickname' )->update ( $info['id'], array ( 'status' => $status) );
            //清空设备表的昵称数据
            _model ( 'screen_device' )->update ( $filter, array (
                    'phone_name_nickname'    => '',
                    'phone_version_nickname' => ''
            ) );
        }

        
        //推送
        _widget('push')->push_device_model($info['phone_name'], $info['phone_version']);

        return 'ok';
    }

    /**
     * 添加或更新品牌昵称
     * @return string[]|string
     */
    public function add_nickname() {
        $phone_info             = Request::post ( 'phone_info', '' );
        $phone_name_nickname    = Request::post ( 'phone_name_nickname', '' );
        $phone_version_nickname = trim($phone_name_nickname);
        $nickname_table = "screen_device_nickname";
        list($phone_name, $phone_version) = explode('|@|', $phone_info);
        if (! $phone_name_nickname || !$phone_name || !$phone_version) {
            return array (
                    'info' => 'fail',
                    'msg' => '参数不符合'
            );
        }

        // 查询条件
        $filter = array (
                'phone_version' => $phone_version,
                'phone_name' => $phone_name
        );

        $nick_info = _model($nickname_table)->read($filter);
        //更新设备昵称表
        if ($nick_info) {
            _model ( $nickname_table )->update ($nick_info['id'], array ('name_nickname' => $phone_name_nickname) );
        } else {
            $new_data = $filter;
            $new_data ['name_nickname'] = $phone_name_nickname;
            _model ( $nickname_table )->create ( $new_data );
        }

        //更新设备表
       // _model ( 'screen_device' )->update ( $filter, array ( 'phone_name_nickname' => $phone_name_nickname) );

        return 'ok';
    }

    /**
     * 添加或更新型号昵称
     * @return string[]|string
     */
    public function add_version_nickname() {
        $phone_info             = Request::post ( 'phone_info', '' );
        $phone_version_nickname = Request::post ( 'phone_version_nickname', '' );
        $nickname_table = "screen_device_nickname";
        $phone_version_nickname = trim($phone_version_nickname);
        list($phone_name, $phone_version) = explode('|@|', $phone_info);
        if (! $phone_version_nickname || !$phone_name || !$phone_version) {
            return array (
                    'info' => 'fail',
                    'msg' => '参数不符合'
            );
        }

        // 查询条件
        $filter = array (
                'phone_version' => $phone_version,
                'phone_name'    => $phone_name
        );

        $nick_info = _model($nickname_table)->read($filter);

        //更新设备昵称表
        if ($nick_info) {
            _model ( $nickname_table )->update ( $nick_info['id'], array ('version_nickname' => $phone_version_nickname) );
        } else {
            $new_data = $filter;
            $new_data ['version_nickname'] = $phone_version_nickname;
            _model ( $nickname_table )->create ( $new_data );
        }

        //更新设备表
      //  _model ( 'screen_device' )->update ( $filter, array ('phone_version_nickname' => $phone_version_nickname) );

        return 'ok';
    }
}