<?php
/**
  * alltosun.com  binding.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 申小宁 (shenxn@alltosun.com) $
  * $Date: 2017年11月17日 下午5:26:03 $
  * $Id: binding.php 382270 2017-11-23 08:36:12Z songzy $
  */
class Action
{
    public function __call($action = '', $params = array())
    {
        echo "视频播放地址";
    }
}