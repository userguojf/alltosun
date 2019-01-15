<?php

/**
 * alltosun.com 路由规则类 Rule.php
 * ============================================================================
 * 版权所有 (C) 2009-2011 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 高竞竞 (gaojj@alltosun.com) $
 * $Date: 2010-12-31 下午03:27:11 $
*/

class AnRule
{
    private static $instance;
    private $rules = array();
    private $alias = array();
    private $redirect = array(); // 待定
    private $dirs = array();
    private $vars = array();
    private $params = array();
    private $paramsParsing = false;
    private $parseMatched = false;
    private $controllerDir = '';

    private function __construct(){}
    private function __clone(){}

    public static function getInstance()
    {
        if (empty(self::$instance)) {
           self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     *
     * @param string $rule
     * @return object $this
     * @example $rule->set('{$module=blog}/{$app=u}/{$visit_id=\d+}/*');
     * @example $rule->set('{$module=blog}/{$app=u}/{$visit_id|get_user_id_by_url}/*');
     * @example $rule->set('{$module=blog}/{$app=u}/*');
     * @example $rule->set('{$module=blog}/{$app=group}/{$group_id=\d+}/*');
     * @example $rule->set('{$module=blog|admin}/*');
     */
    public function set($rule)
    {
        array_push($this->rules, $rule);
        return $this;
    }

    /**
     * 设置别名规则
     * @param $rule 别名规则
     * @return object $this
     * @example $rule->setAlias(array('culture'=>'article')); 默认为controller的替换
     * @TODO $rule->setAlias(array('culture/*'=>'article/*'));
     * @TODO $rule->setAlias(array('blog/{$customer_url}/*'=>'blog/u/{$customer_url}/*'));
     */
    public function setAlias($rule)
    {
        $this->alias = array_merge($this->alias, $rule);
        return $this;
    }

    /**
     * 是否存在规则变量定义
     * @param string $ruleMeta 规则元数据
     */
    private function variableDefined($ruleMeta)
    {
        // 2011-7-21 支持类profile/{$visit_id=\d+}/*这样的先匹配指定字符串，再匹配变量的规则
//        return strncasecmp($ruleMeta, '{$', 2) == 0 && !$this->paramsParsing;
        return strncasecmp($ruleMeta, '{$', 2) == 0;
    }

    /**
     * 默认规则解析，优先目录，次文件
     * @param mixed $urlParam url参数（$ruleMeta为*时），或者参数数组（$ruleMeta为指定字符串时）
     * @param string $ruleMeta 规则元数据，为*匹配所有，也为指定字符串
     * @return bool
     */
    private function defaultRuleParse($urlParams, $ruleMeta = '*')
    {
        if (empty($urlParams)) return false;
        if ($ruleMeta != '*' && $ruleMeta != $urlParams) {
            // 指定字符串的匹配，比如blog/*中的blog
            return false;
        }

        $urlParams     = (array)$urlParams;
        $paramsParsing = &$this->paramsParsing;
        $dirs          = &$this->dirs;
        $params        = &$this->params;
        $controllerDir = &$this->controllerDir;

        // 将剩余的url进行目录和参数的划分
        foreach ($urlParams as $k=>$v) {
            // 如果已经开始解析参数的话，则不再匹配目录
            if (!$paramsParsing) {
                // 目录
                $dir = !empty($dirs) ? implode('/', $dirs).'/' : '';

                $t_dir = $controllerDir.'/'.$dir.$v;
                if (is_dir($t_dir)) {
                    // %00 hack
                    if (realpath($t_dir) != $t_dir) {
                        exit('Access Denied');
                    }
                    $dirs[] = $v;
                    continue;
                }
            }

            $paramsParsing = true;
            $params[] = $v;
        }
        return true;
    }

    /**
     * 自定义规则解析
     * @param string $urlParam url参数
     * @param string $ruleMeta 规则元数据
     * @return mixed 返回解析后的数组array('varName'=>'varValue')，或者解析不符合返回false
     * @tutorial 支持规则{$varName}，即无规则限制，直接匹配变量
     * @tutorial 支持规则{$varName=pregExpression}，正则规则限制
     * @tutorial 支持规则{$varName|funcName=pregExpression}，数据源，默认数据源函数只传入一个参数，即$urlParam
     * @tutorial 支持规则{$varName|funcName:param1:param2=pregExpression}，多个参数的数据源函数，第一个参数为$urlParam
     * @tutorial 支持规则{$varName|funcName}，纯数据源，无规则限制
     */
    private function customRuleParse($urlParam, $ruleMeta)
    {
        $varRuleArr = explode('=', $ruleMeta);

        $varName = substr($varRuleArr[0], 2);

        if (empty($varRuleArr[1])) {
            // 纯变量赋值，无规则解析，如{$varName}或{$varName|funcName}
            $varName = substr($varName, 0, -1);
        }

        if (property_exists($this, $varName)) {
            throw new Exception("{$varName}已经被设为url内置属性名，不能用于自定义属性名");
        }

        if (!empty($varRuleArr[1])) {
            // 规则匹配
            $rule = substr($varRuleArr[1], 0, -1);
            if (!preg_match("/^$rule$/i", $urlParam)) return false;
        }

        if (stripos($varName, '|') !== false) {
            // 数据源匹配
            list($varName, $funcName) = explode('|', $varName);
//            $funcParams = explode(':', $funcName);
//            $funcName   = array_shift($funcParams);
//            array_unshift($funcParams, $urlParam);
            $funcParams = $urlParam;
            if (!is_array($funcParams)) {
                $funcParams = (array)$funcParams;
            }
            $varValue = call_user_func_array($funcName, $funcParams);
            if (empty($varValue)) return false;
        }

        // 如果没有进行数据源匹配或者匹配结果为true的话
        if (!isset($varValue) || $varValue) {
            $varValue = $urlParam;
        }

        !isset($varValue) && $varValue = $urlParam;

        // 御用模块变量名，模块需要重新修正controller_dir
        if ($varName == 'module') {
            $this->controllerDir = MODULE_PATH."/$varValue/controller";
        }
        return array($varName=>$varValue);
    }

    /**
     * 解析规则开始，重置所有参数
     */
    private function parseStart()
    {
        $this->dirs = $this->vars = $this->params = array();
        $this->controllerDir = Config::get('controller_dir');
        // 进入$params解析模式，所有剩余的url参数都进入$params，不再匹配
        $this->paramsParsing = false;
    }

    /**
     * 规则解析
     * @param $url 当前访问的url
     */
    public function parse($url)
    {
        $rules = &$this->rules;
        $alias = &$this->alias;
        $dirs  = &$this->dirs;
        $vars  = &$this->vars;
        $controllerDir = &$this->controllerDir;

        if (!$url) return true;

        // 注意：要求url中用来传递的参数不能含有小数点
        list($url) = explode('.', $url, 2);
        $urls      = explode('/', $url);

        foreach ($rules as $rule) {
            $this->parseStart();

            $ruleMetas = explode('/', $rule);

            $ruleMetasCount = count($ruleMetas);
            foreach ($ruleMetas as $k2=>$ruleMeta) {


                // url比规则少，不匹配，退出当前规则
                // blog/u/
                // blog/u/7/photo/album/*
                if (empty($urls[$k2]) && $ruleMeta != '*') {
                    break;
                }

                if ($ruleMeta == '*') {
                    // 星号为最后一个规则，将剩余的url参数按默认规则解析
                    $urlParams = array_slice($urls, $k2);
                    $this->defaultRuleParse($urlParams);
                    // 匹配成功，退出所有的规则解析
                    $this->parseMatched = true;
                    break 2;
                }

                $urlParam = $urls[$k2];

                // 解析规则{$var=rule}，{$var|source=\d}
                if ($this->variableDefined($ruleMeta)) {
                    $parsedData = $this->customRuleParse($urlParam, $ruleMeta);
                    if (!$parsedData) break;
                    $vars = array_merge($vars, $parsedData);

                    // 目录匹配，{$module=blog}，module可以是个目录
                    $dir = !empty($dirs) ? implode('/', $dirs).'/' : '';
                    if (is_dir($controllerDir.'/'.$dir.$urlParam)) {
                        $dirs[] = $urlParam;
                    }

                } elseif (!$this->defaultRuleParse($urlParam, $ruleMeta)) {
                    break;
                }

                if ($k2 + 1 == $ruleMetasCount) {
                    $this->parseMatched = true;
                    break 2;
                }
            }
        }

        if (!$this->parseMatched) {
            // 没有匹配自定义规则，走默认规则
            $this->defaultRuleParse($urls);
            $this->parseMatched = true;
        }

        $this->parseMatched();
    }

    /**
     * 匹配成功后的数据处理
     */
    private function parseMatched()
    {
        $dirs   = &$this->dirs;
        $vars   = &$this->vars;
        $params = &$this->params;
        $alias  = &$this->alias;
        $url    = AnUrl::getInstance();

        $controller = array_shift($params);
        $action     = array_shift($params);

        if (!empty($controller) && in_array($controller, array_keys($alias))) {
            $controllerFile = $alias[$controller];
        } else {
            $controllerFile = $controller;
        }

        $url['dirs'] = $dirs;
        $url['controller'] = $controller;
        $url['controllerFile'] = $controllerFile;
        $url['action'] = $action;
        $url['params'] = $params;

        foreach ($vars as $k=>$v) {
            $url[$k] = $v;
        }
    }
}
?>