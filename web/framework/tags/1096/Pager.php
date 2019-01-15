<?php

/**
 * alltosun.com 分页处理类 Pager.php
 * ============================================================================
 * 版权所有 (C) 2007-2011 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 高竞竞 (gaojj@alltosun.com) $
 * $Date: 2009-04-13 23:37:57 +0800 $
 * $Id: Pager.php 729 2013-07-30 04:53:06Z anr $
 * @link http://wiki.alltosun.com/index.php?title=Framework:Pager.php
*/

/**
 * 分页处理类
 * @author anr
 * @link http://wiki.alltosun.com/index.php?title=Framework:class:Pager
 */
class Pager
{
    private $page = null;
    private $perPage = 1;
    private $pages = 1;
    private $link = '';
    private $total = 0;
    /**
     * 是否已经生成分页，用来知道是否有最后一页
     * @var bool
     */
    private $isGenerated = false;

    private function getDefaultPage($page = null)
    {
        if (null === $page) {
            $page = intval(@$_GET['page_no']);
            $page < 1 && $page = 1;
        }

        return $page;
    }

    function __construct($perPage)
    {
        $this->perPage = $perPage;
    }

    /**
     * 生成LIMIT 0, 30样式的字符串
     * @param int $page
     */
    public function getLimit($page = null)
    {
        if (null === $page) {
            $page = $this->getDefaultPage($page);
            return 'LIMIT '.($page - 1) * $this->perPage.', '.$this->perPage;
        } else {
            $page = intval($page);
            // 最小
            if ($page < 1) $page = 1;
            // 最大
            if ($this->isGenerated && $page > $this->pages) $page = $this->pages;
            $this->page = $page;
            return 'LIMIT '.($this->page - 1) * $this->perPage.', '.$this->perPage;
        }
    }

    /**
     * 生成分页的样式
     * @param $total
     * @param $page
     * @param $link
     */
    public function generate(&$total, &$page = null, $link = '')
    {
        $page = $this->getDefaultPage($page);

        $this->link = $link;
        if (empty($this->link) && !empty($_SERVER['REQUEST_URI'])) {
            // xss
            $search  = array('"', '\'', '<', '>');
            $replace = array('%22', '%27', '%3C', '%3E');
            $_SERVER['REQUEST_URI'] = str_replace($search, $replace, $_SERVER['REQUEST_URI']);

            // 自动处理分页链接
            $uri = preg_replace('/&page_no=\d+/i', '', $_SERVER['REQUEST_URI']);
            $this->link = ltrim($uri, '/').'&amp;page_no=[#]';
        } else {
            // 处理传入的link只有当前url，没有page_no的形式
            if (stripos($link, '[#]') === false) {
                if (stripos($link, '?') !== false) {
                    $this->link = $link.'&amp;page_no=[#]';
                } else {
                    $this->link = $link.'?page_no=[#]';
                }
            }
        }

        if (is_array($total)) {
            $this->total = count($total);
            if ($this->total > $this->perPage) {
                array_pop($total);
                $this->pages = $page + 1;
            } else {
                $this->pages = $page;
            }
        } else {
            $this->total = $total;
            $this->pages = ceil($this->total / $this->perPage);
            $this->pages < 1 && $this->pages = 1;
        }

        $page > $this->pages && $page = $this->pages;
        $this->page = $page;

        $this->isGenerated = true;

        return $this->pages > 1;
    }

    public function getPagesArray($num = null)
    {
        if ($num === null) {
            return range(1, $this->pages);
        }
        $per = floor($num / 2);
        $min = $this->page - $per;

        if ($num % 2) {
            $max = $this->page + ceil($num / 2) - 1;
        } else {
            $max = $this->page + $per - 1;
        }

        if ($max > $this->pages) {
            $min -= $max - $this->pages;
            $max = $this->pages;
        } elseif ($min < 1) {
            $max += 1 - $min;
            $min = 1;
        }

        $max > $this->pages && $max = $this->pages;
        $min < 1 && $min = 1;

        return range($min, $max);
    }

    public function link($page)
    {
        return str_replace("[#]", $page, $this->link);
    }

    public function next()
    {
        return ($this->page + 1) > $this->pages ? $this->pages : ($this->page + 1);
    }

    public function prev()
    {
        return ($this->page - 1) > 0 ? ($this->page - 1) : 1;
    }

    public function begin()
    {
        return 1;
    }

    public function end()
    {
        return $this->pages;
    }

    public function getPage()
    {
        return $this->page;
    }

    public function getPages()
    {
        return $this->pages;
    }

    public function getTotal()
    {
        return $this->total;
    }

    public function getPerPage()
    {
        return $this->perPage;
    }
}
?>