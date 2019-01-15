/*活动PV的JS统计*/
var preg   = /[\?\&]name\=([a-z\_]+)/;
var res    = preg.exec(window.location.href);
var source = res ? res[1] : ''

	console.log(window.location.href);
console.log(res);
console.log(source);