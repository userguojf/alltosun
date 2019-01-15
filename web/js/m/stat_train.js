var stat =  function(){ };

stat.prototype = {
    _btn    : '.cps_stat',
    _url    : AnUrl('stat/ajax/stat_train'),
    _params :{},

    init:function(){
       var that = this;
       $(this._btn).live('click',function(event) {
           event.preventDefault();
           that.ajaxDate(this);
       })
    },

    ajaxDate:function(obj) {
        this._params.res_name = $(obj).attr('res_name');
        this._params.res_id   = $(obj).attr('res_id');

        $.post(this._url,this._params,function(json){
            var href = $(obj).attr('href');
            window.location.href = href;
        })
    }
};

$(function(){
   var statObi = new stat();
   statObi.init();
})