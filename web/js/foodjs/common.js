<!--移动端版本兼容 -->
if(/Android (\d+\.\d+)/.test(navigator.userAgent)){
	var version = parseFloat(RegExp.$1);
	if(version>2.3){
		var phoneScale = parseInt(window.screen.width)/750;
		document.write('<meta name="viewport" content="width=750, minimum-scale = '+ phoneScale +', maximum-scale = '+ phoneScale +', target-densitydpi=device-dpi">');
	}else{
		document.write('<meta name="viewport" content="width=750, target-densitydpi=device-dpi">');
	}
}else{
	document.write('<meta name="viewport" content="width=750, user-scalable=no, target-densitydpi=device-dpi">');
}
<!--移动端版本兼容 end -->

//判断是用什么设备打开的
var isAndroid = navigator.userAgent.toLowerCase().match(/android/i) == "android";
var isSafari = (/iPhone/i.test(navigator.platform) || /iPod/i.test(navigator.platform) || /iPad/i.test(navigator.userAgent)) && !!navigator.appVersion.match(/(?:Version\/)([\w\._]+)/);

//musicWX是判断是否是用微信打开的
var ua = navigator.userAgent, musicWX = /MicroMessenger/i.test(ua), ios = /ip(?=od|ad|hone)/i.test(ua);


<!----------------------------------------------判断横竖屏,isH为true时为横屏时提示(默认)，为false时为竖屏时提示------------------------->
setVH(true);
var isH;
function setVH(__isH){
	if(__isH==null){
		isH=true;
	}else{
		isH=__isH;
	}
	
	var hDiv;
	
	if(isH==true){//横屏时提示把屏竖过来
		hDiv = document.createElement('div');
		hDiv.id='landscape';
		hDiv.className='horizontal';
		document.body.appendChild(hDiv);
	}else{
		hDiv = document.createElement('div');
		hDiv.id='landscape';
		hDiv.className='vertical';
		document.body.appendChild(hDiv);
	}
}

//判断横屏
var initFlag = true;
var landscape = false;
function orien() {
	//alert(window.orientation);
	if (window.orientation == 90 || window.orientation == -90) {
		//alert("这是横屏");
		if(isH==true){
			if(initFlag) {
				initFlag = false;
				landscape = true;
			}
			$("#landscape").show();
		}else{
			if(initFlag) {
				initFlag = false;
			}
			if(landscape) {
				window.location.reload(true);
			} else {
				$("#landscape").hide();
			}
		}
	} else {
		if(isH==true){
			if(initFlag) {
				initFlag = false;
			}
			if(landscape) {
				window.location.reload(true);
			} else {
				$("#landscape").hide();
			}
		}else{
			if(initFlag) {
				initFlag = false;
				landscape = true;
			}
			$("#landscape").show();
			alert(isH);
		}
		//alert("这是竖屏");
	}
};

orien();
$(window).on("orientationchange", orien);
<!---------------------------------------------------------------------判断横竖屏 end----------------------------------------------->




