/**
 * 省市区厅三级联动搜索
 * @author wangjf
 * @date 2018-06-29 17:31:52
 */
;(function(){
  //初始化省
  var initProvince = function () {
    $('.selectCityBox .default').text('请选择城市').attr('value', '');
    $('input[name="search_filter[city_id]"]').val('');
    $('.selectAreaBox .default').text('请选择区').attr('value', '');
    $('input[name="search_filter[area_id]"]').val('');
  }
  
  //初始化市
  var initCity = function () {
    $('.selectAreaBox .default').text('请选择区县').attr('value', '');
    $('input[name="search_filter[area_id]"]').val('');
  }

  //拼接元素
  var joinHtml = function (list) {
    var arr = eval(list);
    var html = "";
    for(var i=0; i< arr.length; i++){
      html += "<p value= '"+arr[i].id+"'>"+arr[i].name+"</p>"
    }
    return html;
  }

  //省下拉
  var provinceMS = new MySelect({
    'selectBoxName' : 'selectProvinceBox',
    'selectedCb'    : function () { //选中后回调方法
      //初始化省
      initProvince();
      
      //设置选中值
      $(this._getSelectBoxName() + ' input[name="search_filter[province_id]"]').val(this._selectedOptionValue);
      //获取市列表
      $.post(siteUrl + "/business_hall/admin/ajax/get_city_name" , { province_id: this._selectedOptionValue } ,function(json){
        if (json.msg=='ok') {
          //拼接元素
          var html = joinHtml(json.city_info);
          $(".selectCity").html(html);
        } else {
          alert('服务器错误');return;
        }
      },'json');
    },
  });
    
  //市下拉
  var cityMS = new MySelect({
    'selectBoxName' : 'selectCityBox',
    'selectedCb'    : function () { //选中后回调方法
      
      //初始化市
      initCity();
      //设置选中值
      $(this._getSelectBoxName() + ' input[name="search_filter[city_id]"]').val(this._selectedOptionValue);
      
      //获取市列表
      $.post(siteUrl + "/business_hall/admin/ajax/get_area_name" , { city_id: this._selectedOptionValue } ,function(json){
        if (json.msg=='ok') {
          //拼接元素
          var html = joinHtml(json.area_info);
          $(".selectArea").html(html);
        } else {
          alert('服务器错误');return;
        }
      },'json');
    },
  });
    
  //区下拉
  var areaMS = new MySelect({
    'selectBoxName' : 'selectAreaBox',
    'selectedCb'    : function () { //选中后回调方法
      $('input[name="search_filter[area_id]"]').val(this._selectedOptionValue);
    },
  });
  
  //营业厅
  $('#business_hall_title').autocomplete({
      source: '/business_hall/admin/ajax/get_title_field',
  });
})()

