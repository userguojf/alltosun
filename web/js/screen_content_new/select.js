$(function () {
    var business_hall_id = [];
    var phoneName = "";
    var phoneVersion = "";
    var lock     = false;
// 品牌点击
    $('#brand .brand_list').on('click', function (e) {
        e.stopPropagation();
        e.preventDefault();
        $('#brand .select-list').addClass('hidden');
        $('#brand .brand_title').html($(this).html());
        $(this).siblings().removeClass('active');
        $(this).addClass('active');

        var brand_name = $(this).attr('value');
        $.post(model_url, {phone_name: brand_name}, function (json) {
            var html = '<p class="model_list active" value="">全部型号</p>';
            if (json.info == 'ok') {
                var data = json.result;
                for (var i = 0; i < data.length; i++) {
                    var version = data[i]['phone_version'];
                    version += data[i]['phone_version_nickname'] ? '(' + data[i]['phone_version_nickname'] + ')' : '';
                    html += '<p class="model_list" value="' + data[i]['phone_version'] + '"> ' + version + '</p>';
                }
            }
            $('#model .model_title').html('全部型号');
            $('#model .select-list').html(html);
        }, 'json')
    });

// 型号点击
    $('#model').on('click', '.model_list', function (e) {
        e.stopPropagation();
        e.preventDefault();

        $('#model .select-list').addClass('hidden');
        $('#model .model_title').html($(this).html());
        $(this).siblings().removeClass('active');
        $(this).addClass('active');
    });

//省份点击
    $('#province .province_list').on('click', function (e) {
        e.stopPropagation();
        e.preventDefault();

        $('#province .select-list').addClass('hidden');
        $('#province .province_title').html($(this).html());
        $(this).siblings().removeClass('active');
        $(this).addClass('active');

        var province_id = $(this).attr('value');

        $.post(city_url, {province_id: province_id}, function (json) {
            var html = '<p class="city_list active" value="0">全部城市(省份下所有城市)</p>';

            if (json.msg == 'ok') {
                var jsonnum = eval(json.city_info);
                for (var i = 0; i < jsonnum.length; i++) {
                    html += "<p class='city_list' value='" + jsonnum[i].id + "'>" + jsonnum[i].name + "</p>";
                }
            }
            $('#city .city_title').html('全部城市(省份下所有城市)');
            $('#city .select-list').html(html);
            $('#area .area_title').html('全部区县(城市下所有区县)');
            $('#area .select-list').html('<p class="area_list active" value="0">全部区县(城市下所有区县)</p>');
            $('#business .business_title').html('全部营业厅(区县下所有营业厅)');
            $('#business .select-list').html('<p class="business_list active selectInputBusinessHall" value="0">全部营业厅(区县下所有营业厅)</p>');
        }, 'json');
    });

//城市点击
    $('#city').on('click', '.city_list', function (e) {
        e.stopPropagation();
        e.preventDefault();

        $('#city .select-list').addClass('hidden');
        $('#city .city_title').html($(this).html());
        $(this).siblings().removeClass('active');
        $(this).addClass('active');

        var city_id = $(this).attr('value');

        $.post(area_url, {city_id: city_id}, function (json) {
            var html = '<p class="area_list active" value="0">全部区县(城市下所有区县)</p>';

            if (json.msg == 'ok') {
                var jsonnum = eval(json.area_info);
                for (var i = 0; i < jsonnum.length; i++) {
                    html += "<p class='area_list' value='" + jsonnum[i].id + "'>" + jsonnum[i].name + "</p>";
                }
            }
            $('#area .area_title').html('全部区县(城市下所有区县)');
            $('#area .select-list').html(html);
            $('#business .business_title').html('全部营业厅(区县下所有营业厅)');
            $('#business .select-list').html('<p class="business_list selectInputBusinessHall active" value="0">全部营业厅(区县下所有营业厅)</p>');
        }, 'json');
    });

//区县点击
    $('#area').on('click', '.area_list', function (e) {
        e.stopPropagation();
        e.preventDefault();

        $('#area .select-list').addClass('hidden');
        $('#area .area_title').html($(this).html());
        $(this).siblings().removeClass('active');
        $(this).addClass('active');

        var area_id = $(this).attr('value');

        $.post(business_url, {area_id: area_id}, function (json) {
            var html = '<p class="business_list selectInputBusinessHall active" value="0">全部营业厅(区县下所有营业厅)</p>';
            if (json.msg == 'ok') {
                var jsonnum = eval(json.business_info);
                for (var i = 0; i < jsonnum.length; i++) {
                    html += "<p class='business_list selectInputBusinessHall' value='" + jsonnum[i].id + "'>" + jsonnum[i].title + "</p>";
                }
            }
            $('#business .business_title').html('全部营业厅(区县下所有营业厅)');
            $('#business .select-list').html(html);
        }, 'json');
    });

//营业厅点击
    $('#business').on('click', '.business_list', function (e) {
        e.stopPropagation();
        e.preventDefault();

        $('#business .select-list').addClass('hidden');
        $('#business .business_title').html($(this).html());
        $(this).siblings().removeClass('active');
        $(this).addClass('active');

    });

// 添加至右侧列表按钮
    $('#add_right_list').on('click', function () {

        // 品牌
        var brand = $('.brand_select .active').attr('value');
        var brandh = $('.brand_select .active').html();
        // 型号
        var model = $('.model_select .active').attr('value');
        var modelh = $('.model_select .active').html();
        // 省
        var province = $('.province_select .active').attr('value');
        var provinceh = $('.province_select .active').html();
        // 市
        var city = $('.city_select .active').attr('value');
        var cityh = $('.city_select .active').html();
        // 区
        var area = $('.area_select .active').attr('value');
        var areah = $('.area_select .active').html();
        // 厅
        var business = $('.business_select .active').attr('value');
        var businessh = $('.business_select .active').html();
        // 获取类型
        var type = $('#content_type').val();
        var typeh = $('#content_type_text').html();
        var lianjie = '';
        if (province != 0 && province != undefined) {
            lianjie += provinceh;
        }
        if (city != 0 && city != undefined){
            lianjie += '->'+cityh;
        }
        if (area != 0 && area != undefined) {
            lianjie += '->'+areah;
        }
        if (business != 0 && business != undefined) {
            lianjie += '->'+businessh;
        }
        if ((province==0 || province==undefined) && (city==0 || city==undefined) && (area==0 || area==undefined)  && (business==0 || business==undefined) ) {
            $.ajax({
                url : path_url,
                type: 'POST',
                data : { res_id:res_id, res_name:res_name },
                dataType: 'JSON',
                async: false,
                success: function (res) {
                    lianjie += res.info;
                },
            });
        }

        var new_content_id = $('#content_id').val(); //发布内容id
        //营业厅id列表
        var business_hall_list = $('.selectInputBusinessHall');
        $.each(business_hall_list, function (i, n) {
            business_hall_id[i] = $(n).attr('value');
        });

        province_id = province;
        city_id = city;
        area_id = area;
        phoneName = brand;
        phoneVersion = model;
        lock = true;

        var item = '<div class="tr tr_list clearfix">';
        item += '<div class="td info left">'+typeh+'</div>';
        item += '<div class="td desc left">'+lianjie+'</div>';
        item += '<input type="hidden" name="put[province][][" value="'+province+'" />';
        item += '<input type="hidden" name="put[city][][" value="'+city+'" />';
        item += '</div>';
        // 点击添加至右侧列表，发送ajax到后台
        /*$.post(puturl, { content_id:new_content_id,business_hall_ids:business_hall_id,province_id:province_id,city_id:city_id,area_id:area_id, phone_name:phoneName, phone_version:phoneVersion, }, function (json) {
            lock = false;
            if (json.info == 'ok') {
                // $('#item-tbody').load(siteUrl+"/screen_content_new/admin/test_content?content_id="+new_content_id, function () {
                //     //套餐信息不能为空
                //     // $('.set_meal_input').addClass('required');
                // });
                $('#item-tbody').append(item);
                alert('发布成功');
            } else {
                alert(json.info);
            }
        },'json');*/
        $('#item-tbody').append(item);
        if ($('.tr_list').length == 0) {
            $('.tr_list').remove();
            $('.tr_no').addClass('hidden');
        }
        // 重置省市区下拉框
        $('#province .province_list').each(function () {
            if ($(this).attr('value') == 0) {
                $(this).trigger('click');
            }
        });
        // 重置品牌，型号下拉框
        $('#brand .brand_list').each(function () {
            if ($(this).attr('value') == 0) {
                $(this).trigger('click');
            }
        });

    });

    // 点击tr移除整行
    $('#item-tbody').on('click', '.tr_list', function (e) {
        e.stopPropagation();
        e.preventDefault();
        if (window.confirm('确认移除此项吗？')) {
            $(this).remove();
        }
    });
});
