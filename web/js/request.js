define(function(require, exports, module){
    var $ = require('jquery');
    var f = require('jquery.func')
    var u = require('module/url');
    
   /**
     * 简化执行post方法
     * @param verify 验证函数
     * @param success 成功函数
     * @param error 失败函数
     * 
     * demo:
     * Dom 节点规范：<a  data-params="option_id=39&vote_id=29" data-href="http://201406worldcup.sinaapp.com/index.php?anu=vote/ajax/save_vote">反对</a>
     *     data-params post请求参数组合
     *     data-href   post 请求地址
     *     
     * 
     *   $('.saveVote').click(function(){
     *      request.call(this, function(params){
     *          // 验证
     *          return true;
     *          
     *      }, function(json){
     *      
     *          alert(json.info);
     *          // 结果
     *          
     *      }, function(json){
     *      
     *          alert(json.info);
     *          // 失败结果
     *      })
     *  })
     *  
     */
    exports.request = function(verify, success, error) {
        // 
        var strParams = $(this).data('params'), 
            url = u.url($(this).data('href')), 
            params = f.parseParams(strParams);
        
        // 参数验证
        if (!verify(params)) return false;
        
        // 发送请求
        f.post(url, params, function(json){

            // 请求成功
            success(json);
        }, function(json){
            
            // 请求失败
            if (error != undefined && typeof error == 'function') error(json);
        })
    }
})