  var option = {
 
      tooltip : {
          trigger: 'axis'
      },
      legend: {
          data:['活跃数量','体验时长/分钟']
      },
      toolbox: {
          show : true,
          feature : {
              mark : { show: true },
              dataView : { show: true, readOnly: false },
              magicType : { show: true, type: ['line', 'bar', 'stack', 'tiled'] },
              restore : { show: true },
              saveAsImage : { show: true }
          }
      },
      calculable : true,
      xAxis : [
          {
              type : 'category',
              data : echart_data.dates
          }
      ],
      yAxis : [
          {
              type : 'value'
          }
      ],
      series : [
          {
              name:'活跃数量',
              type:'line',
              smooth:true,
              itemStyle: { 
                normal: { 
                  areaStyle: { type: 'default' }, 
                }
              },
              data: echart_data.active_nums
          },
          {
              name:'体验时长/分钟',
              type:'line',
              smooth:true,
              itemStyle: { normal: { areaStyle: { type: 'default' } } },
              data:echart_data.exper_times
          },
      ]
  }; 
  
  var theme = {
      // 默认色板
    color: [
        '#2ec7c9','#b6a2de','#5ab1ef','#ffb980','#d87a80',
      '#8d98b3','#e5cf0d','#97b552','#95706d','#dc69aa',
      '#07a2a4','#9a7fd1','#588dd5','#f5994e','#c05050',
      '#59678c','#c9ab00','#7eb00a','#6f5553','#c14089'
    ],
    
    // 图表标题
    title: {
        textStyle: {
            fontWeight: 'normal',
          color: '#008acd'          // 主标题文字颜色
        }
    },
    
    // 值域
    dataRange: {
        itemWidth: 15,
        color: ['#5ab1ef','#e0ffff', '#FECB2F']
    },
    
    // 区域缩放控制器
    dataZoom: {
        dataBackgroundColor: '#efefff',            // 数据背景颜色
      fillerColor: 'rgba(182,162,222,0.2)',   // 填充颜色
      handleColor: '#008acd'    // 手柄颜色
    },
    
    // 网格
    grid: {
        borderColor: '#eee'
    },
    
    // 类目轴
    categoryAxis: {
        axisLine: {            // 坐标轴线
          lineStyle: {       // 属性lineStyle控制线条样式
              color: '#008acd'
          }
      },
      splitLine: {           // 分隔线
          lineStyle: {       // 属性lineStyle（详见lineStyle）控制线条样式
              color: ['#eee']
            }
        }
    },
    
    // 数值型坐标轴默认参数
    valueAxis: {
        axisLine: {            // 坐标轴线
          lineStyle: {       // 属性lineStyle控制线条样式
              color: '#008acd'
          }
      },
      splitArea : {
          show : true,
          areaStyle : {
              color: ['rgba(250,250,250,0.1)','rgba(200,200,200,0.1)']
          }
      },
      splitLine: {           // 分隔线
          lineStyle: {       // 属性lineStyle（详见lineStyle）控制线条样式
              color: ['#eee']
            }
        }
    },
    
    
    // 折线图默认参数
    line: {
        smooth : true,
        symbol: 'emptyCircle',  // 拐点图形类型
      symbolSize: 3           // 拐点图形大小
    },
    
    textStyle: {
      fontFamily: '微软雅黑, Arial, Verdana, sans-serif'
    }
  };
  
  myEcharts = echarts.init(document.getElementById('mainEchart'), theme);
  myEcharts.setOption(option);