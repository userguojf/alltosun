define(function(require, exports, mdoule){
    var $      = require('jquery');
    var anUrl  = window.AnUrl;

    $(function(){
        $('.deceive_coupon_start_btn').live('click',function(event) {
            event.preventDefault();
            var url = $(this).attr('href');
            var code = anUrl('captcha/coupon_cap');

            var html = "\
                <div class='mayer mayer_box'>\
                <div class='pop-block mayer_not_box'>\
                    <div class='txtbar'>\
                    <p>\
                        <input type='text' id='code' placeholder='请输入验证码' class='txt2 coupon_captcha'>\
                        <span class='codeshow'><img src='"+code+"' alt='' width='93px' height='36px' style='cursor:pointer;' class='captchaImage'></span>\
                        <a href='javascript:void(0);' class='icon icon-refresh captchaRefresh'></a>\
                     </p>\
                  </div>\
                  <div class='pop-btn-wrap2'>\
                     <a href='javascript:void(0);' class='btn btn-danger succ_deceive_coupon' >确认领取</a>\
                     <a href='javascript:void(0);' class='btn btn-warning mayer_fai_btn' >取消</a>\
                  </div>\
                 </div>\
              </div>";

            $('body').append(html);
            
            $('.succ_deceive_coupon').live('click',function(){
                var code = $('.coupon_captcha').val();
                if (code.length == 0) {
                    alert('请输入验证码');
                    return;
                }
                
                $(this).attr('href',url+'&captcha='+ code);
            })
            
            $('.mayer_fai_btn').live('click',function(){
                $('.mayer_box').remove();
            })
            
            var captchaFefresh = function(e){
                var url = anUrl('captcha/coupon_cap');
                var rand = new Date().getTime();
                $(".captchaImage").attr("src", url +'&' + rand);
                if (e) e.preventDefault();
            } 
            
            // 验证码刷新
            $(".captchaRefresh").click(function(e){
                captchaFefresh(e);
            });
        })
    })
})