/**
 * 加载3次,然后进行分页功能函数
 */
$(function(){
    var base = window.base;
    // 暴漏全局变量
    var ajaxPage = function(obj){
        // 初始化加载小分页数
         var beginNum = 1;
    };
    
    // 初始化最大小分页页码
    ajaxPage.prototype.maxNum = 3;
    
    // 公共变量
    ajaxPage.prototype.pageNo = 1;
    
    /**
     * 加载数据进行分页
     * @param  url  请求地址
     * @param  param 请求参数
     * @param  obj   内容数据容器
     * @param  that  分页的容器对象
     */
     ajaxPage.prototype.loadAjax = function(url, params, obj, that, successHtml){
        var  html = '', isShowLoading = undefined;
        
        // 首次展示html
        if (params.start_num == 1) {
            html = '';
            // undefined 不显示 loading
            isShowLoading = undefined;
        } else {
            // 组合之前已有的html展示
            html = obj.html();
            isShowLoading = 0;
        }

        // base.loadData ajax请求
        base.loadData(url, params, function(json){
            // 是否要展示继续加载
            if (json.is_more != 1) {
                that.hide();
            } else {
                if (params.start_num == 1) {
                    that.show();
                }
            }
            html = html +  successHtml(json.list);

            
            if (html && obj) obj.html(html);
             
            if (json.page instanceof Array == false) {
                
                base.makeHtmlPage(params, json, $('#PagerBox'));

                // 加载分页列表
                if (json.page.current_page < json.page.last_page && params.start_num == 3) {
                    $('#PagerBox').show();
                } else {
                    $('#PagerBox').hide();
                }
            }
            
            if (json.is_more == '0') $('.loadMores').hide();

        }, obj, isShowLoading);
    }
    
    window.ajaxPage = ajaxPage;
})