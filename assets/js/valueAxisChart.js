// async function fetchData() {
//   try {
//     const response = await fetch("https://sandbox.coinage.trade/api/priv/performance/monthly?user=15093064590045");
//     const fData = await response.json();
//     const data = Object.keys(fData.scope).map(key => fData.scope[key]);
//     console.log(data)
//     return data;
//   } catch (error) {
//     console.error(`Data error: ${error.message}`);
//   }
// }



// am4core.ready(function() {
am4core.ready(function() {

    // License begin
    am4core.addLicense("ch-custom-attribution");
    // License end

    // Themes begin
    am4core.useTheme(am4themes_animated);
    // Themes end
    
    // Create chart instance
    var chart = am4core.create("performance-chart", am4charts.XYChart);
    
    //
    
    // Increase contrast by taking evey second color
    chart.colors.step = 2;
    
    // Add data
    chart.data = fetchData();
    
    // Create axes
    var dateAxis = chart.xAxes.push(new am4charts.CategoryAxis());
    // dateAxis.renderer.minGridDistance = 50;
    
    // Create series
    function createAxisAndSeries(field, name, opposite, bullet) {
      var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
      if(chart.yAxes.indexOf(valueAxis) != 0){
          valueAxis.syncWithAxis = chart.yAxes.getIndex(0);
      }
      
      var series = chart.series.push(new am4charts.LineSeries());
      series.dataFields.valueY = field;
      series.dataFields.dateX = categoryAxis;
      series.strokeWidth = 2;
      series.yAxis = valueAxis;
      series.name = name;
      series.tooltipText = "{name}: [bold]{valueY}[/]";
      series.tensionX = 0.8;
      series.showOnInit = true;
      
      var interfaceColors = new am4core.InterfaceColorSet();
      console.log(bullet);
      
      switch(bullet) {
        case "triangle":
          var bullet = series.bullets.push(new am4charts.Bullet());
          bullet.width = 12;
          bullet.height = 12;
          bullet.horizontalCenter = "middle";
          bullet.verticalCenter = "middle";
          
          var triangle = bullet.createChild(am4core.Triangle);
          triangle.stroke = interfaceColors.getFor("background");
          triangle.strokeWidth = 2;
          triangle.direction = "top";
          triangle.width = 12;
          triangle.height = 12;
          break;
        case "rectangle":
          var bullet = series.bullets.push(new am4charts.Bullet());
          bullet.width = 10;
          bullet.height = 10;
          bullet.horizontalCenter = "middle";
          bullet.verticalCenter = "middle";
          
          var rectangle = bullet.createChild(am4core.Rectangle);
          rectangle.stroke = interfaceColors.getFor("background");
          rectangle.strokeWidth = 2;
          rectangle.width = 10;
          rectangle.height = 10;
          break;
        default:
          var bullet = series.bullets.push(new am4charts.CircleBullet());
          bullet.circle.stroke = interfaceColors.getFor("background");
          bullet.circle.strokeWidth = 2;
          break;
      }
      
      valueAxis.renderer.line.strokeOpacity = 1;
      valueAxis.renderer.line.strokeWidth = 2;
      valueAxis.renderer.line.stroke = series.stroke;
      valueAxis.renderer.labels.template.fill = series.stroke;
      valueAxis.renderer.opposite = opposite;
    }
    
    createAxisAndSeries("carteira", "Carteira", false, "circle");
    createAxisAndSeries("cdi", "CDI", true, "triangle");
    // createAxisAndSeries("hits", "Hits", true, "rectangle");
    
    // Add legend
    chart.legend = new am4charts.Legend();
    
    // Add cursor
    chart.cursor = new am4charts.XYCursor();
    
    // generate some random data, quite different range
    function generateChartData() {
      var chartData = [];
      var firstDate = new Date();
      firstDate.setDate(firstDate.getDate() - 100);
      firstDate.setHours(0, 0, 0, 0);
    
      var carteira = 1600;
      var cdi = 8700;
    
      for (var i = 0; i < 15; i++) {
        // we create date objects here. In your data, you can have date strings
        // and then set format of your dates using chart.dataDateFormat property,
        // however when possible, use date objects, as this will speed up chart rendering.
        var newDate = new Date(firstDate);
        newDate.setDate(newDate.getDate() + i);
    
        carteira += Math.round((Math.random()<0.5?1:-1)*Math.random()*10);
        cdi += Math.round((Math.random()<0.5?1:-1)*Math.random()*10);
    
        chartData.push({
          date: newDate,
          carteira: carteira,
          cdi: cdi
        });
      }
      return chartData;
    }
    
    }); // end am4core.ready()