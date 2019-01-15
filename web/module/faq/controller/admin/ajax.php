<?php
/**
 * alltosun.com  ajax.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-11-26 下午2:50:02 $
 * $Id$
 */
class Action {
    // 改变常见问题的发布状态
    public function up_line() {
        $id = Request::post ( 'id', 0 );
        $status = Request::post ( 'status', 0 );
        
        if (! $id) {
            return array (
                    'info' => 'no' 
            );
        }
        
        _model ( 'faq_record' )->update ( array (
                'id' => $id 
        ), array (
                'status' => $status 
        ) );
        
        return array (
                'info' => 'ok' 
        );
    }
    
    // 常见问题的优先级
    public function ajax_update() {
        $id = tools_helper::post ( 'id', 0 );
        $value = tools_helper::post ( 'value', - 1 );
        // 判断条件
        if (! $id) {
            return array (
                    'info' => 'failed',
                    'msg' => '未选择数据' 
            );
        }
        
        if ($value == '-1') {
            return array (
                    'info' => 'failed',
                    'msg' => '请填写修改的值' 
            );
        }
        
        // 更新
        $result = _model ( 'faq_record' )->update ( $id, array (
                'view_order' => $value 
        ) );
        
        // 判断结果
        if ($result) {
            return array (
                    'info' => 'ok',
                    'msg' => '修改成功' 
            );
        }
        
        return array (
                'info' => 'failed',
                'msg' => '未修改数据' 
        );
    }
    
    /**
     * 联动的问题
     */
    public function get_question_info() {
        // 获取项目的ID
        $diff_project_id = Request::post ( 'diff_project_id', 0 );
        
        if (! $diff_project_id) {
            return array (
                    'diff_question' => '未选择所属项目',
                    'msg' => 'no' 
            );
        }
        
        if (! in_array ( $diff_project_id, user_defined_config::$diff_project_id )) {
            return array (
                    'diff_question' => '未选择所属项目',
                    'msg' => 'no' 
            );
        }
        
        if ($diff_project_id == 1) {
            $diff_question = user_defined_config::$awifi_diff_question;
        } elseif ($diff_project_id == 2) {
            $diff_question = user_defined_config::$dm_diff_question;
        } elseif ($diff_project_id == 3) {
            $diff_question = user_defined_config::$ibeacom_diff_question;
        }
        
        // 处理
        $info = [ ];
        $i = 0;
        
        foreach ( $diff_question as $k => $v ) {
            $info [$i] ['key'] = $k;
            $info [$i] ['val'] = $v;
            
            $i ++;
        }
        
        return array (
                'diff_question' => $info,
                'msg' => 'ok' 
        );
        ;
    }
}