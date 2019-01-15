 
    console.log('alltosun');
    var siteUrl = "{$smarty.const.STATIC_URL}";
    var color_type  = '{$content_info.font_color_type}';
    var version_list = [];
    var name_list = [];
    var all_checked = '{$all_checked}';
    var lock     = false;
    var startTime, endTime;
    
    $(document).ready(function() { 
    	var default_type = $('.put_phone_default').html();
    	$('.toufang_flag').val(default_type);
    	var youfang = $('.toufang_flag').val();
    	
    	console.log(default_type);
    	console.log(youfang);
}); 
    $(function(){
       //提交验证
    	 $('.final_btn').click(function(){
		 var default_type = $('.put_phone_default').html();
	     var check_default_type = $('.toufang_flag').val();
	     
          //1 为专属
          var is_specify = $('.phone_hidden').val();
          /* var is_specify = $('.phone_hidden').val();
          
          $('.toufang_flag').val(); */
          //4为机型
          var put_type = $('.put_type_hidden').val();
           
          var length = $('.selectInputversion:checked').length;
          
          if(is_specify == 1 && put_type == 4){
              if(length == 1){
                  $("#ufrm").submit();
                 
              }else{
                  alert('请选择专属机型');
                  return false;
              }
          }
          /* if($.trim(default_type)==$.trim(check_default_type)){
        	  $('.put_flage').val(1);
        	} */
         
          $("#ufrm").submit();
          
      });
       
     
  $('#selectDateBegin input').change(function(e){

    startTime = e.target.value;
    $('.js_timeStart').val(startTime);
    $('.statr_time').html(startTime);
    $(this).siblings('span').css({ opacity: 1 })
    //$(this).css({ opacity:1 })
  })
         
    $('#selectDateEnd input').change(function(e){

    endTime = e.target.value
    console.log(e)
    $('.js_endStart').val(endTime);
    $('.end_time').html(endTime);
    $(this).siblings('span').css({ opacity: 1 })
    //$(this).css({ opacity:1 })
    setTimeout(function(){
        if(startTime>endTime) alert("结束时间必须大于开始时间")
    },300)  
  })
     
  //返回
  $('.go_back').on('click',function(){
      $(this).parent().parent().addClass('hidden');
      $('.addmine').removeClass('hidden');
  });
  
})
    $('.put_btn_phone').on('click',function(){
        //初始化
        if(all_checked){
            $('.append_version').html('');
            $('.append_name').html('');
        }
            $('.append_all').html('');
        
          
        //手机型号列表
        var is_checked = $('.selectInputBusinessHall').attr('checked');
        var phone_version_list = $('.selectInputversion:checked');
        var phone_name_list = $('.selectInputname:checked');
        var length = $('.selectInputversion:checked').length;
        var html4 = '';
        if (phone_version_list.length == 0) {
            alert('请选择投放机型！');
            return false;
        }
        var html = '';
        var html2 = '';
        var html3 = '';
        if (is_checked == 'checked') {
            html +='<input type ="hidden" name="is_all" value="1">';
            $('.put_phone_edit_defaule').html('全部机型');
            $('.append_all').append(html);
        }else{
        //append_version
        $.each( phone_version_list, function(i, n){
            //$(n).next('.selectInputname').attr('checked', 'checked');
            version_list[i] = $(n).val();
            
            
            html +='<input type ="hidden" name="phone['+i+'][version]" value="'+version_list[i]+'">';
            });
           $('.append_version').append(html);
           
           $.each( phone_name_list, function(i, n){
               name_list[i] = $(n).val();
               html2 +='<input type ="checked"  name="phone['+i+'][name]" value="'+name_list[i]+'" style="display:none;">';
               });
              $('.append_name').append(html2); 
         //区分
              html3 +='<input type ="hidden" name="is_all" value="0">';
              $('.append_all').append(html3);
              html4 = length+'种机型';
              $('.put_phone_default').html('');
              $('.put_phone_edit_defaule').html(html4);
        }
    });
    
 
    
   ///////////////////////////////////////////////////////////// 
   ////////////////////宣传图颜色////////////////////////////////
    //机型宣传图选择颜色页面显示
    $(".jsColorType").click(function(){
        $('.addmine').addClass('hidden');
        $('.color_select').removeClass('hidden');
   })
   //颜色选择成功后添加主界面显示
   $('.btn_color').click(function(){
        var color_type = $('.jsColorSelect ul li input[type=radio]:checked').val();
        $('.color_hidden').val(color_type);
        if(color_type == 2){
            $('.color_default').html('白色');
        }else{
            $('.color_default').html('黑色');
        }
        $('.addmine').removeClass('hidden');
        $('.color_select').addClass('hidden');
    });
   ///////////////////////////////////////////////////////////////
   
   ///////////////////////////////////////////////////////////// 
   ////////////////////宣传图专属机型////////////////////////////////
    //机型宣传图选择颜色页面显示
    $(".jsIsSpecify").click(function(){
        $('.addmine').addClass('hidden');
        $('.phone_version').removeClass('hidden');
   })
  
   ///////////////////////////////////////////////////////////////
    ////////////////////关闭弹层////////////////////////////////
   
   //颜色选择成功后添加主界面显示
    $('.btn_phone').click(function(){
       var phone_type = $('.jsPhoneSelect ul li input[type=radio]:checked').val();
       $('.phone_hidden').val(phone_type);
       if(phone_type == 1){
           $('.phone_default').html('是');
       }else{
           $('.phone_default').html('否');
       }
        $('.addmine').removeClass('hidden');
        $('.phone_version').addClass('hidden');
    });
   ///////////////////////////////////////////////////////////////
   
   
   ////////////////////投放机型////////////////////////////////
   //机型宣传图选择颜色页面显示
  
  
   $(".put_phone_default").click(function(){
       var html ="";
       var html2 ="";
       var id =  $(this).attr('content_id');
       $('.addmine').addClass('hidden');
       $('.put_phone_version_model').removeClass('hidden');
           var isChecked = $('.selectversionAll').attr('checked');
           if (isChecked == 'checked') {
               $('.selectInputversion').attr('checked','checked');
               $('.selectInputname').attr('checked','checked');
               $('.selectInputversion').attr('disabled','disabled');
           }
  })
  //颜色选择成功后添加主界面显示
  $('.put_btn_phone').click(function(){
      
       $('.addmine').removeClass('hidden');
       $('.put_phone_version_model').addClass('hidden');
   });
  ///////////////////////////////////////////////////////////////
  ///////////////////////////////////////////////////////////////
        //全选反选
  $('.selectversionAll').on('click',function(){ 
            var isChecked = $(this).attr('checked');
            if (isChecked == 'checked') {
                $('.selectInputversion').attr('checked','checked');
                $('.selectInputname').attr('checked','checked');
                $('.selectInputversion').attr('disabled','disabled');
            } else {
                $('.selectInputversion').removeAttr('checked');
                $('.selectInputname').removeAttr('checked');
                $('.selectInputversion').removeAttr('disabled');

            }
        });
  
  $('.selectInputversion').on('click',function(){ 
      var isChecked = $(this).attr('checked');
      var length = $('.selectInputversion:checked').length;
      var all_length =$('.put_phone_version_model').find('.selectInputversion').length;
      if(length == all_length){
          $('.selectversionAll').attr('checked','checked'); 
      }
      if (isChecked == 'checked') {
          $('.append_name').html('');
          $(this).attr("checked", 'checked');
          $(this).parent().parent().find(".selectInputname").attr("checked", 'checked');
      } else {
          $('.append_name').html('');
          $(this).removeAttr('checked');
          $(this).parent().parent().find(".selectInputname").removeAttr("checked");
      }
  });
///////////////////////////////////////////////
    </script>
    <script>
    var contentId = '{$content_info.id}';
    $(function(){

      var oldImgLink = "{$content_info.type}" == 1 ? "{$content_info.link}" : '';
      var oldVideoLink = "{$content_info.type}" == 2 ? "{$content_info.link}" : '';
      
      var content_info_type = "{$content_info.type}";
      
      
      //选择类型
      $('.choose_type').on('click',function(){
        $('.addmine').addClass('hidden');
        $('.put_type').removeClass('hidden');
           
    });
     
    
     //类型选择成功后添加主界面显示
 $('.btn_put_type').click(function(){
     var type = $('.jsPuttypeSelect ul li input[type=radio]:checked').val();
     $('.put_type_hidden').val(type);
     if(type == 1){
         $('.put_type_default').html('图片');
     }else if(type == 2){
         $('.put_type_default').html('视频');
     }else if(type == 3){
         $('.put_type_default').html('链接');
     }else if(type == 4){
         $('.put_type_default').html('机型宣传图');
     }else{
         $('.put_type_default').html('套餐图');
     }
      Chosetype(type);
      $('.addmine').removeClass('hidden');
      $('.put_type').addClass('hidden');
  });
     
      function Chosetype(type){
        //视频初始化
        initVideo();
        //url初始化
        initUrl();
        //宣传图初始化
        initType4();
        //图片初始化
        initImage();
      //套餐图初始化
        initSetMealImg();
        //图片
        if (type == 1 || !type) {
          //图片初始化
          $('.uploadImg').removeClass('hidden');
          $('.uploadImg').removeAttr('style');
          $('.uploadImg .js_perUpAdd').removeClass('hidden');
          $('.putPhone').removeClass('hidden');
          
        //视频
        } else if (type == 2) {
          //视频初始化
          $('.uploadVideo').removeClass('hidden');
          $('.uploadVideo').removeAttr('style');
          $('.jiange_time').addClass('hidden');
          $('.jiange_num').removeClass('hidden');
          $('.putPhone').removeClass('hidden');
          //$('.uploadVideo .js_perUpAdd').html('');
        //链接
        } else  if (type == 3) {
          
          //url初始化
          $('.js_urlLink input[type="text"]').val('');
          $('.js_urlLink').removeClass('hidden');
          $('.link').removeClass('hidden');
          $('.jiange_num').addClass('hidden');
          $('.jiange_time').removeClass('hidden');
          $('.putPhone').removeClass('hidden');
        //宣传图
        } else if (type == 4) {
          
          //宣传图初始化
          $('.uploadImg').removeClass('hidden');
          $('.uploadImg .js_perUpAdd').removeClass('hidden');
          $('.jsPriceType').removeClass('hidden');
          $('.jsColorType').removeClass('hidden');
          $('.jsIsSpecify').removeClass('hidden');
          $('.putPhone').removeClass('hidden');

        }else if (type == 5) {
            $('.selectSetMealImg').removeClass('hidden');
            $('.jsColorType').removeClass('hidden');
            $('.jiange_num').addClass('hidden');
            $('.jiange_time').addClass('hidden');
            $('.putPhone').addClass('hidden');
           // $('input:hidden[name="set_meal"]').val('');
        }
        //解除轮播参数的禁止编辑
        removeDisabled(type);
      }
      
      //图片初始化
      function initImage() {
        //图片初始化
        $('.uploadImg').addClass('hidden');
        $('.link').addClass('hidden');
        $('.jsPriceType').addClass('hidden');
        $('.uploadVideo').addClass('hidden');
        $('.jiange_time').addClass('hidden');
        $('.jiange_num').addClass('hidden');
        $('.uploadImg .js_perUpOuter img').remove();
        $('.uploadImg .js_perUpAdd').css('display', '');
        $('.uploadImg input[type="file"]').val('');
      }
      
      //视频初始化
      function initVideo() {
        //视频初始化
        $('.uploadVideo').removeClass('hidden');
        $('.link').addClass('hidden');
        $('.jiange_time').addClass('hidden');
        $('.jiange_num').removeClass('hidden');
        $('.uploadVideo .js_perUpAdd').html('');
        $('.uploadVideo input[type="file"]').val('');
      }
      
      //url初始化
      function initUrl()
      {
        //url初始化
        $('.js_urlLink input[type="text"]').val('');
        $('.js_urlLink').addClass('hidden');
        $('.jiange_time').removeClass('hidden');
        $('.jiange_num').addClass('hidden');
      }
      
      //宣传图初始化
      function initType4(){
        //宣传图初始化
        $('.jsColorType').addClass('hidden');
        $('.jsIsSpecify').addClass('hidden');
      }
      
      
    //套餐图初始化
      function initSetMealImg() {
      
        $('.selectSetMealImg').addClass('hidden');
        $('.jsColorType').addClass('hidden');
        $('.jiange_time').addClass('hidden');
        $('.jiange_num').addClass('hidden');
        
      }
     
      //轮播次数禁止编辑的解除和添加
      function removeDisabled(type)
      {
        //未选择
        if (!type || type == 1 || type == 4) {
          //禁止编辑
           $('.js_roll_interval').attr('disabled', 'disabled');
           $('.js_roll_num').attr('disabled', 'disabled');
        //视频
        } else if (type == 2) {
          //禁止轮播时长编辑
          $('.js_roll_interval').val(0);
          $('.js_roll_interval').attr('disabled', true);
          //开放轮播次数编辑
          $('.js_roll_num').val(1);
          $('.js_roll_num').attr('disabled', false);

        //链接
        } else  if (type == 3 || type == 5) {
          //开启轮播时长编辑
          $('.js_roll_interval').val(10);
          $('.js_roll_interval').attr('disabled', false);
          //禁止轮播次数编辑
          $('.js_roll_num').val(1);
          $('.js_roll_num').attr('disabled', true);
        } 
      }
      
       // 上传文件的预览
        $(".js_perUpOuter").on('click','.js_perUpArea', function(){
            $(this).closest('.item1').find('.js_perUpFile').trigger('click');
            
          });
        
     
        $(".js_perUpFile").each(function(i){
          $(this).css({ 'position':'absolute', 'left':'-2000px' })
          $(this).change(function(e){
            var type = $('.put_type_hidden').val();
            if (type == 1 || type == 4) {
              handleFileSelect($(this), function (data) {
                //判断是否为动图
                var is = isAnimatedGif(data);
                //动图
                if (is === true) {
                  //禁止轮播时长编辑
                  $('.jiange_num').removeClass('hidden');
                  $('.js_roll_interval').val(0);
                  $('.js_roll_interval').attr('disabled', true);
                  //开放轮播次数编辑
                  $('.js_roll_num').val(1);
                  $('.js_roll_num').attr('disabled', false);
                //静图
                } else if (is === false) {
                    $('.jiange_time').removeClass('hidden');
                  //开启轮播时长编辑
                  $('.js_roll_interval').val(10);
                  $('.js_roll_interval').attr('disabled', false);
                  //禁止轮播次数编辑
                  $('.js_roll_num').val(1);
                  $('.js_roll_num').attr('disabled', true);
                //未知
                } else {
                  alert(is);
                }
              });

            } else {
              //$('.uploadVideo .js_perUpAdd').text($('.uploadVideo input[type="file"]').val());
              $('.uploadVideo .js_perUpAdd').html('<img src="{$smarty.const.SITE_URL}/images/m-zhongduan/video.png" style="width:2.72rem; height:2.72rem;">');
            }
          });
        });
        /////////////////////////////////////
    });
    //上传图片到服务器
    function isAnimatedGif(imgData){
      if (!imgData) {
        return '图片上传失败';
      }
      var start = imgData.indexOf(';base64,');
      if (start < 0) {
        return '图片验证失败';
      }

      var not = imgData.slice(0, start+8);
      var imgData = imgData.replace(not, '');
      var newData =  $.base64.decode(imgData);
      if (newData.indexOf('NETSCAPE2.0') > -1) {
        return true;
      } else {
        return false;
      }
    }
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

   

    </script>     
    
    <script type="text/javascript">
  
    function handleFileSelect(obj, callback)
    {
      //alert(FileReader)
      if (typeof FileReader == "undefined") {
        return false;
      }
      var thisClosest = obj.closest('.item1');
      var thisOuter = thisClosest.find('.js_perUpOuter');
      if (typeof thisClosest.length == "undefined" || typeof thisOuter.length == "undefined") {
        return;
      }
      
      var files = obj[0].files;
      var f = files[0];
      if (!isAllowFile(f.name)) {
        showMsg("请上传常规格式的图片,如：jpg, png等");
        return false;
      }
      
      // 如果浏览器支持html5 FileReader
      if (typeof FileReader != 'undefined') {
        var reader = new FileReader();
        reader.onload = (function(theFile){
            return function (e) {
              var tmpSrc = e.target.result;
              if (tmpSrc.lastIndexOf('data:base64') != -1) {
                tmpSrc = tmpSrc.replace('data:base64', 'data:image/jpeg;base64');
              } else if (tmpSrc.lastIndexOf('data:,') != -1) {
                tmpSrc = tmpSrc.replace('data:,', 'data:image/jpeg;base64,');
              }
              
              doFileSelected(tmpSrc, thisOuter, callback);
            };
        })(f)
        reader.readAsDataURL(f);
        //alert('可以的')
      } else {
        //alert('不可以');
        var tmpSrc = siteUrl+"/images/admin2/pic_select_defalut.png";
        doFileSelected(tmpSrc, thisOuter, callback);
      }
    }

    // 选择图片后的操作
    function doFileSelected(tmpSrc, thisOuter, callback)
    {
      var img = '<img src="'+tmpSrc+'" style="width:2.72rem; height:2.72rem;" class="js_perUpArea" />';
      
      thisOuter.find('.js_perUpAdd').hide();
      thisOuter.find('img').remove();
      thisOuter.prepend(img);
      
      var showId = thisOuter.attr('data-show-id');
      if (showId) {
        if ($("#"+showId).length >= 1) {
          $("#"+showId).find('img').attr('src', tmpSrc);
          
        } else if ($("."+showId).length >= 1) {
          $("."+showId).each(function(i){
            $(this).html(img);
          });
        }
      }
      if (typeof(callback) != 'undefined') {
        callback(tmpSrc);
      }
    }

    //取得文件名的后缀
    function getFileExt(fileName)
    {
      if (!fileName) {
        return '';
      }
      
      var _index = fileName.lastIndexOf('.');
      if (_index < 1) {
        return '';
      }
      
      return fileName.substr(_index+1);
    }

    //是合格的文件名
    function isAllowFile(fileName, allowType)
    {
      var fileExt = getFileExt(fileName).toLowerCase();
      if (!allowType) {
        allowType = ['jpg', 'jpeg', 'png', 'gif'];
      }
      
      if ($.inArray(fileExt, allowType) != -1) {
        return true;
      }
      
      return false;
    }

  //判断是否微信登陆
    function isWeiXin() {
        var ua = window.navigator.userAgent.toLowerCase();
        console.log(ua); //mozilla/5.0 (iphone; cpu iphone os 9_1 like mac os x) applewebkit/601.1.46 (khtml, like gecko)version/9.0 mobile/13b143 safari/601.1
        if (ua.match(/MicroMessenger/i) == 'micromessenger') {
            return true;
        } else {
            return false;
        }
    }
    if (isWeiXin()) {
        console.log(" 是来自微信内置浏览器")
        $('.header').addClass('hidden');
    } else {
        console.log("不是来自微信内置浏览器")
    }
