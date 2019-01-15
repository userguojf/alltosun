$(function(){

    /**
     * Created by sunxs on 15/12/29.
     *
     * 本js主要用于省份的三级联动
     */

    function isObj(obj)
    {

        if(typeof obj !=='undefined') {

            obj.options.length = 1;

        }
    }






    var op = $('#p');//省份
    var oc = $('#c');//市
    var od = $('#d');//区
    var ob = $('#business'); //营业厅

    var cityUrl = siteUrl+'/city/ajax/get_city_list';

    op.change(function() {

        var pid= $(this).val();

        var params = { 'pid':pid };

        $.post(cityUrl,params,function(json){

            isObj(oc[0]);

            isObj(od[0]);

            isObj(ob[0]);

            if(json.info=='ok') {

                $.each(json.data,function(i,element) {

                    var sel = '';

                    html='';

                    html += '<option value="'+ (element['id']) +'"'+sel+'>'+element['name']+'</option>';

                    oc.append(html);


                });

            }

        },'json');

    });


    //城市选项卡
    oc.change(function(){


        var city_id = $('#c').val();


        var districtUrl = siteUrl+'/city/ajax/get_district_list';

        $.post(districtUrl,{ 'city_id':city_id },function(json) {

            isObj(od[0]);

            isObj(ob[0]);

            if(json.info == 'ok') {

                $.each(json.data,function(i,element) {

                    html  = '';

                    html += '<option value="'+ (element['id']) +'">'+element['name']+'</option>';

                    od.append(html);

                });

            }
        },'json');

    });


    od.change(function(){

        //获取省份id
        var privince_id = $('#p').val();

        //获取市id
        var city_id     = $('#c').val();

        //获取地区id
        var area_id      = $('#d').val();


        var businessUrl = siteUrl + '/city/ajax/get_business_hall_list';

        $.post(businessUrl,{ 'privince_id':privince_id,'city_id':city_id,'area_id':area_id },function(json) {

            isObj(ob[0]);

            if(json.info == 'ok') {

                $.each(json.data,function(i,element) {

                    html  = '';

                    html += '<option value="'+ (element['id']) +'">'+element['title']+'</option>';

                    ob.append(html);

                });

            }
        },'json');

    });

});