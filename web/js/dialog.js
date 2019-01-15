/**
 * 后台bootstrap弹框js封装组件
 * @require bootstrap.js
 * @author liw
 * $Id: dialog.js 53045 2013-08-14 01:34:36Z ninghx $
 */

;(function(global){
  
  
/////////////////////////
// 全局实例 window.dialog
/////////////////////////
  
  
  /**
   * dialog实例缓存
   */
  var _cacheIds = {};
  
  /**
   * dialog工厂函数
   * @param {Object, String} config|id 传入dialog配置或者已经实例化的id
   * @return dialog 实例对象
   */
  var dialog = global.dialog = function(config){
    if ( config.id && _cacheIds[config.id] ) {
      return dialog.dialogCache(config.id).show();
    } else if ( typeof config == 'object' ) {
      return dialog.initDialog(config);
    } else if ( typeof config == 'string' ) {
      return dialog.dialogCache(config);
    }
    throw new Error('dialog config is empty.');
  };

  /**
   * 初始化一个dialog
   */
  dialog.initDialog = function(config) {
    
    var conf = _initConfig(config);
    var html = _initHtml(conf);
    var d    = new Dialog(conf);
    
    $('body').append(html);
    _initEvent(conf);
    $('#'+d.id).modal(conf);
    
    return d;
  };
  
  /**
   * 从缓存中获取一个dialog
   */
  dialog.dialogCache = function(id) {
    var cache = _cacheIds[id];
    if ( typeof cache !== 'undefined' && cache.id ) {
      return cache;
    }
  };
  

//////////////////
// Dialog构造函数
//////////////////
  
  
  /**
   * Dialog 构造函数
   * @param {Object} config
   */
  function Dialog(config) {
    this.config = config;
    this.id     = config.id;
    // 存入缓存
    _cacheIds[this.id] = this;
  }
  
  /**
   * 显示一个已经存在的对话框
   * @return {Object} 对话框自身
   */
  Dialog.prototype.show = function() {
    $('#'+this.id).modal('show');
    return this;
  };
  
  /**
   * 隐藏一个已经存在的对话框
   * @return {Object} 对话框自身
   */
  Dialog.prototype.hide = function() {
    $('#'+this.id).modal('hide');
    return this;
  };
  
  /**
   * 显示/隐藏一个已经存在的对话框
   * @return {Object} 对话框自身
   */
  Dialog.prototype.toggle = function() {
    $('#'+this.id).modal('toggle');
    return this;
  };
  
  /**
   * 更改对话框的标题
   * @param {String} str 新标题
   */
  Dialog.prototype.title = function(str) {
    str = str || '&nbsp;';
    $('#'+this.id + ' .dialogTitle').html(str);
    return this;
  };
  
  /**
   * 更改对话框的标题
   * @param {String} content 新内容
   */
  Dialog.prototype.content = function(content) {
    content = content || '';
    $('#'+this.id + ' .dialogContent').html(content);
    return this;
  };
  
  /**
   * 更改对话框确定按钮的文字和是否显示
   * @param {String}  val 确定按钮文字，可不传递，默认为“确定”
   * @param {Boolean} isShow 是否显示出来 默认为true
   */
  Dialog.prototype.okBtn = function(val, isShow) {
    if ( typeof val != 'string' ) {
      isShow = val;
      val = '确定';
    }
    if ( typeof isShow == 'undefined' ) {
      isShow = true;
    }
    $('#'+this.id + ' .dialogOkBtn').html(val);
    if ( isShow ) {
      $('#'+this.id + ' .dialogOkBtn').show();
    } else {
      $('#'+this.id + ' .dialogOkBtn').hide();
    }
    return this;
  };
  
  /**
   * 更改对话框取消按钮的文字和是否显示
   * @param {String}  val 确定按钮文字，可不传递，默认为“取消”
   * @param {Boolean} isShow 是否显示出来 默认为true
   */
  Dialog.prototype.cancelBtn = function(val, isShow) {
    if ( typeof val != 'string' ) {
      isShow = val;
      val = '取消';
    }
    if ( typeof isShow == 'undefined' ) {
      isShow = true;
    }
    $('#'+this.id + ' .dialogCancelBtn').html(val);
    if ( isShow ) {
      $('#'+this.id + ' .dialogCancelBtn').show();
    } else {
      $('#'+this.id + ' .dialogCancelBtn').hide();
    }
    return this;
  };
  
  /**
   * 销毁一个对话框
   * @return {Boolean} true 销毁成功 false 销毁失败
   */
  Dialog.prototype.destory = function() {
    $('#'+this.id).remove();
    _cacheIds[this.id] = undefined;
    return true;
  };
  
  
///////////////
// 私有方法
///////////////
  
  
  /**
   * 初始化事件绑定
   */
  function _initEvent(config)
  {
    var id = config.id;
    // 显示但动画未显示完全
    $('#'+id).on('show'   , config.showFn);
    // 动画显示完全
    $('#'+id).on('shown'  , config.shownFn);
    // 隐藏但动画未隐藏完毕
    $('#'+id).on('hide'   , config.hideFn);
    // 动画完全隐藏
    $('#'+id).on('hidden' , function(){
      config.hiddenFn();
      setTimeout(function(){
        if ( id.indexOf('dialogRandom') == 0 ) {
          dialog(id).destory();
        }
      }, 0);
    });
    // 取消按钮
    $('#'+id+' .dialogCancelBtn').on('click', function(){
      var returnVal = config.cancelFn();
      // 默认隐藏
      if ( returnVal !== false ) {
        dialog(id).hide();
      }
    });
    // 确定按钮
    $('#'+id+' .dialogOkBtn').on('click', function(){
      var returnVal = config.okFn();
      if ( returnVal !== false ) {
        dialog(id).hide();
      }
    });
    // 阻止focus
    $('#'+id).on('focus', function(e){
      e.stopPropagation();
      e.preventDefault();
    });
  }
  
  /**
   * 获取组装好的html
   * @param {Object} config 弹框配置
   * @reutrn {String}
   */
  function _initHtml(config) {
    var modalHtml = '\
      <div id="'+config.id+'" class="modal hide fade in" tabindex="-1" style="display: none;">\
        <div class="modal-header dialogHeader">\
          <button type="button" class="close dialogClose" data-dismiss="modal" aria-hidden="true">×</button>\
          <h3 class="dialogTitle">'+config.title+'</h3>\
        </div>\
        <div class="modal-body dialogContent">'+config.content+'</div>\
        <div class="modal-footer dialogFooter">\
          <a href="javascript:void(0);" class="btn dialogCancelBtn" '+(!config.showCancel ? 'style="display:none;"' : '')+'>'+config.cancelValue+'</a>\
          <a href="javascript:void(0);" class="btn btn-primary dialogOkBtn" '+(!config.showOk ? 'style="display:none;"' : '')+'>'+config.okValue+'</a>\
        </div>\
      </div>';
    return modalHtml;
  }
  
  /**
   * 初始化获取配置
   * @param {Object} config
   * @return {Object} conf
   */
  function _initConfig(config) {
    // content
    var conf = {};
    conf.title       = config.title       || '&nbsp;';
    conf.cancelValue = config.cancelValue || '取消';
    conf.okValue     = config.okValue     || '确定';
    conf.id          = config.id          || 'dialogRandom'+ (parseInt(Math.random()*1000)) +(new Date).getTime();
    conf.content     = config.content     || '';
    
    // callback
    conf.cancelFn      = typeof config.cancelFn == 'function' ? config.cancelFn : _emptyFn;
    conf.okFn          = typeof config.okFn     == 'function' ? config.okFn     : _emptyFn;
    conf.showFn        = typeof config.showFn   == 'function' ? config.showFn   : _emptyFn;
    conf.shownFn       = typeof config.shownFn  == 'function' ? config.shownFn  : _emptyFn;
    conf.hideFn        = typeof config.hideFn   == 'function' ? config.hideFn   : _emptyFn;
    conf.hiddenFn      = typeof config.hiddenFn == 'function' ? config.hiddenFn : _emptyFn;
    
    // 是否锁定，默认锁定
    conf.backdrop    = typeof config.lock   !== 'undefined' ? !!config.lock : true;
    conf.keyboard    = typeof config.esc    !== 'undefined' ? !!config.esc  : true;
    conf.show        = typeof config.show   !== 'undefined' ? !!config.show : true;
    conf.remote      = config.remote;
    
    // 页面元素部分
    conf.showCancel  = typeof config.showCancel  !== 'undefined' ? !!config.showCancel : true;
    conf.showOk      = typeof config.showOk      !== 'undefined' ? !!config.showOk     : true;
    
    return conf;
  }
  
  /**
   * 空函数
   */
  function _emptyFn(){ }
  
})(window);