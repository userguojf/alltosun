/**
 * 省市区多级联动
 *
 * @author  wangl
 */

/**
 * 当前选择的省
 *
 * @var Int
 */
var select_p_id = 0;

/**
 * 当前选择的市
 *
 * @author  wangl
 */
var select_c_id = 0;

/**
 * 当前选择的区
 *
 * @author  wangl
 */
var select_a_id = 0;

/**
 * 当前选择的营业厅
 *
 * @author  wangl
 */
var select_b_id = 0;

/**
 * 获取当前选择的地区
 *
 * @param   String    地区名
 * @return  Int
 *
 * @author  wangl
 */
function get_select(name)
{
    // 省
    if ( name == 'province' ) {
        var val = select_p_id;
    // 市
    } else if ( name == 'city' ) {
        var val = select_c_id;
    // 区
    } else if ( name == 'area' ) {
        var val = select_a_id;
    // 营业厅
    } else if ( name == 'business' ) {
        var val = select_b_id;
    // 其他
    } else {
        var val = 0;
    }
    return val;
}

/**
 * 设置当前选择状态
 *
 * @param   String  地区名
 * @param   Int     地区ID
 *
 * @author  wangl
 */
function set_select(name, val)
{
    // 不是数字，默认为0
    if ( isNaN(val) ) {
        val = 0;
    } else {
        // 转为Int
        val = parseInt(val);
    }

    // 省
    if ( name == 'province' ) {
        select_p_id = val;
    // 市
    } else if ( name == 'city' ) {
        select_c_id = val;
    // 区
    } else if ( name == 'area' ) {
        select_a_id = val;
    // 营业厅
    } else if ( name == 'business' ) {
        select_b_id = val;
    }
}

/**
 * 
 *
 *
 *
 */
function region(p_id, c_id, a_id, b_id)
{
    set_select('province', p_id);
    set_select('city', c_id);
    set_select('area', a_id);
    set_select('business', b_id);

    get_list('province');
    get_list('city');
    get_list('area');
    get_list('business');
}

/**
 * 改变选择的省
 *
 * @author  wangl
 */
$('.js_province').change(function(){
    var p_id   = $(this).val();

    set_select('province', p_id);
    set_select('city', 0);
    set_select('area', 0);
    set_select('business', 0);

    get_list('city');
    //get_list('area');
    //get_list('business');
});

/**
 * 改变选择的市
 *
 * @author  wangl
 */
$('.js_city').change(function(){
    var c_id   = $(this).val();

    set_select('city', c_id);
    set_select('area', 0);
    set_select('business', 0);

    get_list('area');
    //get_list('business');
});

/**
 * 改变选择的区
 *
 * @author  wangl
 */
$('.js_area').change(function(){
    var a_id   = $(this).val();

    set_select('area', a_id);
    set_select('business', 0);

    get_list('business');
});

/**
 * 改变所选择的营业厅
 *
 * @author  wangl
 */
$('.js_business').change(function(){
    var b_id   = $(this).val();

    set_select('business', b_id);
});

/**
 * 设置下拉选项
 *
 * @author  wangl
 */
function set_opt(name, html)
{
    var _class = '.js_'+ name;

    $(_class).html(html);
}

/**
 * 获取列表的回调函数
 *
 * @author  wangl
 */
function callback(name, list)
{
    var select_id = get_select(name);
    var html      = '<option value=0>请选择</option>';

    if ( typeof(list) == 'undefined' ) {
        list = [];
    }
    
    var business_hall_titles= [ ];
    
    for( var i = 0; i < list.length; i++ ) {
        //自动完成准备
        if (name == 'business') {
          business_hall_titles.push({ label: list[i]['name'], id: list[i]['id'] });
        }
        
        if ( select_id == list[i].id ) {
            html += '<option selected value="'+list[i].id+'"> '+list[i].name+'</option>';
        } else {
            html += '<option value="'+list[i].id+'"> '+list[i].name+'</option>';
        }
    }
    if (name == 'business') {
      //营业厅自动完成
      businessAutocomplete(business_hall_titles);
    } else {
      set_opt(name, html);
    }
    
    
}

/**
 * 获取地区列表
 *
 * @author  wangl
 */
function get_list(name)
{
    var post = {
        'res_name': name
    };
    var url  = siteUrl + '/region/get_list';

    // 获取城市列表
    if ( name == 'city' ) {
        // 获取当前选择的省
        var p_id = get_select('province');

        if ( p_id ) {
            post.p_id = p_id;
        } else {
            return callback(name);
        }
    // 获取区列表
    } else if ( name == 'area' ) {
        // 获取当前选择的市
        var c_id = get_select('city');

        if ( c_id ) {
            console.log(c_id, 'true');
            post.c_id = c_id;
        } else {
            console.log(c_id, 'false');
            return callback(name);
        }
    // 获取营业厅列表
    } else if ( name == 'business' ) {
        var a_id = get_select('area');

        if ( a_id ) {
            post.a_id = a_id;
        } else {
            return callback(name);
        }
    }

    $.post(url, post, function(json){
        if ( json.info != 'ok' ) {
            alert(json.info);
            return false;
        }
        callback(name, json.list);
    }, 'json');
}
