<?php

/**
 * alltosun.com rss生成类
 * ============================================================================
 * 版权所有 (C) 2007-2009 北京共创阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 高竞竞 (gaojj@alltosun.com) $
 * $Date: 2009-04-13 23:37:57 +0800 $
 * $Id: Rss.php 203 2012-04-08 14:45:23Z gaojj $
*/

class Rss
{
    private $dom = null;
    private $root = null;
    private $channel = null;

    function __construct()
    {
        $this->dom = new DOMDocument("1.0", 'UTF-8');
        $this->root = $this->dom->createElement('rss');
        $this->root = $this->dom->appendChild($this->root);
        $this->root->setAttribute('version','2.0');
    }

    public function setChannel($array)
    {
        $this->channel = $this->dom->createElement("channel");
        $this->channel = $this->root->appendChild($this->channel);
        $this->createElement($this->channel, $array);
    }

    public function addItem($array)
    {
        $item = $this->dom->createElement("item");
        $item = $this->channel->appendChild($item);
        $this->createElement($item, $array);
    }

    private function createElement($em, $array)
    {
        foreach ($array as $key => $val)
        {
            $item = $this->dom->createElement($key, $val);
            $em->appendChild($item);
        }
    }

    public function genarate()
    {
        header('Content-Type: text/xml');
        echo $this->dom->saveXML();
    }
}
?>