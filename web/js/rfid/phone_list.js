var updateStatus = {
     _id:'',
     _msg :'操作失败',
     _url:siteUrl + '/e/admin/rfid/ajax/update_res_status',
     update:function(obj){
         this._id = $(obj).parent().attr('resId');

         $.post(this._url,{ id:this._id },function(json){
             if (json.info == 'ok') {
                 updateStatus.deleteHtml(obj);
             } else {
                 alert(json.info);
             }
         },'json')
     },
     deleteHtml:function(obj){
       $(obj).parent().parent().fadeOut(function(){
            $(this).remove();
        });
        return false;
     }
 };

 $('.delete_hot').live('click',function(event){
     event.preventDefault();
     event.stopPropagation();
     updateStatus.update(this);
 })

$(function(){
	$('.js_btnHeader').on('click', function(){
	    $('.js_headerNav').toggle();
	});
});

$.fn.extend({
  touchMove: function() {
    var $this = $(this);
    var startX, startY, endX, endY, swipeX, swipeY;
    var container = this[0];
    container.addEventListener('touchstart', function(event) {
      startX = event.changedTouches[0].pageX;
      startY = event.changedTouches[0].pageY;
      swipeX = true;
      swipeY = false;
    });
    container.addEventListener('click', function (event) {
      $(this).css({
        "transform": "translateX(0)",
        "-webkit-transform": "translateX(0)"
      })
    })
    container.addEventListener('touchmove', function(event){
      endX = event.changedTouches[0].pageX;
      endY = event.changedTouches[0].pageY;  
      // 左右滑动
      if(swipeX && Math.abs(endX - startX) - Math.abs(endY - startY) > 0){
       // 阻止事件冒泡
       event.stopPropagation();
       if(endX - startX > 10){ //右滑
        event.preventDefault();
        $(this).css({
          "transform": "translateX(0)",
          "-webkit-transform": "translateX(0)"
        })
       }
       if(startX - endX > 10){ //左滑
        event.preventDefault();
        $(this).css({
          "transform": "translateX(-104px)",
          "-webkit-transform": "translateX(-104px)"
        })
       }
       swipeY = false;
      }
      // 上下滑动
      if(swipeY && Math.abs(endX - startX) - Math.abs(endY - startY) < 0) {
       swipeX = false;
      }  
    });
  }
})

// 增加滑动效果
var $lines = $('.phone-table .table .body > .row >.inner');
for (var i = 0;i<$lines.length;i++) {
	$lines.eq(i).touchMove();
}
