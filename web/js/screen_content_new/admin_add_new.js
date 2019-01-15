$(function () {
    //加载套餐图（修改时）
    if (defaultType == 5) {
        $('.set_meal_add').load(siteUrl+"/screen_content_new/admin/set_meal_add?content_id="+contentId, function () {
            //套餐信息不能为空
            $('.set_meal_input').addClass('required');
        });
    }

    // 点击下拉框展示或隐藏
    $('.select').on('click', function () {
        $(this).find('.select-list').toggleClass('hidden');
    });
    // 点击下拉框的单个标签事件
    $('.selectType .content_type').on('click', function (e) {
        e.stopPropagation();
        e.preventDefault();

        //图片初始化
        initImage();
        // 视频初始化
        initVideo();
        // 链接初始化
        initUrl();
        // 宣传图初始化
        initType4();
        // 套餐图初始化
        initSetMealImg();

        var this_html = $(this).html();
        var type = $(this).attr('value');
        $('#content_type').val(type);
        $('#content_type_text').html(this_html);
        $(this).siblings().removeClass('active');
        $(this).addClass('active');
        $(this).parent('.selectType').addClass('hidden');
        if (type == 1) {
            // 图片初始化
            $('.uploadImg').removeClass('hidden');
            $('#fanwei').removeClass('hidden');

        } else if (type == 2) {
            // 视频初始化
            $('.uploadvideo').removeClass('hidden');
            $('#fanwei').removeClass('hidden');

        } else if (type == 3) {
            // 链接初始化
            $('.uploadlink').removeClass('hidden');
            $('#fanwei').removeClass('hidden');

        } else if (type == 4) {
            // 宣传图初始化
            $('.uploadImg').removeClass('hidden');
            $('.font-color').removeClass('hidden');
            $('.price').removeClass('hidden');
            $('.IsSpecify').removeClass('hidden');
        } else if (type == 5) {
            $('.selectSetMealImg').removeClass('hidden');
            $('.font-color').removeClass('hidden');
            $('.js_import_set_meal').removeClass('hidden');
            $('#fanwei').removeClass('hidden');

            $('input:radio[name="set_meal"]').eq(0).attr('checked', 'checked');
            //加载套餐图
            $('.set_meal_add').load(siteUrl+"/screen_content_new/admin/set_meal_add?content_id="+contentId, function () {
                //套餐信息不能为空
                $('.set_meal_input').addClass('required');
            });
        }

        //解除轮播参数的禁止编辑
        removeDisabled(type);
    });

    // 图片上传预览
    $(".js_perUpArea").click(function () {
        $(this).closest('.js_perUpWrap').find('.js_perUpFile').trigger('click');
    });

    $(".js_perUpFile").each(function (i) {
        $(this).change(function (e) {
            var type = $('#select_type').find('.active').attr('value');
            if (type == 1 || type == 4) {
                handleFileSelect($(this), function (data) {
                    $('#box_upload').addClass('hidden');
                    $('#c_upload').removeClass('hidden');
                    //判断是否为动图
                    var is = isAnimatedGif(data);
                    //动图
                    if (is === true) {
                        //禁止轮播时长编辑
                        $('.js_roll_interval').val(0);
                        $('.js_roll_interval').attr('disabled', true);
                        //开放轮播次数编辑
                        $('.js_roll_num').val(1);
                        $('.js_roll_num').attr('disabled', false);
                        //静图
                    } else if (is === false) {
                        //开启轮播时长编辑
                        $('.js_roll_interval').val(10);
                        $('.js_roll_interval').attr('disabled', false);
                        //禁止轮播次数编辑
                        $('.js_roll_num').val(1);
                        $('.js_roll_num').attr('disabled', true);
                        //未知
                    } else {
                        alert(is);
                    }
                });
            } else {
                $('.uploadvideo .js_perUpAdd').text($('#video_link').val());
            }

        });
    });

    //轮播次数禁止编辑的解除和添加
    function removeDisabled(type) {
        //未选择
        if (!type || type == 1 || type == 4) {
            //禁止编辑
            $('.js_roll_interval').attr('disabled', 'disabled');
            $('.js_roll_num').attr('disabled', 'disabled');
            //视频
        } else if (type == 2) {
            //禁止轮播时长编辑
            $('.js_roll_interval').val(0);
            $('.js_roll_interval').attr('disabled', true);
            //开放轮播次数编辑
            $('.js_roll_num').val(1);
            $('.js_roll_num').attr('disabled', false);

            //链接 和 套餐图
        } else if (type == 3 || type == 5) {
            //开启轮播时长编辑
            $('.js_roll_interval').val(10);
            $('.js_roll_interval').attr('disabled', false);
            //禁止轮播次数编辑
            $('.js_roll_num').val(1);
            $('.js_roll_num').attr('disabled', true);
        }
    }


    //验证图片是否为动图
    function isAnimatedGif(imgData) {
        if (!imgData) {
            return '图片上传失败';
        }
        var start = imgData.indexOf(';base64,');
        if (start < 0) {
            return '图片验证失败';
        }

        var not = imgData.slice(0, start + 8);
        var imgData = imgData.replace(not, '');
        var newData = $.base64.decode(imgData);
        if (newData.indexOf('NETSCAPE2.0') > -1) {
            return true;
        } else {
            return false;
        }
    }

    //
   /* $('.putTypeBut').live('click',function() {
        var putTypeCiock = $(this).val();
        if (putTypeCiock >= 1) {
            if (putTypeCiock == 2) {
                $('#save_rele').css('display','none');
                $('#tf_area').css('display','inline-block');
            } else {
                $('.Sub').text('保存并发布');
                $('#tf_area').css('display','none');
                $('#save_rele').css('display','inline-block');
            }
        } else {
            $('.Sub').text('保存');
            $('#tf_area').css('display','none');
            $('#save_rele').css('display','inline-block');
        }
    });*/

});

// 图片初始化
function initImage() {
    $('.uploadImg').addClass('hidden');
    $('.uploadImg .js_perUpOuter img').remove();
    $('.uploadImg').find('#fileup').val('');
}

//视频初始化
function initVideo() {
    $('.uploadvideo').addClass('hidden');
    $('#video_link').val('');
}

// url初始化
function initUrl() {
    $('.uploadlink').addClass('hidden');
    $('#url_link').val('');
}

//宣传图初始化
function initType4() {
    $('.font-color').addClass('hidden');
    $('.IsSpecify').addClass('hidden');
    $('.price').addClass('hidden');
    $('#fanwei').addClass('hidden');
    $('#xc_price').val('');
}

// 套餐图初始化
function initSetMealImg()
{
    $('.js_import_set_meal').addClass('hidden');
    $('.selectSetMealImg').addClass('hidden');
    $('.font-color').addClass('hidden');
    $('.set_meal_input').removeClass('required');
}


