<?php

/**
 * alltosun.com 操作日志widget log.php
 * ============================================================================
 * 版权所有 (C) 2009-2011 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址：http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 祝利柯 (zhulk@alltosun.com) $
 * $Date: 2011-5-4 下午05:16:31 $
*/

class log_widget
{
    /**
     * 记录操作日志
     * @param string $res_name    操作的表名
     * @param int    $res_id      资源id
     * @param string $action      执行的动作 （新增；修改；删除; 审核）
     */
    public function record($res_name, $res_id, $action)
    {
        $opera = array('新增','修改','删除','审核');

        if (is_string($action) && !in_array($action,$opera)) {
            return false;
        }

        if (is_array($action)) {

            $result = array_diff($action, $opera);

            if ($result) {

                return false;
            }
        }

        if (!$res_id || !$res_name) {

            return false;
        }

        /* 批量添加日志：array('表1','表2','表3'); $res_id=array('表1ID','表2ID','表3ID');
        *  如果每个操作不一样，$action就用$action=array('表1的操作','表2ID的操作','表3ID的操作');
        *  注意一定要一一对齐。
        */
        // 对不同表进行操作
        if (is_array($res_name) && is_array($res_id)) {

            foreach ($res_name as $k => $v) {
                if (is_array($action)) {
                    if (isset($action[$k])) {
                        $this->_do_record($v, $res_id[$k], $action[$k]);
                    } else {
                        return false;
                    }
                } else {
                    $this->_do_record($v, $res_id[$k], $action);
                }
            }
        } elseif (is_string($res_name) && is_array($res_id)) {
            // 对同一张表进行操作
            if (is_array($action)) {
                foreach ($action as $k => $v) {
                    $this->_do_record($res_name, $res_id[$k], $v);
                }
            } elseif(is_string($action)) {
                foreach ($res_id as $v) {
                    $this->_do_record($res_name, $v, $action);
                }
            }
        } else {
            // 普通的日志记录
            $this->_do_record($res_name, $res_id, $action);
        }
        return true;
    }

    /**
     * 写操作日志
     * @param string $res_name    操作的表名
     * @param int    $res_id      资源id
     * @param string $action      执行的动作
     */
    private function _do_record($res_name, $res_id, $action)
    {

        $ip      = Request::getClientIp();
        $member_id = member_helper::get_member_id();

        // 日志记录：(id日志自增id，user_id操作人，action动作，$res_id资源的唯一标识，$res_name表名，$ip操作的ip地址，$add_time添加时间)
        $log_info = array(
            'member_id'      => $member_id,
            'action'      => $action,
            'res_id'      => $res_id,
            'res_name'    => $res_name,
            'ip'          => $ip,
            'add_time'    => date('Y-m-d H:i:s')
        );
        _model('log')->create($log_info);
    }

    /**
     * 拼装操作日志信息
     * @param array 操作日志记录
     * @param string 拼装好的数据
     * @return string
     */
    public function make_log_message($log_info)
    {
        if (!is_array($log_info) || !$log_info) return '';

        $action = $log_info['action'];

        if ($action == 'edit') {
            $revision_list = $this->get_last_two_revision($log_info['res_name'], $log_info['res_id'], $log_info['revision_id']);
            // 因为我是编辑时，取的list所以正常情况下一定有两条记录。但是，可能有的操作添加时没有加操作日志，所以可能取出的只有一条
            if (count($revision_list) == 2) {
                // 找出二者之间的不同
                $diff_arr = array_diff_assoc($revision_list[0]['value'], $revision_list[1]['value']);

                $diff_str = '';
                // 如果只改了一个字段，则显示出来
                if (isset($diff_arr['update_time'])) unset($diff_arr['update_time']);
                if (count($diff_arr) == 1) {
                    foreach ($diff_arr as $key=>$val) {
                        switch ($key) {
                            case 'status':
                                if ($val == 1) {
                                    $diff_str = "，把这条数据还原了！";
                                } elseif ($val == 0) {
                                    $diff_str = "，把这条数据加入了回收站！";
                                }
                                break;
                            case 'title':
                                $diff_str = "，把标题由 ".$revision_list[1]['value'][$key]." 更改为 ".$revision_list[0]['value'][$key];
                                break;
                            case 'content':
                                $diff_str = "，对内容进行了更改，请看详情！";
                                break;
                            default:
                                $diff_str = "，把字段 ".$key." 的值由 ".$revision_list[1]['value'][$key]." 更改为 ".$revision_list[0]['value'][$key];
                        }
                    }
                } elseif (count($diff_arr) > 1) {
                    // 改了多个字段，则只列出对哪些字段做了更改
                    foreach ($diff_arr as $key=>$val) {
                        $diff_str .= ",{$key}";
                    }
                    $diff_str = ltrim($diff_str, ',');
                    $diff_str = "，修改了如下字段 ".$diff_str;
                } else {
                    $diff_str = "，但是没有修改数据";
                }
            }
        }

        //$tmp_str = "%s 对 %s 表执行了 %s 操作，%s 了 %s 表中的 %s";
        $format = '<font color="green">%s</font> 对 <font color="green">%s</font> 表执行了<font color="red">%s</font> 操作，<font color="red">%s</font> 了 <font color="green">%s</font>';
        if (isset($diff_str)) {
            $format = $format.$diff_str;
        }

        if ($log_info['admin_id']) {
            $user_id = $log_info['admin_id'];
        } else {
            $user_id = $log_info['user_id'];
        }

        // 获取用户名
        // 暂时没有该信息，等完了再取消注释
        // $user_name = user_helper::display_name($user_id);
        if ($action == 'add') {
            $action = '新增';
        } elseif ($action == 'edit') {
            $action = '编辑';
        } elseif ($action == 'delete') {
            $action = '删除';
        } elseif ($action == 'verify') {
            $action = '审核';
        }
        $user_name = user_helper::display_name($user_id);
        /* $log_res_names = log_config::$log_res_name;
        $res_name = $log_res_names[$log_info['res_name']]; */
        $res_name = category_helper::get_db_name($log_info['res_name']);
        $str = sprintf($format, $user_name, $res_name, $action, $action, $log_info['title']);


        return $str;
    }


}
?>