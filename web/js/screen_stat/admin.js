/**
 * 下拉操作对象
 * @author wangjf
 * @date 2018-06-29 16:30:34
 */
function MySelect (param) {
  
  this._types = ['1', '2', '3'], //'1':标签名, '2':类名, '3':'id'
  
  //下拉列表显示状态 true 显示 隐藏
  this._selectListDisplayStatus = false,
  
  //下拉后选中的dom
  this._selectedOption = '',
  
  //下拉后选中的value
  this._selectedOptionValue = '',
  
  //下拉后选中的文本
  this._selectedOptionText = ''
    
  //初始化函数
  this._init = function (param) {
//console.log(param);
    //下拉BoxName
    this._selectBoxName = param.selectBoxName || 'select';
    
    //下拉BoxName类型
    this._selectBoxNameType = param.selectBoxNameType || 2;
    
    //下拉列表Name
    this._selectListName = param.selectListName || 'select-list';
    
    //下拉列表Name类型 '1':标签名, '2':类名, '3':'id'
    this._selectListNameType = param.selectListNameType || 2;
    
    //下拉属性Name
    this._selectOptionName = param.selectOptionName || 'p';
    
    //下拉属性Name类型 '1':标签名, '2':类名, '3':'id'
    this._selectOptionNameType = param.selectOptionNameType || 1;
    
    //下拉属性的选中状态类名
    this._selectedClassName = param.selectedClassName || 'active';
    
    //选中后的回调函数 ，默认返回选中的值和名称
    this._selectedCb = param.selectedCb || function () { 
      return true;
    }
    
    //绑定事件
    return this._bindingEvents();
  }
  
  //绑定事件
  this._bindingEvents = function () {
    var _self = this;
    //绑定下拉事件
    $(this._getSelectBoxName()).on('click', function (e) {
      console.log(_self._getSelectBoxName());
      console.log(_self);
      //显示下拉
      _self._displaySelectList();
    });
    
    //绑定元素选择事件
    $(this._getSelectBoxName() + ' ' + this._getSelectListName()  + ' ' + this._getSelectOption()).on('click', function () {
      return _self._select(this);
    });
    
    return true;
  }
  
  /**
   * 
   * 显示下拉列表
   */
  this._displaySelectList = function () {
    //显示
    if (this._selectListDisplayStatus == false) {
      $(this._getSelectBoxName() + ' ' + this._getSelectListName()).removeClass('hidden');
      this._selectListDisplayStatus = true;
    //隐藏
    } else {
      $(this._getSelectBoxName() + ' ' + this._getSelectListName()).addClass('hidden');
      this._selectListDisplayStatus = false;
    }
    
    return true;
    
  }
  
  /**
   * 选择
   * @param elem
   * @returns
   */
  this._select = function (elem) {
    
    //选中的下拉参数对象
    this._selectedOption = elem;
    
    //下拉后选中的value
    this._selectedOptionValue  = $(this._selectedOption).attr('value'),
    
    //下拉后选中的文本
    this._selectedOptionText = $(this._selectedOption).text();
    
    //选中状态
    $(this._getSelectBoxName() + ' ' + this._getSelectListName()  + ' ' + this._getSelectOption()).removeClass(this._selectedClassName);
    
    //选中值置顶
    $(this._getSelectBoxName() + ' .default').attr('value', this._selectedOptionValue)
    $(this._getSelectBoxName() + ' .default').text(this._selectedOptionText);
    
    $(elem).addClass(this._selectedClassName);
    
    return this._selectedCb();
  }
  
  /**
   * 隐藏下拉列表
   */
  this._hiddenSelectList = function () {
    this._getSelectListObj().addClass('hidden');
    
    return true;
  }
  
  /**
   * 获取下拉box name
   */
  this._getSelectBoxName = function () {
    if (this._selectBoxNameType == 1) {
      return this._selectBoxName;
    } else if (this._selectBoxNameType == 2) {
      return '.' + this._selectBoxName;
    } else if (this._selectBoxNameType == 3) {
      return '#' + this._selectBoxName;
    } 
    return this._selectBoxName;
  }
  
  /**
   * 获取下拉列表name
   */
  this._getSelectListName = function () {
    if (this._selectListNameType == 1) {
      return this._selectListName;
    } else if (this._selectListNameType == 2) {
      return '.' + this._selectListName;
    } else if (this._selectListNameType == 3) {
      return '#' + this._selectListName;
    } 
    return this.selectListName;
  }
  
  /**
   * 获取下拉属性name
   */
  this._getSelectOption = function () {
    if (this._selectOptionNameType == 1) {
      return this._selectOptionName;
    } else if (this._selectOptionNameType == 2) {
      return '.' + this._selectOptionName;
    } else if (this._selectOptionNameType == 3) {
      return '#' + this._selectOptionName;
    } 
    return this._selectOptionName;
  }
  
  //初始化
  this._init(param);
}


/**
 * 省市区厅三级联动搜索
 * @author wangjf
 * @date 2018-06-29 17:31:52
 */
function SearchSelect () {

  //初始化省
  this._initProvince = function () {
    $('.selectCityBox .default').text('请选择城市').attr('value', '');
    $('input[name="search_filter[city_id]"]').val('');
    $('input[name="search_filter[area_id]"]').val('');
  }
  
  //初始化市
  this._initCity = function () {
    $('.selectAreaBox .default').text('请选择区县').attr('value', '');
    $('input[name="search_filter[area_id]"]').val('');
  }

  //拼接元素
  this._joinHtml = function (list) {
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
      SearchSelect._initProvince();
      //设置选中值
      $(this._getSelectBoxName() + 'input[name="search_filter[province_id]"]').val(this._selectedOptionValue);
      
      //获取市列表
      $.post("{AnUrl('business_hall/admin/ajax/get_city_name')}" , { province_id: this._selectedOptionValue } ,function(json){
        if (json.msg=='ok') {
          //拼接元素
          var html = SearchSelect._joinHtml(json.city_info);
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
      
      //初始化省
      SearchSelect._initCity();
      
      //设置选中值
      $(this._getSelectBoxName() + 'input[name="search_filter[city_id]"]').val(this._selectedOptionValue);
      
      //获取市列表
      $.post(siteUrl + "/business_hall/admin/ajax/get_area_name" , { city_id: this._selectedOptionValue } ,function(json){
        if (json.msg=='ok') {
          //拼接元素
          var html = SearchSelect._joinHtml(json.area_info);
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
}
