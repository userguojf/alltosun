<?php

/**
 * alltosun.com 数据库操作类 model.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 王敬飞 (wangjf@alltosun.com) $
 * $Date: 2017年6月6日 下午6:31:12 $
 * $Id$
 */
class model
{
    private static $db = NULL;

    public function __construct()
    {
        self::$db = Db::instantiation();
    }

    /**
     * 获取count
     */
    public function get_total($table, $filter, $count_type = '*', $handle = '')
    {
        $where = $this->to_where($filter);

        if ($handle) {
            $where .= $handle;
        }
        $result = self::$db->count($table, $where, $count_type);

        if ($result === false) {
            return 0;
        } else {
            return $result;
        }
    }

    public function get_fields_sum($table, $field, $filter)
    {
        $where = $this->to_where($filter);

        $result = self::$db->one($table, $where, "SUM({$field})");


        if ($result === false || !isset($result["SUM({$field})"])) {
            return 0;
        } else {
            return $result["SUM({$field})"];
        }


    }

    /**
     * 数据添加创建
     * @param unknown $table
     * @param unknown $new_data
     */
    public function create($table, $new_data)
    {
        $keys = implode(',', array_keys($new_data));
        $values = implode(',', array_values($new_data));

        return self::$db->insert($table, $keys, $values);
    }

    /**
     * 更新数据
     * @param unknown $filter 条件
     * @param unknown $update_info
     */
    public function update($table, $filter, $update_data)
    {

        if (!$filter) {
            return array('errno' => 1001, 'msg' => 'model error:update filter not null');
        }

        if (!is_array($update_data)) {
            return array('errno' => 1001, 'msg' => 'model error:update data errors');
        }

        $set = ' SET ';

        //set语句处理
        foreach ($update_data as $k => $v) {
            $set .= " {$k}={$v},";
        }
        $set = rtrim($set, ',');

        //where 语句处理
        $where = $this->to_where($filter);

        return self::$db->update($table, $set, $where);

    }

    /**
     * 获取详情(一条)
     * @param unknown $table
     * @param unknown $id
     * @return boolean|mixed
     */
    public function get_info($table, $filter, $order = '')
    {
        if (!$filter) {
            return array();
        }

        $where = $this->to_where($filter);

        if ($order) {
            $where .= $order;
        }

        return self::$db->one($table, $where, '*');
    }

    /**
     * 数组条件转换where语句
     * @param unknown $filter
     * @return string
     */
    public function to_where($filter)
    {
        if (!$filter) {
            return '';
        }

        $where = '';

        if (is_array($filter)) {

            foreach ($filter as $k => $v) {

                if (!$where) {
                    $where = " WHERE ";
                }

                if (strpos($k, '<') || strpos($k, '>')) {
                    $where .= " {$k}{$v} AND";
                } else {
                    $where .= " {$k}={$v} AND";
                }

            }

            $where = rtrim($where, 'AND');
        } else {

            if (!$where) {
                $where = " WHERE ";
            }

            $where .= "id={$filter} ";
        }

        return $where;
    }
}