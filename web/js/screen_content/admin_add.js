
////////////////套餐图结束///////////////////
$(function(){
  //加载套餐图（修改时）
  if (defaultType == 5) {
    $('.set_meal_add').load(siteUrl+"/screen_content/admin/set_meal_add?content_id="+contentId, function () {
    //套餐信息不能为空
    $('.set_meal_input').addClass('required');
    });
  }

  $('.btn-close').on('click',function(){
      $(this).closest('.pop-bg').addClass('hidden');

  });
  
  $('.selectType').on('change', function () {
    type = $(this).val();
    
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
      $('.uploadImg .js_perUpAdd').removeClass('hidden');
      
    //视频
    } else if (type == 2) {
      //视频初始化
      $('.uploadVideo').removeClass('hidden');
      $('.uploadVideo .js_perUpAdd').html('<em>+</em>从本地选择');
    //链接
    } else  if (type == 3) {
      
      //url初始化
      $('.js_urlLink input[type="text"]').val('');
      $('.js_urlLink').removeClass('hidden');
    //宣传图
    } else if (type == 4) {
      $('.uploadImg').removeClass('hidden');
      $('.uploadImg .js_perUpAdd').removeClass('hidden');
      $('.jsColorType').removeClass('hidden');
      $('.jsPrice').removeClass('hidden');
      $('.jsIsSpecify').removeClass('hidden');
    //套餐图
    } else if (type == 5) {
      $('.selectSetMealImg').removeClass('hidden');
      $('.jsColorType').removeClass('hidden');
      $('input:radio[name="set_meal"]').eq(0).attr('checked', 'checked');
      
      $('.js_import_set_meal').removeClass('hidden');
      
      //加载套餐图
      $('.set_meal_add').load(siteUrl+"/screen_content/admin/set_meal_add?content_id="+contentId, function () {
        //套餐信息不能为空
        $('.set_meal_input').addClass('required');
      });
    }
    //解除轮播参数的禁止编辑
    removeDisabled(type);
  });
  
  //图片初始化
  function initImage() {
    //图片初始化
    $('.uploadImg').addClass('hidden');
    $('.uploadImg .js_perUpOuter img').remove();
    $('.uploadImg .js_perUpAdd').css('display', '');
    $('.uploadImg input[type="file"]').val('');
  }
  
  
  //视频初始化
  function initVideo() {
    //视频初始化
    $('.uploadVideo').addClass('hidden');
    $('.uploadVideo input[type="file"]').val('');
  }
  
  //url初始化
  function initUrl()
  {
    //url初始化
    $('.js_urlLink input[type="text"]').val('');
    $('.js_urlLink').addClass('hidden');
  }
  
  //宣传图初始化
  function initType4(){
    //宣传图初始化
    $('.jsColorType').addClass('hidden');
    $('.jsIsSpecify').addClass('hidden');
    $('.jsPrice').addClass('hidden');
  }
  
  //套餐图初始化
  function initSetMealImg() {
    $('.selectSetMealImg').addClass('hidden');
    $('.jsColorType').addClass('hidden');
    $('.js_import_set_meal').addClass('hidden');
    $('input:radio[name="set_meal"]').attr('checked', false); //去除选中状态
    $('.set_meal_input').removeClass('required');
    $('.selectSetMealImg').addClass('hidden');
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

    //链接 和 套餐图
    } else  if (type == 3 || type == 5) {
      //开启轮播时长编辑
      $('.js_roll_interval').val(10);
      $('.js_roll_interval').attr('disabled', false);
      //禁止轮播次数编辑
      $('.js_roll_num').val(1);
      $('.js_roll_num').attr('disabled', true);
    } 
  }
  

  ////////////////////
  // 修改投放范围
  $(".js_saveAndPutArea").click(function(e){
    e.preventDefault();
    
    $(":input[name='put_type']").val(2);
    $(".js_saveBtn").trigger('click');
  });
  ////////////////////
  
    var tip = ['温馨提示：此处封面最佳上传尺寸：362＊391px', '温馨提示：此处封面最佳上传尺寸：356＊224', '温馨提示：此处封面最佳上传尺寸：729＊340px',  '温馨提示：此处封面最佳上传尺寸：320＊149px'];
    $('#selectResName').change(function(){
        var selectResName = $('#selectResName option:selected').val();
        var txt = '';

        if (selectResName == 'home') {
            txt = tip[0];
        } else if (selectResName == 'dressing') {
            txt = tip[1];
        } else if (selectResName == 'mall') {
            txt = tip[2];
        } else if (selectResName == 'mobile-index') {
               txt = tip[3];
           }
        $('#selectResName').parent().next().text(txt);
    });

    $('.putTypeBut').live('click',function() {
            var putTypeCiock = $(this).val();
            if (putTypeCiock >= 1) {
                if (putTypeCiock == 2) {
                  $('.Sub').text('下一步');
                } else {
                  $('.Sub').text('保存并发布');
                }
            } else {
                $('.Sub').text('保存');
            } 
    });
    
    /////////////////////////////////////
    // 上传文件的预览
    $(".js_perUpArea").click(function(){
      
      $(this).closest('.js_perUpWrap').find('.js_perUpFile').trigger('click');
      
    });
    $(".js_perUpFile").each(function(i){
      $(this).css({ 'position':'absolute', 'left':'-2000px' })
      $(this).change(function(e){
        var type = $('.selectType').val();
        if (type == 1 || type == 4) {
          handleFileSelect($(this), function (data) {
            //判断是否为动图
            var is = isAnimatedGif(data);
            //动图
            if (is === true) {
              //禁止轮播时长编辑
              $('.js_roll_interval').val(0);
              $('.js_roll_interval').attr('disabled', true);
              //开放轮播次数编辑
              $('.js_roll_num').val(1);
              $('.js_roll_num').attr('disabled', false);
            //静图
            } else if (is === false) {
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
          $('.uploadVideo .js_perUpAdd').text($('.uploadVideo input[type="file"]').val());
          $('.uploadVideo .js_perUpChange').removeClass('hidden');
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

$('.inputImgBtn').live('change',function() {
    //previewImage(this);
    var content_img = document.getElementById('content_img');
    previewImage(this,content_img,'content','250','133');
});
//保存或发布按钮
$('.form-group').on('click', '.js_subBtn',  function () {
  //验证
  var title     = $("input[name='content[title]']").val();
  var type      = $(".selectType").val();
  var startTime = $("input[name='content[start_time]").val();
  var endTime   = $("input[name='content[end_time]").val();
  
  if (!title || !type || !startTime || !endTime || type == 5) {
    
    if (type == 5) {
      //系统 AnFORM 处理
      var len = $('.tag').length;
      if ( len < 1 ) {
        $('.set_meal_nickname_id').removeClass('hidden');
        return false;
      } else {
        $('.set_meal_nickname_id').addClass('hidden');
      }
    }
    
    //系统 AnFORM 处理
    
  } else {
    $('#putLoading').removeClass('hidden');
  }
  
});


function check_set_meal()
{
  var set_meal_nickname_id          = $("input[name='set_meal_info[nickname_id]']").val();
  var set_meal_retail_price         = $("input[name='set_meal_info[retail_price]']").val();
  var set_meal_recommended_position = $("input[name='set_meal_info[recommended_position]']").val();
  var set_meal_selling_point_1      = $("input[name='set_meal_info[selling_point_1]']").val();
  var set_meal_selling_point_2      = $("input[name='set_meal_info[selling_point_2]']").val();
  var set_meal_selling_point_3      = $("input[name='set_meal_info[selling_point_3]']").val();
  var set_meal_param_1              = $("input[name='set_meal_info[param_1]']").val();
  var set_meal_param_2              = $("input[name='set_meal_info[param_2]']").val();
  var set_meal_param_3              = $("input[name='set_meal_info[param_3]']").val();
  var set_meal_param_4              = $("input[name='set_meal_info[param_4]']").val();
  var set_meal_param_5              = $("input[name='set_meal_info[param_5]']").val();
  var set_meal_param_6              = $("input[name='set_meal_info[param_6]']").val();
  
  if (!set_meal_nickname_id) {
    //return "请选择对应机型"
  } 
  
  if ( !set_meal_retail_price ) {
    return '请输入零售价';
  } 

  if ( !set_meal_selling_point_1 || !set_meal_selling_point_2 || !set_meal_selling_point_3 ) {
    return '卖点不能为空';
  } 

  if ( !set_meal_param_1 || !set_meal_param_2 || !set_meal_param_3 || !set_meal_param_4 || !set_meal_param_5 || !set_meal_param_6) {
    return '套餐信息不能为空';
  }
  
  return 'ok'
}


