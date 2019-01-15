$(function () {
	if (type == 'min') {
		
	} else if (type == 'hour') {
		$('#container').highcharts({
			// 图标配置
			chart: {
				plotBorderWidth: 1,	// plotBorderWidth 绘图区边框宽度
	            zoomType: 'xy'		// zoomType 缩放类型	“x”、“y”、“xy”
			},
			// 标题
			title: {
	            text: '西单营业厅设备信号强度分布图',
	        },
	        // 副标题
	        subtitle: {
	            text: date
	        },
			// 图例
			legend: {
	            layout: 'vertical',	//布局方式: 垂直布局
	            align: 'left',		// 对齐方式：左对齐
	            x: 80,				// 水平偏移
	            verticalAlign: 'top',	// 垂直对齐方式
	            y: 55,				// 垂直便宜
	            floating: true,		// 图例是否浮动，设置浮动后，图例将不占位置
	            backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'
	        },
	        // x坐标轴
	        xAxis: {
	        	// 坐标轴类型
	        	type: 'datetime',
	            // 坐标轴标签
	            labels: {
	                format: '{value}时'
	            },
	            dateTimeLabelFormats: {
	                day: '%H'
	            },
	            // x轴名称
	            title: {
	                text: '时间(小时)'
	            }
	         },
	         // y轴
	         yAxis: [{
	            startOnTick: false,
	            endOnTick: false,
	            title: {
	                text: '信号强度（实际为负值，越接近0信号越强）'
	            },
	            labels: {
	                format: '{value} dbm'
	            },
	            maxPadding: 0.2, 
	         }, { // Secondary yAxis
	            title: {
	                text: '设备数',
	            },
	            labels: {
	                format: '{value} 个',
	            },
	            opposite: true
	        }],
	        // 数据提示框
	        tooltip: {
	            shared: true	// 共享提示框
	        },
	        plotOptions: {
	             bubble: {
	                minSize: 3,
	                maxSize: 10
	            },
	        },
	        // 数据数
	        series: [
	            {
		            name: '设备',	// 数据名
		            type: 'bubble',	// 类型
		            tooltip: {		// 数据提示框
		            	useHTML: true,
		            	headerFormat: '<table>',
		            	pointFormat: '<tr><th colspan="2"><h4>{point.trend}  </h4></th></tr>' +
		                	'<tr><th>信号强度：</th><td>{point.rssi}dbm  </td></tr>' +
		                	'<tr><th>上次信号强度：</th><td>{point.last_rssi}dbm  </td></tr>' +
		                	'<tr><th>更新时间：</th><td>{point.last_uptime}</td></tr>',
		                	footerFormat: '</table>',
		                	followPointer: true
		            },
		            // 数据内容
		            data: data
		        },
		        {
		        	name: '较近个数',
		        	type: 'spline',
		        	yAxis: 1,
		        	data: near,
		            tooltip: {
		                 headerFormat: ' ',
		                 valueSuffix: '个'
		            }
		        },
		        {
		            name: '中间个数',
		            type: 'spline',
		            yAxis: 1,
		            data: mid,
		            tooltip: {
		                valueSuffix: '个'
		            }
		        },
		        {
		            name: '较远个数',
		            type: 'spline',
		            yAxis: 1,
		            data: far,
		            tooltip: {
		                valueSuffix: '个'
		            }
		        }
	        ]
	    });
	}
});