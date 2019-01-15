/**
 * 瀑布流
 * test版本 shenxn
 */
(function($) {
    $.fn.doWookmark = function(o,options) {
        var defaults = {
                handler   : null,
                page      : window.page,
                page_num  : 0,
                isLoading : false,
                apiurl    : '',
                uploadUrl : '',
                type      : 'li'
        };
        var o = $.extend(defaults,o);
        o.sel = options.container.selector;

        //绑定事件
        function onScroll(event) {
          if(!o.isLoading) {
            var closeToBottom = ($(window).scrollTop() + $(window).height() > $(document).height() - 200);
            if(closeToBottom) {
              loadData();
            }
          }
        };

        //计算高度
        function applyLayout() {
          if(o.handler) {
             o.handler.wookmarkClear();
          }

          o.handler = $(''+ options.container.selector +' '+ o.type + '');
          o.handler.wookmark(options);
        };

        //加载数据
        function loadData(type,milestoneid) {
          o.isLoading = true;

          $(o.sel).after('<div class="loading" id="loading"><img src="../images/loading_small.gif">加载中</div>');
          $.ajax({
            url: o.apiurl,
            dataType: 'json',
            data: {'page': o.page, 'page_num':o.page_num },
            success: onLoadData
          });
        };

        function onLoadData(json) {
          ++ o.page_num ;
          o.isLoading = false;
          $('#loading').remove();

          if (json.info == 'ok') {
            var html = '';
            var i = 0, length = json.data.length, image;
            for(i; i < length; i++) {
              var info = json.data[i];
              // 图片加载完调用计算高度
              (function(){
                  var loadImg = new Image();
                  loadImg.src = info.cover;
                  loadImg.onload = function() {
                      applyLayout();
                  }
              }())

              html += "<li>";
              html += "<a href = "+ AnUrl("photo/show_pics?res_name="+info.res_name+"&res_id="+info.res_id+"&pic_res="+json.table+"&pic_id="+info.id)+">"
              html += "<img src='" + info.cover +"' width='213px' onerror=\"this.src='images/default/general.gif'\" >";
              html += "</a></li>";
            }

            $item = $(html).hide();
            $(o.sel).append($item);
            $item.fadeIn();

            applyLayout();
          } else {
             window.isAddMore = 0;
          }

          if(o.page_num >= json.pages_num){
              $('.pages-box').show();
              o.isLoading = true;
          }else{
              o.isLoading = false;
          }
        };

	    $(document).ready(new function() {
	      $(window).bind('scroll',onScroll);
	      loadData('one');
	    });
    }
})(jQuery);
