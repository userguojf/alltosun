<?php

/**
 * alltosun.com Smarty常用函数 smarty.php
 * ============================================================================
 * 版权所有 (C) 2009-2011 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 高竞竞 (gaojj@alltosun.com) $
 * $Date: 2010-12-28 下午08:05:48 $
*/

/**
 * widget标签处理函数
 * @param array $param
 * @param object $smarty
 * @return string | null
 */
function smarty_function_widget($params, &$smarty)
{
    global $mc_wr;

    // 当类名与表名同时，缓存自动生效。否则不生效
    $model = $params['model'];
    $func  = $params['func'];
    isset($params['file']) && $file = $params['file'];
    isset($params['assign']) && $assign = $params['assign'];
    isset($params['cache']) && $cache = $params['cache'];

    if (defined('CACHE') && CACHE && isset($cache) && !empty($cache) && is_object($mc_wr)) {
        // 缓存的命名空间，从在resource表中取表名，不存在，取类名
        $table = get_resource($model, 'table');
        if (empty($table)) $table = $model;

        $key = __FUNCTION__.serialize($params);
        $args = func_get_args();
        // 在函数内使
        $tem = $mc_wr->call_user_func_array(__FUNCTION__, $args, "{$table}list_ns", $key);
        // @TODO 对于评论翻页、以及需要保留用户相关信息的应用有问题（暂不能使用缓存）
        if (null !== $tem) {
            return $tem;
        }
    }

    $smarty->assign('params', $params);

    $this_widget = _widget($model);
    $smarty->assign('this_widget', $this_widget);

    $list = $this_widget->$func($params);
    $smarty->assign('list', $list);

    // 如果没有传入file，则直接返回widget处理结果
    if (!isset($file) && !isset($assign)) {
        return $list;
    }

    // 可通过$params['assign']来设置widget返回的值用该变量名接受
    if (isset($assign)) {
        $smarty->assign($assign, $list);
    }

    // 支持module.widget形式的name
    if (strpos($model, '.') !== false) {
        list($module_name, $widget_name) = explode('.', $model);
    } else {
        $module_name = $widget_name = $model;
    }

    if (isset($file)) {
        // 优先www
        $tpl = Config::get('template_dir')."/widget/{$module_name}/{$file}";
        if (!file_exists($tpl)) {
            // 其次module
            $tpl = MODULE_PATH."/{$module_name}/template/widget/{$file}";
        }
        return $smarty->fetch($tpl);
    }

    // 当只对返回值进行assign操作
    return;
}

/**
 * widget_group标签处理函数（调用一组smarty_function_widget()模块）
 * @param array $params
 * @param object $smarty
 * @return string
 */
function smarty_function_widget_group($params, &$smarty)
{
    $content = '';
    foreach ($params['widget'] as $v) {
        $content .= smarty_function_widget($v, $smarty);
    }
    return $content;
}

/**
 * 包含core模块的模板
 * @param array $params
 * @param obj $smarty
 */
function smarty_function_include_core($params, &$smarty)
{
    if (empty($params['file'])) {
        throw new SmartyException('请输入包含的模板地址');
        return false;
    }

    $smarty->assign($params);

    return $smarty->fetch(MODULE_CORE."/template/{$params['file']}");
}

/**
 * 包含根目录的公共模版
 * @param array $params
 * @param obj $smarty
 */
function smarty_function_include_www($params, &$smarty)
{
    if (empty($params['file'])) {
        throw new SmartyException('请输入包含的模板地址');
        return false;
    }

    $smarty->assign($params);

    return $smarty->fetch(ROOT_PATH."/template/default/{$params['file']}");
}

/**
 * 加载js文件，默认同一模板渲染一次的话，该js文件只会加载一次
 * 如果传入索引reload=1或者赋值smarty变量load_js_nostatic=1的话，则重复加载
 * @param array $params 参数数组，索引file，和索引lib两者必传其一；索引theme用于指定jqueryui采用的theme
 * @param obj $smarty
 * @return string
 */
function smarty_function_load_js($params, &$smarty)
{
    if (empty($params['file']) && empty($params['lib'])) {
        throw new SmartyException('请输入要加载的js文件');
        return false;
    }

    // In case google cdn is blocked
    $script_ext = '';
    $static_url = '';

    // 加载js lib
    if (!empty($params['lib'])) {
        switch ($params['lib']) {
            case 'jquery':
                if (JQUERY_GOOGLE_CDN) {
                    $params['file'] = 'http://ajax.googleapis.com/ajax/libs/jquery/'.JQUERY_VER.'/jquery.min.js';
                    $script_ext = '<script type="text/javascript">
                    if (typeof jQuery == "undefined") document.write(\'<script type="text/javascript" src="js/jquery'.JQUERY_VER.'.js"><\'+"/script>");
                    </script>';
                } else {
                    $params['file'] = $static_url.'/js/jquery'.JQUERY_VER.'.js';
                }
                break;
            case 'jquery-ui':
                if (empty($params['theme'])) $params['theme'] = 'base';

                if (JQUERY_GOOGLE_CDN) {
                    $params['file'] = array(
                        'http://ajax.googleapis.com/ajax/libs/jqueryui/'.JQUERY_UI_VER.'/jquery-ui.min.js',
                        'http://ajax.googleapis.com/ajax/libs/jqueryui/'.JQUERY_UI_VER.'/i18n/jquery.ui.datepicker-zh-CN.js'
                    );

                    $ui_css['file'] = 'http://ajax.googleapis.com/ajax/libs/jqueryui/'.JQUERY_UI_VER.'/themes/'.$params['theme'].'/jquery-ui.css';

                    $script_ext = '<script type="text/javascript">
                    if (typeof jQuery.ui == "undefined") {
                      document.write(\'<script type="text/javascript" src="js/jqueryui/'.JQUERY_UI_VER.'/jquery-ui.js"><\'+"/script>");
                      document.write(\'<script type="text/javascript" src="js/jqueryui/'.JQUERY_UI_VER.'/i18n/jquery.ui.datepicker-zh-CN.js"><\'+"/script>");
                      document.write(\'<link rel="stylesheet" href="js/jqueryui/'.JQUERY_UI_VER.'/themes/'.$params['theme'].'/jquery-ui.css" type="text/css" />\');
                    }
                    </script>';
                } else {
                    $params['file'] = array(
                        'jqueryui/'.JQUERY_UI_VER.'/jquery-ui.js',
                        'jqueryui/'.JQUERY_UI_VER.'/i18n/jquery.ui.datepicker-zh-CN.js'
                    );
                    $ui_css['file'] = $static_url.'js/jqueryui/'.JQUERY_UI_VER.'/themes/'.$params['theme'].'/jquery-ui.css';
                }

                $ui_css_script = smarty_function_load_css($ui_css, $smarty);
                $script_ext = $ui_css_script.$script_ext;
                break;
            default:
                throw new SmartyException('对不起，暂不支持该js类库');
                return false;
        }
    }

    $script = '';
    $params['file'] = (array)$params['file'];
    foreach ($params['file'] as $v) {
        // 加载本地js文件
        if (strncasecmp($v, 'http://', 7) != 0) {
            if ($params['module']) {
                if ($params['site']) {
                    $module_absolute_path = MODULE_PATH.'/'.$params['module'].'/js/'.$params['site'].'/'.$v;
                    $module_file_path = $static_url.'/module/'.$params['module'].'/js/'.$params['site'].'/'.$v;
                } else {
                    $module_absolute_path = MODULE_PATH.'/'.$params['module'].'/js/'.$v;
                    $module_file_path = $static_url.'/module/'.$params['module'].'/js/'.$v;
                }
                if (!file_exists($module_absolute_path)) {
                    return;
                }
                $v = $module_file_path;
            } else {
                $v = $static_url.'/js/'.$v;
            }
        }

        $script .= '<script type="text/javascript" src="'.$v.'"></script>';
    }
    $script = $script.$script_ext;

    // 如果赋值smarty变量load_js_nostatic=1的话，则重复加载（AnForm中设置load_js_nostatic=1）
    if (!empty($smarty->getVariable('load_js_nostatic')->value)) {
        return $script;
    }

    static $loaded_js = array();

    $key = md5(serialize($params));

    // 如果传入索引reload=1的话，则重复加载
    if (isset($loaded_js[$key]) && empty($params['reload'])) {
        return '';
    }

    $loaded_js[$key] = $script;

    return $loaded_js[$key];
}

/**
 * 加载css文件，默认同一模板渲染一次的话，该css文件只会加载一次
 * 如果传入索引reload=1或者赋值smarty变量load_css_nostatic=1的话，则重复加载
 * @param array $params 参数数组，索引file必传
 * @param obj $smarty
 * @return string
 */
function smarty_function_load_css($params, &$smarty)
{
    if (empty($params['file'])) {
        throw new SmartyException('请输入要加载的css文件');
        return false;
    }

    // 加载本地css
    if (strncasecmp($params['file'], 'http://', 7) != 0) {
        if ($params['module']) {
            if ($params['site']) {
                $module_absolute_path = MODULE_PATH.'/'.$params['module'].'/css/'.$params['site'].'/'.$params['file'];
                $module_file_path = '/module/'.$params['module'].'/css/'.$params['site'].'/'.$params['file'];
            } else {
                $module_absolute_path = MODULE_PATH.'/'.$params['module'].'/css/'.$params['file'];
                $module_file_path = '/module/'.$params['module'].'/css/'.$params['file'];
            }

            if (!file_exists($module_absolute_path)) {
                return;
            }
            $params['file'] = $module_file_path;
        } else {
            $params['file'] = '/'.$params['file'];
        }
    }

    $script = '<link rel="stylesheet" href="'.$params['file'].'" type="text/css" />';

    if (!empty($smarty->getVariable('load_css_nostatic')->value)) {
        return $script;
    }

    static $loaded_css = array();

    $key = md5($params['file']);

    if (isset($loaded_css[$key]) && empty($params['reload'])) {
        return '';
    }

    $loaded_css[$key] = $script;

    return $loaded_css[$key];
}

/**
 * 去除html注释
 * @param array $params
 * @param obj $smarty
 * @return string
 */
function smarty_prefilter($out_put, &$smarty)
{
    // 去除注释
    $out_put = preg_replace('/<!--[^>|\n]*?({.+?})[^<|{|\n]*?-->/', '\1', $out_put);
    $out_put = preg_replace('/<!--.*?-->/', '', $out_put);
    $out_put = preg_replace("/\s*[\n\r]+\s*/", '',$out_put);
    return $out_put;
}

/**
 * 调用AnForm生成html代码
 * @param array $params
 * @param obj $smarty
 * @return string
 */
function smarty_function_AnForm($params, &$smarty)
{
    return AnForm::generateHtml($params);
}

/**
 * smarty的img标签处理函数
 * @param array $params
 * @param obj $smarty
 * return string
 */
function smarty_function_img($params, &$smarty)
{
    $output = '<img';
    foreach ($params as $k=>$v) {
        // 增加匹配Underscore的模板解析标签<%，不需要加任何内容直接返回
        if ($k == 'src' && strncasecmp('<', $v, 1) !== 0) {
            // image src
            if (strncasecmp('http://', $v, 7) !== 0) {
                // local image
                if (strncasecmp('/', $v, 1) !== 0) {
                    // 补全路径分隔符
                    $v = '/'.$v;
                }
                // 必须要写images目录，这样的话upload目录也可以用
                /*
                if (strncasecmp('/images', $v, 6) !== 0) {
                    $v = '/images'.$v;
                }
                */
                $v = STATIC_URL.$v;
            }
        }
        $output .= " {$k}=\"{$v}\"";
    }
    $output .= "/>";
    return $output;
}
?>