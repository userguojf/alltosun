//主框架程序
var stageWidth=window.innerWidth;
var stageHeight=window.innerHeight;

var awardCode;//兑换码

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
		window.location.href='map.html';
	});
	
});

function change(str) {
	document.getElementById("phone").value = str.replace(/\D/gi, "");
}



//调用接口把手机号传到后台
function sendPhone(_phone){
	$('#phone_btn').off('click');
	$('#award').hide();
	$('#watting').show();
	
	//将手机号发送给后台，返回兑换码，赋给awardCode，然后执行showCard
	awardCode='1111 1111 1111 1111';
	showCard();
}


//显示得到的兑换码
function showCard(){
	$('#award_card').html(awardCode);
	$('#watting').hide();
	$('#card').show();
	//myself
	$('#liuzi').hide();
	$('#eat2').hide();
	//点此重发短信按钮
	$('#resend').on('click',function(){
		resend();
	});
	
	//复制按钮
	/*$('#copy_btn').on('click',function(){
		copyToClipboard('2222222222');
	});*/
	
	
}


//重发短信
function resend(){
	alert('重发短信');
}


function GetQueryString(name)
{
     var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
     var r = window.location.search.substr(1).match(reg);
     if(r!=null)return  unescape(r[2]); return null;
}
