//诸多-------------------------- 静态数据
var staticProperty={};
//*********************************其他属性定义区****************************************************
staticProperty.debug=false;//测试模式
//-------------------------------------------------------------------------------------请求数据类型 地址
staticProperty.gameId=2;
staticProperty.proName="game2_zhuzhu_pro";
staticProperty.host="http://www.pzclub.cn/game/"
staticProperty.wxShareUrl=staticProperty.host+"weixin/js-sdk/sample.php"//微信分享
staticProperty.baseShareUrl=staticProperty.host+"weixin/userInformation/weixiAccreditSkipUrl.php?sUd="+staticProperty.gameId;
staticProperty.share_url = 'http://www.pzclub.cn/e/game/save_user_num';

staticProperty.shareData={
    title:"愤怒的小鸟激萌开战",
    link:"http://www.360sides.com/ZBHWAP/angrybirds?share=jsh",//分享链接
    desc:"我在《愤怒的小鸟激萌开战》中打败了全国99%的用户，你也来参加吧，还有各种豪礼相送哦～",
    imgUrl:"http://www.pzclub.cn/game/images/share.jpg",//分享图片地址,
    shareOk:null
}

//-----------------------------------------------------------------------------分享
//---------------------------------------------微信分享
staticProperty.wxShare=function(shareData,_isLoadSdk)
{
    if(_isLoadSdk==null){
        _isLoadSdk = true;
    }

    //微信
    function wxShare()
    {
        function getJSSDKData(backFun){
            $.ajax({
                url: 'http://www.pzclub.cn/game/get_ticket.php',
                type: "post",
                data: { url: window.location.href },
                dataType: "json",
                success: function (configData) {
                    backFun(configData);
                },
                
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    console.log(XMLHttpRequest);
                }
            });
        }
        getJSSDKData(function(configData){
            wx.config({
                debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
                appId: 'wxa89d357db6ccb4bf', // 必填，公众号的唯一标识
                timestamp: configData.timestamp, // 必填，生成签名的时间戳
                nonceStr: configData.nonceStr, // 必填，生成签名的随机串
                signature: configData.signature,
                jsApiList: ["chooseImage","uploadImage","onMenuShareTimeline","onMenuShareAppMessage","onMenuShareQQ","onMenuShareWeibo"] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
            });

            wx.ready(function(){
                wx.onMenuShareTimeline({
                    title: shareData["desc"], // 分享标题
                    link: shareData["link"], // 分享链接
                    imgUrl: shareData["imgUrl"], // 分享图标
                    success: function () {
                        // 用户确认分享后执行的回调函数
                    	$.get(staticProperty.share_url, { type:1 },function(){  });
                    },
                    cancel: function () {
                        // 用户取消分享后执行的回调函数
                    }
                });
                wx.onMenuShareAppMessage({
                    title: shareData["title"], // 分享标题
                    desc: shareData["desc"], // 分享描述
                    link: shareData["link"], // 分享链接
                    imgUrl: shareData["imgUrl"], // 分享图标
                    success: function () {
                    	$.get(staticProperty.share_url, { type:2 },function(){  });
                        // 用户确认分享后执行的回调函数
                        //staticProperty.requestData(staticProperty.dataType.addShare,shareData.shareOk,{openid:staticProperty.openid});
                    },
                    cancel: function () {
                        // 用户取消分享后执行的回调函数
                    }
                });
                wx.onMenuShareQQ({
                    title: shareData["title"], // 分享标题
                    desc: shareData["desc"], // 分享描述
                    link: shareData["link"], // 分享链接
                    imgUrl: shareData["imgUrl"], // 分享图标
                    success: function () {
                    	$.get(staticProperty.share_url, { type:3 },function(){  });
                        // 用户确认分享后执行的回调函数
                       // staticProperty.requestData(staticProperty.dataType.addShare,shareData.shareOk,{openid:staticProperty.openid});
                    },
                    cancel: function () {
                        // 用户取消分享后执行的回调函数
                    }
                });
                wx.onMenuShareWeibo({
                    title: shareData["title"], // 分享标题
                    desc: shareData["desc"], // 分享描述
                    link: shareData["link"], // 分享链接
                    imgUrl: shareData["imgUrl"], // 分享图标
                    success: function () {
                    	$.get(staticProperty.share_url, { type:4 },function(){  });
                        // 用户确认分享后执行的回调函数
                      //  staticProperty.requestData(staticProperty.dataType.addShare,null,{openid:staticProperty.openid});
                    },
                    cancel: function () {
                        // 用户取消分享后执行的回调函数
                    }
                });
            });
            wx.error(function(res){
                for(r in res) {
//                    alert(res.r);
                }
            });
        });
    }

    if(_isLoadSdk)
        wxShare();   //调用分享sdk信息
}