// 1、生成引用样式文件
var _page = {
        // css 文件所在url
        _baseUrl:'http://nba.alltosun.net',
        // 动画加载css
        init : function() {
            var element = document.createElement('link');
            element.href = this._baseUrl + '/css/page.css';
            element.id = 'page-css';
            element.type = 'text/css';
            element.rel = 'stylesheet';
            document.body.appendChild(element);
        },

        prev : function(link) {
             return "<a href='" + link + "' class='pages-length'>上一页</a>";
        },
        
        next : function(link) {
            return "<a href='" + link + "' class='pages-length'>下一页</a>";
        },
        
        begin: function(link) {
            return "<a href='" + link + "' class='pages-length'>首页</a>";
        },
        end :  function(link) {
            return "<a href='" + link + "' class='pages-length'>末页</a>";
        },

        arrayToPage : function(numArr, linkArr, currentNum) {
            var html = '';

            var count = numArr.length, html;
            for (var i = 0; i < count; i++) {
                if (numArr[i] == currentNum) {
                    html = html + "<a href='" + linkArr[i] + "' class='curr' >" + numArr[i] + "</a>";
                } else {
                    html = html + "<a href='" + linkArr[i] + "'>" + numArr[i] + "</a>";
                }
            }
            
            return html;
        },
        
        build : function(numArr, linkArr, currentNum, beginLink, endLink, prevLink, nextLink, totalPage) {
            var html = '';
            
            if (!numArr instanceof Array || !linkArr instanceof Array) {
                if (console) console.log('args error');
                return;
            }
            
            html += this.begin(beginLink);

            if (currentNum > 1) {
                html += this.prev(prevLink);
            }
            
            html += this.arrayToPage(numArr, linkArr, currentNum);
            
            if (currentNum < totalPage) {
                html += this.next(nextLink);
                html += this.end(endLink);
            }
            
            return html;
        },
        
        show: function(numArr, linkArr, currentNum, beginLink, endLink, prevLink, nextLink, totalPage) {
            this.init();
            
            var html = '<div class="pages-wrap clearfix"><div class="pages right">';
            html = html + this.build(numArr, linkArr, currentNum, beginLink, endLink, prevLink, nextLink, totalPage)
            html = html + '</div></div>';
            
            return html;
        }
        
}
// 页码数量
var numArr = [1,2,3],
    // 页码数量对应的链接
    linkArr = ['http://t.vic.sina.com.cn/1', 'http://t.vic.sina.com.cn/2', 'http://t.vic.sina.com.cn/3'],
    // 上一个链接
    prevLink = 'http://t.vic.sina.com.cn/prev',
    // 下一个链接
    nextLink = 'http://t.vic.sina.com.cn/next',
    // 首页链接
    beginLink = 'http://t.vic.sina.com.cn/1';
    // 末页链接
    endLink = 'http://t.vic.sina.com.cn/end';
    // 当前页码
    currentPage = 3;
    // 总页码
    totalPage = 10; 

var test = _page.show(numArr, linkArr, currentPage, beginLink, endLink, prevLink , nextLink, totalPage);

window.onload = function(){
    document.getElementById('test').innerHTML = test;
}

