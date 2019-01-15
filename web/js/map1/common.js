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

        if (typeof options.expires === 'number') {
            var days = options.expires, t = options.expires = new Date();
            t.setDate(t.getDate() + days);
        }
        
        value = String(value);
        
        return (document.cookie = [
            encodeURIComponent(key), '=',
            options.raw ? value : encodeURIComponent(value),
            options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
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

common.cpsReplaceUrl = function() {

    var aCpsElements = $('.cps_goods').not('.cps_url');

    $.each(aCpsElements, function(k, v) {
        var shop_name = $(v).attr('store_id');
        var  url = $(v).attr('href');

        //百度
        if (shop_name == 35) {
            var url = $(v).attr('href');
            $(v).attr('href', url + '?ch=G&zch=' + window.unionId);
            $(v).addClass('cps_url');
        }
    });
}

common.ref_function = function() {
    $('.is_ref_btn').click(function(){
        common.setCookie('ref','zbh', 7200);
        window.location.href = siteUrl + '/e/coupon';
    })
}

common.add_index_image_box = function() {
    $('.back_image_red').click(function(){
        $(this).find('img').addClass('border-red');
    })
    //back_image_hui
    $('.back_image_hui').click(function(){
        $(this).find('.acive').show();
        
    })
}
$(function(){
    common.getLocation();
    common.fReplaceUrl();
    common.cpsReplaceUrl();
    common.ref_function();
    common.add_index_image_box();
})