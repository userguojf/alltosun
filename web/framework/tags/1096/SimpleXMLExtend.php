<?php

/**
 * alltosun.com SimpleXMLElement扩展CDATA SimpleXMLExtend.php
 * ============================================================================
 * 版权所有 (C) 2007-2009 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 高竞竞 (gaojj@alltosun.com) $
 * $Date: 2012-02-03 00:42:46 +0800 (五, 03  2 2012) $
 * $Id: SimpleXMLExtend.php 143 2012-02-02 17:25:16Z gaojj $
*/

class SimpleXMLExtend extends SimpleXMLElement
{
    public function addCDATA($node_name, $cdata_text)
    {
        $node_sxe = $this->addChild($node_name);

        //DOMElement $node
        $node_element = dom_import_simplexml($node_sxe);

        //DOMDocument $no
        $node_document = $node_element->ownerDocument;

        //DOMDocument::createCDATASection
        $node_element->appendChild($node_document->createCDATASection($cdata_text));
    }
}
?>