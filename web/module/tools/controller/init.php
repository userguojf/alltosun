<?php
/**
 * alltosun.com  初始化一些简便操作 init.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 王德康 (wangdk@alltosun.com) $
 * $Date: 2014-5-13 下午10:09:47 $
 * $Id$
 */

/**
 * 简化Debug信息
 */
if (isset($_GET['w']) && $_GET['w'] == 1) {
    $_GET['powerby'] = 'alltosun';
    $_GET['debug']   = 1;
    $_GET['cache']   = 0;
}

?>