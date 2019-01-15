define(function(require, exports, module) {
    var $ = require('jquery');

    exports.setFollow = function(url,class_box){
        $("." + class_box).click(function(){
           var follow_id = $(this).attr('follow_id');

           if ($(this).html() === "已关注") {
               return false;
           }

           $.post(url,{ uid:follow_id },function(data){
                if (data.info == 'ok') {
                    $("." + class_box).html("已关注");
                } else {
                    alert(data.msg);
                }
           },'json')
        });
    }
});
