<?php

/**
 * 单元测试入口
 * ============================================================================
 * 版权所有 (C) 2007-2009 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 安然 (anr@alltosun.com) $
 * $Date: 2011-2-12 下午06:40:44 $
*/

if (isset($_GET['mc_flush'])) {
	$mc_wr = new memcache;
	$mc_wr->connect('127.0.0.1', 11211);
	$mc_wr->flush();
	$mc_wr->connect('127.0.0.1', 11212);
	$mc_wr->flush();
}

header('Content-Type:text/html; charset=utf8');
?>
Model测试<br>
使用ModelRes类使用ad表，Model类使用ad_model表，支持cache=0
<li><a href="test_model.php" target="_blank">Model类测试，单库</a> <a href="test_model.php?cache=0" target="_blank">关闭缓存</a></li>
<li><a href="test_model.php?a" target="_blank">Model类测试，多库-自动选择从库</a> <a href="test_model.php?a&cache=0" target="_blank">关闭缓存</a></li>

<li><a href="test_model.php?modelres" target="_blank">ModelRes类测试，单库</a> <a href="test_model.php?modelres&cache=0" target="_blank">关闭缓存</a></li>
<li><a href="test_model.php?modelres&a" target="_blank">ModelRes类测试，多库-自动选择从库</a> <a href="test_model.php?modelres&a&cache=0" target="_blank">关闭缓存</a></li>
<li><a href="test_model.php?modelres&attribute" target="_blank">ModelRes类测试，单库，带扩展属性</a> <a href="test_model.php?modelres&attribute&cache=0" target="_blank">关闭缓存</a></li>
<li><a href="test_model.php?modelres&a&attribute" target="_blank">ModelRes类测试，多库-自动选择从库，带扩展属性</a> <a href="test_model.php?modelres&a&attribute&cache=0" target="_blank">关闭缓存</a></li>

<br>
<br>
<br>
ModelResObserve-观察者测试<br>
使用ModelRes类使用ad表，Model类使用ad_model表，支持cache=0
<li><a href="test_ModelResObserver.php" target="_blank">Model类测试，单库</a> <a href="test_ModelResObserver.php?cache=0" target="_blank">关闭缓存</a></li>
<li><a href="test_ModelResObserver.php?a" target="_blank">Model类测试，多库-自动选择从库</a> <a href="test_ModelResObserver.php?a&cache=0" target="_blank">关闭缓存</a></li>

<li><a href="test_ModelResObserver.php?modelres" target="_blank">ModelRes类测试，单库</a> <a href="test_ModelResObserver.php?modelres&cache=0" target="_blank">关闭缓存</a></li>
<li><a href="test_ModelResObserver.php?modelres&a" target="_blank">ModelRes类测试，多库-自动选择从库</a> <a href="test_ModelResObserver.php?modelres&a&cache=0" target="_blank">关闭缓存</a></li>
<li><a href="test_ModelResObserver.php?modelres&attribute" target="_blank">ModelRes类测试，单库，带扩展属性</a> <a href="test_ModelResObserver.php?modelres&attribute&cache=0" target="_blank">关闭缓存</a></li>
<li><a href="test_ModelResObserver.php?modelres&a&attribute" target="_blank">ModelRes类测试，多库-自动选择从库，带扩展属性</a> <a href="test_ModelResObserver.php?modelres&a&attribute&cache=0" target="_blank">关闭缓存</a></li>

<br>
<br>
<br>
Model的hook测试<br>
<li><a href="test_model_hook.php" target="_blank">Model hook类测试</a> <a href="test_model_hook.php?cache=0" target="_blank">关闭缓存</a></li>

<br>
<br>
<br>
Memcache测试<br>
<li><a href="test_mc.php" target="_blank">Memcache测试</a></li>
<li><a href="?mc_flush">清空 Memcache</a></li>

<br>
<br>
<br>
DB测试<br>
<li><a href="test_db.php" target="_blank">DB测试</a></li>

<br>
<br>
<br>
Cache类测试<br>
<li><a href="test_cache.php" target="_blank">Cache类测试</a></li>

