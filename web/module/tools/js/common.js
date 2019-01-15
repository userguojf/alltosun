defined(function(require, explorts, module ){
    /**
     * 闪烁效果，主要用户提示。
     * @param ele 元素对象
     * @param cls 类名
     * @param times 闪烁时间
     * shakeColor($("#ID"),"red",3);
     */
    exports.shakeColor = function (ele,cls,times) {
        var i = 0,t= false ,o =ele.attr("class")+" ",c ="",times=times||2;
        if(t) return;
        t= setInterval(function(){
            i++;
            c = i%2 ? o+cls : o;
            ele.attr("class",c);
            if(i==2*times){
                clearInterval(t);
                ele.removeClass(cls);
            }
        },200);
    }
    
    /**
     * 换一换按钮旋转
     */
    exports.transForm = function(mes){
        var i = m = 0;
        var obj = $('.icon-change i');
        var time = setInterval(function(){
            i += 30;
            // 旋转
            obj.css('-webkit-transform','rotate('+ i +'deg)');
            obj.css('-moz-transform', 'rotate('+ i +'deg)');
            obj.css('filter:progid', 'DXImageTransform.Microsoft.BasicImage(rotation=3)');
            obj.css('-moz-transform', 'rotate('+ i +'deg)');
            obj.css('-o-transform', 'rotate('+ i +'deg)');
            obj.css('-webkit-transform', 'rotate('+ i +'deg)');
            obj.css('transform', 'rotate(270deg)');
            if (m > mes) clearInterval(time);
            m++;
        }, 100);
})
