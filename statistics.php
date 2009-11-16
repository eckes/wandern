<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <title>Statistik</title>
    <META http-equiv="content-type" content="text/html; charset=UTF-8">
    <style type="text/css">
      @import "/scripts/dojo-release-1.3.2/dojo/resources/dojo.css";
    </style>
    <style type="text/css">
      .chartcontainer
      {
        width:  500px;
        height: 300px;
        border: 1px solid blue;
      }
    </style>

    <!-- required for Tooltip: a default dijit theme: -->
    <link rel="stylesheet" type="text/css" href="/scripts/dojo-release-1.3.2/dijit/themes/tundra/tundra.css">
    <link rel="stylesheet" type="text/css" href="../css/style.php">

    <script type="text/javascript">
        var djConfig = {parseOnLoad:true, isDebug:false};
    </script>

    <script type="text/javascript" src="/scripts/dojo-release-1.3.2/dojo/dojo.js"></script>
    <script type="text/javascript">

      dojo.require("dojox.data.XmlStore");
      dojo.require("dojox.charting.Chart2D");
      dojo.require("dojox.charting.themes.PlotKit.green");
      dojo.require("dojox.charting.action2d.Highlight");
      dojo.require("dojox.charting.action2d.Tooltip");

      function drawCharts(a_years)
      {
        var count = a_years.length;
        var mlabels = [{value: 1, text: ""},
                       {value: 2, text: "Jan"}, {value: 3, text: "Feb"}, 
                       {value: 4, text: "Mär"}, {value: 5, text: "Apr"}, 
                       {value: 6, text: "Mai"}, {value: 7, text: "Jun"},
                       {value: 8, text: "Jul"}, {value: 9, text: "Aug"},
                       {value: 10, text: "Sep"}, {value: 11, text: "Okt"},
                       {value: 12, text: "Nov"}, {value: 13, text: "Dez"}, {value: 14, text: ""}];
        for (var i = 0; i < count; i++)
        {
          if(typeof a_years[i]=='undefined') continue;
          var year = a_years[i];
          var kmpm = year.overall/12; // kilometers per month
          var akmpm = new Array(kmpm, kmpm, kmpm, kmpm, kmpm, kmpm, kmpm, kmpm, kmpm, kmpm, kmpm, kmpm, kmpm, kmpm);
          var wpm  = year.nowalks/12; // walks per month
          var cid = "chart" + year.theyear;

          var chart1 = new dojox.charting.Chart2D(cid);
          chart1.setTheme(dojox.charting.themes.PlotKit.green);
          chart1.addAxis("x", {title:"Monat", vertical: false, labels:mlabels, minorLabels:true, min:1,max:14}); 
          chart1.addAxis("y", {vertical: true, includeZero: true, max:year.max*1.1});
          chart1.addPlot("default", {type:"Columns", gap:5}); 
          chart1.addSeries("Series 1", year.months, {plot:'default'}); 

          chart1.addPlot("avg", {type:"Lines", markers:false, hAxis:"x", vAxis:"y"});
          chart1.addSeries("kpm", akmpm, {plot:"avg", stroke:{style:"Dash"}});

          var anim1b = new dojox.charting.action2d.Tooltip(chart1, "default");
          var anim1a = new dojox.charting.action2d.Highlight(chart1, "default");

          chart1.render();
          document.getElementById("summary" + year.theyear).innerHTML = "<b>Total:</b> " + year.overall + " km, <b>Walks this year:</b> " + year.nowalks + ", <b>Average per month:</b> " + kmpm.toFixed(0) + " km";
        }
      }

      var gotXml = function(a_items, a_req)
      {
        var count = a_items.length;
        var years = new Array();

        if(0 == count)
        {
          document.getElementById("contenttable").style.display = 'none';
          document.getElementById("statusline").innerHTML = "<p style='color:red'>No data read! Login!</p>";
        }

        for (var i = 0; i < count; i++)
        {
          var item = a_items[i];
          var year = new Object();

          year.theyear = Number(item.store.getValue(item, "theyear"));
          year.nowalks = Number(item.store.getValue(item, "count"));
          year.overall = Number(item.store.getValue(item, "overall"));
          year.max     = 0;
          year.months = new Array(0,0,0,0,0,0,0,0,0,0,0,0,0);
          var months = item.store.getValue(item, "months");
          var ma = months.store.getValues(months, "month");
          for(var j = 0; j < ma.length; j++)
          {
            var month = Number(ma[j].store.getValue(ma[j], "monthnr"));
            var length = Number(ma[j].store.getValue(ma[j], "len"));
            year.months[month] = length;
            if(length > year.max)
            {
              year.max=length;
            }
          }
          years[year.theyear] = year;
        }
        drawCharts(years);
      }

      function getXml()
      {
        var storeArgs = { url: '/wandern/stats2.php',
                          attributeMap: {"monthnr":"@nr", "bookid":"@id", "len":"@length"}
                        };
        var store = new dojox.data.XmlStore(storeArgs);
        var req   = store.fetch({onComplete: gotXml});
      }

      dojo.addOnLoad(getXml);
    </script>
  </head>
  <body>
    <table id="contenttable">
      <tr><td id="label2006" style=""><h2>2006</h2> <span id="summary2006"></span></td><td id="label2007"><h2>2007</h2> <span id="summary2007"></span></td></tr>
      <tr><td class="chartcontainer" id="chart2006" ></td><td class="chartcontainer" id="chart2007"></td></tr>
      <tr><td id="label2008" style=""><h2>2008</h2> <span id="summary2008"></span></td><td id="label2009"><h2>2009</h2> <span id="summary2009"></span></td></tr>
      <tr><td class="chartcontainer" id="chart2008" ></td><td class="chartcontainer" id="chart2009"></td></tr>
    </table>
    <div id="statusline"></div>
  </body>
</html>

