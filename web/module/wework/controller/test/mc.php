<?php
/**
 * alltosun.com  mc.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-2-23 下午6:16:11 $
 * $Id$
 * 
<xml>
<ToUserName><![CDATA[wx176cd5d3aae00630]]></ToUserName>
<Encrypt><![CDATA[Xy7/5S0j5HWVGskxzAzzq7sqpEO2V1YAs3kPpjYbl8gD0ttPAOxp6VAwQmwAPq1QQyU8s92S4PLNWjkDA8MWnc3cJ/6morOwm76/MOYtNS8B+AYC5kb7QEKh6E2QfJ6DZ0TaFycPoRlf0OY2l/X5M7bGQ9MCJrgYDZVtdAnVh7YZvn73LL1kndyaMwawNn5wKVnbYD3Y1bFtYaImHltUZEKZvAGjeJOOmiHVrP+ooV2h0nFW3MgYPcCDQOTNDTKpJ6SCY5R27hnbrHRNiXxChp3Lpu1/wt9zvIy2aMs+biKZyI9T5iNFr5X4WhePiKTcKGm1PTvlZ+fm2VArP2qfDDa10SAtm1iw3GgCMrqghCUlUFz7z9jKAWQeso0cujaaTLMS/9AzgFnFAHsU2zbboeU/gm6t4BjLMJuVOdwGAsCGnddSTjzBo60vSzB0dRvL0W+LybqdztNPWP81MlStxw==]]></Encrypt>
<AgentID><![CDATA[1000002]]></AgentID>
</xml>
 */

class Action
{
    public function index()
    {
        global $mc_wr;
        $result1 = $mc_wr->get('wework1');
        $result2 = $mc_wr->get('wework2');
        $result3 = $mc_wr->get('wework3');
        p($result1);
        p($result2);
        p($result3);
    }
   
}