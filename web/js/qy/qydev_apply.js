//提交
var QydevApply = QydevApply || {};

/**表单提交验证 */
QydevApply.check_business_hall_title = false;
QydevApply.check_user_number         = false;
QydevApply.check_username            = false;
QydevApply.check_phone               = false;
QydevApply.check_goods               = false;
var info = {};

QydevApply.form_check = function(self) { //,callback
    //检查check_user_number
    var type = $(self).attr('name');

    if (type  == 'business_hall_title') {
        info.business_hall_title = $(self).val();

        if(!info.business_hall_title){
            QydevApply.errorTips(self ,type);

            QydevApply.check_business_hall_title = false;

            return false; 
        } 
        if (!isNaN(info.business_hall_title)) {
            QydevApply.errorTips(self ,type);
            QydevApply.check_business_hall_title = false;

            return false;
        } else {
        	$.post(siteUrl + '/e/qydev_apply/check_yyt_title' , { title:info.business_hall_title } ,function(json) {
                if (json.msg =='ok') {
                    QydevApply.cancleErrorTips(self , type);
                    QydevApply.check_business_hall_title = true;
                } else {
                    QydevApply.errorTips(self ,type);
                    QydevApply.check_business_hall_title = false;
                }
            },'json');

            return true;
        }
    }

    if (type  == 'user_number') {
    	info.user_number = $(self).val();
         if(!info.user_number){
             QydevApply.errorTips(self ,type);
             QydevApply.check_user_number = false;

             return false; 
         }
         if (13 == info.user_number.length) {
             $.post(siteUrl + '/e/qydev_apply/check_user_number' , { user_number:info.user_number } ,function(json) {
                    if (json.msg =='ok') {
                        QydevApply.cancleErrorTips(self , type);
                        QydevApply.check_user_number = true;
                    } else {
                        QydevApply.errorTips(self ,type);
                        QydevApply.check_user_number = false;

                    }
                },'json');
             return true;
         } else {
             QydevApply.errorTips(self ,type);
             QydevApply.check_user_number = false;

             return false;
         }
    }

    if (type  == 'username') {
    	info.username = $(self).val();

    	if(!info.username){
            QydevApply.errorTips(self ,type);
            QydevApply.check_username = false;

            return false; 
        }
        if (!isNaN(info.username)) {
            QydevApply.errorTips(self , type)
            QydevApply.check_username = false;

            return false;
        } else {
            QydevApply.cancleErrorTips(self , type);
            QydevApply.check_username = true;

            return true;
        }

    }

    if (type  == 'phone') {
    	info.phone = $(self).val();

        if(!(/^1[34578]\d{9}$/.test(info.phone))){
            QydevApply.errorTips(self , type);
            QydevApply.check_phone = false;

            return false; 
        } else {
             QydevApply.cancleErrorTips(self , type);
             QydevApply.check_phone = true;

             return true;
        }
    }

//    callback();
}
//错误提示
QydevApply.errorTips = function( self , name ){
        $(self).parent().parent('.check_btn').addClass('tips-danger');

        if (name == 'business_hall_title') {
            $('.yyt_error_tips').show();
        } else if (name == 'user_number') {
            $('.un_error_tips').show();
        } else if (name == 'username') {
            $('.name_error_tips').show();
        } else if (name == 'phone') {
            //*手机号重复
            $('.phone_error_tips').show();
        } 
}
//去掉单个错误提示
QydevApply.cancleErrorTips = function( self , name ){
    $(self).parent().parent('.check_btn').removeClass('tips-danger');

    if (name == 'business_hall_title') {
        $('.yyt_error_tips').hide();
    } else if (name == 'user_number') {
        $('.un_error_tips').hide();
    } else if (name == 'username') {
        $('.name_error_tips').hide();
    } else if (name == 'phone') {
        $('.phone_error_tips').hide();
    } 
}
//去掉全部的错误提示
QydevApply.cancleAllErrorTips = function(){
	$('.check_btn').removeClass('tips-danger');
	$('.tips').hide();
	
	return true;
}
//验证的表单数组
QydevApply.checkBlurArr = ['business_hall_title' ,'user_number','username','phone'];
QydevApply.checkKeyUpArr = ['business_hall_title' ,'username'];

QydevApply.checkFormArr = function(){
    for(var i = 0; i < QydevApply.checkBlurArr.length; i++)
    { 
        var blurObj = "."+QydevApply.checkBlurArr[i];
        $(blurObj).blur(function(){
            var self = this;
            QydevApply.form_check(self);
        }) 
    }
    for(var i = 0; i < QydevApply.checkKeyUpArr.length; i++)
    { 
        var keyUpObj = "."+QydevApply.checkKeyUpArr[i];
        $(keyUpObj).keyup(function(){
            var self = this;
            QydevApply.form_check(self);
        }) 
    }
}
//复选框
QydevApply.checkBoxfunction = function(){
    var obj = document.getElementsByName("goods");
    info.check_box = [];
    for(var k in obj){
        if(obj[k].checked) {
        	info.check_box.push(obj[k].value);
        }
        console.log('开始');
        console.log(k);	
        console.log(obj[k].checked);
        console.log(obj[k].value);
        console.log('结束');
        
    }
}

QydevApply.checkFormArr();



    $('._btn_submit').click(function() {

    	var result = true;
    	var i = 0;
    	for(i = 0; i < QydevApply.checkBlurArr.length; i++)
        {
            var blurObj = "."+QydevApply.checkBlurArr[i];
            result = QydevApply.form_check(blurObj);

            if (!result) {
            	break;
            }
        }
    	console.log(QydevApply.check_business_hall_title);
    	
    	if (!QydevApply.check_business_hall_title ||
	    	!QydevApply.check_user_number ||
	    	!QydevApply.check_username ||
	    	!QydevApply.check_phone
	    	) 
    	{
    		return false;
    	}
    	
    	//成功去掉错误的提示
    	QydevApply.cancleAllErrorTips();
    	
    	//查看复选框的情况
    	QydevApply.checkBoxfunction();

    	//符合条件保存
        $.post(siteUrl + '/e/qydev_apply/apply_save' , info ,function(json) {
            if (json.msg =='ok') {
            	$('.pop-success').show();
            } else {
            	alert(json.data);
            }
        },'json')
    })
    
//关闭浮层
    $('.close').on('click',function (){
    	$('.pop-success').hide();
//    	window.location.reload();
    	
    })