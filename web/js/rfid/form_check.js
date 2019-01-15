////三级联动
var phone_name    = '';
var phone_version = '';
var phone_color   = '';

$('.phone_name').change(function(){
    phone_name =  $(this).val();
    $.post( siteUrl + '/rfid/admin/ajax/get_phone_version' , { phone_name:phone_name } ,function(json){
        var html    = "<option value='0'>请选择型号</option>";
        if (json.msg == 'ok') {
            var jsonnum = eval(json.version_info);

            for(var i = 0; i < jsonnum.length; i ++){
                html += "<option value= '"+jsonnum[i]+"'>"+jsonnum[i]+"</option>";
            }

            $('.phone_version').html('').append(html);
            $('.phone_version').trigger('change');
        } else {
            $('.phone_version').html('').append(html);
            $('.phone_version').trigger('change');
        }
    },'json')
})

$('.phone_version').change(function(){
    phone_version = $(this).val();
    phone_name    = $('#brand').val();
    phone_color   = $('#color').val();
    $.post( siteUrl + '/rfid/admin/ajax/get_phone_color' , { phone_name:phone_name,phone_version:phone_version } ,function(json){

        var html    = "<option value='0'>请选择颜色</option>";
        if (json.msg=='ok') {
            var jsonnum = eval(json.color_info);

            for(var i=0; i< jsonnum.length; i++){
               html += "<option value= '"+jsonnum[i]+"'>"+jsonnum[i]+"</option>";
            }

            $('.phone_color').html('').append(html);
            $('.phone_color').trigger('change');
        }else {
            var html = "<option  value='0'>请选择颜色</option>";
            $('.phone_color').html('').append(html);
            $('.phone_color').trigger('change');
        }
   },'json')
})
////

var formCheck = formCheck || {};

info          = {};

formCheck.init = function()
{
    formCheck.tipBool        = true;

    formCheck.sign           = false;
    formCheck.url            = false;
    formCheck.check_label_id = false;
    formCheck.check_brand    = false;
    formCheck.check_type     = false;
    formCheck.check_color    = false;
    formCheck.check_imei     = false;
}

//开始就调用
formCheck.init();

formCheck.Fields = ['label','brand','type','color','imei'];

formCheck.checkInfo = function(type) {
    //获取值
    if (type == 'label') {
        var labelVal = $("#label_id").val();

        if (labelVal) {
            formCheck.check_label_id = true;
            info.label_id = labelVal;

            return true;
        } else {
            $('.error-tips').addClass('popup').append('<div class="inner">提示：标签ID未填写</div>');
            //清除提示
            formCheck.tipMsg(formCheck.sign , formCheck.url);

            return false;
        }
    }

    if (type == 'brand') {
        var brandVal = $("#brand").val();
        console.log(brandVal);
        if ( parseInt(brandVal) != 0) {
            formCheck.check_brand = true;
            info.name = brandVal;

            return true;
        } else {

            $('.error-tips').addClass('popup').append('<div class="inner">提示：请选择手机品牌</div>');
            //清除提示
            formCheck.tipMsg(formCheck.sign , formCheck.url);

            return false;
        }
    }

    if (type == 'type') {
        var typeVal = $("#type").val();

        if (parseInt(typeVal) != 0 ) {
            formCheck.check_type = true;
            info.version = typeVal;

            return true;
        }
        if (parseInt(typeVal) == 0 && formCheck.check_brand){
            $('.error-tips').addClass('popup').append('<div class="inner">提示：请选择手机型号</div>');
            //清除提示
            formCheck.tipMsg(formCheck.sign , formCheck.url);

            return false;
        }
    }

    if (type == 'color') {
        var colorVal = $("#color").val();

        if (parseInt(colorVal) != 0) {
            formCheck.check_color = true;
            info.color = colorVal;

            return true;
        } 
        if (parseInt(colorVal) == 0 && formCheck.check_type){
            $('.error-tips').addClass('popup').append('<div class="inner">提示：请选择手机颜色</div>');
            ////清除提示
            formCheck.tipMsg(formCheck.sign , formCheck.url);

            return false;
        }
    }

    if (type == 'imei') {
        var imeiVal = $("#imei").val();

        if (!imeiVal) {
            $('.error-tips').addClass('popup').append('<div class="inner">提示：请填写imei</div>');
            //清除提示
            formCheck.tipMsg(formCheck.sign , formCheck.url);

            return false;
        }

        if (!imeiVal || imeiVal.length !=6 || parseInt(imeiVal).toString().length != 6) {
            $('.error-tips').addClass('popup').append('<div class="inner">提示：imei为末六位的数字</div>');
            //清除提示
            formCheck.tipMsg(formCheck.sign , formCheck.url);

            return false;
        } else {
            formCheck.check_imei = true;
            info.imei =  imeiVal;

            return true;
        }
    }
}

formCheck.eachCheck = function(arrFields)
{
    for (var i = 0 ; i < arrFields.length; i++) {
        formCheck.tipBool = formCheck.checkInfo(arrFields[i]);

        if (!formCheck.tipBool) {
            break;
        }
    }
}

formCheck.tipMsg = function(sign , url)
{
    setTimeout(function(){
        $('.error-tips').removeClass('popup').html('');
        if (sign) {
            if (!url) {
                window.location.reload();
            } else {
                window.location.href = siteUrl + '/e/admin/rfid/phone';
            }
        }
    },1200);
}

//提交
$('.phone_save').on('click',function(){
	var letsgo = $(this);
    //初始化
    formCheck.init();

    //检查字段信息
    formCheck.eachCheck(formCheck.Fields);

    if (formCheck.check_label_id && formCheck.check_brand && formCheck.check_type && formCheck.check_color && formCheck.check_imei) {

           var resId = $('#label_id').attr('resId');

           if (resId) {
               info.resId = resId;
           }

           $.post(siteUrl + '/e/admin/rfid/ajax/phone_save' , info ,function(json) {
                if (json.msg =='ok') {
                    if (!resId) {
                        $('.error-tips').addClass('popup').append('<div class="inner"><img src="'+siteUrl+'/images/succ.png" alt="">添加成功</div>');
                        //清除提示
                        formCheck.sign = true;

                        if (letsgo.hasClass('save_tip')) {
                            formCheck.url  = true;
                        }
                        formCheck.tipMsg(formCheck.sign ,formCheck.url);
                    } else {
                        $('.error-tips').addClass('popup').append('<div class="inner"><img src="'+siteUrl+'/images/succ.png" alt="">编辑成功</div>');
                        //清除提示
                        formCheck.sign = true;
                        formCheck.url  = true;

                        formCheck.tipMsg(formCheck.sign,formCheck.url);
                    }
                    
                } else {
                    $('.error-tips').addClass('popup').append('<div class="inner">提示：'+json.data+'</div>');
                    //清除提示
                    formCheck.tipMsg(formCheck.sign ,formCheck.url);
                }
            },'json')
    } 
})