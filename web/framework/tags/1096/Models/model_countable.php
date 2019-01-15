<?php

/**
 * alltosun.com 单资源计数器模型 model_countable.php
 * ============================================================================
 * 版权所有 (C) 2007-2011 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 安然 (anr@alltosun.com), 高竞竞 (gaojj@alltosun.com) $
 * $Date: 2011-10-23 下午09:41:08 $
 * $Id: model_countable.php 225 2012-04-11 17:09:58Z gaojj $
*/

/**
 * 单资源计数器模型
 * @author anr@alltosun.com gaojj@alltosun.com
 * @package AnModel
 * @tutorial 本模型只适合已指定资源类型的资源计数，即模型表中只有唯一的主键res_id
 */
class model_countable extends Model
{
    /**
     * 表名
     * @tutorial 总表的名称不能以_hour, _day, _week, _month结尾
     * @var string
     */
    public $table = 'count';

    /**
     * 是否启用精确到小时的计数模型（默认不启用）
     * @tutorial time字段需要int(10)
     * @var bool
     */
    protected $count_hour = FALSE;

    /**
     * 是否启用精确到天的计数模型（默认启用）
     * @var bool
     */
    protected $count_day = TRUE;

    /**
     * 是否启用精确到周的计数模型（默认启用）
     * @var bool
     */
    protected $count_week = TRUE;

    /**
     * 是否启用精确到月的计数模型（默认启用）
     * @var bool
     */
    protected $count_month = TRUE;

    /**
     * setter
     * @param string $property
     * @param mixed $value
     */
    public function __set($property, $value)
    {
        $this->$property = $value;
    }

    /**
     * 返回资源的计数
     * @param mixed $filter array('res_id'=>70); 或 70
     * @return array 1维数组
     * @tutorial 如取当前时间的计数，$filter不要包含时间条件
     * @example _uri('count', '70'); // 取总数
     * @example _uri('count_hour', '70'); // 取本小时的计数（如果启用精确到小时的话）
     * @example _uri('count_day', '70'); // 取本天的计数
     * @example _uri('count_week', '70'); // 取本周的计数
     * @example _uri('count_month', '70'); // 取本月的计数
     * @example _uri('count_day', '70--1'); // 取前1天的计数
     * @example _uri('count_day', '70-2010200'); // 取2010年第200天的计数
     * @example _uri('count_day', array('res_id'=>70, 'time'=>-1)); // 取前1天的计数
     * @example _uri('count_day', array('res_id'=>70, 'time'=>2010200)); // 取2010年第200天的计数
     * @example _model('count')->read(array('res_id'=>70)); // 注意数组的顺序（res_name必须在前，否则increment清理不了缓存）
     */
    public function read($filter)
    {
        $time = '';

        // 解析参数
        if (is_array($filter)) {
            // 支持字符串主键
            $res_id = $filter['res_id'];
            $time = isset($filter['time']) ? $filter['time'] : '';
        } elseif (is_numeric($filter)) {
            $res_id = 0 + $filter;
        } else {
            list($res_id, $time) = explode('-', $filter, 2);
        }

        // 取缓存数据
        if (!empty($this->cache) && is_object($this->mc_wr)) {
            $mc_key = "{$this->table}-{$res_id}";
            $info = $this->mc_table_ns->get($mc_key);
            if (FALSE !== $info) {
                // 命中mc
                return $info;
            }
        }

        // 查询数据库
        $new_filter = array(
            'res_id' => $res_id
        );
        if ($time) {
            $format_time = $this->formatTime($time);
            if ($format_time) {
                $new_filter['time'] = $format_time;
            }
        }

        $info = $this->__call('read', array($new_filter));

        // 写入缓存
        if (!empty($this->cache) && is_object($this->mc_wr)) {
            $this->mc_table_ns->set($mc_key, $info);
        }

        return $info;
    }

    /**
     * 当前浏览计数++
     * 不用输入时间
     * @param int $res_id 需要计数++的资源id
     * @param int $value 需要增加的计数值（默认为1）
     * @return int 更新的记录数
     * @example _model('count')->increment(70, 5);
     */
    public function increment($res_id, $value = 1)
    {
        $table = $this->table;
        $value  = 0 + $value;

        if ($value == 0) return 0;

        if ($this->isTotalModel()) {
            // 同步更新
            if ($this->count_hour) _model("{$table}_hour")->increment($res_id, $value);
            if ($this->count_day) _model("{$table}_day")->increment($res_id, $value);
            if ($this->count_week) _model("{$table}_week")->increment($res_id, $value);
            if ($this->count_month) _model("{$table}_month")->increment($res_id, $value);
        }

        if (!empty($this->cache) && is_object($this->mc_wr)) {
            // 删除对应单条记录的缓存
            $this->mc_table_ns->delete("{$table}-{$res_id}");
        }

        if ($value < 0) {
            // 防溢出
            $info = $this->__call('read', array('res_id'=>$res_id));
            if ($info['count'] + $value < 0) $value = 0 - $info['count'];
        }

        $info = array('res_id'=>$res_id);
        $time = $this->formatTime();
        if ($time) $info['time'] = $time;

        return $this->__call('create', array($info, "UPDATE `count`=`count`+{$value}"));
    }

    /**
     * 获取指定1小时/天/周/月内的前n位排行
     * @param int $num 获取前多少位排行
     * @param int $t 获取指定时间
     * @return array 2维数组
     * @example _model('count')->getTop(10) // 取总排行Top10
     * @example _model('count_day')->getTop(10) // 取今日排行Top10
     * @example _model('count_week')->getTop(10) // 取本周排行Top10
     * @example _model('count_month')->getTop(10) // 取本月排行Top10
     * @example _model('count_day')->getTop(10, -1) // 取昨日排行Top10
     * @example _model('count_week')->getTop(10, -1) // 取上周排行Top10
     * @example _model('count_month')->getTop(10, -1) // 取上个月排行Top10
     * @todo 取今日/周/月排行时，会存在当天/周/月第一天0点时无数据的情况，考虑下连续2个计数周期内的排行如何实现
     */
    public function getTop($num, $t = 0)
    {
        $num = 0 + $num;
        $time = $this->formatTime($t);
        if ($time) {
            $filter = array('time'=>$time);
            return $this->__call('getList', array($filter, 'ORDER BY `count` DESC LIMIT '.$num));
        }

        return $this->__call('getList', array('ORDER BY `count` DESC LIMIT '.$num));
    }

    /**
     * 处理计数器的时间
     * 根据当前计数器类型，返回对应一年中第几天，第几周，第几月数。
     * @param int $t，为空取当前时间对就一年中第几天、周、月数
     * @return string 2010365
     * @example formatTime();  // 返回当前天、周、月
     * @example formatTime(2010365); // 直接返回
     * @example formatTime(2010365); // 直接返回
     * @example _model('count')->formatTime(); // 直接返回空，因为总数没有时间
     * @example _model('count_week')->formatTime();  // 返回当前时间对应一年的第几周，类似于：201001,201050
     * @example _model('count_month')->formatTime(); // 返回当前时间对应一年的第几月，类似于：201001,201004
     * @example _model('count_day')->formatTime(-1); // 返回当前时间前一天对应一年的第几天，类似于：2010000,2010365
     * @example _model('count_week')->formatTime(-1) // 返回当前时间前一周对应一年的第几周，类似于：201001,201050
     * @todo 对HourModel的支持
     */
    protected function formatTime($t = 0)
    {
        if ($this->isTotalModel()) return ''; // 总数没有时间
        if ($t > 0 && $t < 210000000) return $t; // 201036524
        if     ($t > 210000000) $tt = $t;
        elseif ($t == 0) $tt = $_SERVER['REQUEST_TIME']; // 为空，取当前时间
        elseif ($t < 0 ) {
            // 取之前的时间
            if ($this->isHourModel()) $tt = $_SERVER['REQUEST_TIME'] + $t * 3600; // -1为前1小时，-2为前2小时……
            elseif ($this->isDayModel()) $tt = $_SERVER['REQUEST_TIME'] + $t * 86400; // -1为前1天，-2为前2天……
            elseif ($this->isWeekModel()) $tt = $_SERVER['REQUEST_TIME'] + $t * 7 * 86400; // -1为前1周，-2为前2周……
            elseif ($this->isMonthModel()) {
                // 先取本月时间，在格式化为月份时减去对应月份
                $m = date('m');
                $y = date('Y');
                $m = $m + $t;
                if ($m <= 0) {
                    // last year
                    $y = $y + floor(($m - 1) / 12);
                    $m = 12 + $m % 12;
                }
                return $y.$m;
            }
        }

        if ($this->isHourModel()) return date('Y', $tt).sprintf('%03d', date('z', $tt)).date('H', $tt); // 取小时
        elseif ($this->isDayModel()) return date('Y', $tt).sprintf('%03d', date('z', $tt)); // 取天
        elseif ($this->isWeekModel()) return date('o', $tt).sprintf('%02d', date('W', $tt)); // 取周
        elseif ($this->isMonthModel()) return date('Ym', $tt); // 取月
    }

    /**
     * 是否是总计数模型
     * @return bool
     */
    protected function isTotalModel()
    {
        return !$this->isHourModel() && !$this->isDayModel() &&
               !$this->isMonthModel() && !$this->isWeekModel();
    }

    /**
     * 是否是小时计数模型
     * @return bool
     */
    protected function isHourModel()
    {
        return substr_compare($this->table, '_hour', -5) === 0;
    }

    /**
     * 是否是日计数模型
     * @return bool
     */
    protected function isDayModel()
    {
        return substr_compare($this->table, '_day', -4) === 0;
    }

    /**
     * 是否是周计数模型
     * @return bool
     */
    protected function isWeekModel()
    {
        return substr_compare($this->table, '_week', -5) === 0;
    }

    /**
     * 是否是月计数模型
     * @return bool
     */
    protected function isMonthModel()
    {
        return substr_compare($this->table, '_month', -6) === 0;
    }
}
?>