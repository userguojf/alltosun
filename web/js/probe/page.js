/**
 * 分页接口
 *
 * @param	Array	数据列表
 * @param	Int		每页展示多少条
 */
function page(list, per_page, func1, func2)
{
	this.per_page = per_page;			// 每页显示多少条
    this.list     = list;				// 列表
    this.total    = this.list.length;	// 共条数
    this.func1    = func1;
    this.func2    = func2;

    this.event();
    // 显示页码选择按钮
    this.pages(1);
}

/**
 * 类的原型
 *
 */
page.prototype = {
	/**
	 * 显示上一个下一个或者1234之类的选项
	 *
	 * @param	当前页码
	 */
    pages: function(page)
    {
    	if ( this.total < 1 ) {
    		return false;
    	}

    	// 总页数
    	var num = parseInt(this.total / this.per_page);

    	if ( num * this.per_page < this.total ) {
    		num ++;
    	}

    	// 开始页码
    	var start = page - 2;

    	if ( start <= 0 ) {
    		start = 1;
    	}

    	// 结束页码
    	var end  = start + 5;

    	if ( end >= num ) {
    		start = num - 5 > 0 ? num - 5 : 1;
    		end   = num;
    	}

    	if ( typeof(this.func2) == 'function' ) {
    		this.func2(page, start, end, num);
    	} else {
    		var html = '<div class="pagination pagination-right"><ul>';

    		// 上一页
        	if ( page * 1 > 1 ) {
        		html += '<li class="js_pageno" page="'+ (page * 1 - 1) +'"><a href="javascript:;">&lt;</a></li>';
        	}

        	for ( ; start <= end; start ++ ) {
        		var curr = page == start ? 'curr' : '';
        		html    += '<li class="js_pageno" page="'+ start +'"><a href="javascript:;" class="'+ curr +'">'+ start +'</a></li> ';
        	}

        	// 下一页
        	if ( page * 1 + 1 <= num ) {
        		html += '<li class="js_pageno" page="'+ (page * 1 + 1) +'"><a href="javascript:;">&gt;</a></li>';
        	}

        	html += '<li><a href="javascript:void(0);">共 '+ num +' 页</a></li>';
        	html += '</ul></div>';

        	$('.js_pages').html(html);
    	}

    	// 列表开始
    	start = (page - 1) * this.per_page;
    	// 列表结束
    	end   = start + this.per_page;

    	if ( end > this.total ) {
    		end = this.total;
    	}

    	var list = [];

    	for ( var i = 0; start < end; start ++, i ++ ) {
    		list[i] = this.list[start];
    	}

    	if ( typeof(this.func1) == 'function' ) {
    		this.func1(list);
    	}
    },
    /**
     * 点击事件
     *
     * @param	页码
     */
    event: function()
    {
    	var page = this;

    	$('.js_pageno').live('click', function(){
    		var pageno = $(this).attr('page');
    		page.pages(pageno);
    	});
    }
};