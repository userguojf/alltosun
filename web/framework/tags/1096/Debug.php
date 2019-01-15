<?php

/**
 * alltosun.com 调试信息文件 Debug.php
 * ============================================================================
 * 版权所有 (C) 2007-2009 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: gaojj $
 * $Date: 2012-01-19 02:37:25 +0800 (四, 19  1 2012) $
 * $Id: Debug.php 1074 2015-12-01 12:45:50Z anr $
 * @link http://wiki.alltosun.com/index.php?title=Framework:Debug.php
*/

class AnDebug
{
    public static $class = '';
    public static $dkey = 0;
    public static $op = array(); // AnDebug::$op[] = array('tyep'=>'sql', 'info'=>array())

    public static function array2str($info)
    {
        return substr(str_replace(' ', '', var_export($info, true)), 0, 120);
    }

    public static function echoMC($info)
    {
        if (empty($info)) {
            return ;
        }
        if (AnDebug::$class == 'tclass') AnDebug::$class = 'tclass2';
        else AnDebug::$class = 'tclass';

        $msg  = "<table cellspacing='0' class='" . AnDebug::$class ."'>\n";
        $msg .= "  <tr><th rowspan='2' width='20'>".(++AnDebug::$dkey)."</th>";
        $msg .= "<td width='60'>{$info['time']} ms</td>";
        $msg .= "<td class='bold'>{$info['fun']}<br>\n{$info['op']}<br>\n{$info['ns']}";
        if (is_array($info['key'])) {
            $msg .= implode(',', $info['key']);
        } else {
            $msg .= $info['key'];
        }
        $msg .= "<br>\n";
        if (is_array($info['mc_key'])) {
            $msg .= implode(',', $info['mc_key']);
        } else {
            $msg .= $info['mc_key'];
        }
        $msg .= "</td></tr>\n";
        //$msg .= "  <tr><td>Explain</td><td><table cellspacing='0' class='nokey'><tr class='firsttr'><td width='5%' class='firsttd'>id</td><td width='10%'>select_type</td><td width='12%'>table</td><td width='5%'>type</td><td width='20%'>possible_keys</td><td width='10%'>key</td><td width='8%'>key_len</td><td width='5%'>ref</td><td width='5%'>rows</td><td width='20%'>Extra</td></tr>\n";
        //$msg .= "    <tr></tr></table></td></tr>\n";
        $msg .= "</table>\n";
        echo $msg;
    }

    public static function echoDB($info)
    {
        if (AnDebug::$class == 'tclass') AnDebug::$class = 'tclass2';
        else AnDebug::$class = 'tclass';

        $msg  = '<table cellspacing="0" class="'.AnDebug::$class.'"><tr><th rowspan="2" width="20">'.(++AnDebug::$dkey).'</th><td width="60">'.$info['time'].' ms</td><td class="bold">'.$info['db']."<br>\n".htmlspecialchars($info['sql']) . $info['sql_info'] . "\n<br />" . $info['sql_real'] . (($info['info']) ? "\n<br />" . $info['info'] : '') . "</td></tr>\n";
        if(!empty($info['explain'])) {
            $msg .= '<tr><td>Explain</td><td><table cellspacing="0"  class="nokey"><tr class="firsttr"><td width="5%" class="firsttd">id</td><td width="10%">select_type</td><td width="12%">table</td><td width="5%">type</td><td width="20%">possible_keys</td><td width="10%">key</td><td width="8%">key_len</td><td width="5%">ref</td><td width="5%">rows</td><td width="20%">Extra</td></tr><tr>';
            foreach ($info['explain'] as $ekey => $explain) {
                ($ekey == 'id') ? $tdclass = ' class="firsttd"' : $tdclass='';
                if ($ekey == 'key' && !$explain) $tdclass = ' class="nokey"';
                if (empty($explain)) $explain = '-';
                $msg .=  '<td'.$tdclass.'>'.$explain.'</td>';
            }
            $msg .= '</tr></table></td></tr>' . "\n";
        }
        $msg .= "</table>\n";
        echo $msg;
    }

/**
 * 总输出
 */
public static function echoMsg()
{
    echo '<div id="an_debug" style="clear: both;">
    <!--
    ***************************** debug *********************************************
    -->
    <hr>
    <style>
    .tclass, .tclass2 {text-align:left;width:760px;border:0;border-collapse:collapse;margin-bottom:5px;table-layout: fixed; word-wrap: break-word;background:#FFF;float:left;}
    .tclass table, .tclass2 table {width:100%;border:0;table-layout: fixed; word-wrap: break-word;}
    .tclass table td, .tclass2 table td {border-bottom:0;border-right:0;border-color: #ADADAD;}
    .tclass th, .tclass2 th {border:1px solid #000;background:#CCC;padding: 2px;font-family: Courier New, Arial;font-size: 11px;}
    .tclass td, .tclass2 td {border:1px solid #000;background:#FFFCCC;padding: 2px;font-family: Courier New, Arial;font-size: 11px;}
    .tclass2 th {background:#D5EAEA;}
    .tclass2 td {background:#FFFFFF;}
    .firsttr td {border-top:0;}
    .firsttd {border-left:none !important;}
    .nokey {background:#FFFCCC;}
    .bold {font-weight:bold;}
    </style>
    ';

    // 执行时间，内存使用，执行sql次数
    echo '<table class="tclass"><tr><td>time: ';
    echo AnPHP::runTime();
    echo " :\n memory_get_usage: ";
    echo number_format(memory_get_usage());
    echo "\n exec sql no: ";
    echo DB::$SQLNO;
    echo '</td></tr></table>';

    // 数据库操作
    /*echo "<br><br>数据库操作<br>\n";
    foreach (self::$op as $v) {
        AnDebug::$dkey = 0;
        if ('db' === $v['type']) {
            self::echoDB($v['info']);
        }
    }

    // 缓存操作
    echo "<br><br>缓存操作<br>\n";
    foreach (self::$op as $v) {
        AnDebug::$dkey = 0;
        if ('mc' === $v['type']) {
            self::echoMC($v['info']);
        }
    }*/

    // 缓存与数据库操作
    echo "<br><br>缓存与数据库混合操作<br>\n";
    AnDebug::$dkey = 0;
    foreach (self::$op as $v) {
        if ('db' === $v['type']) {
            // var_dump($v['info']);
            self::echoDB($v['info']);
        } elseif ('mc' === $v['type']) {
            // var_dump($v['info']);
            self::echoMC($v['info']);
        }
    }

    $class = '';

    if ($values = get_included_files()) {
        ($class == 'tclass')?$class = 'tclass2':$class = 'tclass';
        echo '<table class="'.$class.'">';
            foreach ($values as $fkey => $file) {
                echo '<tr><th width="20">'.($fkey+1).'</th><td>'.$file.'</td></tr>' . "\n";
            }
        echo "</table>\n";
    }

    if (!empty($_COOKIE) && $values = $_COOKIE) {
        ($class == 'tclass')?$class = 'tclass2':$class = 'tclass';
        $i = 1;
        echo '<table class="'.$class.'">';
            foreach ($values as $ckey => $value) {
                echo '<tr><th width="20">'.$i.'</th><td width="250">$_COOKIE[\''.$ckey.'\']</td><td>'.var_export($value, true).'</td></tr>' . "\n";
                $i++;
            }
        echo "</table>\n";
    }

    if (0 && $server = $_SERVER) {
        ($class == 'tclass')?$class = 'tclass2':$class = 'tclass';
        $i = 1;
        echo '<table class="'.$class.'">';
            foreach ($server as $ckey => $value) {
                echo '<tr><th width="20">'.$i.'</th><td width="250">$_SERVER[\''.$ckey.'\']</td><td>'.var_export($value, true).'</td></tr>' . "\n";
                $i++;
            }
        echo "</table>\n";
    }

    if (!empty($_GET) && $values = $_GET) {
        ($class == 'tclass')?$class = 'tclass2':$class = 'tclass';
        $i = 1;
        echo '<table class="'.$class.'">';
            foreach ($values as $ckey => $value) {
                echo '<tr><th width="20">'.$i.'</th><td width="250">$_GET[\''.$ckey.'\']</td><td>'.var_export($value, true).'</td></tr>' . "\n";
                $i++;
            }
        echo "</table>\n";
    }

    // 2008-08-05 anran,add echo $_POST
    if (!empty($_POST) && $values = $_POST) {
        ($class == 'tclass')?$class = 'tclass2':$class = 'tclass';
        $i = 1;
        echo '<table class="'.$class.'">';
            foreach ($values as $ckey => $value) {
                if (is_array($value)) $value = var_export($value, true);
                echo '<tr><th width="20">'.$i.'</th><td width="250">$_POST[\''.$ckey.'\']</td><td>'.htmlentities($value).'</td></tr>' . "\n";
                $i++;
            }
        echo "</table>\n";
    }

    // 2008-08-05 anran,add echo $_FILES
    if (!empty($_FILES) && $values = $_FILES) {
        ($class == 'tclass')?$class = 'tclass2':$class = 'tclass';
        $i = 1;
        echo '<table class="'.$class.'">';
            foreach ($values as $ckey => $value) {
                echo '<tr><th width="20">'.$i.'</th><td width="250">$_FILES[\''.$ckey.'\']</td><td>'.var_export($value, true).'</td></tr>' . "\n";
                $i++;
            }
        echo "</table>\n";
    }

    // 2010-01-19 anran,add echo $_SESSION
    // var_dump($_SESSION);
    if (!empty($_SESSION) && $values = $_SESSION) {
        ($class == 'tclass')?$class = 'tclass2':$class = 'tclass';
        $i = 1;
        echo '<table class="'.$class.'">';
            foreach ($values as $ckey => $value) {
                echo '<tr><th width="20">'.$i.'</th><td width="250">$_SESSION[\''.$ckey.'\']</td><td>'.var_export($value, true).'</td></tr>' . "\n";
                $i++;
            }
        echo "</table>\n";
    }

    // 2010-02-05 anr@alltosun.com
    if (AnPHP::$model && $values = AnPHP::$model) {
        ($class == 'tclass')?$class = 'tclass2':$class = 'tclass';
        $i = 1;
        echo '<table class="'.$class.'">';
        echo '<tr><th width="20">m</th><td width="250">db from config</td><td width="80">Model</td><td>parent_class:self_class</td></tr>' . "\n";
            foreach ($values as $db_op => $value) {
                foreach ($value as $res_name => $split_list) {
                    foreach ($split_list as $split_key => $m) {
                        echo '<tr><th width="20">'.$i.'</th><td>'.$db_op.'</td><td>'.$res_name.'</td><td>'.$m->getPClass() . ':' . $m->class_file.'</td></tr>' . "\n" . "\n";
                        $i++;
                    }
                }
            }
        echo "</table>\n";
    }

    // 2010-07-05 gaojj@alltosun.com Smarty debug info
    $smarty = Response::getView();
    if (@$smarty->debugging) {
        if ($smarty instanceof Smarty3) {
            // Smarty3
            $smarty_version = 3;
            // 赋值的变量信息
            $smarty_tpl_vars = $smarty->tpl_vars;
            // 加载的配置信息
            $smarty_config_vars = $smarty->config_vars;
            $smarty_debug_info = Smarty_Internal_Debug::$template_data;
        } else {
            $smarty_version = 2;
            // Smarty2
            $smarty_tpl_vars = $smarty->_tpl_vars;
            $smarty_config_vars = $smarty->_config[0]['vars'];
            $smarty_debug_info = $smarty->_smarty_debug_info;
        }
        //-------------------------------赋值的变量信息-------------------------------
        ($class == 'tclass') ? $class = 'tclass2' : $class = 'tclass';
        echo '<table class="'.$class.'">';
        echo '<tr><th width="20">v</th><td width="150">variable</td><td>value</td></tr>' . "\n";
        $i = 1;
        foreach ($smarty_tpl_vars as $k=>$v) {
            echo '<tr>
                    <th width="20">'.$i.'</th>
                    <td>$'.$k.'</td>
                    <td>'.var_export($v, true).'</td>
                  </tr>' . "\n";
            $i++;
        }
        echo "</table>\n";
        //-------------------------------赋值的变量信息-------------------------------

        //-------------------------------加载的配置信息-------------------------------
        ($class == 'tclass') ? $class = 'tclass2' : $class = 'tclass';
        echo '<table class="'.$class.'">';
        echo '<tr><th width="20">v</th><td width="150">config</td><td>value</td></tr>' . "\n";
        $i = 1;
        foreach ($smarty_config_vars as $k=>$v) {
            echo '<tr>
                    <th width="20">'.$i.'</th>
                    <td>$'.$k.'</td>
                    <td>'.var_export($v, true).'</td>
                    </tr>' . "\n";
            $i++;
        }
        echo "</table>\n";
        //-------------------------------加载的配置信息-------------------------------

        //-------------------------------包含的模板信息-------------------------------
        ($class == 'tclass') ? $class = 'tclass2' : $class = 'tclass';
        echo '<table class="'.$class.'">';
        if ($smarty_version == 3) {
            echo '<tr><th width="20">t</th><td width="350">filename</td><td>exec_time</td></tr>' . "\n";
            $i = 0;
            foreach ($smarty_debug_info as $k=>$v) {
                $i++;
                echo '<tr>
                        <th width="20">'.$i.'</th>
                        <td width="250">'.$v['name'].'</td>
                        <td>(compile '.sprintf('%.5f', $v['compile_time']).'
                            )(render '.sprintf('%.5f', $v['render_time']).'
                            )(cache '.sprintf('%.5f', $v['cache_time']).')</td>
                      </tr>' . "\n";
            }
        } elseif ($smarty_version == 2) {
            echo '<tr><th width="20">t</th><td width="250">filename</td><td width="80">depth</td><td>exec_time</td></tr>' . "\n";
            foreach ($smarty_debug_info as $k=>$v) {
                $v['exec_time'] = isset($v['exec_time']) ? $v['exec_time'] : '';
                echo '<tr>
                        <th width="20">'.$k.'</th>
                        <td width="250">'.$v['filename'].'</td>
                        <td>'.$v['depth'].'</td>
                        <td>'.$v['exec_time'].'</td>
                      </tr>' . "\n";
            }
        }
        echo "</table>\n";
        //-------------------------------包含的模板信息-------------------------------
    }


    // 2010-3-1 gaojj@alltosun.com xhprof性能分析结束，数据处理
    if (isset($_GET['xhprof']) && $_GET['xhprof'] === 1 && function_exists('xhprof_disable')) {
        $xhprof_data = xhprof_disable();

        // 修改namespace为当前域名
        $xhprof_ns = defined("PROJECT_NS") ? PROJECT_NS : 'other';
        $xhprof_ns = $_SERVER['HTTP_HOST'];

        // 分析数据输出到各自项目的子目录中
        // 在$xhprofui_url中查看数据时需根据ns定位到子目录
        $xhprof_output_dir = ini_get('xhprof.output_dir').$xhprof_ns;
        if (!is_dir($xhprof_output_dir)) {
            mkdir($xhprof_output_dir);
        }

        $xhprof_runs = new XHProfRuns_Default($xhprof_output_dir);
        $run_id = $xhprof_runs->save_run($xhprof_data, $xhprof_ns);

        $xhprofui_url = 'http://xhprof.alltosun.net';
        echo '<br /><a href="'.$xhprofui_url.'/index.php?run='.$run_id.'&source='.$xhprof_ns.'" target="_blank">点击此处查看XHProf的分析报告</a>';
    }
}}
?>