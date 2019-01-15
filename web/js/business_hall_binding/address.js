//三级联动
$('#province').change(function(){
    var province_id ='';
    province_id     =  $(this).val(); 

     $.post(siteUrl + '/api/ajax/get_city_name', { province_id:province_id } ,function(json){
        if (json.msg=='ok') {
            var html = "<option value='0'>请选择所属市</option>";
                var jsonnum = eval(json.city_info);

                for(var i=0; i< jsonnum.length; i++){
                    html += "<option value= '"+jsonnum[i].id+"'>"+jsonnum[i].name+"</option>";
                }

                $('#city').html('').append(html);
                $('#city').trigger('change');
        }
     },'json')
})

$('#city').change(function(){
	var city_id = '';
    city_id     =  $(this).val();

    $.post( siteUrl + '/api/ajax/get_area_name', { city_id:city_id } ,function(json){

        if (json.msg=='ok') {
            var html = "<option value='0'>请选择所属地区</option>";
            var jsonnum = eval(json.area_info);

            for(var i=0; i< jsonnum.length; i++){
                html += "<option value= '"+jsonnum[i].id+"'>"+jsonnum[i].name+"</option>";
            }

            $('#area').html('').append(html);
            $('#area').trigger('change');

        } else {
            var html = "<option  value='0'>请选择所属地区</option>";
            $('#area').html('').append(html);
            $('#area').trigger('change');
        }
    },'json')
})
//提交
var BusinessHallBindingCheck = BusinessHallBindingCheck || {};

/**表单提交验证 */
BusinessHallBindingCheck.check_business_hall_title = false;
BusinessHallBindingCheck.check_province_id         = false;
BusinessHallBindingCheck.check_city_id             = false;
BusinessHallBindingCheck.check_area_id             = false;
BusinessHallBindingCheck.check_address             = false;

BusinessHallBindingCheck.form_check = function(self, callback) {
    //检查check_user_number
    var type = $(self).attr('name');

    if (type  == 'business_hall_title') {
        var business_hall_title = $(self).val();

        if(!business_hall_title){  
            $(self).parent('.check_btn').addClass('item-danger');

            BusinessHallBindingCheck.check_business_hall_title = false;

            $('._btn_submit').addClass('btn-disabled');
            $('._btn_submit').removeClass('btn-primary');
            return false; 
        } else {
           if (!isNaN(business_hall_title)) {
               $(self).parent('.check_btn').addClass('item-danger');

               BusinessHallBindingCheck.check_business_hall_title = false;
               
               $('._btn_submit').addClass('btn-disabled');
               $('._btn_submit').removeClass('btn-primary');
           } else {
               
               BusinessHallBindingCheck.check_business_hall_title = true;

               $(self).parent('.check_btn').removeClass('item-danger');
            
           }
           
       }
    }

    if (type  == 'province_id') {
        var province_id = $(self).val();

        if(parseInt(province_id) == 0){ 
            //$(self).parent('.check_btn').addClass('item-danger');

            BusinessHallBindingCheck.check_province_id = false;

            return false; 
        } else {

            BusinessHallBindingCheck.check_province_id = true;

            $('._btn_submit').addClass('btn-disabled');
            $('._btn_submit').removeClass('btn-primary');
            $(self).parent('.check_btn').removeClass('item-danger');
        }
    }

    if (type  == 'city_id') {
        var city_id = $(self).val();
        
        if(parseInt(city_id) == 0){
            //$(self).parent('.check_btn').addClass('item-danger');

            BusinessHallBindingCheck.check_city_id = false;

            $('._btn_submit').addClass('btn-disabled');
            $('._btn_submit').removeClass('btn-primary');
            return false; 
        } else {
            BusinessHallBindingCheck.check_city_id = true;

            $(self).parent('.check_btn').removeClass('item-danger');
        }
    }

    if (type  == 'area_id') {
        var area_id = $(self).val();
        
        if(parseInt(area_id) == 0){ 
            //$(self).parent('.check_btn').addClass('item-danger');

            BusinessHallBindingCheck.check_area_id = false;

            $('._btn_submit').addClass('btn-disabled');
            $('._btn_submit').removeClass('btn-primary');
            return false; 
        } else {
            BusinessHallBindingCheck.check_area_id = true;

            $(self).parent('.check_btn').removeClass('item-danger');
        }
    }

    if (type  == 'address') {
        var address = $(self).val();

        if(!address){ 
            $(self).parent('.check_btn').addClass('item-danger');

            BusinessHallBindingCheck.check_address = false;

            $('._btn_submit').addClass('btn-disabled');
            $('._btn_submit').removeClass('btn-primary');
            return false; 
        } else {
            if (!isNaN(address)) {
                $(self).parent('.check_btn').addClass('item-danger');

                BusinessHallBindingCheck.check_address = false;
                $('._btn_submit').addClass('btn-disabled');
                $('._btn_submit').removeClass('btn-primary');
                
            } else {
                BusinessHallBindingCheck.check_address = true;

                $(self).parent('.check_btn').removeClass('item-danger');
            }
            
        }
    }

    callback();
}

    $('.form_input').blur(function(){
        var self = this;
        BusinessHallBindingCheck.form_check(self, function(){ 
            if (BusinessHallBindingCheck.check_business_hall_title  &&
                BusinessHallBindingCheck.check_province_id          &&
                BusinessHallBindingCheck.check_city_id              &&
                BusinessHallBindingCheck.check_area_id              &&
                BusinessHallBindingCheck.check_address
                ) 
            {
                $('._btn_submit').addClass('btn-primary');
                $('._btn_submit').removeClass('btn-disabled');
            }
        })
    })

    $('.form_select_pro').change(function(){
    	var value = $(this).val();
console.log(value);
        var self = this;
        BusinessHallBindingCheck.form_check(self, function(){ 
            if (value != 0 && BusinessHallBindingCheck.check_business_hall_title  &&
                BusinessHallBindingCheck.check_province_id          &&
                BusinessHallBindingCheck.check_city_id              &&
                BusinessHallBindingCheck.check_area_id              &&
                BusinessHallBindingCheck.check_address
                ) 
            {
                $('._btn_submit').addClass('btn-primary');
                $('._btn_submit').removeClass('btn-disabled');
            }
        })
    }) 

    $('.form_select_city').change(function(){
		var value = $(this).val();
		console.log(value);
		        var self = this;
		        BusinessHallBindingCheck.form_check(self, function(){ 
		            if (value != 0 && BusinessHallBindingCheck.check_business_hall_title  &&
		                BusinessHallBindingCheck.check_province_id          &&
		                BusinessHallBindingCheck.check_city_id              &&
		                BusinessHallBindingCheck.check_area_id              &&
		                BusinessHallBindingCheck.check_address
		                ) 
		            {
		                $('._btn_submit').addClass('btn-primary');
		                $('._btn_submit').removeClass('btn-disabled');
		            }
		        })
    })

    $('.form_select_area').change(function(){
        var value = $(this).val();
console.log(value);
        var self = this;
        BusinessHallBindingCheck.form_check(self, function(){ 
            if (value != 0 && BusinessHallBindingCheck.check_business_hall_title  &&
                BusinessHallBindingCheck.check_province_id          &&
                BusinessHallBindingCheck.check_city_id              &&
                BusinessHallBindingCheck.check_area_id              &&
                BusinessHallBindingCheck.check_address
                ) 
            {
                $('._btn_submit').addClass('btn-primary');
                $('._btn_submit').removeClass('btn-disabled');
            } else {
                $('._btn_submit').addClass('btn-disabled');
                $('._btn_submit').removeClass('btn-primary');
            }
        })
    })
    //激活按钮
    $("#address").keyup(function(){
    	
        var value = $(this).val();
        //地址也至少写两个字
//        if (value > 2 ) {
//            //激活按钮
//            $('._btn_submit').addClass('btn-primary');
//            $('._btn_submit').removeClass('btn-disabled');
//        }
        var self = this;
        BusinessHallBindingCheck.form_check(self, function(){ 
            if ( value && BusinessHallBindingCheck.check_business_hall_title  &&
                BusinessHallBindingCheck.check_province_id          &&
                BusinessHallBindingCheck.check_city_id              &&
                BusinessHallBindingCheck.check_area_id              &&
                BusinessHallBindingCheck.check_address
                ) 
            {
                $('._btn_submit').addClass('btn-primary');
                $('._btn_submit').removeClass('btn-disabled');
            }
        })
    } )

    $("#business_hall_title").keyup(function(){
        var value = $(this).val();
        //地址也至少写两个字
//        if (value > 1 ) {
//            //激活按钮
//            $('._btn_submit').addClass('btn-primary');
//            $('._btn_submit').removeClass('btn-disabled');
//        }
        var self = this;
        BusinessHallBindingCheck.form_check(self, function(){ 
            if ( value && BusinessHallBindingCheck.check_business_hall_title  &&
                BusinessHallBindingCheck.check_province_id          &&
                BusinessHallBindingCheck.check_city_id              &&
                BusinessHallBindingCheck.check_area_id              &&
                BusinessHallBindingCheck.check_address
                ) 
            {
                $('._btn_submit').addClass('btn-primary');
                $('._btn_submit').removeClass('btn-disabled');
            }
        })
    } )

    $('._btn_submit').click(function() {
        if ($(this).hasClass('btn-disabled')) {
            return false;
        }

        var info = {};

        //判断营业厅填写
        info.business_hall_title = $("#business_hall_title").val();

        if(!info.business_hall_title ){ 
            $('#business_hall_title').parent('.check_btn').addClass('item-danger');

            BusinessHallBindingCheck.check_business_hall_title = false;

            return false; 
        }

      //判断选择省
        info.province_id         = $("#province").val();
        
        if(info.province_id == 0){ 
            $('#province').parent('.check_btn').addClass('item-danger');

            BusinessHallBindingCheck.check_province_id = false;

            return false; 
        } else {

            BusinessHallBindingCheck.check_province_id = true;

            $(self).parent('.check_btn').removeClass('item-danger');
        }

        //判断选择市
        info.city_id             = $("#city").val();

        if(info.city_id == 0){ 
            $('#city').parent('.check_btn').addClass('item-danger');

            BusinessHallBindingCheck.check_city_id = false;

            return false; 
        } else {

            BusinessHallBindingCheck.check_city_id = true;

            $(self).parent('.check_btn').removeClass('item-danger');
        }

      //判断选择区
        info.area_id             = $("#area").val();

        if(info.area_id ==0){ 
            $('#area').parent('.check_btn').addClass('item-danger');

            BusinessHallBindingCheck.check_area_id = false;

            return false; 
        } else {

            BusinessHallBindingCheck.check_area_id = true;

            $(self).parent('.check_btn').removeClass('item-danger');
        }

      //判断详细地址填写
        info.address             = $("#address").val();

        if(info.address ==0){ 
            $('#address').parent('.check_btn').addClass('item-danger');

            BusinessHallBindingCheck.address = false;

            return false; 
        }

        //已经提供好的数据
        info.user_number = user_number;

        //符合条件保存
        $.post(siteUrl + '/e/business_hall_binding/address_save' , info ,function(json) {
            if (json.msg =='ok') {
                window.location.href = siteUrl + '/e/business_hall_binding/binding_success?type=2';
            } else {
                alert(json.data);
            }
        },'json')
    })