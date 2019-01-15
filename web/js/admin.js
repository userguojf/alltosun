// 删除提示信息 为了兼容原有后台程序移植过来的 将来将要被废除
var prompt = {
  'prompt': "确定要删除记录吗?",
  'nochange': "您没有要删除的记录",
  'errors': "删除失败"
};
var predefineParam;

var resType = resType || '';
var resName = resName || '';
res_name = '';
$(function(){
	
  $('#start_time, #end_time,#close_time').datepicker({
      dateFormat: 'yy-mm-dd',
      showButtonPanel: true
  });
  
  $('#from_time, #to_time').datepicker({
      dateFormat: 'yy-mm-dd',
      showButtonPanel: true
  });
  
  $('#vip_start_time, #vip_end_time, #birth').datepicker({
      dateFormat: 'yy-mm-dd',
      showButtonPanel: true
  });
  
  //时间插件  带时分秒
  $('.begin_time').datetimepicker({
      currentText: '当前时间',
      closeText: '确定',
      timeText: '时间',
      hourText: '时',
      minuteText: '分',
      secondText: '秒',
      timeFormat: "HH:mm:ss",
      dateFormat: "yy-mm-dd",
      minDate: new Date()
  });
  
  $('.last_time').datetimepicker({
      currentText: '当前时间',
      closeText: '确定',
      timeText: '时间',
      hourText: '时',
      minuteText: '分',
      secondText: '秒',
      timeFormat: "HH:mm:ss",
      dateFormat: "yy-mm-dd"
  });

  //点击列表选中checkbox
  $(".dataBox table tbody  tr").click(function(e){
    var clickTarget = $(e.target);
    // 当直接点击checkbox时，不做checked的切换
    if (clickTarget.is("input.listSelect")) {
      return;
    }
    var listCheckbox = $("input.listSelect", $(this));
    if (listCheckbox.is(":disabled")) {
      return;
    }
    if (listCheckbox.attr("checked")) {
      listCheckbox.removeAttr("checked");
    } else {
      listCheckbox.attr("checked", "checked");
    }
  });

  // 用于去除mozilla中radio和checkbox的bug问题
  if($.browser.mozilla) $("form").attr("autocomplete", "off");

  // 全选
  $("input.selectAll").click(function(){
    console.log($(this).attr("checked"));
    if ($(this).attr("checked")) {
      $("input.selectAll, input.listSelect").not(":disabled").attr("checked", "checked");
    } else {
      $("input.selectAll, input.listSelect").not(":disabled").removeAttr("checked");
    }
  });

  //操作警告
  $(".warningAction").click(function(e){
    e.preventDefault();
    e.stopPropagation();
    if (!confirm("确定要执行该操作吗？")) {
      return false;
    }

    var clickObj = $(this);
    var url = clickObj.attr("href");
    $.post(url, {}, function(json){
      if (json.info != 'ok') {
        alert(json.info);
      } else {
        clickObj.closest("tr").fadeOut(function(){
          $(this).remove();
          interLineColor();
        });
      }
    }, 'json');
    return false;
  });

  // 单个删除
  $(".deleteOne").click(function(e){
    e.preventDefault();
    e.stopPropagation();
    if (!confirm("确定要删除该条记录吗？")) {
      return false;
    }

    var clickObj = $(this);
    var url = clickObj.attr("href");
    $.post(url, {}, function(json){
      if (json.info != 'ok') {
        alert(json.info);
      } else {
        clickObj.closest("tr").fadeOut(function(){
          $(this).remove();
          interLineColor();
        });
      }
    }, 'json');
    return false;
  });
  
  //单个还原
  $(".recoverOne").click(function(e){
    e.preventDefault();
    e.stopPropagation();
    if (!confirm("确定要还原该条记录吗？")) {
      return false;
    }

    var clickObj = $(this);
    var url = clickObj.attr("href");
    $.post(url, {}, function(json){
      if (json.info != 'ok') {
        alert(json.info);
      } else {
        clickObj.closest("tr").fadeOut(function(){
          $(this).remove();
          interLineColor();
        });
      }
    }, 'json');
    return false;
  });
  
  //前台显示
  $(".front_view").click(function(e){
    e.preventDefault();
    e.stopPropagation();
    if (!confirm("确定让本区域显示在前台吗？")) {
      return false;
    }

    var clickObj = $(this);
    var url = clickObj.attr("href");
    $.post(url, {}, function(json){
      if (json.info != 'ok') {
        alert(json.info);
      } else {
        clickObj.closest("tr").fadeOut(function(){
          $(this).remove();
          interLineColor();
        });
      }
    }, 'json');
    return false;
  });

  // 批量删除
  $(".deleteAll").click(function(e){
    e.preventDefault();
    var url = $(this).attr("href");
    var ids = getCheckedIds();
    deleteAll(url, ids);
    $("input[name=selectAll]").not(":disabled").removeAttr("checked");
    return false;
  });

  // 点击列表选中checkbox
  $("tbody > tr", $("#AnTable")).click(function(e){
    var clickTarget = $(e.target);
    // 当直接点击checkbox时，不做checked的切换
    if (clickTarget.is("input[name=listSelect]")) {
      return;
    }
    var listCheckbox = $("input[name=listSelect]", $(this));
    if (listCheckbox.is(":disabled")) {
      return;
    }
    if (listCheckbox.attr("checked")) {
      listCheckbox.removeAttr("checked");
    } else {
      listCheckbox.attr("checked", "checked");
    }
  });

  // 批量转移分类
  $('#moveCategory').click(function(){
    $.getJSON("admin/category/get_list&res_name="+resName, function(data){
      var input='&nbsp;&nbsp;&nbsp;';
      input += '<select name="move_category" id="moveCategorySelect">';
      input += '<option>请选择目标分类</option>';
      $.each(data, function(i,item){
        input += '<option value="'+item['id']+'">'+item['name']+'</option>';
      });
      input += '</select>';
      $('#moveCategory').after(input);
      $('#moveCategory').unbind();
      $("#moveCategorySelect").change(function(){
        var categoryId = $(this).val();
        moveCategory(categoryId);
        $(this).children("option:first").attr('selected', 'selected');
        return false;
      });
    });
  });
});

/**
 * 批量删除
 * @param url
 * @return
 */
function deleteAll(url, ids){
  var idstr = ids.join(',');
  if (!idstr) {
    alert("请选择要删除的记录");
    return false;
  }
  if (!confirm("确定要删除这些记录吗？")) {
    return false;
  }
	var postData = { 'id': idstr };
	$.post(url, postData, function(json){
	  if (json.info != 'ok') {
	    alert(json.info);
	  } else {
	    $.each(ids, function(k, v){
	      $("#dataList"+v).fadeOut(function(){
	        $(this).remove();
	      });
	    });
	    interLineColor();
	  }
	}, 'json');
}

/**
 * 为了兼容原有后台程序移植过来的 将来将要被废除
 * 单个删除
 * @param url
 * @param id
 * @return
 */
function del_one(url,id){
  if(!confirm(prompt.prompt)) return false;
  var sendata = {id:id, 'res_name':res_name};
  if (typeof(predefineParam) != 'undefined') {
    $.extend(sendata, predefineParam);
  }
  $.getJSON(url, sendata,function(data){
    if(data.info == 'ok'){
      var text = 'list_'+id;
      if (typeof(predefineFun) == 'undefined') {
        $("#"+text).fadeOut();
      } else {
        $.extend({ybo:predefineFun});
        $.ybo(text);
      }
    }else{
      alert(data.info);
      return false;
    }
  });
}
/**
 * 为了兼容原有后台程序移植过来的 将来将要被废除
 * 批量删除
 * @param url
 * @return
 */
function del(url){
  if(!confirm(prompt.prompt)) return false;
  var ids = '';
  $(".select_s input:checked").each(function(){
    var id = $(this).closest("tr").attr("id").substring(5);
    ids += id+',' ;
  });
  if(!ids){
    alert(prompt.nochange);
    return false;
  }
  var sendata = {id:ids};
  if (typeof(predefineParam) != 'undefined') {
    $.extend(sendata, predefineParam);
  }
  $.getJSON(url, sendata, function(json){
    if (json.info == 'ok'){
        var $obj = $(".select_s input:checked").parents("tr");
        if (typeof(predefineFun) == 'undefined') {
          $obj.fadeOut();
        } else {
          $obj.each(function(i, n){
            var jobj = $(n);
            $.extend({ybo:predefineFun});
            $.ybo(jobj.attr("id"));
          });
        }
      $(".selectAll").attr("checked",false);
    } else {
      alert(prompt.errors);
    }
  });
}

/**
 * 批量转移分类
 * @param categoryId 分类id
 * @require 更新对应class="category"的分类td的内容
 * @TODO 与user_list中的审核功能合并抽取
 * @author gaojj@alltosun.com
 */
function moveCategory(categoryId)
{
	if(!confirm('确定要批量转移吗?')) return false;

	var id = getCheckedId();
    if (!id) {
        alert("你没有选择要操作的记录");
        return false;
    }

	var url = site_url + '/admin/category/move';
    var data = {'category_id': categoryId, 'id[]': id};
    $.getJSON(url, data, function(json){
        if (json.info != 'ok') {
            alert(json.info);
            return;
        }

        var categoryName = json.category_name;

        var articleCategorySelectors = [];

        $.each(id, function(k, v){
        	// 更新对应class="category"的分类td的内容
            articleCategorySelectors.push('#list_'+v+' .category');
        });

        var newCategoryhtml = '<a href="admin/article&cat_id='+categoryId+'" target="_blank">'+categoryName+'</a>';
        var articleCategorySelector = articleCategorySelectors.join(',');

        $(articleCategorySelector).html(categoryName).effect("highlight", {}, 300).effect("highlight", {}, 300);
    });
}

/**
 * 获取页面中选中的checkbox对应的ids
 * @requires checkbox上统一加name="listSelect"
 * @requires tr的class="dataList1"
 * @return Array 所有选中的id数组
 */
function getCheckedIds()
{
  var ids = [];
  $("input.listSelect:checked").not(":disabled").each(function(){
    var selectId = $(this).closest("tr").attr("id").substring(8);
    ids.push(selectId);
  });
  return ids;
}

/**
 * 为了兼容原有后台程序移植过来的 将来将要被废除
 * 获取页面中选中的checkbox的值
 * 本方法中获取页面选中的checkbox必须在checkbox上统一加class="listCheck"，并且tr的class="list_1"
 * @return Array 所有选中的id数组
 * @author gaojj@alltosun.com
 */
function getCheckedId()
{
  var id = [];
  // checkbox上统一加class="listSelect"
    $("input.listSelect:checked").each(function(){
      // tr的class="list_1"
        var selectId = $(this).closest("tr").attr("id").substring(8);        
        id.push(selectId);
    });
    return id;
}

/**
 * 表格隔行换色
 * @return
 */
function interLineColor()
{
	$("tr:odd").removeClass("even").addClass("odd");
	$("tr:even").removeClass("odd").addClass("even");
}

/**
 * 是否是中文
 */
function isChinese(str)
{
  return new RegExp("[\\u4e00-\\u9fa5]", "").test(str);
}

// 获取排序的view_order
function getViewOrder()
{
  var viewOrderArr = { };
  var list = $(".dataBox tbody tr");
  var total = list.length;
  $.each(list ,function(viewOrder, v){
    var key = $(this).attr('id').substring(8);
    if (!key) {
      return true;
    }
    viewOrderArr[key] = viewOrder + 1;
  });
  return viewOrderArr;
}

// 判断一个对象是否是同一个对象 (只判断了第一层)
function isSameObj(obj1, obj2)
{
  for(var i in obj1) {
    if (obj1[i] !== obj2[i]) {
      return false;
    }
  }
  for(var j in obj2) {
    if (obj2[j] !== obj1[j]) {
      return false;
    }
  }
  return true;
}

$(function () {
//编辑器采用上传模式 *require resType
  var xheditorUploadUrl =  siteUrl + "/news/handler/file_uploader&source=xheditor&immediate=1&file_field=filedata";
  var myUploadUrl = siteUrl + "/news/handler/file_uploader/my_file_upload";
  var xheditorSettings = {
      height        : 400,
      wordDeepClean : false,
      inlineScript  : true,
      internalScript: true,
      linkTag       : true,
      upImgUrl      : xheditorUploadUrl,
      upImgExt      : "jpg,jpeg,gif,png",
      upFlashUrl    : xheditorUploadUrl,
      upFlashExt    : "swf",
      upMediaUrl    : xheditorUploadUrl,
      upMediaExt    : "flv,avi,mp4",
      upLinkUrl     : myUploadUrl,
      upLinkExt     : "rar,pdf,txt,zip,doc,docx,jpg,jpeg,gif,png"
  };

  $.each($('.xheditor-upload'), function(){
    // 如果定义了id，证明这个xh实例需要被用到，可在xheditorObjs对象中通过下标访问到
    // 如 xheditorObjs['newsContent']
    var id = $(this).attr('id');
    if ( id ) {
      xheditorObjs[id] = $(this).xheditor(xheditorSettings);
    } else {
      $(this).xheditor(xheditorSettings);
    }
  });
  
  /**
   * 编码URL
   */
  $('#searchForm').submit(function(e){
    e.preventDefault();
    var params = $(this).serialize();
    var href = $(this).attr('action');
    href += '&' + params;
    window.location.href = href;
  });
  // 单个删除
  $(".deleteOnes").click(function(e){
    e.preventDefault();
    e.stopPropagation();
    if (!confirm("确定要删除该条记录吗？")) {
      return false;
    }

    var clickObj = $(this);
    var url = clickObj.attr("href");
    var lo_url = $(this).attr("lo_url");
    $.post(url, {}, function(json){
      if (json.info != 'ok') {
        alert(json.info);
      } else {
          window.location.assign(lo_url);
      }
    }, 'json');
    return false;
  });
});

function autoassign(id,url){
    var cache = {};
    $( "#"+id ).autocomplete({
        minLength: 1,
        source: function( request, response ) {
            var term = request.term;
            if ( term in cache ) {
                response( cache[ term ] );
                return;
          }

        $.getJSON(url, request, function( data, status, xhr ) {
            cache[ term ] = data;
            response( data );
        });
     }
    });
}

/**
 * html5选择照片后的预览(需要特定的class和ID 如：js_upFileBox， js_upFile)
 * @param evt
 * @returns {Boolean}
 */
function handleFileSelect(obj, callback)
{
  //alert(FileReader)
  if (typeof FileReader == "undefined") {
    return false;
  }
  var thisClosest = obj.closest('.js_perUpWrap');
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
  var img = '<img src="'+tmpSrc+'" style="width:100%; height:100%;" class="do_img"/>';
  
  thisOuter.find('.js_perUpAdd').hide();
  thisOuter.find('.js_perUpChange').show();
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

autoHeight();

$(window).resize(function(){
  autoHeight();
})
//左右等高
function autoHeight(){
	  var leftH= $('.form-add').height();
	  var rightH = $('.form-add-view').height();
	  if (leftH > rightH){
		$('.form-add-view').css({ height:(leftH+100) });
	  } else {
		$('.form-add-view').css({ height:'auto' });
	  }
}