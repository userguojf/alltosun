/**
 * 代码统计
 */
    //拿到引入JS代码的script便签
    _awifi.res  = document.getElementById("_awifi_stat");
    //取出script标签的src属性 并取出name值
    _awifi.preg = /[\?\&]name\=([a-z\_]+)/;
    _awifi.arr  = _awifi.preg.exec(_awifi.res.src);
    _awifi.event_name = _awifi.arr ? _awifi.arr[1] : '';
    //获取路径
    _awifi._url = _awifi.res.src.split('/js')[0];
    //拼接路径
    _awifi.url  = _awifi._url+"/stat_pv/ajax/event_stat";

    $.ajax({
        url: _awifi.url,
        type: "post",
        data: { event_name : _awifi.event_name },
        dataType: "json",
    });