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
    
    //选中后的显示元素Name
    this._selectedDisplayName = param.selectedDisplayName || 'default';
    
    //选中后的显示元素Name类型 '1':标签名, '2':类名, '3':'id'
    this._selectedDisplayNameType = param._selectedDisplayNameType || 2;
    
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
      //显示下拉
      _self._displaySelectList();
    });
    
    //绑定元素选择事件
    $(this._getSelectBoxName() + ' ' + this._getSelectListName()).on('click', this._getSelectOption(), function () {
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
    $(this._getSelectBoxName() + ' ' + this._getSelectedDisplayName()).attr('value', this._selectedOptionValue)
    $(this._getSelectBoxName() + ' ' + this._getSelectedDisplayName()).text(this._selectedOptionText);
    
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
  
  /**
   * 获取选中后的显示name
   */
  this._getSelectedDisplayName = function () {
    if (this._selectedDisplayNameType == 1) {
      return this._selectedDisplayName;
    } else if (this._selectedDisplayNameType == 2) {
      return '.' + this._selectedDisplayName;
    } else if (this._selectedDisplayNameType == 3) {
      return '#' + this._selectedDisplayName;
    } 
    return this._selectedDisplayName;
  }
  
  //初始化
  this._init(param);
}
