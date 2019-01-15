//-----------------------------------------------------------------------------分享
//---------------------------------------------微信分享
staticProperty.wxShare=function(shareData,_isLoadSdk)
{
    if(_isLoadSdk==null){
        _isLoadSdk = true;
    }

    function wxShare()
    {
        function getJSSDKData(backFun){
            var Url = siteUrl + '/wework/get_param';
            $.ajax({
                url : Url,
                type: "post",
                // encodeURIComponent( location.href.split('#')[0] )
//                console.log(window.location.href);
                // window.location.href
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
//             console.log(configData);
//             alert(configData.appId)
            wx.config({
                debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
                appId: configData.appId, // 必填，公众号的唯一标识
                timestamp: configData.timestamp, // 必填，生成签名的时间戳
                nonceStr: configData.nonceStr, // 必填，生成签名的随机串
                signature: configData.signature,
                jsApiList: ["chooseImage","uploadImage","onMenuShareTimeline","onMenuShareAppMessage","onMenuShareQQ","onMenuShareWeibo"] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
            });

            wx.ready(function(){
                // 分享到朋友圈
                wx.onMenuShareTimeline({
                    title: shareData["title"], // 分享标题
                    link: shareData["link"], // 分享链接，该链接域名必须与当前企业的可信域名一致
                    imgUrl: shareData["imgUrl"], // 分享图标
                    success: function () {
                        // 用户确认分享后执行的回调函数
                    	$.post( siteUrl + "/wework/news/share_save",{ 'id' : shareId, 'type' : 1 },function(json){
                        },'json')
                    },
                    cancel: function () {
                        // 用户取消分享后执行的回调函数
                    }
                });
                // 分享给朋友
                wx.onMenuShareAppMessage({
                    title: shareData["title"], // 分享标题
                    desc: shareData["desc"], // 分享描述
                    link: shareData["link"], // 分享链接，该链接域名必须与当前企业的可信域名一致
                    imgUrl: shareData["imgUrl"], // 分享图标
                    type: '', // 分享类型,music、video或link，不填默认为link
                    dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
                    success: function () {
                        // 用户确认分享后执行的回调函数
                    	$.post( siteUrl + "/wework/news/share_save",{ 'id' : shareId, 'type' : 2 },function(json){
                        },'json')
                    },
                    cancel: function () {
                        // 用户取消分享后执行的回调函数
                    }
                });
                // 分享到QQ
                wx.onMenuShareQQ({
                    title: shareData["title"], // 分享标题
                    desc: shareData["desc"], // 分享描述
                    link: shareData["link"], // 分享链接
                    imgUrl: shareData["imgUrl"], // 分享图标
                    success: function () {
                        // 用户确认分享后执行的回调函数
                    	$.post( siteUrl + "/wework/news/share_save",{ 'id' : shareId, 'type' : 3 },function(json){
                        },'json')
                    },
                    cancel: function () {
                        // 用户取消分享后执行的回调函数
                    }
                });
                // 分享到腾讯微博
                wx.onMenuShareWeibo({
                    title: shareData["title"], // 分享标题
                    desc: shareData["desc"], // 分享描述
                    link: shareData["link"], // 分享链接
                    imgUrl: shareData["imgUrl"], // 分享图标
                    success: function () {
                        // 用户确认分享后执行的回调函数
                    	$.post( siteUrl + "/wework/news/share_save",{ 'id' : shareId, 'type' : 4 },function(json){
                        },'json')
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

    if(_isLoadSdk) {
        wxShare();   //调用分享sdk信息
    }
}
// 调用
staticProperty.wxShare(staticProperty.shareData);