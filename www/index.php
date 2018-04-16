<?php

require_once 'inc/functions.php';
require_once 'inc/db_connect.php';
$db = new dbconn();

$header='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Food Diary</title>

<link rel="stylesheet" href="font-awesome-4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/script.js"></script>
<script type="text/javascript" src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script type="text/javascript" src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="js/highcharts.js"></script>
<script src="js/modules/data.js"></script>
<script src="js/themes/dark.js"></script>
<link rel="stylesheet" href="css/style.css" />

</head>

<body>';

echo $header;
echo navbar();
echo '<div id="graphs" style="width:100%;" ></div>';
echo '<span align="left"><p>';
echo '* Future values for weight, weight change, and calories to maintain weight assume every day is like the last full day\'s worth of calories.<BR>';
echo '* "Cal Diff" is the sum of the difference between estimated maintenance calories and 7-day average calorie consumption.  '
        . 'This value resets at 3500, indicating one estimated lb lost (generic approximation).<BR>';
echo '* Calorie macro breakdown may not match exactly to total calories due to nutrition label rounding.  '
        . 'Breakdown is calculated as 4 calories per gram for carbs and protein.  '
        . 'Fat is calculated as 9 calories per gram.<BR>';
echo '* Ideal values use a BMI of 21.75 and the Mifflin - St Jeor formulas<BR>';
echo '</p></span>';

$height=70;

$BMI = weightranges($height);
$healthyweight = ($BMI[3]['min']+$BMI[3]['max'])/2;

/*
 * men = 10*w + 6.25*h - 5*a + 5
 * women = 10*w + 6.25*h - 5*a - 161
 * w in kg (1lb=0.45359237kg)
 * h in cm (1in=2.54cm)
 * a in yrs
 * 
 * 1.2 Sedentary, little or no exercise and desk job
 * 1.375 Lightly Active, light exercise, or sports 1-3 days a week
 * 1.55 Moderately active, moderate exercise, or sports 3-5 days a week
 * 1.725 Very Active, hard exercise, or sports 6-7 days a week
 * 1.9 extremely Active, hard daily exercise or sports and phsyical job
 */


echo "<script>$(function () {

    /**
     * In order to synchronize tooltips and crosshairs, override the
     * built-in events with handlers defined on the parent element.
     */
    var website = 'http://tuff.mynetgear.com/health/diary.php?date=';
    $('#graphs').bind('mouseleave mouseout ', function(e) {
        var chart,
          point,
          i,
          event;

        for (i = 0; i < Highcharts.charts.length; i = i + 1) {
          chart = Highcharts.charts[i];
          event = chart.pointer.normalize(e.originalEvent);
          point = chart.series[0].searchPoint(event, true);

          point.onMouseOut(); 
          chart.tooltip.hide(point);
          chart.xAxis[0].hideCrosshair(); 
        }
      });
    function resizegraph() {
        var chart;
        for (i = 0; i < Highcharts.charts.length; i = i + 1) {
            chart = Highcharts.charts[i];
            chart.setSize($('#graphs').width(), null);
        }
    }
    var resizeTimer;
    $(window).bind('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(resizegraph, 1000);
    });
    $('#graphs').bind('mousemove touchmove touchstart', function (e) {
        var chart,
            point,
            i,
            event;

        for (i = 0; i < Highcharts.charts.length; i = i + 1) {
            chart = Highcharts.charts[i];
            event = chart.pointer.normalize(e.originalEvent); // Find coordinates within the chart
            
            var points = [];
            for (j = 0; j < chart.series.length; j = j + 1) {
                serie = chart.series[j];
                if (!serie.visible || serie.enableMouseTracking === false) continue;

                point = serie.searchPoint(event, true);
                // Get the hovered point
                if (point) points.push(point); 
            }

            if (points.length) {
                if (chart.tooltip.shared) { chart.tooltip.refresh(points); } 
                else { chart.tooltip.refresh(points[0]); }
                chart.xAxis[0].drawCrosshair(e, points[0]);
            }
        }
    });
    /**
     * Override the reset function, we don't need to hide the tooltips and crosshairs.
     */
    Highcharts.Pointer.prototype.reset = function () {
        return undefined;
    };
    Highcharts.setOptions({
        lang: {
            thousandsSep: ''
        }
    });




    /**
     * Synchronize zooming through the setExtremes event handler.
     */
    function syncExtremes(e) {
        var thisChart = this.chart;

        if (e.trigger !== 'syncExtremes') { // Prevent feedback loop
            Highcharts.each(Highcharts.charts, function (chart) {
                if (chart !== thisChart) {
                    if (chart.xAxis[0].setExtremes) { // It is null while updating
                        chart.xAxis[0].setExtremes(e.min, e.max, undefined, false, { trigger: 'syncExtremes' });
                    }
                }
            });
        }
    }

    // Get the data.
    $.getJSON('ajax_refresh.php?f=main', function (activity) {
        $.each(activity.datasets, function (i, dataset) {
            
            
            // Add X values
            dataset.data = Highcharts.map(dataset.data, function (val, j) {return [activity.xData[j], val];});
            dataset.data1 = Highcharts.map(dataset.data1, function (val, j) {return [activity.xData[j], val];});
            dataset.data2 = Highcharts.map(dataset.data2, function (val, j) {return [activity.xData[j], val];});
            dataset.data3 = Highcharts.map(dataset.data3, function (val, j) {return [activity.xData[j], val];});
            dataset.data4 = Highcharts.map(dataset.data4, function (val, j) {return [activity.xData[j], val];});
            dataset.data5 = Highcharts.map(dataset.data5, function (val, j) {return [activity.xData[j], val];});
            dataset.data6 = Highcharts.map(dataset.data6, function (val, j) {return [activity.xData[j], val];});
            
            //weight graph
            if (dataset.avg > dataset.ideal){
                avgoffset = -7
                goaloffset = 15
            }
            else {
                avgoffset = 15
                goaloffset = -7
            }
            var mylines = [];
            var mybands = [];
            var myheight = 200;
            var labelsenabled = true;
            //weight graph
            if (i == 0){
                mybands = [
                    { color: '".$BMI[0]['color']."', from: ".$BMI[0]['min'].",  to: ".$BMI[0]['max'].", label: {text: '<span style=\"color:".$BMI[0]['textcolor']."\">".$BMI[0]['desc']."</span>', align: 'left'}, zIndex: 1},
                    { color: '".$BMI[1]['color']."', from: ".$BMI[1]['min'].",  to: ".$BMI[1]['max'].", label: {text: '<span style=\"color:".$BMI[1]['textcolor']."\">".$BMI[1]['desc']."</span>', align: 'left'}, zIndex: 1},
                    { color: '".$BMI[2]['color']."', from: ".$BMI[2]['min'].",  to: ".$BMI[2]['max'].", label: {text: '<span style=\"color:".$BMI[2]['textcolor']."\">".$BMI[2]['desc']."</span>', align: 'left'}, zIndex: 1},
                    { color: '".$BMI[3]['color']."', from: ".$BMI[3]['min'].",  to: ".$BMI[3]['max'].", label: {text: '<span style=\"color:".$BMI[3]['textcolor']."\">".$BMI[3]['desc']."</span>', align: 'left'}, zIndex: 1},
                    { color: '".$BMI[4]['color']."', from: ".$BMI[4]['min'].",  to: ".$BMI[4]['max'].", label: {text: '<span style=\"color:".$BMI[4]['textcolor']."\">".$BMI[4]['desc']."</span>', align: 'left'}, zIndex: 1},
                    { color: '".$BMI[5]['color']."', from: ".$BMI[5]['min'].",  to: ".$BMI[5]['max'].", label: {text: '<span style=\"color:".$BMI[5]['textcolor']."\">".$BMI[5]['desc']."</span>', align: 'left'}, zIndex: 1},
                    { color: '".$BMI[6]['color']."', from: ".$BMI[6]['min'].",  to: ".$BMI[6]['max'].", label: {text: '<span style=\"color:".$BMI[6]['textcolor']."\">".$BMI[6]['desc']."</span>', align: 'left'}, zIndex: 1},
                    { color: '".$BMI[7]['color']."', from: ".$BMI[7]['min'].",  to: ".$BMI[7]['max'].", label: {text: '<span style=\"color:".$BMI[7]['textcolor']."\">".$BMI[7]['desc']."</span>', align: 'left'}, zIndex: 1},
                ];
            }
            //weight change graph
            if (i == 1){
                myheight = 100;
                labelsenabled = false;
            }

            //calorie graph
            if (i == 2){
                ymin = 0;
                mylines = [{
                    value: Math.round(dataset.avg),
                    color: Highcharts.getOptions().colors[3],
                    dashStyle: 'dash',
                    width: 1,
                    zIndex: 30,
                    label: { text: '<span style=\"color:'+Highcharts.getOptions().colors[3]+'\">Average: '+Math.round(dataset.avg)+'</span>', 
                                align: 'right',
                                margin: 0,
                                x: 0,
                                y: avgoffset
                           }
                },{
                    value: Math.round(dataset.ideal),
                    color: Highcharts.getOptions().colors[4],
                    dashStyle: 'dash',
                    width: 1,
                    zIndex: 30,
                    label: { text: '<span style=\"color:'+Highcharts.getOptions().colors[4]+'\">Ideal: '+Math.round(dataset.ideal)+'</span>', 
                                align: 'right', 
                                margin: 0, 
                                x: 0,
                                y: goaloffset
                           }
                },{
                    value: Math.round(dataset.diet),
                    color: Highcharts.getOptions().colors[5],
                    dashStyle: 'dash',
                    width: 1,
                    zIndex: 30,
                    label: { text: '<span style=\"color:'+Highcharts.getOptions().colors[5]+'\">Goal: '+Math.round(dataset.diet)+'</span>', 
                                align: 'left', 
                                margin: 0, 
                                x: 0,
                                y: 15
                           }
                }];
            }
            
            //Calorie Macro Breakdown Graph
            if (i == 3){
                myheight = 180;
                labelsenabled = false;
                mylines = [{
                    value: Math.round(dataset.carbspct),
                    //color: 'black',
                    color: Highcharts.getOptions().colors[2],
                    dashStyle: 'dash',
                    width: 1,
                    zIndex: 4,
                    label: { text: '<span style=\"color:'+Highcharts.getOptions().colors[2]+'\">Carbs Goal: '+Math.round(dataset.carbspct)+'%</span>', 
                                align: 'center',
                                margin: 0,
                                x: 0,
                                y: 10
                           }
                },{
                    value: Math.round(dataset.carbspct+dataset.fatpct),
                    //color: 'blue',
                    color: Highcharts.getOptions().colors[1],
                    dashStyle: 'dash',
                    width: 1,
                    zIndex: 4,
                    label: { text: '<span style=\"color:'+Highcharts.getOptions().colors[1]+'\">Fat Goal: '+Math.round(dataset.fatpct)+'%</span>', 
                                align: 'center',
                                margin: 0,
                                x: 0,
                                y: 10
                           }
                },{
                    value: Math.round(dataset.carbspct+dataset.fatpct+dataset.proteinpct),
                    //color: 'green',
                    color: Highcharts.getOptions().colors[0],
                    dashStyle: 'dash',
                    width: 1,
                    zIndex: 4,
                    label: { text: '<span style=\"color:'+Highcharts.getOptions().colors[0]+'\">Protein Goal: '+Math.round(dataset.proteinpct)+'%</span>', 
                                align: 'center',
                                margin: 0,
                                x: 0,
                                y: 10
                           }
                }];
            }
            //Weekly Averages
            if (i == 4){
                myheight = 180;
                labelsenabled = false;
            }
            
            //myheight = 200;
            
            myname = 'chart'+i;
            $('<div class=\"chart\" id=\"'+myname+'\">')
                .appendTo('#graphs')
                .highcharts({
                    chart: {
                        renderTo: 'graphs',
                        marginLeft: 40, // Keep all charts left aligned
                        marginRight: 30,
                        spacingTop: 15,
                        spacingBottom: 15,
                        zoomType: 'x',
                        resetZoomButton: {
                            position: {
                                x: -220,
                                y: 5
                            },
                            relativeTo: 'chart'
                        },
                        events: {
                            click: function (event) {
                                
                                var clickdate = Highcharts.dateFormat('%Y-%m-%d', event.xAxis[0].value);
                                window.open('http://tuff.mynetgear.com/health/diary.php?date='+clickdate);
                            },
                            load: function (event) {
                                //event.target.reflow();
                                id = '#'+myname;
                                var chart = $(id).highcharts();
                                setTimeout(function() {
                                    chart.setSize($('#graphs').width(), myheight);
                                    console.log('test ',i,$('#graphs').width());
                                }, 0);
                                
                                //console.log('test ',i,$('#graphs').width());
                            },
                            render: function (event) {
                                
                                
                            }
                        },
                        width: null,
                        height: myheight,
                    },
                    tooltip: {
                        xDateFormat: '%A, %B %d, %Y',
                        positioner: function (labelWidth, labelHeight, point) {
                            var tooltipX, tooltipY;
                            var chart = this.chart;
                            return {
                                x: chart.plotWidth-109,
                                y: chart.plotTop-35
                            };
                        },

                        valueDecimals: dataset.valueDecimals,
                        useHTML: true,
                        split: false,
                        shared: true,
                        shape: 'rect',
                    },
                    plotOptions: {
                        series: {
                            lineWidth: 1,
                            cursor: 'pointer',
                            states: {
                                hover: {
                                    enabled: true,
                                    lineWidthPlus: 0,
                                }
                            },
                            
                        },
                        area: {
                            stacking: null,
                            fillOpacity: .2,
                            lineColor: 'rgba(255,000,000,0)',
                            enableMouseTracking: false,
                            
                        },
                        areaspline: {stacking: 'percent'}, //normal, percent, or null
                    },
                    title: {
                        text: dataset.title,
                        align: 'left',
                        margin: 0,
                        x: 30
                    },
                    credits: { enabled: false },
                    legend: { enabled: false },
                    xAxis: {
                        crosshair: true,
                        tickInterval: 4*7 * 24 * 3600 * 1000, // one week
                        gridLineWidth: 1,
                        gridZIndex: 0,
                        events: { setExtremes: syncExtremes },
                        type: 'datetime',
                        min: new Date('2016/10/28').getTime(),
                        labels: { enabled: labelsenabled },
                    },
                    yAxis: {
                        title: { text: null },
                        tickInterval: dataset.tickinterval,
                        gridZIndex: 0,
                        plotBands: mybands,
                        plotLines: mylines,
                        min: dataset.ymin,
			max: dataset.ymax,
                    },
                    series: [
                    {data: dataset.data, name: dataset.name, type: dataset.type,
                        marker: {enabled: false},
                        point: {events: {click: function(){ window.open(website+(new Date(this.x)).toISOString().slice(0,10));}}},
                        tooltip: {valueSuffix: ' ' + dataset.unit,}},
                    {data: dataset.data1, name: dataset.name1, type: dataset.type1,
                        marker: {enabled: false},
                        point: {events: {click: function(){ window.open(website+(new Date(this.x)).toISOString().slice(0,10));}}},
                        tooltip: {valueSuffix: ' ' + dataset.unit1,}},
                    {data: dataset.data2, name: dataset.name2, type: dataset.type2, 
                        marker: {enabled: false},
                        point: {events: {click: function(){ window.open(website+(new Date(this.x)).toISOString().slice(0,10));}}}, 
                        tooltip: {valueSuffix: ' ' + dataset.unit2}},
                    {data: dataset.data3, name: dataset.name3, type: dataset.type3, 
                        marker: {enabled: false},
                        point: {events: {click: function(){ window.open(website+(new Date(this.x)).toISOString().slice(0,10));}}}, 
                        tooltip: {valueSuffix: ' ' + dataset.unit3}},
                    {data: dataset.data4, name: dataset.name4, type: dataset.type4, 
                        marker: {enabled: false},
                        point: {events: {click: function(){ window.open(website+(new Date(this.x)).toISOString().slice(0,10));}}}, 
                        tooltip: {valueSuffix: ' ' + dataset.unit4}},
                    {data: dataset.data5, name: dataset.name5, type: dataset.type5, 
                        marker: {enabled: false},
                        point: {events: {click: function(){ window.open(website+(new Date(this.x)).toISOString().slice(0,10));}}}, 
                        tooltip: {valueSuffix: ' ' + dataset.unit5}},
                    {data: dataset.data6, name: dataset.name6, type: dataset.type6, 
                        marker: {enabled: false},
                        point: {events: {click: function(){ window.open(website+(new Date(this.x)).toISOString().slice(0,10));}}}, 
                        tooltip: {valueSuffix: ' ' + dataset.unit6}},
                    ]
                });
        });
    });

});
</script>";
echo '</body></html>';
?>