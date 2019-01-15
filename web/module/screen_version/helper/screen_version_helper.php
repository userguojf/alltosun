<?php
/**
 * alltosun.com 主页面 screen_version_help.php
 * ============================================================================
 * 版权所有 (C) 2009-2018 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 赵高举 (zhaogj@alltosun.com) $
 * $Date: 2018/4/18 11:44 $
 * $Id$
 */

class screen_version_helper
{
    /**
     * 根据版本号获取该版本号下的设备数量
     * @param $version
     * @return bool
     */
    static function get_screen_count_by_version($version)
    {
        if (!$version) {
            return false;
        }

        $filter = array(
            'version_no' => $version
        );
        $count = _model('screen_device')->getTotal($filter);
        return $count;
    }

    /**
     * 根据版本号和省份获取下面的设备数量
     * @param $version
     * @param $provinceid
     * @return bool
     */
    static function get_screen_count_by_provinceid($version, $provinceid)
    {
        if (!version || !$provinceid) {
            return false;
        }

        $filter = array(
            'version_no' => $version,
            'province_id' => $provinceid
        );
        $count = _model('screen_device')->getTotal($filter);
        return $count;
    }

    /**
     * 获取某个省份下有设备的营业厅数量
     * @param $provinceid
     * @return bool
     */
    static function get_business_count_by_provinceid($provinceid, $version_no)
    {
        if (!$provinceid || !$version_no) {
            return false;
        }

        $count = _model('screen_device')->getCol('select count(distinct business_id) from screen_device where province_id = "'.$provinceid.'" and version_no = "'.$version_no.'"');

        return $count[0];

    }


    /**
     * 根据营业厅id获取营业厅下面的设备数量
     * @param $businessid
     * @param $version_no
     * @return bool
     */
    static function get_screen_count_by_businessid($businessid, $version_no)
    {
        if (!$businessid) {
            return false;
        }

        $filter = array(
            'business_id' => $businessid,
            'version_no' => $version_no
        );
        $count = _model('screen_device')->getTotal($filter);
        return $count;

    }


    /**
     * 根据版本号获取当前版本下有设备的省份数量
     * @param $version_no
     * @return bool
     */
    static function get_province_count_by_versionno($version_no)
    {
        if (!$version_no) {
            return false;
        }

        $pro_version_list_count = _model('screen_device')->getCol(" select count(distinct province_id) from screen_device where version_no='" . $version_no . "' ORDER BY `province_id` ");
        return $pro_version_list_count[0];

    }
}