$(function(){
    window.base = {};
    
    base.makeHtmlPage = function(params, json, obj) {
        
        if (params.start_num == 1) {
            var pageHtml = '<a href="javascript:void(0);" data-href="' + json.page.first_url + '" class="page-btn">首页</a>';

            if (json.page.current_page > 1) {
               pageHtml +=  '<a href="javascript:void(0);" data-href="' + json.page.prev_url + '" class="page-btn">上一页</a>';
            }

            for (var i = 0;  i < json.page.pages_nums.length; i++) {
                if (json.page.pages_nums[i] == json.page.current_page) {
                    pageHtml += '<span>' + json.page.pages_nums[i] + '</span>';
                } else {
                    pageHtml += '<a href="javascript:void(0);" data-href="' + json.page.pages_nums[i] + '">' + json.page.pages_nums[i] + '</a>';
                }
            }
            
            if (json.page.current_page < json.page.last_page) {
                pageHtml += '<a href="javascript:void(0);" data-href="' + json.page.next_url + '"  class="page-btn">下一页</a>';
            }
            
            pageHtml += '<a href="javascript:void(0);" data-href="' + json.page.last_url + '" class="page-btn">末页</a>';
            
            obj.html(pageHtml);
        }
    }
    /**
     * 扩充数据加载，用于ajax分页中
     */
    base.loadData = function(url, params, success, obj, isLoading) {
        if (isLoading == undefined) base.showLoading(obj);
        base.get(url, params, success, function(json){
             $('.loadMores').hide();
            obj.html('<span class="map-box-loading txtcenter">' + json.info + '</span>');
        }); 
    }
    
    /**
     * 判断是否是数组
     */
    base.isArray = function isArray(obj) {   
        return Object.prototype.toString.call(obj) === '[object Array]';    
    }  
    
    /**
     * 加载loading
     */
    base.showLoading = function(obj) {
        obj.html('<img class="map-box-loading" src="../images/loading.gif">');
    }
    
    /**
     * 扩充jQuery中的$.get方法
     * @param string url
     * @param object params
     * @param success callback function
     * @param error   callback function
     */
     base.get = function(url, params, success, error){
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
    base.post = function(url, params, success, error){
        $.post(url, params, function(json){
            if (json.info == 'ok') {
                success(json);
            } else {
                error(json);
            }
        },'json')
    };
    
    /**
     * 扩充jQuery中的$.ajax方法 支持跨域
     * @param string url
     * @param object params
     * @param success callback function
     * @param error   callback function
     */
    base.ajax = function(url, params, success){
        $.ajax({
            url: url,
            dataType:'jsonp',
            type:'get',
            data:params,
            success:success,
            error:function(code, info) {
                console.log(info);
            }
        })
    };
    
    /**
     * 显示弹层并且定位屏幕居中
     * @param layer jQuery节点对象
     */
    base.show = function(layer) {
        var a = $( document ).scrollTop();
        var top = layer.offset().top;
        var height = $( window ).height();
        var h = a + (height - layer.height() ) / 2; 
        layer.show().offset({top: h});
    },
    
    /**
     * 弹出层跟随滚动条滚动
     */
    base.setCenter = function(layer){
         $(document).scroll(function(){
             var a = $( document ).scrollTop();
             var top = layer.offset().top;
             var height = $( window ).height();
             var h = a + (height - layer.height() ) / 2; 
             layer.offset({top: h});
         })
    };
    
    /**
     * 解析URL地址参数为对象
     * @return object
     */
    base.getUrlPrams = function() {
        var url = location.search || location.href, obj = {}, re = /[^&?]*=[^&]*/g, match;
        
        while((match = re.exec(url)) != null) {
            var arr = match[0].split('=');
            if (arr.length  == 2) {
                obj[arr[0]] = arr[1];
            }
        }
        return obj;
    };
    
    /**
     * 事件绑定
     * @param element 需要绑定的事件对象
     * @param eventName  绑定的事件名字 
     * @param listenerFunc  监听的事件函数
     * @param useCapture  事件流捕获阶段，还是冒泡阶段
     */
    base.bindEvent = function(element, eventName, listenerFunc, useCapture) {
        
        if (element.addEventListener) {
            element.addEventListener(eventName, listenerFunc, !!useCapture);
        } else {
            if (element.attachEvent) {
                element.attachEvent('on' + eventName, listenerFunc);
            } else {
                element['on' + eventName] = listenerFunc;
            }
        }
    };

     base.cookie = function(name, value, options) {
         if (typeof value != 'undefined') { // name and value given, set cookie
             options = options || {};
             if (value === null) {
                 value = '';
                 options.expires = -1;
             }
             var expires = '';
             if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
                 var date;
                 if (typeof options.expires == 'number') {
                     date = new Date();
                     date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
                 } else {
                     date = options.expires;
                 }
                 expires = '; expires=' + date.toUTCString(); // use expires attribute, max-age is not supported by IE
             }
             var path = options.path ? '; path=' + options.path : '';
             var domain = options.domain ? '; domain=' + options.domain : '';
             var secure = options.secure ? '; secure' : '';
             document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
         } else { // only name given, get cookie
             var cookieValue = null;
             if (document.cookie && document.cookie != '') {
                 var cookies = document.cookie.split(';');
                 for (var i = 0; i < cookies.length; i++) {
                     var cookie = jQuery.trim(cookies[i]);
                     // Does this cookie string begin with the name we want?
                     if (cookie.substring(0, name.length + 1) == (name + '=')) {
                         cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                         break;
                     }
                 }
             }
             return cookieValue;
         }
     }
     
    /**
     * 光标定位
     * @params int obj jQuery对象
     * @params int v   位置
     */
    base.setFocusPos=function(obj, v){
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
    
    /**
     * 判断是否是微信浏览器
     */
    base.isWeiXin = function() {
            var ua = navigator.userAgent.toLowerCase();
            if(ua.match(/MicroMessenger/i)=="micromessenger" || ua.match(/IEMobile/i)=="iemobile") {
                return true;
            } else {
                return false;
            }
    }
    
    /**
     * 增加debug方式
     * 根据url参数自动拼接url链接
     */
    base.url = function(url){
        var debugStr = 'powerby=alltosun&debug=1&cache=0', obj = base.getUrlPrams();
        if (obj.powerby && obj.debug && obj.cache) {
            url = url.indexOf('?') > -1 ? url + '&' : url +'?';
            return url + debugStr;
        } 
        
        return url;
    }
    
    /**
     * 移除HTML标签
     */
    base.removeHTLM = function setContent(str) {
        str = str.replace(/<\/?[^>]*>/g,''); //去除HTML tag
        str.value = str.replace(/[ | ]*\n/g,'\n'); //去除行尾空白
        //str = str.replace(/\n[\s| | ]*\r/g,'\n'); //去除多余空行
        return str;
    }
})
