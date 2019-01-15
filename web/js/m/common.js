/**
* 手机版公用js文件
**/


if (typeof(common) == 'undefined') {
    var common = {};
}

//h5地理位置获取坐标 
common.getLocation = function () {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position){
            var localLat = position.coords.latitude;
            var localLng = position.coords.longitude;

            common.setCookie('localLat', localLat, 3600*12);
            common.setCookie('localLng', localLng, 3600*12);
        });
    }
}

common.setCookie = function(key, value, options) {
    
    // key and at least value given, set cookie...
    if (arguments.length > 1 && String(value) !== "[object Object]") {

        if (value === null || value === undefined) {
            options.expires = -1;
        }

        //已经重新改造现在分钟计时，
        if (typeof options.expires === 'number') {
            var times = options.expires;
            var t = new Date();
            t.setTime(t.getTime() + times*60*1000);
        }
        
        value = String(value);

        return (document.cookie = [
            encodeURIComponent(key), '=',
            options.raw ? value : encodeURIComponent(value),
            options.expires ? '; expires=' + t.toGMTString() : '', // use expires attribute, max-age is not supported by IE
            options.path ? '; path=' + options.path : '',
            options.domain ? '; domain=' + options.domain : '',
            options.secure ? '; secure' : ''
        ].join(''));
    }
}

common.getCookie = function(name) 
{ 
    var arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");
 
    if(arr=document.cookie.match(reg))
 
        return unescape(arr[2]); 
    else 
        return null; 
}

//所有的站内链接添加渠道号
common.fReplaceUrl = function(){
    // 获取所有链接进行拼接
    var aAllDoElements = $('a').not('.fUrl');
    
    $.each(aAllDoElements, function(k, v){
        var url = $(v).attr('href');

        if (url != undefined) {
            if (url.indexOf('pzclub.cn') > -1 || url.indexOf('youhui.live.189.cn') > -1 || url.indexOf('alltosun.net') > -1) {
            	if (url.indexOf('#') > -1) {
                    var urls = url.split("#");
                    $(v).attr('href', urls[0] + '&f='+unionId + '#'+ urls[1]);
                    $(v).addClass('fUrl');
                } else {
                    $(v).attr('href', url + '&f='+unionId);
                    $(v).addClass('fUrl');
                }
            }
        }

    });
}