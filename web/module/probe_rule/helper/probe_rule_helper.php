<?php
/**
 * alltosun.com  probe_rule_helper.php
 * ============================================================================
 * 版权所有 (C) 2009-2015 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明: 这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 王磊 (wangl@alltosun.com) $
 * $Date: 2017-4-6 下午3:17:24 $
*/ 
class probe_rule_helper
{
    /**
     * 获取规则列表
     *
     * @return  Array
     */
    static public function get_rule_list()
    {
        $filter = array(
            'status'    =>  1
        );

        return _model('probe_rule')->getList($filter);
    }

    /**
     * 获取规则选项
     *
     * @param   Array   营业厅信息
     *
     * @return  String
     */
    static public function rule_option($b_info)
    {
        if ( !$b_info ) {
            return '';
        }

        $list = self::get_rule_list();

        // 获取规则列表
        if ( !$list ) {
            return '';
        }

        // 获取营业厅下添加的规则
        $business_rule = self::business_rule_list($b_info['id']);

        // 拼模板中用到的html代码
        $html  = '<div class="form-group form-inline"><label class="col-sm-3 control-label"><em class="red">*</em>规则</label><div class="col-sm-9">';

        foreach ($list as $k => $v) {
            $info = array();

            // 通过遍历营业厅下的规则判断当前规则在是否在营业厅下添加
            foreach ($business_rule as $key => $val) {
                if ( $v['id'] == $val['rule_id'] && $info = $val ) {
                    break;
                }
            }

            if ( $info ) {
                $html .= '<span class="js_option"><label class="checkbox">';
                $html .= '<input type="checkbox" name="info['.$v['id'].'][select]" class="js_checkbox" checked="checked" /> '.$v['content'];
                $html = str_replace('%d', '<input name="info['.$v['id'].'][value][]" class="js_option_text" style="width: 30px;" type="text" value="%d" />', $html);
                $html .= '</label></span>';

                if ( $v['alias'] == 'minute' ) {
                    $html = sprintf($html, $info['value']);
                } else if ( $v['alias'] == 'continued' ) {
                    $value = explode('-', $info['value']);
                    $html = sprintf($html, $value[0], $value[1]);
                } else if ( $val['alias'] == 'terminal' ) {
                    
                }
            } else {
                $html .= '<span class="js_option"><label class="checkbox"><input type="checkbox" name="info['.$v['id'].'][select]" id="'.$v['id'].'" class="js_checkbox" /> '.$v['content'];
                $html = str_replace('%d', '<input name="info['.$v['id'].'][value][]" class="js_option_text" style="width: 30px;" type="text" value="" />', $html);
                $html .= '</label></span>';
            }
        }
        $html .= '</div></div>';

        return $html;
    }

    /**
     * 获取营业厅下的规则
     *
     * @param   Int 营业厅ID
     *
     * @return  Array
     */
    static public function business_rule_list($b_id)
    {
        if ( !$b_id ) {
            return array();
        }

        return _model('probe_business_rule')->getList(array('business_id'=>$b_id, 'status'=>1));
    }

    /**
     * 获取营业厅下规则
     *
     * @param   Int 营业厅ID
     *
     * @return  String
     */
    static public function business_rule( $b_id )
    {
        // 获取营业厅下规则列表
        $list = self::business_rule_list($b_id);

        if ( !$list ) {
            return '暂无规则';
        }

        // 最终返回
        $html = '';

        // 遍历规则列表
        foreach ($list as $k => $v) {
            // 规则信息
            $rule_info = _model('probe_rule')->read(array('id'=>$v['rule_id'], 'status'=>1));

            if ( !$rule_info ) {
                continue;
            }

            if ( $rule_info['alias'] == 'minute' ) {
                $html .= '<span>规则'.($k + 1).'：'.sprintf($rule_info['content'], (int)$v['value']).'</span><br />';
            } else if ( $rule_info['alias'] == 'continued' ) {
                $value = explode('-', $v['value']);
                $html .= '<span>规则'.($k + 1).'：'.sprintf($rule_info['content'], (int)$value[0], (int)$value[1]).'</span><br />';
            } else if ( $rule_info['alias'] == 'terminal' ) {
                $html .= '<span>规则'.($k + 1).'：'.$rule_info['content'].'</span><br />';
            } else {
                continue;
            }
        }

        return $html;
    }

    /**
     * 获取营业厅下规则，并拼成一定格式
     *
     * @param   Int 营业厅ID
     *
     * @return  Array
     */
    static public function get_rules($b_id)
    {
        if ( !$b_id ) {
            return array();
        }

        // 获取营业厅下规则列表
        $list = self::business_rule_list($b_id);
        // 最终返回数据
        $res  = array();

        // 遍历列表，拼返回格式
        foreach ($list as $k => $v) {
            if ( !$v['alias'] || !$v['value'] ) {
                continue;
            }

            // 规则别名
            $key        = $v['alias'];
            // 规则值。住：有些规则有一个值，有些有多个值，多个值用-分隔
            $value      = explode('-', $v['value']);

            // 多个值的规则返回数组
            if ( isset($value[1]) ) {
                $res[$key] = $value;
            // 一个值的规则返回字符串
            } else {
                $res[$key] = (string)$value[0];
            }
        }

        return $res;
    }
}
?>