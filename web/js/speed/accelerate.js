        var file_url = siteUrl + '/speed/probe';
        var xhr;        //对象
        var startTime;  //时间
        var fileSize;   //文件大小为0
        var speedArray = []; //average
        var avSpeed    = 0;

        var beatTime;
        var second = 9;

        function entry()
        {
            UpladFile(file_url);

            beatTime = setInterval(function(){
                $('.beat_time').html('<p class="time">正在进行Wi-Fi测速，整个过程大概需要'+second+'秒</p><p class="status">正在检查网络环境...</p>');
                
                if (0 == parseInt(second)) {
                    speedResult(avSpeed);
                    //清除计时
                    clearInterval(beatTime)
                }

                --second;
            }, 800);
            
            
        }
        

        //上传文件方法
        function UpladFile(file_url) {

            var url = file_url;            //接受文件地址

            xhr = new XMLHttpRequest();    // XMLHttpRequest 对象

            xhr.open("post", url, true );  //post方式，url为服务器请求地址，true 该参数规定请求是否异步处理。

            xhr.onload  = onComplete;  //请求完成 调用方法
            xhr.onerror = onFailed;    //请求失败 调用方法

            xhr.onprogress = progressFunction; //上传进度调用方法实现

            //开始执行方法
            xhr.onloadstart = function(){
                startTime = new Date().getTime(); //开始时间
                fileSize  = 0;                    //文件大小为0
            };

            xhr.send(); //开始上传
        }

        //上传进度实现方法，上传过程中会频繁调用该方法
        function progressFunction(evt) 
        {
            if (1 == parseInt(second)) {
                //终止上传
                stopLoad();
            }

            //强制终止的
            if((evt.total - evt.loaded ) > 0 && 0 == (evt.loaded - fileSize) ) {
                lastOpetation();
                return ;
            }

            var perload = evt.loaded - fileSize; //计算该分段上传的文件大小，单位b

            var nowTime = new Date().getTime();          //获取当前时间
            var pertime = (nowTime - startTime) / 1000;  //计算出上次调用该方法时到现在的时间差，单位为s

            startTime   = new Date().getTime();          //重新赋值时间，用于下次计算

            fileSize    = evt.loaded;            //重新赋值已上传文件大小，用以下次计算

            //上传速度计算
            var speed  = perload / pertime;      //单位b/s

            //计算时间准确的速度
            //var bspeed = speed;

            var unit  = 'b/s';//单位名称

            speed = speed/1024;
            unit  = 'k/s';
            //Number 四舍五入为指定小数位数的数字
            speed = speed.toFixed(1);

            //剩余时间
            //var resttime   = ((evt.total - evt.loaded) / bspeed ).toFixed(1);

            speedArray.push(speed);

        }

        //成功响应
        function onComplete(evt) 
        {
            if (parseInt(second) > 3) {
                second = 3
            }

            lastOpetation();
        }

        //强制终止或者完成
        function lastOpetation()
        {
            //处理测速的平均值
            handleSpeed();

            //记录
            record(clickTime , speedArray , avSpeed.toFixed(1));
        }
 
        //处理平均值
        function handleSpeed()
        {
            //服务断接收完文件返回的结果
            avSpeed = averageSpeed(speedArray);

            if(avSpeed/1024 < 1){
                avUnit = 'k/s';
                $('.average_speed').text(avSpeed.toFixed(1));
                $('.unit').text(avUnit);
                
            }

            if(avSpeed/1024 > 1 ){
                avUnit   = 'M/s';
                var avMb = avSpeed/1024;
                $('.average_speed').text(avMb.toFixed(1));
                $('.unit').text(avUnit);
            }
        }

        //测速失败
        function onFailed(evt) {
             alert("测速失败");
        }

        //平均数方法
        function averageSpeed(arr)
        {
             var sum = parseInt(0);
             
             for(var i = 0; i < arr.length; i++)
             { 
                 sum += parseInt(arr[i]); 
             } 
             
             return Math.round(sum/parseInt(arr.length));
        }

        //终止测速
        function stopLoad()
        {
            //终止
            xhr.abort();
        }

        //初始化
        function init()
        {
             xhr        = null;        //对象
             startTime  = 0;  //时间
             fileSize   = 0;   //文件大小为0
             speedArray = []; //average
             avSpeed    = 0;
             clickTime  = 0;

             second     = 9;
             $('.beat_time').html('<p class="time">正在进行Wi-Fi测速，整个过程大概需要10秒</p><p class="status">正在检查网络环境...</p>');
             clearInterval(beatTime);
        }

        //存表
        function record(clickTime , speedArr , avSpeed)
        {
            if (!clickTime || !speedArr || !avSpeed) {
                return ;
            }

            var speed = speedArr.join(',');

            var url  = siteUrl + '/speed/ajax';
            var post = { 'click_time': clickTime, 'speeds': speed, 'avg_speed': avSpeed }

            $.post(url, post, function(json){}, 'json');
        }

        //测速的提示语
        function speedResult(speed)
        {
            if (speed < 100) {
            	 var tips  = '网速比蜗牛还慢！还怎么忍？';
                 tips += '<a href="'+siteUrl+'/spitslot" class="btn">我要吐槽</a>';
                 
                /*var tips  = '<div class="left"><p>网速比蜗牛还慢,</p><p>还怎么忍？</p></div>';
                    tips += '<div class="right">';
                    tips += '<a href="'+siteUrl+'/spitslot'+'">';
                    tips += '<img src="'+siteUrl+'/images/popup-btn.png" alt="">';
                    tips += '</a>';
                    tips += '</div>';
                */
                    $('.rank_province').text(rndNum(300 , 400));
            }

            if (100 < speed && speed < 300) {
                var tips = '网速快过自行车！听首音乐放松下吧~';

                $('.rank_province').text(rndNum(200 , 300));
            }

            if (300 < speed && speed < 400) {
                var tips = '哇塞，你的网速比动车还快！美剧杠杠滴~';

                $('.rank_province').text(rndNum(100 , 200));
            }

            if (500 < speed ) {
                var tips = '哇塞，你的网速比动车还快！美剧杠杠滴~';

                $('.rank_province').text(rndNum(50 , 100));
            }

            $('.js_speedTip').html(tips);

            $('.beat_btn').addClass('none');
            $('.show_rank').removeClass('none');
        }

        //随机数生成
        function rndNum(min , max)
        {
            // 100~300
            return Math.floor(Math.random()*(max - min) + min);
        }
        