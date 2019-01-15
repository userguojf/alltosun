(function(){
    /**
     * 事件绑定
     */
    function bind(g, f, h, e) {
        if (g.addEventListener) {
            g.addEventListener(f, h, !!e)
        } else {
            if (g.attachEvent) {
                g.attachEvent("on" + f, h)
            } else {
                g["on" + f] = h
            }
        }
    }
    
    /**
     * 解析url参数
     * @param url
     * @param param 
     * @return string
     */
    var getParam = function(url, param) {
        var q = url.match(new RegExp( param + "=[^&]*")),
        n = "";
        if (q && q.length) {
           n = q[0].replace(param + "=", "");
        }
        return n;
    }
    
    // hashChange监控
    var hashChange = {};
    hashChange.oldHash = '';

    /**
     * 解析hash为对象
     * @param string hash
     * @retunr object
     */
    hashChange.hashStringToObject = function(hash) {
            var obj = {}, re = /[^&#]*=[^&]*/g, match;
            while((match = re.exec(hash)) != null) {
                var arr = match[0].split('=');
                if (arr.length  == 2) {
                    obj[arr[0]] = arr[1];
                }
            }
            return obj;
    }
    
    hashChange.getHash = function(){
        var h = location.hash;
            
        if (!h) {
             return '';
        } else {
             return h;
        }
   };
        
   hashChange.isHashChanged = function() {
        var current = this.getHash();
        if (current !== hashChange.oldHash) {
            return true;
        } 
        return false;
   };
    
   hashChange.bindFunc = function(func) {
        hashChange.oldHash = hashChange.getHash();
        
        var hashchange = 'hashchange' , documentMode = document.documentMode;
        // documentMode > 7 是IE8+
        if (('on' + hashchange in window) && documentMode === void 0 || documentMode > 7 ) {
            window.onhashchange = function() {
                func(hashChange.hashStringToObject(location.hash));
            }
        } else {
            hashChange.timer = setInterval(function(){
                var ischanged = hashChange.isHashChanged();
                if (ischanged) {
                    func(hashChange.hashStringToObject(location.hash));
                    clearInterval(hashChange.timer);
                }
            }, 150);
        }
    }
    
    window.ifrH = function(e){
            // 接收参数iframe的URL
        var searchUrl = window.location.search, 
            // 获取高度的标签ID
            divId = '_autoHeight',
            // 获取整体页面高度
            b = document.getElementById(divId),
            //  请求URL地址
            ifrmUrl = 'http://nba.weibo.com/', 
            // 要执行函数名
            cbn = '', 
            // 自动高度的ifmId
            ifmId = '',
            // 高度值
            l = '',
            params = '';
        
        // ifrmID
        if (e && e.ifmId) {
            ifmId = e.ifmId;
        }
        
        // 函数
        if (e && e.cbn) {
            cbn = e.cbn;
            // 是否有回调
            if (e.callback) {
                hashChange.bindFunc(function(obj){
                    e.callback(obj);
                })
            }
        } else {
            // 计算高度与函数不同时生效
            if (!b) {
                b = document.createElement("div");
                b.id = divId;
                b.style.cssText = "clear:both;"
                document.body.appendChild(b);
            }
            
            l = b.offsetTop + b.offsetHeight;
        }
        
        if (window.postMessage) {
            var str = '';
            if (!e) {
                str = "l:" + l +':' +  ( + new Date());
            } else {
                str = "cbn:" + e.cbn +':' +  ( + new Date());
            }
            window.parent.postMessage(str, ifrmUrl);
            
        } else {
            
            var iframe = document.createElement('iframe');
            iframe.style.display = "none";
            iframe.width = 0;
            iframe.height = 0;
            document.body.appendChild(iframe);
            
            iframe.onload = function() {
                document.getElementsByTagName('body')[0].removeChild(iframe);
            }
            
            if (e && e.type == "onload" && ifmId) {
                b.parentNode.removeChild(b);
                b = null;
            }
            
            iframe.src = (decodeURIComponent(ifrmUrl) || 
                    "http://nba.weibo.com/proxy.html") + "?h=" + l + "&ifmID=" + (ifmId || "ifm") + "&cbn=" + cbn + "&mh=" + l + "&t=" + ( (+ new Date()));
        }
        
    }
    
    window.onload = function() {
        // ifrH({type: "onload"});
    }
    
    //ifrH();
})();