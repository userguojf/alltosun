<?php

/**
 * alltosun.com 框架初始化文件 init.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 高竞竞 (gaojj@alltosun.com) $
 * $Date: 2010-2-25 上午12:53:20 $
 * $Id: init.php 1096 2015-12-03 10:11:58Z liudh $
 * @link http://wiki.alltosun.com/index.php?title=Framework:init.php
 */

// xhprof性能分析启动
if (isset($_GET['xhprof']) && $_GET['xhprof'] == 1 && function_exists('xhprof_enable')) {
    /**
     * 启用xhprof性能分析
     * @param int 同时记录CPU时间、内存使用量；不分析PHP内置函数；
     * @param array 忽略的函数列表，数据格式为array('ignored_functions' =>  array('func1', 'func2'))；
     * @example xhprof_enable(); xhprof_enable(0);
     */
    xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY + XHPROF_FLAGS_NO_BUILTINS);
}

AnPHP::init();

/**
 * 初始化类
 * 及一些框架参数的存储
 * @http://wiki.alltosun.com/index.php?title=Framework:class:AnPHP
 */
class AnPHP
{
    public static $dir = ''; // 当前路径，即框架路径
    public static $dir_3rd = ''; // 第三方类库路径
    public static $timezone = 'Asia/Shanghai';
    public static $model = array(); // model 对象缓存
    public static $uri = array(); // uri 数据缓存
    public static $widgets = array(); // widgets 对象缓存
    public static $startTime = 0.0; // 开始时间，微秒
    public static $lastRunTime = 0.0; // 上次时间
    public static $autoload = array(); // 需要加载的第3方类名与定义文件对应关系 , AnPHP::$autoload['XHProfRuns_Default'] = '/xhprof-0.9.2/xhprof_lib/utils/xhprof_lib.php';
    public static $cache = 0; // 缓存设置，0为关闭，1为打开
    public static $version = 'trunk'; // 框架版本号
    public static $sysModel = array('resource', 'table', 'attribute', 'attribute_value', 'attribute_relation', 'resource_split_assign', 'resource_route');

    public static function init()
    {
        error_reporting(E_ALL | E_STRICT);
        ob_start();
        date_default_timezone_set(self::$timezone);

        self::$startTime = microtime(true);
        self::$dir = dirname(__FILE__);

        // 第三方类库路径
        if (!self::$dir_3rd) {
            if ('trunk' === basename(self::$dir)) {
                self::$dir_3rd = self::$dir . '/../3rd';
            } else {
                // tags & branches
                self::$dir_3rd = self::$dir . '/../../3rd';
            }
        }

        spl_autoload_register(array(__CLASS__, 'framework_autoload'));

        // fix zend framework include_path
        set_include_path(get_include_path() . PATH_SEPARATOR . self::$dir_3rd);

        if (get_magic_quotes_gpc()) {
            $_POST = self::stripslashesRecursive($_POST);
            $_GET = self::stripslashesRecursive($_GET);
            $_COOKIE = self::stripslashesRecursive($_COOKIE);
            $_REQUEST = self::stripslashesRecursive($_REQUEST);
        }
    }

    /**
     * 将自动添加的\处理掉
     * @param $array string直接处理后返回，array递规处理
     * @return 与输入类型相符
     */
    public static function stripslashesRecursive($array)
    {
        if (empty($array) || is_numeric($array)) {
            return $array;
        } elseif (is_array($array)) {
            return array_map(array(__CLASS__, 'stripslashesRecursive'), $array);
        } else {
            return stripslashes($array);
        }
    }

    /**
     * 类自动加载
     * @param string $class_name
     */
    public static function framework_autoload($class_name)
    {
        switch ($class_name) {
            case 'Smarty':
                require self::$dir_3rd . '/Smarty/libs/Smarty.class.php';
                break;
            case 'JavaScriptPacker':
                require self::$dir_3rd . '/JavaScriptPacker/class.JavaScriptPacker.php';
                break;
            case 'getid3':
                if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
                    require self::$dir_3rd . '/getID3-1.9/getid3.php';
                } else {
                    require self::$dir_3rd . '/getID3-1.8/getid3.php';
                }

                //require self::$dir_3rd.'/getid3/getid3.php';
                break;
            case 'MSN':
                require self::$dir_3rd . '/phpmsnclass_1.9/msn.class.php';
                break;
            case 'PHPMailer':
                require self::$dir_3rd . '/PHPMailer-5.1/class.phpmailer.php';
                /*
                if (version_compare(PHP_VERSION, '5.3.0') >= 0){
                    require self::$dir_3rd.'/PHPMailer-5.2.x/PHPMailerAutoload.php';
                }else{
                    require self::$dir_3rd.'/PHPMailer-5.1/class.phpmailer.php';
                }
                */
                // require self::$dir_3rd.'/phpMailer_v2.3/class.phpmailer.php';
                break;
            case 'HTMLPurifier_Config':
                require self::$dir_3rd . '/htmlpurifier/library/HTMLPurifier.safe-includes.php';
                require self::$dir_3rd . '/htmlpurifier/library/HTMLPurifier.func.php';
                break;
            case 'XHProfRuns_Default':
                require self::$dir_3rd . '/xhprof-0.9.2/xhprof_lib/utils/xhprof_lib.php';
                require self::$dir_3rd . '/xhprof-0.9.2/xhprof_lib/utils/xhprof_runs.php';
                break;
            case 'alipay_service':
                require self::$dir_3rd . '/Alipay/alipay_service.php';
                break;
            case 'alipay_notify':
                require self::$dir_3rd . '/Alipay/alipay_notify.php';
                break;
            case 'Securimage':
            case 'Securimage_Color':
//                 require self::$dir_3rd.'/Securimage_2_0_1_BETA/securimage.php';
                //require self::$dir_3rd.'/Securimage 2.0.1 BETA/securimage.php';
                require self::$dir_3rd . '/Securimage3/securimage.php';
                break;
            case 'IpLocation':
                require self::$dir_3rd . '/qqwry/qqwry.class.php';
                break;
            case 'PHPExcel':
                require self::$dir_3rd . '/PHPExcel/PHPExcel.php';
                require self::$dir_3rd . '/PHPExcel/PHPExcel/Writer/Excel5.php';
                break;
            case 'TCPDF':
                require self::$dir_3rd . '/tcpdf/tcpdf.php';
                break;
            default:
                if (self::$autoload && array_key_exists($class_name, self::$autoload)) {
                    // 加载自定义的类与文件对应关系
                    require self::$dir_3rd . self::$autoload[$class_name];
                } elseif (defined('MODULE_PATH') && ('_helper' === substr($class_name, -7) || '_config' === substr($class_name, -7))) {
                    // 加载 xxx_helper 或 xxx_config 自定义在 module 下的静态类
                    // 如：company_helper::get_company_domain() 对应文件：company/helper/company_helper.php
                    // 如：company_config::$personal_email      对应文件：company/config/company_config.php
                    $module_name = substr($class_name, 0, -7);
                    // helper或config
                    $class_type = substr($class_name, -6);
//                     require MODULE_PATH.'/'.$module_name.'/'.$class_type.'/'.$class_name.'.php';

                    if (self::RequireExistsFile(ROOT_PATH . '/module/' . $module_name . '/' . $class_type . '/' . $class_name . '.php')) {
                        // 加载项目下 xxx_helper 或 xxx_config 类，如：company_helper::get_company_domain() 对应文件：company/helper/company_helper.php
                        return TRUE;
                    }
                    if ($p = strrpos($module_name, '_')) {
                        // 加载 company_depart_helper::get_company_domain() 对应文件：company/helper/company_depart_helper.php 2017-08-21 add
                        if (self::RequireExistsFile(ROOT_PATH . '/module/' . substr($module_name, 0, $p) . '/' . $class_type . '/' . $class_name . '.php')) {
                            return TRUE;
                        }
                    }
                } elseif (strncasecmp('An', $class_name, 2) === 0) {
                    // 加载以An开头的框架类。加载的文件名为除去An后的部分。如：AnForm对应文件名：Form.php
                    $file_name = substr($class_name, 2);
                    if ('AnException' === $class_name) {
                        // 兼容老的类名
                        require self::$dir . '/' . $file_name . '.php';
                    } else
                        if (defined('MODULE_CORE') && file_exists(MODULE_CORE . '/helper/' . $file_name . '.php')) {
                            // 优先加载 MODULE_CORE 下自定义类
                            require MODULE_CORE . '/helper/' . $file_name . '.php';
                        } elseif (file_exists(self::$dir . '/' . $file_name . '.php')) {
                            // 再加载框架中的类
                            require self::$dir . '/' . $file_name . '.php';
                        }
                } elseif (file_exists(self::$dir . '/' . $class_name . '.php')) {
                    // AnPHP Class
                    require self::$dir . '/' . $class_name . '.php';
                } elseif (file_exists(self::$dir . '/Models/' . $class_name . '.php')) {
                    // AnPHP Models
                    require self::$dir . '/Models/' . $class_name . '.php';
                }
                break;
        }
    }

    /**
     * 添加自动加载类
     * @param string $class_name
     * @param string $file_path 从根目录开始
     * @example AnPHP::autoload('XHProfRuns_Default', '/xhprof-0.9.2/xhprof_lib/utils/xhprof_lib.php');
     */
    public static function autoload($class_name, $file_path)
    {
        self::$autoload[$class_name] = $file_path;
    }

    /**
     * 添加自动加载类
     * @param string $file_path
     * @example AnPHP::load('/xhprof-0.9.2/xhprof_lib/utils/xhprof_lib.php');
     */
    public static function load($file_path)
    {
        require self::$dir_3rd . $file_path;
    }

    /**
     * 检查是否为AnPHP自带的系统model
     * @param string $res_name
     */
    public static function isSysModel($res_name)
    {
        return in_array($res_name, self::$sysModel);
    }

    /**
     * 如果文件存在，加载
     * @param string $f
     * @param bool $require_once 是否使用 require_once ，默认不使用
     * @return boolean
     */
    public static function RequireExistsFile($f, $require_once = FALSE)
    {
        if (file_exists($f)) {
            if ($require_once) {
                require_once $f;
            } else {
                require $f;
            }
            return TRUE;
        }
        return FALSE;
    }

    /**
     * 框架执行时间，微秒
     * @return float 执行时间
     */
    public static function runTime()
    {
        return number_format(microtime(true) - self::$startTime, 6);
    }

    /**
     * 上次执行的时间
     * @return float 执行时间
     */
    public static function lastRunTime()
    {
        if (empty(self::$lastRunTime)) {
            self::$lastRunTime = microtime(true);
            return number_format(self::$lastRunTime - self::$startTime, 6);
        } else {
            $t = number_format(microtime(true) - self::$lastRunTime, 6);
            self::$lastRunTime = microtime(true);
            return $t;
        }
    }
}

/**
 * 生成
 */
function gen_pass($string)
{
    isset($_SERVER['HTTP_USER_AGENT']) || $_SERVER['HTTP_USER_AGENT'] = __FUNCTION__;
    $key = strtr($_SERVER['HTTP_USER_AGENT'] . __FILE__, ' ', '');
    $key_len = strlen($key);
    $str_len = strlen($string);

    $code = '';
    for ($i = 0; $i < $str_len; ++$i) {
        $k = $i % $key_len;
        $code .= $string[$i] ^ $key[$k];
    }

    return $code;
}

/**
 * 加载widget对象
 * @param $name widget名称
 * @return obj
 * @author gaojj@alltosun.com
 */
function _widget($name)
{
    if (!$name) {
        throw new AnException('Widget Error.', '_widget() name is empty.');
    }

    if (isset(AnPHP::$widgets[$name])) {
        return AnPHP::$widgets[$name];
    }

    if (!DB::legalName($name)) {
        throw new AnException('Widget Error.', "Widget {$name} is invaild.");
    }

    $widget_file = ROOT_PATH . '/widget/' . $name . '.php';
    if ($widget_file === realpath($widget_file) && file_exists($widget_file)) {
        // 加载widget类
        require $widget_file;
        $class = $name . '_widget';
    } elseif (defined('MODULE_PATH')) {
        // 加载module中的widget类
        // 支持module.widget形式的name
        if (strpos($name, '.') !== false) {
            list($module_name, $widget_name) = explode('.', $name);
        } else {
            $module_name = $widget_name = $name;
        }

        $widget_file = MODULE_PATH . '/' . $module_name . '/widget/' . $widget_name . '.php';
        if (DIRECTORY_SEPARATOR == "\\") {
            $widget_file = strtr($widget_file, '/', DIRECTORY_SEPARATOR);
        }
        if ($widget_file !== realpath($widget_file) || !file_exists($widget_file)) {
            throw new AnException('Widget Error.', "Widget {$widget_name} file in module {$module_name} does not exist.\nwidget file is:{$widget_file}");
        }
        require $widget_file;
        $class = $widget_name . '_widget';
    } else {
        throw new AnException('Widget Error.', "Widget {$name} file does not exist.\nwidget file is:{$widget_file}");
    }

    return AnPHP::$widgets[$name] = new $class();
}

/**
 * 加载对应的类生成对象
 * @param string $res_name 类名：可是res_name、model名(框架内置、自定义)、表名（动态类）
 * @param string $db_op 或配置中的数据库参数的key名，默认为db
 * @return ModelRes|Model 相应的对象
 * @example _model('service'); // res_name，由 ModelRes类衍生而来，对应实体类为article
 * @example _model('comment'); // 类名，由 model类衍生而来
 * @example _model('comment')->getTotal();
 * @link http://wiki.alltosun.com/index.php?title=Framework:_model%28%29
 */
function _model($res_name, $db_op = 'db', $split_key = 0)
{
    if ($db_op == 'db') {
        $db_op_conf = Config::get('module_db_conf');

        if (!empty($db_op_conf[$res_name])) {
            $db_op = $db_op_conf[$res_name];
        }
    }

    global $mc_wr;
    static $ModelResObserver = array();
    static $ModelCacheRoot = NULL;
    if (NULL === $ModelCacheRoot) {
        if (defined('CACHE') && CACHE && is_object($mc_wr) && !Config::get('objectCacheClose')) {
            $ModelCacheRoot = TRUE;
        } else {
            $ModelCacheRoot = FALSE;
        }
    }

    // 接收分库分表预配置，通过 Model::preSplitKey($res_name, $db_op) 设定的值
    // 优先级： Model::init_after()设定 > Model::init()设定 > resource表定义 > _model()传值 > Model::preSplitKey()设定 > 类文件定义
    if (!$split_key && isset(Model::$preSplitKey[$res_name])) {
        $split_key = Model::$preSplitKey[$res_name];
    }

    // 内存中有直接返回
    if (isset(AnPHP::$model[$db_op][$res_name][$split_key])) {
        return AnPHP::$model[$db_op][$res_name][$split_key];
    }

    // 合法的model名验证与类名同 added by ninghx 2011.11.25
    // preg_match("/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]{0,}$/", $res_name)
    if (!$res_name) {
        throw new AnException('Model Error.', 'model name is empty!');
    } elseif (!DB::legalName($res_name)) {
        throw new AnException('Model Error.', "model name '{$res_name}' is invalid!");
    }

    //加载全局默认的观察者
    if (empty($ModelResObserver) && Config::get('ModelResObserver')) {
        ModelResObserver::loadConfig('ModelResObserver');
    }

    // 加载指定res的观察者
    if (!isset($ModelResObserver[$res_name]) && Config::get('ModelResObserver_' . $res_name)) {
        $ModelResObserver[$res_name] = 1;
        ModelResObserver::loadConfig('ModelResObserver_' . $res_name);
    }

    // 对象缓存，缓存中有直接返回
    if ($ModelCacheRoot) {
        $objectKey = $res_name . '-' . $db_op . '-' . $split_key;
        $tem = $mc_wr->NS('')->get($objectKey);
        if ($tem) {
            // array($class_name, $class_file, @serialize($tem_class))
            if ($tem[1] && !class_exists($tem[0], false)) {
                if (file_exists($tem[1])) {
                    require $tem[1];
                } else {
                    throw new AnException('Model Error.', '类定义文件不存在：' . $tem[1]);
                }
            }
            $model = unserialize($tem[2]);
            $model->mc_wr = $mc_wr;
            $model->setDB($db_op);
            return AnPHP::$model[$db_op][$res_name][$split_key] = $model;
        }
    }

    $resource_info = array();
    $model_name = ''; // res_name 对应的是哪个 model
    $class_name = ''; // 类名：由 $model_name 及 类定义文件是否存在来判断
    $class_file = ''; // 类的定义文件
    $class_type = ''; // sys,resource,dynamics,static
    $table_name = ''; // 表名：resourece中注册动静态类表中有、未注册动态类取自身、未注册静态类为空
    $pk = '';
    $res_type = NULL;
    $class_file_1 = '';
    $class_file_2 = '';

    if (AnPHP::isSysModel($res_name)) {
        // 从框架中加载的类，类名是  model_ 为前缀
        $model_name = $res_name;
        $table_name = $res_name; // table
        $class_name = 'model_' . $res_name;
        $class_type = 'sys';
    } else {
        // 取 resource 册表中的配置
        // 从resource表找到相关信息 array('id'=>'res_type', 'name'=>'res_name', 'model'=>'model_name', ' 'route'=>'0', ' 'split'=>'', 'table'=>'table_name', 'db_op'=>'数据库配置key')
        $resource_info = _uri('resource', $res_name);

        if ($resource_info) {
            // model路由和分库分表处理 2012-01-05
            if ($split_key && ($resource_info['route'] || $resource_info['split'])) {
                $split_info = AnSplit::model($res_name, $split_key); // array('model'=>'', 'db_op'=>'', 'table'=>'')
                !empty($split_info['model']) && $model_name = $split_info['model'];
                !empty($split_info['db_op']) && $db_op = $split_info['db_op'];
                !empty($split_info['table']) && $table_name = $split_info['table'];
            }

            // 已注册的类，补充model相关参数
            !$model_name && $model_name = $resource_info['model'];
            if (empty($split_info['db_op']) && !empty($resource_info['db_op'])) $db_op = $resource_info['db_op'];
            !$table_name && $table_name = $resource_info['table'];
            $res_type = $resource_info['id'];

            if ('ModelRes' === $model_name || 'Model' === $model_name || AnPHP::isSysModel($model_name) || strncmp($model_name, 'model_', 6) === 0) {
                // 已注册，动态类，model为Model/ModelRes类或框架中的类（类名以model_为前缀）
                $class_name = $model_name;
                $class_type = 'resource';
            }
        } else {
            // 未注册的类
            $model_name = $res_name;
        }

        if (!$class_name) {
            if (class_exists($model_name . '_model', false)) {
                // 如果类定义文件存在就不用再加载
                $class_name = $model_name . '_model';
                $class_type = 'static';
            } else {
                // 静态类
                $class_file_1 = ROOT_PATH . '/model/' . $model_name . '.php';
                // if (defined('MODULE_PATH')) $class_file_2 = MODULE_PATH . '/' .$model_name . '/model/' . $model_name . '.php';
                // 2013-02-21 添加支持加载module里的model，_model('friend.friend'), _model('friend.someone')
                if (defined('MODULE_PATH')) {
                    if (false !== strpos($model_name, '.')) {
                        list($module_name, $m) = explode('.', $model_name);
                        $class_file_2 = MODULE_PATH . '/' . $module_name . '/model/' . $m . '.php';
                    } else {
                        $class_file_2 = MODULE_PATH . '/' . $model_name . '/model/' . $model_name . '.php';
                    }
                }

                if ((file_exists($class_file_1) && $class_file = $class_file_1) || ($class_file_2 && file_exists($class_file_2) && $class_file = $class_file_2)) {
                    $class_name = $model_name . '_model';
                    $class_type = 'static';
                    require $class_file;
                }
            }
        }

        if (!$class_name) {
            // 动态类
            $table_name = $res_name; // table
            $class_type = 'dynamics';
            // 从配置中取 pk // Config::Set('pk', array('table_name' => 'pk_name'))
            // ($conf_pk = Config::get('pk')) && !empty($conf_pk[$table_name]) && $pk = $conf_pk[$table_name];
            if (!$pk) $pk = _model('table', $db_op)->pk($table_name);
            if ($pk) {
                $class_name = $resource_info['model'] = 'ModelRes';
            } else {
                $class_name = $resource_info['model'] = 'Model';
            }
        }
    }

    // 生成对象
    AnPHP::$model[$db_op][$res_name][$split_key] = $tem_class = new $class_name($db_op, $mc_wr, $table_name, $res_type);
    $tem_class->class_name = $class_name; // class_name名
    $tem_class->class_file = $class_file; // 类定义文件
    $tem_class->class_type = $class_type; // class_type名
    $tem_class->res_name = $res_name; // res_name 在 _model() 中调用的参数。
    $tem_class->model_name = $model_name; // 由哪个model类来执行操作
    if ($pk) $tem_class->pk = $pk; // 赋值pk

    // 对象缓存
    // 2015-01-04 只有动态类进行缓存，因为静态类还要设置其它参数
    if ('dynamics' === $class_type && $ModelCacheRoot && !empty($objectKey)) {
        $model = clone $tem_class;
        $model->initialization();
        $model->tb;
        $model->db_master = null;
        unset($model->mc_wr, $model->db, $model->db_slave, $model->table_org);
        $mc_wr->NS('')->set($objectKey, array($class_name, $class_file, @serialize($model)), 3600);
    }

    return $tem_class;
}

/**
 * 返回 uri 统一资源对应的数据，如果filed不为空，返回对应字段内容
 * @example $arr = _uri(1, 102323); // 返回 res_type=1,res_id=102323对应的数组
 * @example $str = _uri(1, 102323, 'name'); // 返回 res_type=1,res_id=102323对应的数组中name字段的值
 * @param $res_type 统一资源号(或res_name)，为空返回array()
 * @param $res_id res_id号，默认null返回对象，类型可以是数字、数组、字符串。作为一个参数传给model中read()
 * @param $filed 对应的字段
 * @return array
 * @author anr@alltosun.com
 * @link http://wiki.alltosun.com/index.php?title=Framework:_uri%28%29
 */
function _uri($res_type, $res_id = null, $field = null)
{
    if (!$res_type || !$res_id) {
        // $res_id为null时，_uri($res_type)的作用与_model($res_type)相同
        if (null === $res_id) return _model($res_type);
        elseif (!$field) return array();
        else return '';
    }

    if (is_array($res_id)) {
        $mc_key = serialize($res_id);
    } else {
        $mc_key = $res_id;
    }

    if (!isset(AnPHP::$uri[$res_type][$mc_key])) {
        AnPHP::$uri[$res_type][$mc_key] = _model($res_type)->read($res_id);
    }
    $info = AnPHP::$uri[$res_type][$mc_key];

    if (!$field) return $info;
    if (isset($info[$field])) return $info[$field];
    return '';
}

/**
 * 清空_uri的缓存
 * @author gaojj@alltosun.com
 * @deprecated since 2012-01-12
 */
function _uri_cache_clear()
{
    AnPHP::$uri = array();
}

/**
 * 分库分表
 * @author anr
 *
 */
class AnSplit
{
    public static function model($res_name, $split_key)
    {
        $resource_info = _uri('resource', $res_name);

        if (!$resource_info) {
            throw new AnException('Model Error.', '没有resource数据。');
        }

        if (!$split_key || !is_numeric($split_key)) {
            throw new AnException('Model Error.', '没有split_key数据。');
        }

        $meta = array();
        // 处理 model route
        if ($resource_info['route']) {
            $resource_route_info = _uri('resource_route', array('split_key' => $split_key, 'resource_id' => $resource_info['id']));
            if ($resource_route_info) {
                $meta['model'] = $resource_route_info['model'];
            }
        }

        // 处理分库分表
        if ('assign' === $resource_info['split']) {
            // 分配库表方式
            $resource_split_info = _uri('resource_split_assign', array('split_key' => $split_key, 'resource_id' => $resource_info['id']));
            if ($resource_split_info) {
                $meta['db_op'] = $resource_split_info['db_op'];
                $meta['table'] = $resource_split_info['table'];
            } else {
                if (isset($resource_info['db_op_list'])) {
                    $meta['db_op'] = $resource_info['db_op_list'][array_rand($resource_info['db_op_list'])];
                } else {
                    $meta['db_op'] = $resource_info['db_op'];
                }
                if (isset($resource_info['table_list'])) {
                    $meta['table'] = $resource_info['table_list'][array_rand($resource_info['table_list'])];
                } else {
                    $meta['table'] = $resource_info['table'];
                }
                // 写入
                _model('resource_split_assign')->create(array('split_key' => $split_key, 'resource_id' => $resource_info['id'], 'db_op' => $meta['db_op'], 'table' => $meta['table']));
            }
        } elseif ($resource_info['split']) {
            if (isset($resource_info['db_op_list'])) {
                $meta['db_op'] = $resource_info['db_op_list'][$split_key % count($resource_info['db_op_list'])];
            }
            if (isset($resource_info['table_list'])) {
                $meta['table'] = $resource_info['table_list'][$split_key % count($resource_info['table_list'])];
            }
        }

        return $meta;
    }
}


/**
 * 得到res_type对应的所有信息 类名，表名(后补res_type)
 * 使用内存缓存，memcache共2级缓存
 * @param $res_type 可以为res_type或res_name
 * @param $field 为字段名
 * @return array('id'=>'6', 'name'=>'product', 'model'=>'product', 'table'=>'product')
 * @author anr@alltosun.com
 */
function get_resource($res_type, $field = null)
{
    if ($res_type) return false;
    else return _uri('resource', $res_type, $field);
}

/**
 * 从resource表中获取资源号
 * @param $res_name 资源名称
 * @return int
 * @author gaojj@alltosun.com
 */
function get_res_type($res_name)
{
    if (is_numeric($res_name)) return $res_name;
    return get_resource($res_name, 'id');
}

/**
 * 清除对应的 res_type 的命名空间
 * @param $res_type
 */
function mc_delete_ns($res_type)
{
    _model($res_type)->mc_delete_ns();
}

/**
 * 判断IP是否与指定IP相等或在IP段内
 * @param string $ip
 * @param mixed $ip_range 指定IP：192.168.1.1；指定IP段：192.168.1.*；指定IP段：192.168.*.*；指定一系列IP：array(192.168.*.*, 192.168.*.1, 192.168.1.1)
 * @return bool  如果相等或在IP段内返回true
 * @author anr@alltosun.com
 */
function ip_in($ip, $ip_range)
{
    if (!$ip || !$ip_range) return false;
    $ip_range = (array)$ip_range;
    if (in_array($ip, $ip_range)) return true;
    foreach ($ip_range as $v) {
        if (strpos($v, '.*')) {
            $ip_ban_pass_end = str_replace('.*', '.255', $v);
            $v = str_replace('.*', '.0', $v);
            if (sprintf("%u", ip2long($ip_ban_pass_end)) >= sprintf("%u", ip2long($ip)) AND sprintf("%u", ip2long($ip)) >= sprintf("%u", ip2long($v))) {
                return true;
            }
        }
    }
    return false;
}

/**
 * 加载对应的开放平台然后生成对象
 * @param string $vendor 第三方的运营商
 * @param string $type 接口类型 手机 页面
 * @return string|multitype:
 */
function AnOpenApi($vendor, $type = '')
{
    $openapi_config = Config::get('openapi');
    if (!$openapi_config[$vendor]) {
        return '缺少' . $vendor . '的配置文件';
    }

    $vendor_config = $openapi_config[$vendor];

    $is_multi_config = false;
    foreach ($vendor_config as $k => $v) {
        if (is_array($v)) {
            $is_multi_config = true;
            break;
        }
    }

    if ($is_multi_config && $type) {
        if (!$openapi_config[$vendor][$type]) {
            return '缺少' . $type . '的配置文件';
        } else {
            $vendor_config = $openapi_config[$vendor][$type];
        }
    }

    return AnOpenApi::connect($vendor, $vendor_config);
}

?>