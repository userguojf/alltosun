<?php

/**
 * alltosun.com ModelResObserver，ModelRes的观察者 ModelResObserver.php
 * ============================================================================
 * 版权所有 (C) 2007-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 安然 (anr@alltosun.com) $
 * $Date: 2011-12-05 14:33:57 +0800 $
 * $Id: ModelResObserver.php 432 2012-08-10 11:14:51Z anr $
*/

/*
 * 触发的时机：在ModelRes中针对单条记录执行 create(createBEF)/update(updateBEF)/read/delete(deleteBEF) 动作时自动触发
 * 传递给事件的参数notify()：array($this->name, $id, &$info)
 * 事件传递给函数的参数：array('model_name', id, &$info(更新或读取的数据)) // 与上对应
 * 被事件触发的函数可以通过 $param[2] 来获取数据并对数进行修改，因为此参数是引用传递，所以修改后的结果会一起返回。如果需要得到事件名称可以通过 ModelResObserver::$event 来获取
 *
 * 如果ModelRes执行getList()时亦会解发，针对于结果集中每个记录都会触发
 * 同理delete()删除多条记录时，针对每个删除记录都会触发
 * ModelRes 注册方法：
 * 1、普通函数与类：ModelResObserver::attach('model_name', '[create|update|read|delete]', '[fun_name|array(ob_name, fun_name)]|array(model_name, fun_name)]|array(widget_name, fun_name)]');
 * 2、Model类   ：ModelResObserver::attachModel('model_name', '[create|update|read|delete]', 'array(model_name, fun_name)]');
 * 3、Widget类  ：ModelResObserver::attach('model_name', '[create|update|read|delete]', 'array(widget_name, fun_name)]');



 *
 * @example
 *

// 定义要触发的函数
function an_ModelResObserver($param)
{
    $param[2]['title'] = array();
}

// ModelResObserver::attachModel('ad', 'create', array('ad', 'log_by_observer')); // 调用 _model('ad')->log_by_observer()
// ModelResObserver::attachWidget('ad', 'update', array('ad', 'log_by_observer')); // 调用 _widget('ad')->log_by_observer()
// ModelResObserver::attach('ad', 'create', array('user_read', 'read_by_observer')); // 调用 user_read::read_by_observer()

// 在ad model 的read事件中注册函数an_ModelResObserver
ModelResObserver::attach('ad', 'read', 'an_ModelResObserver');

// 执行modelres
$info_ad = _model('ad')->read(100);
var_dump($info_ad['title']); // array()

 */

/**
 * ModelRes观察者
 * @author anr@alltosun.com
 * @package AnModel
 */
class ModelResObserver
{
    // 注册的事件与动作
    public  static $Observers = array();
    private static $Observers_lock = array();
    // 当前发生的事件名，应用程序可以通过 ModelResObserver::$event 来得到当前发生的事件
    public static $event = '';
    private static $ObserversEvent = array('create', 'createBEF', 'update', 'updateBEF', 'read', 'delete', 'deleteBEF');

    /**
     * 从Config中加载
     * @param string $name
     * Config::set('ModelResObserver', array(array('attachModel', 'ad', 'create', array('ad', 'log_by_observer')), array(继续绑定配置)))
     */
    static function loadConfig($name = 'ModelResObserver')
    {
        $list = Config::get($name);
        if ($list && is_array($list)) {
            foreach ($list as $v) {
                $attach = array_shift($v);
                if     ('attachModel'  === $attach) call_user_func_array(array('ModelResObserver', 'attachModel'), $v);
                elseif ('attachWidget' === $attach) call_user_func_array(array('ModelResObserver', 'attachWidget'), $v);
                else call_user_func_array(array('ModelResObserver', 'attach'), $v);
            }
        }
    }

    /**
     * 注册普通函数至指定 model 的指定事件中
     * @param string $name model名，
     * @param string $event 事件名：create(createBEF)/update(updateBEF)/read/delete(deleteBEF)
     * @param string/array $fun 可以被 call_user_func_array() 调用的格式
     */
    static function attach($model_name, $event, $fun)
    {
        if (!in_array($event, self::$ObserversEvent)) {
            throw new AnException('Model Error.', 'no event to attache');
        }
        self::$Observers[$model_name][$event][] = array(0, $fun);
        return true;
    }

    /**
     * 注册 Model 至指定 model 的指定事件中
     * @param string $name model名，
     * @param string $event 事件名：create(createBEF)/update(updateBEF)/read/delete(deleteBEF)
     * @param array $fun array('model_name', 'fun')
     */
    static function attachModel($name, $event, $fun)
    {
        self::$Observers[$name][$event][] = array(1, $fun);
        return true;
    }

    /**
     * 注册 widget 至指定 model 的指定事件中
     * @param string $name model名，
     * @param string $event 事件名：create(createBEF)/update(updateBEF)/read/delete(deleteBEF)
     * @param array $fun array('widget_name', 'fun')
     */
    static function attachWidget($name, $event, $fun)
    {
        self::$Observers[$name][$event][] = array(2, $fun);
        return true;
    }

    /**
     * 注销事件
     */
    static function detach($name, $event = '', $fun = '')
    {
        if (!$event && isset(self::$Observers[$name])) {
            unset(self::$Observers[$name]);
        } elseif (!$fun && isset(self::$Observers[$name][$event])) {
            unset(self::$Observers[$name][$event]);
        } elseif (isset(self::$Observers[$name][$event])) {
            foreach (self::$Observers[$name][$event] as $k => $v) {
                if (!$v) unset(self::$Observers[$name][$event][$k]);
                elseif ($fun === $v[1]) unset(self::$Observers[$name][$event][$k]);
            }
        }
        if (isset(self::$Observers[$name][$event]) && !self::$Observers[$name][$event]) unset(self::$Observers[$name][$event]);
        return true;
    }

    /**
     * 是否注册
     */
    static function isAttach($name, $event)
    {
        return isset(self::$Observers[$name][$event]);
    }

    /**
     * 执行
     * 一般放在ModelRes中执行，参数参考 attach
     */
    static function notify($name, $event, $param)
    {
        $test = Request::getParam('test', 0);
        if (isset(self::$Observers[$name][$event]) && !isset(self::$Observers_lock[$name][$event])) {
            self::$event = $event;
            self::$Observers_lock[$name][$event] = 1;
            foreach (self::$Observers[$name][$event] as $v) {
                if (!$v) continue;
                elseif (1 === $v[0]) _model($v[1][0])->$v[1][1]($param);
                elseif (2 === $v[0]) _widget($v[1][0])->$v[1][1]($param);
                // else call_user_func_array($v[1], $param_arr);
                else {
                    if (is_string($v[1])) $v[1]($param);
                    else call_user_func_array($v[1], array($param));
                }
            }
            // self::$Observers_lock[$name][$event] = 0;
            unset(self::$Observers_lock[$name][$event]);
        }
        return null;
    }
}
?>