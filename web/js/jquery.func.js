define(function(require, exports, module ){
    var $ = require('jquery');

    /**
     * 扩充jQuery中的$.get方法
     * @param string url
     * @param object params
     * @param success callback function
     * @param error   callback function
     */
    exports.get = function(url, params, success, error){
        $.get(url, params, function(json){
            if (json.info == 'ok') {
                success(json);
            } else {
                error(json);
            }
        },'json')
    };

    /**
     * 扩充jQuery中的$.post方法
     * @param string url
     * @param object params
     * @param success callback function
     * @param error   callback function
     */
    exports.post = function(url, params, success, error){
        $.post(url, params, function(json){
            if (json.info == 'ok') {
                success(json);
            } else {
                error(json);
            }
        },'json')
    };
    
//    exports.post = function(url, params, success, error) {
//        $.ajax({
//            url:url,
//            type : 'POST',
//            data : params,
//            dataType : "json",
//            success:success,
//            error:function(error){console.log(error)},
//            timeout:100000
//        });
//    }
    
    /**
     * 弹出层跟随滚动条滚动
     */
    exports.setCenter = function(layer){
         $(document).scroll(function(){
             var a = $( document ).scrollTop();
             var top = layer.offset().top;
             var height = $( window ).height();
             var h = a + (height - layer.height() ) / 2; 
             layer.offset({top: h});
         })
    }
    
    /**
     * 计算字数区分中英文
     */
    exports.strLen = function (sString) {
        var sStr,iCount,i,strTemp ;
        iCount = 0 ;
        sStr = sString.split("");
        for (i = 0 ; i < sStr.length ; i ++) {
            strTemp = escape(sStr[i]);
            if (strTemp.indexOf("%u",0) == -1) {
                iCount = iCount + 1 ;
            } else {
                iCount = iCount + 2 ;
            }
        }
        return Math.floor(iCount) ;
    };

    /**
     * 解析URL地址参数为对象
     * @return object
     */
    exports.getUrlPrams = function() {
        var url = location.search || location.href, obj = {}, re = /[^&?]*=[^&]*/g, match;
        
        while((match = re.exec(url)) != null) {
            var arr = match[0].split('=');
            if (arr.length  == 2) {
                obj[arr[0]] = arr[1];
            }
        }
        return obj;
    }
    
    /**
     * 事件绑定
     * @param element 需要绑定的事件对象
     * @param eventName  绑定的事件名字 
     * @param listenerFunc  监听的事件函数
     * @param useCapture  事件流捕获阶段，还是冒泡阶段
     */
    exports.bindEvent = function(element, eventName, listenerFunc, useCapture) {
        
        if (element.addEventListener) {
            element.addEventListener(eventName, listenerFunc, !!useCapture);
        } else {
            if (element.attachEvent) {
                console.log('on' + eventName);
                element.attachEvent('on' + eventName, listenerFunc);
            } else {
                element['on' + eventName] = listenerFunc;
            }
        }
    }
    
    exports.cookie = function (key, value, options) {
        
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

    exports.getCookie =  function (name) 
    { 
        var arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");
     
        if(arr=document.cookie.match(reg))
     
            return unescape(arr[2]); 
        else 
            return null; 
    }

    /**
     * 光标定位
     * @params int obj jQuery对象
     * @params int v   位置
     */
    exports.setFocusPos=function(obj, v){
      var range,len,v = v === undefined ? 0 : parseInt(v);
      obj.each(function(k, o){
          if($.browser.msie){
              range=o.createTextRange();
              v === 0 ? range.collapse(false):range.move("character",v);
              range.select();
          }else{
              len=o.value.length;
              v === 0 ? o.setSelectionRange(len,len): o.setSelectionRange(v,v);
          }
          o.focus();
      });
      return obj;
    }
})