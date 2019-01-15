<?php

/**
 * alltosun.com 无限级分类 Categroy.php
 * ============================================================================
 * 版权所有 (C) 2009-2010 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 宁海新 (ninghx@alltosun.com) $
 * $Date: 2010-6-8 上午10:28:20 $
 * $Id: Category.php 143 2012-02-02 17:25:16Z gaojj $
*/

class category
{
    protected $id = 'id';
    protected $name = 'name';
    protected $parent_id = 'parent_id';
    protected $view_order = 'view_order';

    //后补充的项目
    protected $parents = 'parents';
    protected $children = 'children';
    protected $orders = 'orders';
    protected $level = 'level';
    protected $onote = 'onote';

    //私有
    var $data;

    function __construct ($data, $name = '', $view_order = '')
    {
        if ($name) {
            $this->name = $name;
        }
        if ($view_order) {
            $this->view_order = $view_order;
        }
        $this->_init_tree($data);
    }

    //获取处理好的分类的二维数组
    function get_category_list()
    {
        return $this->data;
    }

    //获取options 数组
    function get_html_option($name = '')
    {
        if (!$name) {
            $name = $this->name;
        }
        $option = array();
        foreach ($this->data as &$v) {
            $option[$v[$this->id]] = str_repeat('&nbsp;&nbsp;', $v[$this->level]) . $v[$name];
        }
        return $option;
    }

    //显示为li结构
    function show_html_li($tmplate='')
    {
        if (empty($tmplate)) {
            $tmplate = '<a href="##id">#name</a>';
        }
        $tan = array('#id'=>0, '#name'=>'none');
        $str = '';
        $cur = array();
        foreach ($this->data as $v) {
            if ($cur) {
                $tan['#id'] = $cur[$this->id];$tan['#name'] = $cur[$this->name];
                $str .= '<li rel="' .$cur[$this->id]. '">' . strtr($tmplate, $tan);
                if (isset($cur[$this->children])) {
                    $str .= '<ul>';
                } else {
                    if ($cur[$this->level] == $v[$this->level]) {
                        $str .= '</li>';
                    } else {
                        $n = abs($v[$this->level] - $cur[$this->level]);
                        $str .= str_repeat('</li></ul>', $n) . '</li>';
                    }
                }
            }
            $cur = $v;
        }
        $tan['#id'] = $cur[$this->id];$tan['#name'] = $cur[$this->name];
        $str .= '<li rel="'. $cur[$this->id] .'">' . strtr($tmplate, $tan);
        if ($cur[$this->level] > 1) {
            $str .= str_repeat('</li></ul>', $cur[$this->level] -1) . '</li>';
        } else {
            $str .= '</li>';
        }
        echo $str;
    }

    //对分类数据进行预处理
    protected function _init_tree($data)
    {
        $this->data = array();
        foreach ($data as $v) {
            $this->data[$v[$this->id]] = $v;
        }

        foreach ($this->data as &$v) {
            $this->_build($v[$this->id]);
        }
        //排序
        uasort($this->data, array($this, "_category_sort"));
    }

    //给分类数据添加附加信息
    protected function _build ($id)
    {
        if ($this->data[$id][$this->parent_id] == $id) {
            throw new Exception('the id "' . $id . '" is invalid! it\' id eq parent_id');
        }
        $parent_id = $this->data[$id][$this->parent_id];
        //下级子分类
        if (isset($this->data[$parent_id])) {
            if (!isset($this->data[$parent_id][$this->children])) {
                $this->data[$parent_id][$this->children] = array(0=>$id);
            } else {
                $this->data[$parent_id][$this->children][] = $id;
            }
        }
        $level = 0;
        $parents = array();
        $orders = array();

        //遍历出所有父级别分类
        while (isset($this->data[$parent_id]) && (!in_array($parent_id, $parents))) {
            $parents[] = $parent_id;
            if (!empty($this->view_order)) {
                $orders[] = $this->data[$parent_id][$this->view_order];
            }
            $level ++;
            $parent_id = $this->data[$parent_id][$this->parent_id];
        }

        $this->data[$id][$this->parents] = array_reverse($parents);
        if (!empty($orders)) {
            $this->data[$id][$this->orders] = array_reverse($orders);
        }
        $this->data[$id][$this->level] = $level;
    }

    //分类排序
    protected function _category_sort($a, $b)
    {
        //初始化
        $tmp_a = $a[$this->parents];
        $tmp_b = $b[$this->parents];
        if ($a[$this->level] == $b[$this->level]) {
            //不处理
        } elseif ($a[$this->level] > $b[$this->level]) {
            $tmp_b[] = $b[$this->id];
        } else {
            $tmp_a[] = $a[$this->id];
        }
        //父分类比较
        for ($i=0; isset($tmp_a[$i]) && isset($tmp_b[$i]); $i++) {
            if ($tmp_a[$i] != $tmp_b[$i]) {
                if (!empty($this->view_order)) {
                    //父分类同级排序字段比较
                    $tmp_a_order = isset($a[$this->orders][$i]) ? $a[$this->orders][$i] : $a[$this->view_order];
                    $tmp_b_order = isset($b[$this->orders][$i]) ? $b[$this->orders][$i] : $b[$this->view_order];

                    return $tmp_a_order == $tmp_b_order ? ($tmp_a[$i] < $tmp_b[$i] ? -1 : 1) : ($tmp_a_order < $tmp_b_order ? -1 : 1);
                } else {
                    //id 值比较
                    return $tmp_a[$i] < $tmp_b[$i] ? -1 : 1;
                }
            }
        }
        //父分类相同情况
        if ($a[$this->level] == $b[$this->level]) {
            //分类同级比较
            if (!empty($this->view_order) && $a[$this->view_order] != $b[$this->view_order]) {

                return $a[$this->view_order] < $b[$this->view_order] ? -1 : 1;
            } else {

                return $a[$this->id] < $b[$this->id] ? -1 : 1;
            }
        } elseif($a[$this->level] > $b[$this->level]) {
            return 1;
        } else {
            return -1;
        }
    }

    //获取所有子分类
    public function get_all_children($id)
    {
        $level = $this->data[$id][$this->level];
        $children = array($id);
        foreach($this->data as &$val) {
            if (isset($val[$this->parents][$level]) && ($val[$this->parents][$level] == $id)) {
                $children[] = $val[$this->id];
            }
        }
        return $children;
    }

    public function get_children($id)
    {
        $children = array();
        foreach ($this->data as $val) {
            if ($val[$this->parent_id] == $id) {
                $children[$val[$this->id]] = $val;
            }
        }
        return $children;
    }

    //获取分类的全称
    public function get_full_name($id, $sep = ':')
    {
        $name = array();

        if ($this->data[$id][$this->parents]) {
            foreach ($this->data[$id][$this->parents] as $v) {
                $name[] = $this->data[$v][$this->name];
            }
        }
        $name[] = $this->data[$id][$this->name];
        return implode($sep, $name);
    }

    //获取某分类
    public function get($id)
    {
        return isset($this->data[$id]) ? $this->data[$id] : array();
    }

    //获取上级分类
    public function get_parent_id($id)
    {
        return $this->data[$id][$this->parent_id];
    }

    //获取所有父分类
	public function get_parents($id)
	{
		$parents = array();
		$parent_id = $this->data[$id][$this->parent_id];
		//遍历出所有父级别分类
        while (isset($this->data[$parent_id]) && (!in_array($parent_id, $parents)))
        {
            $parents[] = $parent_id;
            $parent_id = $this->data[$parent_id][$this->parent_id];
        }
        $parents[] = $id;
		sort($parents, SORT_NUMERIC);
		return $parents;
	}
}
?>