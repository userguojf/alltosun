define(function(require, exports, module ){
    var $ = require('jquery');
    
    /**
     * 计算字数区分中英文
     */
    exports.strLen = function (sString) {
        var sStr,iCount,i,strTemp ;
        iCount = 0 ;
        sStr = sString.split("");
        for (i = 0 ; i < sStr.length ; i ++) {
            strTemp = escape(sStr[i]);
            if (strTemp.indexOf("%u",0) == -1) {
                iCount = iCount + 1 ;
            } else {
                iCount = iCount + 2 ;
            }
        }
        return Math.floor(iCount) ;
    };
})