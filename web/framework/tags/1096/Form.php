<?php

/**
 * alltosun.com Form类 Form.php
 * ============================================================================
 * 版权所有 (C) 2009-2010 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 宁海新 (ninghx@alltosun.com) $
 * $Date: 2011-1-6 下午05:51:22 $
 * $Id: Form.php 650 2013-02-26 01:31:27Z ninghx $
*/

class AnForm
{
    // 数组验证规则
    static public $rules;
    // 数组提示信息
    static public $msgs;
    // 数组过滤信息
    static public $filters;
    // 表单提交方式
    static public $method;
    // 允许调用进行验证的方法
    static public $validate_funcs = array('required', 'minlength', 'maxlength', 'email', 'digits', 'number', 'identityCard', 'equalTo');
    // 允许调用的过滤函数
    static public $filter_funcs = array('filter_string', 'filter_keyword');
    // html的其他属性
    static public $other_attrs;
    // 字段类型默认值
    static public $fields_type;
    // 表单错误的处理方法
    static public $errorPlacement;

    /**
     * 生成html
     * 生成后台验证的规则
     * 如果没有type并且没有action 生成form的底部
     * 如果没有type但是有action 生成form头以及对应js
     * 其他生成对应的html结构和验证规则
     * @param array $params
     * @return string
     */
    public static function generateHtml($params)
    {
        $html = '';
        // 用来验证同一模版不同操作（添加和编辑等）时产生不同的验证规则
        $generate = 1;
        if ($params['type'] || $params['action']) {
            if (isset($params['type']) && !in_array($params['type'], array('text', 'password', 'select', 'checkbox', 'radio', 'textarea', 'hidden', 'submit', 'reset', 'file'))) {
                throw new AnFormGenerateException('调用不存在的表单类型');
            }
            if ($params['action']) {
                self::getMethod($params);
                $func = 'generateForm';
            } else {
                // 将名称加以处理 用来验证是否存在验证规则
                if (strpos($params['name'], '[')) {
                    $rule_name_arr = explode('[', trim($params['name'], ']'));
                    $rule = self::$rules[$rule_name_arr[0]][$rule_name_arr[1]];
                } else {
                    $rule = self::$rules[$params['name']];
                }
                // 当trigger存在但是为空或者不存在但是有验证规则时不再次生成规则以及html
                // @todo $rule 如果是空数组存在问题 应该为isset($rule) 现在isset导致搜索时不进行解析了
                if ((isset($params['trigger']) && empty($params['trigger'])) || (!isset($params['trigger']) && $rule)) {
                    $generate = 0;
                }
                if ($generate) {
                    // 获取验证规则
                    self::getRules($params);
                    if (in_array($params['type'], array('text', 'password', 'hidden', 'submit', 'reset', 'file'))) {
                        $func = 'generateInput';
                    } else {
                        $func = 'generate'.ucfirst($params['type']);
                    }
                }
            }
            if ($generate) {
                $html .= self::$func($params);
            }
        // 生成对应的尾部文件
        } else {
            $html .= self::generateFormEnd();
        }
        return $html;
    }

    /**
     * 获取表单提交方式
     * @param array $params
     */
    private static function getMethod($params)
    {
        if ($params['method']) {
            self::$method = ucfirst(strtolower($params['method']));
        }
    }

    /**
     * 获取数组规则以及提示信息
     * @param array $params
     */
    private static function getRules($params)
    {
        $rule = array();

        // 获取默认类型字段 是字符串还是数组
        if (strpos($params['name'], '[]') !== false) {
            $params['name'] = substr($params['name'], 0, -2);
            $fields_type = 'arr';
        } else {
            $fields_type = 'str';
        }

        // 获取validate的验证规则 将validate中的验证条件转化为xx=true的格式 统一到$params中
        if (isset($params['validate'])) {
            $val_arr = explode(' ', $params['validate']);
            foreach ($val_arr as $k=>$v) {
                $params[$v] = true;
            }
        }

        foreach ($params as $k=>$v) {
            if (in_array($k, self::$validate_funcs)) {
                $rule[$k] = $v;
                // 提示信息的组织
                $msg[$k.'_msg'] = $params[$k.'_msg'] ? $params[$k.'_msg']:$params['name'].'资料不完整';
            } else {
                $base_attr_name = array('type', 'filter', 'validate', 'class', 'id', 'value', 'name', 'options', 'selected', 'radios', 'checked', 'trigger');
                if (!in_array($k, $base_attr_name)) {
                    // 其他附属属性 比如readonly等
                    $other_attr[$k] = $v;
                }
            }
        }
        // 将名称加以处理  对应到某个表的某个字段
        if (strpos($params['name'], '[')) {
            $name_arr = explode('[', trim($params['name'], ']'));
            self::$rules[$name_arr[0]][$name_arr[1]] = $rule;
            self::$other_attrs[$name_arr[0]][$name_arr[1]] = $other_attr;
            self::$msgs[$name_arr[0]][$name_arr[1]] = $msg;
            self::$fields_type[$name_arr[0]][$name_arr[1]] = $fields_type;
        } else {
            self::$rules[$params['name']] = $rule;
            self::$other_attrs[$params['name']] = $other_attr;
            self::$msgs[$params['name']] = $msg;
            self::$fields_type[$params['name']] = $fields_type;
        }

        // 获取字段的过滤条件
        if (isset($params['filter'])) {
            $filter_arr = explode(' ', $params['filter']);
            foreach ($filter_arr as $k=>$v) {
                if (!in_array($v, self::$filter_funcs)) {
                    throw new AnFormFilterException('你调用的'.$v.'过滤方法不存在。');
                }
                if (strpos($params['name'], '[')) {
                    self::$filters[$name_arr[0]][$name_arr[1]][$v] = true;
                } else {
                    self::$filters[$params['name']][$v] = true;
                }
            }
        }
    }

    /**
     * 获取参数中的有效元素
     * @param array $params
     * @param int $tip
     * @return string
     */
    private static function getValidTag($params, $tip = 0)
    {
        $valid_tag = '';

        // 获取html的id 如果存在显示 如果不存在不显示
        if (!$tip) {
            $id = '';
            if ($params['id']) {
                $id .= $params['id'];
            }
            if ($id) {
                $valid_tag .= ' id="'.$id.'"';
            }
        }

        // 获取html的class 如果存在显示  如果不存在不显示
        $class = '';
        if ($params['class']) {
            $class .= $params['class'];
        }

        if ($params['validate'] && !$tip) {
            if ($class) {
                $class .= ' ';
            }
            // @todo 要不要过滤到用不到的validate
            $class .= $params['validate'];
        }
        if ($class) {
            $valid_tag .= ' class="'.$class.'"';
        }

        if (!$params['action'] && !$tip) {
            // 获取类似于min_len = 4,max_len=18之类的验证  主要用于jquery插件用于验证
            $validate = self::getValidate($params);
            if ($validate) {
                $valid_tag .= $validate;
            }
        }

        return $valid_tag;
    }

    // 获取类似于min_len = 4,max_len=18之类的验证  主要生成到对应的html上面用于jquery插件用于验证
    private static function getValidate($params)
    {
        $result = '';
        if (strpos($params['name'], '[]') !== false) {
            $params['name'] = trim($params['name'], '[]');
        }
        if (strpos($params['name'], '[')) {
            $name_arr = explode('[', trim($params['name'], ']'));
            $rule = self::$rules[$name_arr[0]][$name_arr[1]];
            $other_attr = self::$other_attrs[$name_arr[0]][$name_arr[1]];
        } else {
            $rule = self::$rules[$params['name']];
            $other_attr = self::$other_attrs[$params['name']];
        }

        $val_arr = array();
        if (isset($params['validate'])) {
            $val_arr = explode(' ', $params['validate']);
        }
        foreach ($rule as $k=>$v) {
            if (!in_array($k, $val_arr)) {
                $result .= ' '.$k.'="'.$v.'"';
            }
        }
        // 其他属性
        if ($other_attr) {
            foreach ($other_attr as $k=>$v) {
                $result .= ' '.$k.'="'.$v.'"';
            }
        }

        return $result;
    }

    /**
     * 验证数据并返回对应的数据结构
     * @param strings $template 模板名称
     */
    public static function parse($template)
    {
        // 获取模板并进行解析
        // 增加load_js_nostatic和load_css_nostatic的赋值，直接渲染js和css

        // 方案一 采用Smarty3的新特性，局部赋值渲染
//        $smarty = Response::getView();
//        $data = array('load_js_nostatic'=>1, 'load_css_nostatic'=>1);
//        $tpl = $smarty->createTemplate($template, $data);
//        $tpl->display();

        // 方案二 在渲染完毕后，去除这两个变量的赋值，保证下次渲染的纯净

        Response::assign('load_js_nostatic', 1);
        Response::assign('load_css_nostatic', 1);
        Response::fetch($template);
        Response::getView()->clearAssign(array('load_js_nostatic', 'load_css_nostatic'));
//        Response::fetch($template);

        $form_method = self::$method ? self::$method : 'Post';
        if ($form_method == 'Post') {
            $data = $_POST;
        } else {
            $data = $_GET;
        }
        /*// 按照规则进行验证
        foreach ($data as $k=>$v) {
            if (is_array($v)) {
                foreach ($v as $k1=>$v1) {
                    if (self::$fileds_type[$k][$k1] == 'str') {
                        $v1 = isset($v1) ? $v1 : '';
                    } else {
                        $v1 = isset($v1) ? $v1 : array();
                    }
                    // 利用规则进行验证数据的正确性
                    if (self::$rules[$k][$k1]) {
                        $validate_methods = self::$rules[$k][$k1];
                        foreach ($validate_methods as $k2=>$v2) {
                            // 抛出异常
                            if (!in_array($k2, self::$validate_funcs)) {
                               throw new AnFormRuleException('你调用的'.$k2.'方法php部分不存在请自行添加。');
                            }
                            if (in_array($k2, array('maxlength', 'minlength', 'min', 'max'))) {
                                self::$k2($v1, $v2, self::$msgs[$k][$k1][$k2.'_msg']);
                            // 判断两个字段是否相等
                            } elseif ($k2 == 'equalTo') {
                                $target = trim($v2, '#');
                                self::$k2($v1, $data[$k][$target], self::$msgs[$k][$k1][$k2.'_msg']);
                            } else {
                                self::$k2($v1, self::$msgs[$k][$k1][$k2.'_msg']);
                            }
                        }
                    }

                    // 过滤关键词
                    if (isset(self::$filters[$k][$k1])) {
                        foreach (self::$filters[$k][$k1] as $k2=>$v2) {
                            // 调用filter类中的方法进行过滤
                            $data[$k][$k1] = AnFilter::$k2($v1);
                        }
                    }
                }
            }
        }*/

        if (self::$rules && is_array(self::$rules)) {
            foreach (self::$rules as $k=>$v) {
                foreach ($v as $k1=>$v1) {
                    if (!isset($data[$k][$k1])) {
                        if (self::$fields_type[$k][$k1] == 'str') {
                            $data[$k][$k1] = '';
                        } else {
                            $data[$k][$k1] = array();
                        }
                    }
                    // 调用form类中的方法进行验证
                    if ($v1 && is_array($v1)) {
                        foreach ($v1 as $k2=>$v2) {
                            // 抛出异常
                            if (!in_array($k2, self::$validate_funcs)) {
                               throw new AnFormRuleException('你调用的'.$k2.'方法php部分不存在请自行添加。');
                            }
                            if (in_array($k2, array('maxlength', 'minlength', 'min', 'max'))) {
                                self::$k2($data[$k][$k1], $v2, self::$msgs[$k][$k1][$k2.'_msg']);
                            // 判断两个字段是否相等
                            } elseif ($k2 == 'equalTo') {
                                $target = trim($v2, '#');
                                self::$k2($data[$k][$k1], $data[$k][$target], self::$msgs[$k][$k1][$k2.'_msg']);
                            } else {
                                self::$k2($data[$k][$k1], self::$msgs[$k][$k1][$k2.'_msg']);
                            }
                        }
                    }
                }
            }
        }

        // 过滤字符串
        if (self::$filters && is_array(self::$filters)) {
            foreach (self::$filters as $k=>$v) {
                foreach ($v as $k1=>$v1) {
                    // 调用filter类中的方法进行过滤
                    if (is_array($v1)) {
                        foreach ($v1 as $k2=>$v2) {
                            $data[$k][$k1] = AnFilter::$k2($data[$k][$k1]);
                        }
                    } else {
                        $data[$k] = AnFilter::$k1($data[$k]);
                    }
                }
            }
        }

        // 返回数组
        return $data;
    }

    /**
     * 生成form格式的html
     * @param array $params
     * @return string
     */
    private static function generateForm($params)
    {
        $form = '<form action="'.$params['action'].'" method="'.$params['method'].'"';

        if ($params['enctype']) {
            $form .= ' enctype="multipart/form-data"';
        }
        // 提示信息的验证
        if ($params['errorPlacement']) {
            self::$errorPlacement = $params['errorPlacement'];
        }
        if (self::getValidTag($params)) {
            $form .= self::getValidTag($params);
        }

        $form .= '>';

        return $form;
    }

    /**
     * 生成input格式的html 包括input/password/hidden/submit/reset
     * @param array $params
     * @return string
     */
    private static function generateInput($params)
    {
        $input = '<span><input type="'.$params['type'].'" name="'.$params['name'].'" value="'.$params['value'].'"';
        if (self::getValidTag($params)) {
            $input .= self::getValidTag($params);
        }

        /*$name_arr = explode('[', trim($params['name'], ']'));
        if (self::$other_attrs[$name_arr[0]][$name_arr[1]]) {
            foreach (self::$other_attrs[$name_arr[0]][$name_arr[1]] as $k=>$v) {
                $input .= ' '.$k.'='.$v;
            }
        }*/

        $input .= ' /></span>';
        return $input;
    }

    /**
     * 生成slect下拉框格式的html
     * @param array $params
     * @return string
     */
    private static function generateSelect($params)
    {
        $select = '<span><select name="'.$params['name'].'"';

        if (self::getValidTag($params)) {
            $select .= self::getValidTag($params);
        }

        $select .= ' >';
        $select .= '<option value="">请选择</option>';
        if ($params['options'] && is_array($params['options'])) {
            foreach ($params['options'] as $k=>$v) {
                $select .= '<option value="'.$k.'"';
                if ($k == $params['selected']) {
                    $select .= ' selected="selected"';
                }
                $select .= '>'.$v.'</option>';
            }
        }
        $select .= '</select></span>';

        return $select;
    }

    /**
     * 生成复选框格式的html
     * @param array $params
     * @return string
     */
    private static function generateCheckbox($params)
    {
        $checkboxes = '';
        // 用于判断前台验证class的临时变量
        $i = 0;
        // 用于生成labelid
        if ($params['id']) {
            $label_id = $params['id'];
        } else {
            $label_id = 'anFormCheckbox';
        }
        if ($params['checkboxes'] && is_array($params['checkboxes'])) {
            foreach ($params['checkboxes'] as $k=>$v) {
                if ($i == 0) {
                    $checkboxes .= '<span>';
                }
                $checkboxes .= '<input id="'.$label_id.$k.'" type="checkbox" name="'.$params['name'].'"';
                if ($params['checked'] && in_array($k, $params['checked'])) {
                    $checkboxes .= ' checked="checked"';
                }
                $checkboxes .= ' value="'.$k.'"';
                if (self::getValidTag($params, $i)) {
                    $checkboxes .= self::getValidTag($params, $i);
                }
                $checkboxes .= ' /><label for="'.$label_id.$k.'">'.$v.'</label>';
                $i++;
                if ($i == count($params['checkboxes'])) {
                    $checkboxes .= '</span>';
                }
            }
        }
        return $checkboxes;
    }

    /**
     * 生成单选框格式的html
     * @param array $params
     * @return string
     */
    private static function generateRadio($params)
    {
        $radio = '';
        // 用于判断前台验证class的临时变量
        $i = 0;
        // 用于生成labelid
        if ($params['id']) {
            $label_id = $params['id'];
        } else {
            $label_id = 'anFormRadio';
        }
        if ($params['radios'] && is_array($params['radios'])) {
            foreach ($params['radios'] as $k=>$v) {
                if ($i == 0) {
                    $radio .= '<span>';
                }
                $radio .= '<input type="radio" id="'.$label_id.$k.'" name="'.$params['name'].'"';
                if ($k == $params['checked']) {
                    $radio .= ' checked="checked"';
                }
                $radio .= ' value="'.$k.'"';
                if (self::getValidTag($params, $i)) {
                    $radio .= self::getValidTag($params, $i);
                }
                $radio .= ' /><label for="'.$label_id.$k.'">'.$v.'</label>';
                $i++;
                if ($i == count($params['radios'])) {
                    $radio .= '</span>';
                }
            }
        }
        return $radio;
    }

    /**
     * 生成textarea格式的html
     * @param array $params
     * @return string
     */
    private static function generateTextarea($params)
    {
        $textarea = '<span><textarea name="'.$params['name'].'"';

        if (self::getValidTag($params)) {
            $textarea .= self::getValidTag($params);
        }

        $textarea .= ' />'.$params['value'].'</textarea></span>';

        return $textarea;
    }

    /**
     * 生成form的底部以及验证的js
     * @return string
     */
    private static function generateFormEnd()
    {
        if (self::$errorPlacement) {

        }
        $form_end = '</form>
                <script type="text/javascript">
                    $(function(){
                        $("form").validate({
                              ignore: ".ingore",
                              errorPlacement: function(error, element) {';
        if (self::$errorPlacement) {
            $form_end .= self::$errorPlacement.'(error, element);';
        } else {
            $form_end .= 'error.insertAfter(element.parent());';
        }
        $form_end .= '}});
                    });
                </script>';
        return $form_end;
    }

    /**
     * 验证数据是否必填
     * @param mixed $val
     * @param string $msg
     */
    public static function required($val, $msg)
    {
        if ($val === '' || $val === array()) {
            throw new AnFormParseException($msg);
        }
    }

    /**
     * 验证数据是否为整数
     * @param mixed $val
     * @param string $msg
     */
    public static function digits($val, $msg)
    {
        $val = intval($val);
        if (!$val) {
            throw new AnFormParseException($msg);
        }
    }

    /**
     * 验证数据是否为数字
     * @param mixed $val
     * @param string $msg
     */
    public static function number($val, $msg)
    {
        $val = floatval($val);
        if (!$val) {
            throw new AnFormParseException($msg);
        }
    }

    /**
     * 验证数据最小长度
     * @param string $val
     * @param int $len
     * @param string $msg
     */
    public static function minlength($val, $len, $msg)
    {
        if (strlen($val)<$len) {
            throw new AnFormParseException($msg);
        }
    }

    /**
     * 验证数据最大长度
     * @param string $val
     * @param int $len
     * @param string $msg
     */
    public static function maxlength($val, $len, $msg)
    {
        if (strlen($val)>$len) {
            throw new AnFormParseException($msg);
        }
    }

    /**
     * 验证数据是否是email
     * @param mixed $val
     * @param string $msg
     */
    public static function email($val, $msg)
    {
        $result =  filter_var($val, FILTER_VALIDATE_EMAIL);
        if (!$result) {
            throw new AnFormParseException($msg);
        }
    }

    /**
     * 判断两个值是否相等
     * @param string $val
     * @param string $target_val
     * @param string $msg
     */
    public static function equalTo($val, $target_val, $msg)
    {
        if ($val != $target_val) {
            throw new AnFormParseException($msg);
        }
    }

    public static function identityCard($val, $msg)
    {
        if (!preg_match("/^\d{15}(\d{2}[0-9X])?$/i", $val)){
            throw new AnFormParseException($msg);
        }
        if (strlen($val) == 15) {
            if(intval("19".substr($val, 6, 2)) < 1900 || intval("19".substr($val, 6, 2)) > date('Y')){
                throw new AnFormParseException($msg);
            }
            $birth = substr($val, 6, 2)."-".substr($val, 8, 2)."-".substr($val, 10, 2);
            if(!self::is_date($birth)){
                throw new AnFormParseException($msg);
            }
        }
        if (strlen($val) == 18) {
            if(intval("19".substr($val, 6, 2)) < 1900 || intval("19".substr($val, 6, 2)) > date('Y')){
                throw new AnFormParseException($msg);
            }
            $birth = substr($val, 6, 4)."-".substr($val, 10, 2)."-".substr($val, 12, 2);
            if(!self::is_date($birth)){
                throw new AnFormParseException($msg);
            }
            $iW = array(7,9,10,5,8,4,2,1,6,3,7,9,10,5,8,4,2,1);
            $iSum = 0;
            $card_arr = str_split($val);
            for ($i=0; $i<17; $i++) {
                $iC = $card_arr[$i];
                $iVal = intval($iC);
                $iSum += $iVal * $iW[$i];
            }
            $iJYM = $iSum % 11;
            if($iJYM == 0) $sJYM = "1";
            else if($iJYM == 1) $sJYM = "0";
            else if($iJYM == 2) $sJYM = "x";
            else if($iJYM == 3) $sJYM = "9";
            else if($iJYM == 4) $sJYM = "8";
            else if($iJYM == 5) $sJYM = "7";
            else if($iJYM == 6) $sJYM = "6";
            else if($iJYM == 7) $sJYM = "5";
            else if($iJYM == 8) $sJYM = "4";
            else if($iJYM == 9) $sJYM = "3";
            else if($iJYM == 10) $sJYM = "2";
            $cCheck = strtolower($card_arr[17]);
            if ( $cCheck != $sJYM ) {
                throw new AnFormParseException($msg);
            }
        }
        $lv_area_id = substr($val, 0, 2);
        if ($lv_area_id=="11" || $lv_area_id=="12" || $lv_area_id=="13" || $lv_area_id=="14" || $lv_area_id=="15" || $lv_area_id=="21" ||
            $lv_area_id=="22" || $lv_area_id=="23" || $lv_area_id=="31" || $lv_area_id=="32" || $lv_area_id=="33" || $lv_area_id=="34" ||
            $lv_area_id=="35" || $lv_area_id=="36" || $lv_area_id=="37" || $lv_area_id=="41" || $lv_area_id=="42" || $lv_area_id=="43" ||
            $lv_area_id=="44" || $lv_area_id=="45" || $lv_area_id=="46" || $lv_area_id=="50" || $lv_area_id=="51" || $lv_area_id=="52" ||
            $lv_area_id=="53" || $lv_area_id=="54" || $lv_area_id=="61" || $lv_area_id=="62" || $lv_area_id=="63" || $lv_area_id=="64" ||
            $lv_area_id=="65" || $lv_area_id=="71" || $lv_area_id=="82" || $lv_area_id=="82" ) {
        } else {
            throw new AnFormParseException($msg);
        }
    }

    private static function is_date($str, $separator = "-")
    {
        $date_arr = explode($separator, $str);
        if (count($date_arr) != 3) {
            return false;
        }
        $year = intval($date_arr[0]);
        $month = intval($date_arr[1]);
        $day = intval($date_arr[2]);
        if ($month > 12 || $month < 1) {
            return false;
        }
        if (($month == 1 || $month == 3 || $month == 5 || $month == 7 || $month == 8 || $month == 10 || $month == 12) && ($day > 31 || $day < 1)) {
            return false;
        }
        if (($month == 4 || $month == 6 || $month == 9 || $month == 11) && ($day > 30 || $day < 1)) {
            return false;
        }
        if ($month == 2) {
            if($day < 1) {
                return false;
            }
            $leap_year = false;
            if (($year%100) == 0) {
                if(($year%400) == 0) {
                    $year = true;
                }
            } else {
                if(($year%4) == 0) {
                    $year = true;
                }
            }
            if ($leap_year) {
                if($day > 29) {
                    return false;
                }
            } else {
                if($day > 28) {
                    return false;
                }
            }
        }
        return true;
    }
}
?>