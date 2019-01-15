//主框架程序
var stageWidth=window.innerWidth;
var stageHeight=window.innerHeight;
var awardCode;//兑换码
var phone ;
var tel ;

$(function(){
	//输入手机号确认按钮
	$('#phone_btn').on('click',function(){
		//手机号验证
		var ValidPhone =new RegExp(/(^(13|15|17|18|14)(\d){9}$)|(^189\d{8}$)/);
		var phone = $("#phone").val();
		if(!ValidPhone.test(phone)){
			alert("请填写正确的手机号");
			return false;
		}
		
		sendPhone(phone);//调用接口把手机号传到后台
		
	});
	
	$('#yingyeting').on('click',function(){
		var mapurl = mapUrl + "/event/map.html";
		window.location.href = mapurl;
	});
	
});

function change(str) {
	document.getElementById("phone").value = str.replace(/\D/gi, "");
}



//调用接口把手机号传到后台
function sendPhone(phone){
	
	//将手机号发送给后台，返回兑换码，赋给awardCode，然后执行showCard
	//15097568909
	var url = siteUrl + 'apa';
	$.post( url , { phone:phone, 's':source }, function(json){
		
		if (json.info=='ok'){
			$('#phone_btn').off('click');
			$('#phone_btn').hide();
			$('#phone').hide();
			$('#watting').show();			
			
			code       = json.code;
			tel        = json.tel;
			
			awardCode  = json.code;
			showCard();
		} else if (json.info =='no'){
			alert(json.msg);
			 
		} else{
			alert(json.msg);
			awardCode = json.code;
			showCard();
		}
		
	},'json')
	
}


//显示得到的兑换码
function showCard(){
	$('#award_card').html(awardCode);
	$('#watting').hide();
	$('#liuzi').hide();
	$('#eat2').hide();
	$('#card').show();
	$('#pass1').css('height','700px');
	
	//点此重发短信按钮
	$('#resend').on('click',function(){
		resend();
	});
	
	
}


//重发短信
function resend(){
	
	var rehttp = siteUrl + 'resend';
	$.post( rehttp,{ tel:tel , code:code  } ,function(text){
		if (text.info == 'ok') {
			alert(text.msg);
		} else {
			alert(text.msg);
		}
		
		
	},'json')

}


function GetQueryString(name)
{
     var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
     var r = window.location.search.substr(1).match(reg);
     if(r!=null)return  unescape(r[2]); return null;
}
