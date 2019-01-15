var BusinessHallBinding = BusinessHallBinding || {};

/**表单提交验证 */
BusinessHallBinding.check_input_is_true = function(self) {
    var type = $(self).attr('name');

    if (type  == 'user_phone') {
        var phone = $(self).val();

        if(!(/^1[34578]\d{9}$/.test(phone))){ 
            return false; 
        }  else {
           return true; 
        }
    }

    if (type  == 'user_name') {
        var user_name = $(self).val();

        if (!isNaN(user_name)) {
            return false;
        }

        if(user_name.length < 2 || user_name.length > 20){ 
            return false; 
        }  else {
           return true;
        }
    }

    //
    if (type  == 'user_mac') {
        var user_mac = $(self).val();
        user_mac = user_mac.replace(/-/g, '');

        if(user_mac.length != 12){ 
            return false; 
        }  else {
           return true;
        }
    }

    if (type  == 'user_number') {
        var user_number = $(self).val();

        if(!(/^[0-9]{13}$/.test(user_number))){ 
        	console.log(1);
            return false; 
        }  else {
        	console.log(0);
           return true;
        }
    }
} 
BusinessHallBinding.check_input_apply = function(self, callback) {

    //显示红色文字
    if (BusinessHallBinding.check_input_is_true(self)) {
         $(self).parent('.check_btn').removeClass('item-danger');
    } else {
         $(self).parent('.check_btn').addClass('item-danger');
    }

    callback();
}

/**遮罩 */
BusinessHallBinding.mask_show = function() {
    $('.mask_show').show();
}

BusinessHallBinding.mask_hide = function() {
    $('.mask_show').hide();
}

$(function(){
    $('.other_apply_btn').click(function(){ 
        BusinessHallBinding.mask_show();
    })

    $('.mask_show').click(function(){ 
        BusinessHallBinding.mask_hide();
    })
    
    $('.check_btn > input').change(function(){
        var self = this;

        BusinessHallBinding.check_input_apply(self, function(){ 
            if (BusinessHallBinding.check_input_is_true($("input[name='user_number']")) &&
                BusinessHallBinding.check_input_is_true($("input[name='user_mac']")) &&
                BusinessHallBinding.check_input_is_true($("input[name='user_phone']")) &&
                BusinessHallBinding.check_input_is_true($("input[name='user_name']"))) 
            {
                $('.btn_submit').addClass('btn-primary');
                $('.btn_submit').removeClass('btn-disabled');
            } else {
                $('.btn_submit').addClass('btn-disabled');
                $('.btn_submit').removeClass('btn-primary');
            }
        })
    })

    $("input[name='user_phone']").keyup(function(){
        var value = $(this).val();

        if (value.length > 10) {
            //激活按钮
            $(this).change();
        }
    })

     $("input[name='user_name']").keyup(function(){
        var value = $(this).val();

        if (value.length > 2) {
            //激活按钮
            $(this).change();
        }
    })

    $("input[name='user_mac']").keyup(function(){
        var value = $(this).val();

        if (value.length > 11) {
            //激活按钮
            $(this).change();
        }
    })

    $("input[name='user_number']").keyup(function(){
        var value = $(this).val();

        if (value.length > 12) {
            //激活按钮
            $(this).change();
        }
    })

    $('.btn_submit').click(function() {
        if ($(this).hasClass('btn-disabled')) {
            return false;
        }

        var parmas = {};
        parmas.user_phone = $("input[name='user_phone']").val();
        parmas.user_number = $("input[name='user_number']").val();
        parmas.user_name = $("input[name='user_name']").val();
        parmas.user_mac = $("input[name='user_mac']").val();

        $.post(siteUrl + '/e/business_hall_binding/binding_apply_save', parmas ,function(json) {
            if (json.code == 100000) {
                window.location.href = siteUrl + '/e/business_hall_binding/binding_success';
                return;
            }

            if (json.code == 100020) {
                window.location.href = siteUrl + '/e/business_hall_binding/binding_apply_business&user_number='+json.msg;
                return;
            }
            
            if (json.code == 100030) {
                window.location.href = siteUrl + '/e/business_hall_binding/binding_success&type=3';
                return;
            }

            var type = json.show;
            $("input[name='"+ type +"']").parent('.check_btn').addClass('item-danger');
            $("." + type + "_text > span").text(json.msg);
        },'json')
    })
})