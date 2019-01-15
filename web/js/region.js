/**
 * 省市区多级联动
 *
 * @rely    jquery
 *
 * @author  wangl
 */

/**
 * 区域类
 *
 * @author  wangl
 */
function region(p_id, c_id, a_id, b_id)
{
    this.p_id  = p_id;
    this.c_id  = c_id;
    this.a_id  = a_id;
    this.b_id  = b_id;
}

/**
 * 类原型
 *
 * @author  wangl
 */
region.prototype = {
    /**
     * 初始化
     *
     * @author  wangl
     */
    init: function(name)
    {
        var post = {
            'res_name': name
        };
        var _class = '';

        var select_id = 0;

        if ( name == 'province' ) {
            _class    = '.js_province';
            select_id = this.p_id;
        } else if ( name == 'city' ) {
            _class    = '.js_city';
            select_id = this.c_id;
            post.p_id = this.p_id;
        } else if ( name == 'area' ) {
            _class    = '.js_area';
            select_id = this.a_id;
            post.c_id = this.c_id;
        } else if ( name == 'business' ) {
            _class    = '.js_business';
            select_id = this.b_id;
            post.a_id = this.a_id;
        } else {
            return false;
        }

        var list = this.get_list(name, post);
        var html = this.callback(name, list, select_id);
        $(_class).html(html);
    },
    start: function()
    {
        // 初始化省
        this.init('province');

        // 初始化市
        if ( typeof(this.p_id) != 'undefined' ) {
            this.init('city');
        }

        // 初始化区
        if ( typeof(this.c_id) != 'undefined' ) {
            this.init('area');
        }

        // 初始化营业厅
        if ( typeof(this.a_id) != 'undefined' ) {
            this.init('business');
        }

        this.event();
    },
    /**
     * 获取省市区列表
     *
     * @author  wangl
     */
    get_list: function(name, post)
    {
        // 设置ajax同步请求
        $.ajaxSetup({   
            async: false  
        });

        var list  = [];
        var url   = siteUrl + '/region/get_list';

        $.post(url, post, function(json){
            if ( json.info != 'ok' ) {
                alert(json.info);
                return false;
            }
            list = json.list; 
        }, 'json');

        // 设置ajax异步请求
        $.ajaxSetup({
            async: true
        });

        return list;
    },
    callback: function(name, list, select_id)
    {
        var html = '<option value=0>请选择</option>';

        for( var i = 0; i < list.length; i++ ) {
            if ( select_id == list[i].id ) {
                html += '<option selected value="'+list[i].id+'"> '+list[i].name+'</option>';
            } else {
                html += '<option value="'+list[i].id+'"> '+list[i].name+'</option>';
            }
        }

        return html;
    },
    event: function()
    {
        var _this = this;

        /**
         * 改变选择的省
         *
         * @author  wangl
         */
        $('.js_province').change(function(){
            var p_id   = $(this).val();
            _this.p_id = p_id;
            _this.c_id = 0;
            _this.a_id = 0;
            _this.b_id = 0;

            if ( p_id == 0 ) {
                $('.js_city').html(_this.callback('city', [], 0));
                $('.js_area').html(_this.callback('area', [], 0));
                $('.js_business').html(_this.callback('business', [], 0));
            } else {
                _this.init('city');
                $('.js_area').html(_this.callback('area', [], 0));
                $('.js_business').html(_this.callback('business', [], 0));
            }
        });

        /**
         * 改变选择的市
         *
         * @author  wangl
         */
        $('.js_city').change(function(){
            var c_id   = $(this).val();
            _this.c_id = c_id;
            _this.a_id = 0;
            _this.b_id = 0;

            if ( c_id == 0 ) {
                _this.callback('area', [], 0);
                _this.callback('business', [], 0);
            } else {
                _this.init('area');
                $('.js_business').html(_this.callback('business', [], 0));
            }
        });

        /**
         * 改变选择的区
         *
         * @author  wangl
         */
        $('.js_area').change(function(){
            var a_id   = $(this).val();
            _this.a_id = a_id;
            _this.b_id = 0;

            if ( a_id == 0 ) {
                _this.callback('business', [], 0);
            } else {
                _this.init('business');
            }
        });

        /**
         * 改变所选择的营业厅
         *
         * @author  wangl
         */
        $('.js_business').change(function(){
            var b_id   = $(this).val();
        });
    }
};
