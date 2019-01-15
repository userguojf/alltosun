!function($){
  var dialog = function(params){
    this.defaults = {
        'title':'提示信息',
        'content':'<div>内容为空</div>',
        'okValue':'确定',
        'cancelValue':'取消',
        'ok' : null,
        'cancel': null,
        'showMask':true,
        'vAlign':'middle',
        'init':null,
        'close':null,
        'hideClose':0
    };
    this.opts = $.extend({},this.defaults, params);
    
    this.outerBox = '';
    this.headBox = '';
    this.innerBox = '';
    this.footerBox = '';
    this.mask = '';
    this.maskClose = false;
    this.okBtn = '';
    this.cancelBtn = '';
    this.closeBtn = '';
    this.height = '';
    this.width = '';
    this.top = '';
    this.left = '';
    this.dialogInterval = null; 
    
    this._init();
    
    window.qDialog = this;
  }

  $.extend(dialog.prototype, {
    _init : function(){
      if ($(".aceDialog").length > 0) {
        return false;
      }
      
      // admin
        this.outerBox = $('<div class="aceDialog float-prompt hidden" style="display:block; margin:0;"></div>');
        this.headBox = $('<div class="aceHeader float-prompt-title"></div>');
        this.innerBox = $('<div class="float-prompt-con"></div>');
        this.contentBox = $('<div class="aceContent"></div>');
        this.footerBox = $('<div class="aceFooter clearfix"></div>');
        this.mask = $('<div class="aceMask float-bg"></div>').click(function(e){
          e.stopPropagation();
          if (qDialog.opts.maskClose == true) {
            qDialog.close();
          }
        });
        this.okBtn = $('<button type="button" class="okBtn btn btn-success btn-sm right">'+this.opts.okValue+'</button>').click(function(){
          if (qDialog.opts.ok != null) {
            qDialog.opts.ok();
          } else {
            qDialog.close();
          }
        });
        //this.cancelBtn = $('<a href="javascript:void(0);" id="cancelBtn" class="cancelBtn btn">'+this.opts.cancelValue+'</a>').click(function(){
          //qDialog.close();
        //});
        this.closeBtn = $('<a href="javascript:void(0);" class="icon-close">关闭</a>').click(function(){
          qDialog.close();
        });
        
        this.headBox.append(this.closeBtn).append(this.opts.title);
        
        // front
      
      $('body').append(this.outerBox);
      (this.outerBox).append(this.headBox).append(this.innerBox);
      this.innerBox.append(this.contentBox).append(this.footerBox);
      if (this.opts.cancelValue != '') {
        this.footerBox.append(this.cancelBtn);
      }
      if (this.opts.okValue != '') {
        this.footerBox.append(this.okBtn);
      }
            
      this.contentBox.append(this.opts.content);
      this.mask.insertBefore(this.outerBox);
      
      if ($('.aceContent').html() == '') {
        $('.aceContent').css('padding', '10px');
      }
      
      this.height = $(".aceDialog").height();
      this.show();
    },
    show:function(){
        if (this.outerBox.hasClass('hidden')) {
          this.outerBox.css("top", "-800px").removeClass('hidden');
          if (this.opts.init != null && $('.aceDialog .edui-editor').length <= 0) {
            this.opts.init();
          }
          
          this.width = $(".aceDialog").width();
          this.height = $(".aceDialog").height();
          //console.log(this.width, $(document).width(), $(window).width());
          this.left = Math.floor(($(document).width()-this.width)/2);
          if (this.left < 0) {
            this.left = 0;
          }
          this.outerBox.removeClass('hidden').css({ "top":"-"+(this.height + 200)+"px", "left":this.left+'px' });
          this.showMask();
          
          this.top = Math.floor(($(window).height()-this.height)/2) + getScrollTop() - 50;
          if (this.top < 0) {
            this.top = 0;
          }
          $('.aceDialog').hide().css('top', this.top+'px').show();
        }
    },
    close:function(){
      if (!this.outerBox.hasClass('hidden')) {
        this.hideMask();
        this.outerBox.addClass('hidden').remove();
        if (this.opts.close != null) {
          this.opts.close();
        }
      }
    },
    showMask:function()
    {
      this.mask.removeClass('hidden');
    },
    hideMask:function()
    {
      $(".aceMask").hide().remove();
    }
  });
  
  if(!window.ace){
    ace = {};
  }
  $.extend(ace, {
    dialog:function(e){
      return new dialog(e);
    }
  })
}(jQuery);