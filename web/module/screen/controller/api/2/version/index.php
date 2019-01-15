<?php
/**
 * alltosun.com  index.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2017年7月21日 下午3:04:52 $
 * $Id$
 */

class Action
{
    /**
     * 比较版本是否为最新版本
     */
    public function index()
    {
        $version_no  = tools_helper::post('version_no', '');
        if (!$version_no) {
            api_helper::return_api_data(1003, '请输入版本号', array(), $api_log_id);
        }
        // 验证接口
        $check_params = array(
        );
        $api_log_id = api_helper::check_sign($check_params, 0);

        //status = 1 未发布  2已发布  0 已删除

        //最新且已发布的
        $info     = _model('screen_version')->read(array('status'=>2), ' ORDER BY `id` DESC ');

        //版本号已发布的
        $new_info = _model('screen_version')->read(array('version_no like ' => '%'.$version_no.'%', 'status'=>2), ' ORDER BY `id` DESC ');

        $url = '';
        if (!empty($info['path']) && !empty($info['id']) && !empty($new_info['id'])) {
            if ($info['id'] > $new_info['id']) {
                $url = SITE_URL.$info['path'];
                _model('screen_version')->update($info['id'], array('down_num'=>$info['down_num']+1));
            } else {
//                 $url = SITE_URL.$new_info['path'];
//                 _model('screen_version')->update($new_info['id'], array('down_num'=>$new_info['down_num']+1));
                    $url = '';
            }

        }

        //如果传过来的版本号没有信息  用最新且已发布的地址
        if (empty($new_info)) {
            if ($info) {
                $url = SITE_URL.$info['path'];
                _model('screen_version')->update($info['id'], array('down_num'=>$info['down_num']+1));
            } else {
                $url = '';
            }

        }

        api_helper::return_api_data(1000, 'success', array('url'=>$url), $api_log_id);
    }

    /**
     * 取最新发布版本
     */
    public function get_version_info()
    {

        $version_no  = tools_helper::post('version_no', '');

        // 验证接口
        $check_params = array(
        );
        $api_log_id = api_helper::check_sign($check_params, 0);

        $result = array(
                'version_no' => '',
                'path'       => '',
                'intro'      => '',
                'size'       => '',
                'add_time'   => ''
        );

        //status = 1 未发布  2已发布  0 已删除

        //最新且已发布的
        $info     = _model('screen_version')->read(array('status'=>2), ' ORDER BY `id` DESC ');

        //没有新版本则返回空
        if (!$info) {
            api_helper::return_api_data(1000, 'success', $result, $api_log_id);
        }

        $is_new = 0;
        //当前版本如果为空则返回新发布的版本
        if (!$version_no) {
            $result = array(
                'version_no' => $info['version_no'],
                'path'       => SITE_URL.$info['path'],
                'intro'      => $info['intro'],
                'size'       => screen_helper::get_filesize($info['size']),
                'add_time'   => $info['add_time']
            );

            api_helper::return_api_data(1000, 'success', $result, $api_log_id);
        }

        //和当前版本相同则返回空
        if ($info['version_no'] == $version_no) {
            api_helper::return_api_data(1000, 'success', $result, $api_log_id);
        }

        //比较
        $new_version_arr    = explode('.', str_replace('v', '', $info['version_no']));
        $curr_version_arr   = explode('.', str_replace('v', '', $version_no));

        $is_new             = 0;
        foreach ($new_version_arr as $k => $v) {

            if (!isset($curr_version_arr[$k])) {
                break;
            }

            if ($v > $curr_version_arr[$k]) {
                $is_new = 1;
                break;
            } else if ($v < $curr_version_arr[$k]) {
                api_helper::return_api_data(1000, 'success', $result, $api_log_id);
            }
        }

        //如果没有url,则是版本号前N位相同， 长度不同
        if (!$is_new) {
            //新发布的版本号长度大于当前版本则使用新发布的版本号
            if (count($new_version_arr) > count($curr_version_arr)) {
                $is_new = 1;
            }
        }

        if ($is_new) {
            $result = array(
                    'version_no' => $info['version_no'],
                    'path'       => SITE_URL.$info['path'],
                    'intro'      => $info['intro'],
                    'size'       => screen_helper::get_filesize($info['size']),
                    'add_time'   => $info['add_time']
            );
        }
        api_helper::return_api_data(1000, 'success', $result, $api_log_id);
    }
}