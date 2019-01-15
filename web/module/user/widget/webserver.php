<?php
/**
 * alltosun.com  webserver.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 王德康 (wangdk@alltosun.com) $
 * $Date: 2014-1-4 下午4:31:41 $
 * $Id$
 */

require_once ROOT_PATH.'/helper/nusoap-0.9.5/lib/nusoap.php';

class webserver_widget
{
    public function init()
    {

//         $ws = "http://webservice.webxml.com.cn/WebServices/WeatherWS.asmx?wsdl";
//         $client = new SoapClient($ws);//使用 wsdl方式
//         $cityCode = "991";
//         $result = $client->getWeather(array('theCityCode'=>$cityCode));
//         var_dump($result);
        //exit;

        ini_set('soap.wsdl_cache_enabled',0);
        ini_set('soap.wsdl_cache_ttl',0);

        $ws = "http://180.168.119.194:18010/bop/services/TradeService?wsdl";

        try {
            $client = new SoapClient($ws);
            $cityCode = "991";
            $result = $client->tradeFullInfoSync(array('theCityCode'=>$cityCode));
        } catch (SoapFault $fault) {
            trigger_error("SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR);
        }
        var_dump($result);
        // http://ausns.alltosun.net/QpayExternalServices/NewCustomer.php?wsdl
        // http://180.168.119.194:18010/bop/services/SinaTradeService?wsdl
       // $client = new soapclient('http://ausns.alltosun.net/QpayExternalServices/NewCustomer.php?wsdl');
        //  $client = new nusoap_client('http://180.168.119.194:18010/bop/services/TradeService?wsdl',true);
        $wsdl = "http://180.168.119.194:18010/bop/services/TradeService?wsdl";


        $params = $post_info = array();
        // 订单号
        $post_info['code'] = '199141235998457';
        //订单创建事件
        $post_info['createTime'] = '2013-12-26 09:52:14';
        // 是否需要发票
        $post_info['isNeededInvoice'] = 1;
        // 发票抬头
        $post_info['invoiceTitle'] = 'wangdk';
        // 发票备注
        $post_info['invoiceContent'] = '发票备注';
        // 实际运费
        $post_info['acutalTransFee'] = 12.00;
        // 商品总金额
        $post_info['totalActual'] = 499.0;
        // 外部积分
        $post_info['totalOuterPoint'] = 0;
        // 内部积分
        $post_info['totalInnerPoint'] = 0;
        // 订单虚拟货币总金额
        $post_info['totalVc'] = 0;
        // 买家备注
            // 定制号码
            $exp_info['jersey_no']= 22;
            // 定制名称
            $exp_info['jersey_name'] = 'YOU NAME';
            // 定制的哪个球队
            $exp_info['team_name']   = 'bulls';
            // 定制的图片样式
            $exp_info['image']       = 'http://nba.alltosun.net/images/jersey/bulls/jersey_small.png';
            // 买家备注
            $exp_info['remark']      = '快点发货哦';

        $post_info['remark']  = json_encode($exp_info);
        // 卖家备注
        $post_info['sellerMemo'] = '卖家备注';
        // 是否需要包装
        $post_info['isNeededPacking'] = 1;
        // 店铺ID
        $post_info['omsShopId'] = 2915069635;
        // 终端来源
        $poost_info['source'] = 'pc';
        // 扩展字段
        $post_info['extProp1'] = '';

            $line_info = array();
            // sku
            $line_info[0]['extentionCode'] = '';
            // 数量
            $line_info[0]['qty'] = 1;
            // 单价
            $line_info[0]['unitPrice'] = 0;
            // 市场价
            $line_info[0]['listPrice'] = 0;
            // markdown价格
            $line_info[0]['mdPrice'] = 0;
            // 行总计
            $line_info[0]['discountFee'] = 0;
            // 订单行使用外部积分金额
            $line_info[0]['outerPointValue'] = 0;
            // 订单行使用内部积分金额
            $line_info[0]['innerPointValue'] = 0;
            // 其它虚拟货币金额（如预付卡）
            $line_info[0]['otherVc'] = 0;
            // 平台订单行ID
            $line_info[0]['platformLineId'] = 0;
            // 扩展字段1
            $line_info[0]['extProp1']       = '';
            // 订单行所享受的促销活动
            // $line_info[0]['promotions']  = '';

        // 订单明细行
        $post_info['lines'] = $line_info;
        //echo $post_info['lines'];

            $delivery_info = array();
            $delivery_info[0]['receiver']      = '王大康';
            $delivery_info[0]['contactPerson'] = '王小康';
            $delivery_info[0]['receiverPhone'] = '18618155653';
            $delivery_info[0]['country']       = '中国';
            $delivery_info[0]['province']      = '北京';
            $delivery_info[0]['city']          = '北京市';
            $delivery_info[0]['district']      = '昌平区';
            $delivery_info[0]['address']       = '立水桥龙德紫金401';
            $delivery_info[0]['zipcode']       = '111000';

        // 发货信息集合
        $post_info['deliveryInfoList'] = $delivery_info;

            $platform_member_info = array();
            $platform_member_info['loginName'] = '小小人故事';
            $platform_member_info['realName']  = '王小康';
            $platform_member_info['gender']    = '男';
            $platform_member_info['birthday']  = '2012-12-12';
            $platform_member_info['telephone'] = '010-8523321';
            $platform_member_info['mobile']    = '18618145432';
            $platform_member_info['email']     = 'wangdk@alltosun.com';
            $platform_member_info['country']   = '中国';
            $platform_member_info['province']  = '北京';
            $platform_member_info['city']      = '北京市';
            $platform_member_info['zipCode']   = '100001';
            $platform_member_info['address']   = '立水桥龙德紫金';
            $platform_member_info['vipCode']   = 0;

        // 平台会员信息
        $post_info['platformMemInfo']  = $platform_member_info;

        // 平台订单类型
        $post_inf['platformOrderType']  = '';

        $params['trade'] = json_encode($post_info);

        // NBA微博店铺
        $params['nick']  = 2915069635;
        $params['sign']  = 'alltosun';

        $client = new nusoap_client($wsdl, 'wsdl');
        $err = $client->getError();
        var_dump($err);

        print_r($client);
        $result = $client->call('tradeFullInfoSync', array('parameters' => $params));
        var_dump($result);
        $client = new SoapClient($wsdl,      array(  'trace'      => true,
                'exceptions' => true,
        ));
        //
        var_dump($client);
        $vem = $client->__call('tradeFullInfoSync',array('paramters'=>$params));
        echo '<br>';
        echo '<br>';

        echo '<br>';

        echo '<br>';

        echo '<br>';

        var_dump($vem);

//         $err = $client->getError();
//         var_dump($err);

    }
}
?>