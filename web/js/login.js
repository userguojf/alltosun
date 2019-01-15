$(function(){
  $('.btn-primary').click(function(e){
    var $this = $(this);
    e.preventDefault();
    var userName = $.trim($('#username').val());

    if (!userName) {
        $('.done').hide();
        $('.ecode').hide();
        $('.ename').show();
        $('.epass').hide();
        return;
    }

    var password = $.trim($('#password').val());
    if (!password) {
        $('.done').hide();
        $('.ecode').hide();
        $('.ename').hide();
        $('.epass').show();
        return;
    }

     var vcode = $.trim($('#vcode').val());
     if (!vcode) {
         $('.done').hide();
         $('.ecode').show();
         $('.ename').hide();
         $('.epass').hide();
         return;
     }

     password = $.base64().encode(userName+password);

     $.post( url ,{ username:userName,password:password,vcode:vcode,is_complex:true },function(json){
         if (json.info=='3') {
            $('.empty').hide();
            $('.ucode').show();
            $('.uname').hide();
            $('.upass').hide();
            $('.locked').hide();
        } else if (json.info=='1') {
            $('.empty').hide();
            $('.uname').show();
            $('.upass').hide();
            $('.ucode').hide();
            $('.locked').hide();
        } else if (json.info=='2') {
            $('.empty').hide();
            $('.upass').show();
            $('.uname').hide();
            $('.ucode').hide();
            $('.locked').hide();
        } else if (json.info=='ok') {
            $('.done').hide();
            $('.locked').hide();
            $('.empty').hide();
            var frm = $this.closest('form');
            frm.submit();
        } else if (json.info=='error') {
             $('.empty').hide();
             $('.upass').hide();
             $('.uname').hide();
             $('.ucode').hide();
             $('.locked').text(json.msg);
             $('.locked').show();
        }},'json')
});
var captchaFefresh = function(e){
  var rand = new Date().getTime();
    $(".captchaImage").attr("src", captchaUrl +'&' + rand);
    if (e) e.preventDefault();
};

// 验证码刷新
$(".captchaImage").click(function(e){
    captchaFefresh(e);
});
})