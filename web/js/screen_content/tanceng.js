var raidoVal = selectVal = '';
var selectId = 0;

$(".btn_url").on('click', function(){
	window.location.href=siteUrl + '/screen_content_new/admin/add';
});

$(".upload_tanceng").on('click', function(){
	$('.upload_choice').show()
});
//radio 取消选择
btnCancle('upload_choice_concel', 'upload_choice');
btnCancle('make_pic_cancle', 'make_pic');

//7图返回选择
$('.7_pic_return').on('click', function(){
	$('.7_pic').hide();
	$('.7_pic_choice').hide();
	$('.upload_choice').show();
})
$('.7_pic_close').on('click', function(){
	$('.7_pic').hide();
	$('.7_pic_choice').hide();
})
//7图下一步
$('.7_pic_next').on('click', function(){
	if (!selectVal) {
		alert('请选择一个机型宣传图模板');
		return '';
	}

	$('.7_pic').hide();
	$('.7_pic_choice').hide();

	var src = siteUrl + '/upload' + selectVal	;
	$('.make_pic_src').attr('src', src);
	$('.make_pic').show();
})

//7图的点击选择
$('.ul_next').on('click', '.selcet_7_pic', function(){
	if (!$(this).hasClass('active')) {
		$(this).addClass('active');
		selectVal = $(this).attr('srcval');
		selectId = $(this).attr('useid');
	};
	$(this).siblings().removeClass("active");
})

//radio的下一步
$(".upload_result").on('click', function(){
	raidoVal = $('input:radio[name="radio_choice"]:checked').val();;
	// 下一步 自身隐藏
	$('.upload_choice').hide();

	var value = parseInt(raidoVal);
	if ( value == 1 ) {
//		$('.make_pic').show();
		$('.7_pic').show();
		$('.7_pic_choice').show();
	} else if ( value == 2) {
		window.location.href=siteUrl + '/screen_content_new/admin/add';
	}
});

// 制作图片点击保存
$(".make_pic_save").on('click', function(){
	window.location.href = siteUrl + '/screen_content_new/admin/add?model_id='+selectId + '&src=' + selectVal;
})

//使用此背景按钮
$(".use_pic").on('click', function(){
	selectVal = $(this).attr('srcval');
	selectId = $(this).attr('useid');

	var src = siteUrl + '/upload' + selectVal;
	$('.make_pic_src').attr('src', src);
	$('.make_pic').show();
});



//点击取消函数
function btnCancle(click_class, cancel_class)
{
	var click_class = '.' + click_class;
	var cancel_class = '.' + cancel_class;
	$(click_class).on('click', function(){
		$(cancel_class).hide();
	});
}


// 上传文件的预览
//$(".js_perUpArea").click(function(){
//  
//  $(this).closest('.js_perUpWrap').find('.js_perUpFile').trigger('click');
//  
//});

$(".js_perUpFile").each(function(i){
  $(this).change(function(e){
      handleFileSelect($(this), function (data) {

    	  $.post(siteUrl + '/screen_content_new/admin/ajax/upload_file',{ data:data },function(json){
              if (json.errcode == 'ok') {
            	  var html = '<li class="selcet_7_pic" srcval="'+ json.srcval +'" useid='+ json.use_id+'>';
     			 html += '<div class="pic"><img src="'+data+'"></div>';
     			 html += '<div class="btn-radio"></div>';
     			 html += '</li>';

     			 $('.li_next').after(html);
     			 var num = $('.selcet_7_pic').length;
     			 console.log(num)
     			$(".selcet_7_pic").eq(7).remove(); 
              } else {
                  alert(json.errmsg);
              }
          },'json')
			 
      });
  });
});
/////////////////////////////////////

//图片上传预览    IE是用了滤镜。

function previewImage(file,obj,pic,width,height)
{
    var MAXWIDTH  = width;
    var MAXHEIGHT = height;

    //var div = document.getElementById('preview');
    var div = obj;

    if (file.files && file.files[0])
    {
        div.innerHTML ='<img id='+pic+' width="375px;">';
        var img = document.getElementById(pic);
        //console.log(img);
        img.onload = function(){
            var rect = clacImgZoomParam(MAXWIDTH, MAXHEIGHT, img.offsetWidth, img.offsetHeight);
            img.width  =  rect.width;
            img.height =  rect.height;

        };

        var reader = new FileReader();
        reader.onload = function(evt){ img.src = evt.target.result; };
        //console.log(file.files[0]);
        reader.readAsDataURL(file.files[0]);
    }
    else //兼容IE
    {
        var sFilter='filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(sizingMethod=scale,src="';
        file.select();
        var src = document.selection.createRange().text;
        div.innerHTML = '<img id=imghead>';
        var img = document.getElementById('imghead');
        img.filters.item('DXImageTransform.Microsoft.AlphaImageLoader').src = src;
        var rect = clacImgZoomParam(MAXWIDTH, MAXHEIGHT, img.offsetWidth, img.offsetHeight);
        status =('rect:'+rect.top+','+rect.left+','+rect.width+','+rect.height);                        //"+rect.top+"
        div.innerHTML = "<div id=divhead style='width:"+rect.width+"px;height:"+rect.height+"px;margin-top:0px;"+sFilter+src+"\"'></div>";

    }
}

function clacImgZoomParam( maxWidth, maxHeight, width, height ){
    var param = { top:0, left:0, width:width, height:height };
    if( width>maxWidth || height>maxHeight )
    {
        rateWidth = width / maxWidth;
        rateHeight = height / maxHeight;
         
        if( rateWidth > rateHeight )
        {
            param.width =  maxWidth;
            param.height = Math.round(height / rateWidth);
        }else
        {
            param.width = Math.round(width / rateHeight);
            param.height = maxHeight;
        }
    }

    param.left = Math.round((maxWidth - param.width) / 2);
    param.top = Math.round((maxHeight - param.height) / 2);
    return param;
}