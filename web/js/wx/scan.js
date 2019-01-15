/**
 * 初始化微信验证
 *
 * @author  wangl
 */
function wx_init()
{
    this._success = '';
    this._error   = '';

    this.init();
}

wx_init.prototype = {
    init: function () {
        var _this = this;

        // 获取appid等参数
        $.post(siteUrl + '/qydev/scan/get_param', {}, function(json){
            if ( json.info != 'ok' ) {
                if ( typeof(_this._error) == 'function' ) {
                    _this._error(json.info);
                } else {
                    alert(json.info);
                }
                return ;
            }

            var param = json.param;

            param.debug     = false;
            param.jsApiList = ['scanQRCode'];

            wx.config(param);
        }, 'json');

        // 微信验证成功
        wx.ready(function(){
            if ( typeof(_this._success) == 'function' ) {
                _this._success();
            }
        });

        // 微信验证失败
        wx.error(function(err){
            if ( typeof(err.errMsg) != 'undefined' ) {
                var msg = err.errMsg;
            } else {
                var msg = '微信验证失败';
            }

            if ( typeof(_this._error) == 'function' ) {
                _this._error(msg);
            } else {
                alert(msg);
            }
        });
    },
    success: function (call) {
        if ( typeof(call) == 'function' ) {
            this._success = call;
        }
        return this;
    },
    error: function (call) {
        if ( typeof(call) == 'function' ) {
            this._error = call;
        }
        return this;
    }
};

function init()
{
    var wxinit = new wx_init;

    return wxinit;
}

/**
 * 微信扫一扫
 *
 * @author  wangl
 */
function wx_scan()
{
    // 成功的回调函数
    this._success = '';
    // 失败的回调函数
    this._error   = '';
}

wx_scan.prototype = {
    /**
     * 设置成功的回调函数
     *
     * @param   function    成功的回调函数
     *
     * @author  wangl
     */
    success: function(call) {
        if ( typeof(call) == 'function' ) {
            this._success = call;
        }
        return this;
    },
    /**
     * 设置失败的回调函数
     *
     * @param   function    失败的回调函数
     *
     * @author  wangl
     */
    error: function (call) {
        if ( typeof(call) == 'function' ) {
            this._error = call;
        }
        return this;
    }
};

/**
 * 调用扫一扫
 *
 * @author  wangl
 */
function scan()
{
    var wxscan = new wx_scan;

    wx.scanQRCode({
        desc: 'scanQRCode desc',
        needResult: 1,              // 默认为0，扫描结果由微信处理，1则直接返回扫描结果，
        scanType: ["qrCode"],       // 可以指定扫二维码还是一维码，默认二者都有
        success: function (data) {
            // 扫描成功
            if ( data.errMsg == 'scanQRCode:ok' ) {
                // 并且设置有成功的回调函数
                if ( typeof(wxscan._success) == 'function' ) {
                    wxscan._success(data.resultStr);
                }
            // 扫描失败
            } else {
                // 并且有失败的回调函数
                if ( typeof(wxscan._error) == 'function' ) {
                    wxscan._error(data.errMsg);
                }
            }
        },
        error: function(data){
            // 扫描失败
            if ( typeof(wxscan._error) == 'function' ) {
                wxscan._error(data.errMsg);
            }
            /*
            if( data.errMsg.indexOf('function_not_exist') > 0 ){
                alert('版本过低请升级');
            }
            */
        }
    });

    return wxscan;
}
