<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <title>Statistik</title>
    <META http-equiv="content-type" content="text/html; charset=UTF-8">
    <style type="text/css">
      @import "/scripts/dojo-release-1.3.2/dojo/resources/dojo.css";
    </style>

    <link rel="stylesheet" href="/scripts/dojo-release-1.3.2/dijit/themes/tundra/tundra.css">
    <link rel="stylesheet" type="text/css" href="../css/style.php">
    <script type="text/javascript" src="/scripts/dojo-release-1.3.2/dojo/dojo.js" djConfig="parseOnLoad: true, isDebug: false"/>
      <script type="text/javascript">

      dojo.require("dojox.data.XmlStore");
      dojo.require("dojox.charting.Chart2D");
      dojo.require("dojox.charting.themes.PlotKit.green");

      dojo.require("dojox.charting.action2d.Highlight");
      dojo.require("dojox.charting.action2d.Tooltip");

      function loadcb(a_req, a_rsp)
      {
        var years = a_req.getElementsByTagName("year");
        alert(a_req);
        alert(a_rsp);
      }

      function getStatistics()
      {
        var xhrArgs = {
              url: '/wandern/stats2.php',
              handleAs: "xml",
              load:loadcb,
              error:function(error) { alert("An unexpected error occurred: " + error); },
              preventCache: true
        };
        var deferred = dojo.xhrGet(xhrArgs);
      }

      function drawCharts(a_years)
      {
        var count = a_years.length;
        var mlabels = [{value: 2, text: "Jan"}, {value: 3, text: "Feb"}, 
                       {value: 4, text: "Mar"}, {value: 5, text: "Apr"}, 
                       {value: 6, text: "May"}, {value: 7, text: "Jun"},
                       {value: 8, text: "Jul"}, {value: 9, text: "Aug"},
                       {value: 10, text: "Sep"}, {value: 11, text: "Oct"},
                       {value: 12, text: "Nov"}, {value: 13, text: "Dec"}];
        for (var i = 0; i < count; i++)
        {
          if(typeof a_years[i]=='undefined') continue;
          var year = a_years[i];
          var kmpm = year.overall/12; // kilometers per month
          var wpm  = year.nowalks/12; // walks per month
          var cid = "chart" + year.theyear;

          var chart1 = new dojox.charting.Chart2D(cid);
          chart1.setTheme(dojox.charting.themes.PlotKit.green);
          chart1.addAxis("x", {vertical: false, labels:mlabels, minorLabels:true, min:1,}); 
          chart1.addAxis("y", {vertical: true, includeZero: true});
          chart1.addPlot("default", {type:"Columns", gap:5}); 
          chart1.addSeries("Series 1", year.months, {plot:'default'}); 

          var anim3b = new dojox.charting.action2d.Tooltip(chart1, "default");
          var anim3a = new dojox.charting.action2d.Highlight(chart1, "default");

          chart1.render();
          console.log("rendered chart " + cid);

        }
      
      }

      var gotXml = function(a_items, a_req)
      {
        var count = a_items.length;
        var years = new Array();

        for (var i = 0; i < count; i++)
        {
          var item = a_items[i];
          var year = new Object();
 
          year.theyear =           Number(item.store.getValue(item, "theyear"));
          year.nowalks = Number(item.store.getValue(item, "count"));
          year.overall = Number(item.store.getValue(item, "overall"));
          year.months = new Array(0,0,0,0,0,0,0,0,0,0,0,0,0);
          var months = item.store.getValue(item, "months");
          var ma = months.store.getValues(months, "month");
          for(var j = 0; j < ma.length; j++)
          {
            year.months[Number(ma[j].store.getValue(ma[j], "monthnr"))] = Number(ma[j].store.getValue(ma[j], "len"));
          }
          console.log("Located year: " + year.theyear);
          console.log("# number of walks: " + year.nowalks);
          console.log("# overall kilometers: " + year.overall);
          console.log("---");
          years[year.theyear] = year;
        }
        drawCharts(years);
      }

      function getXml()
      {
        var storeArgs = {
          url: '/wandern/stats2.php',
          attributeMap: {"monthnr":"@nr", "bookid":"@id", "len":"@length"}
        };
        var store = new dojox.data.XmlStore(storeArgs);
        var req   = store.fetch({onComplete: gotXml});
      }

      //dojo.addOnLoad(getStatistics);

      dojo.addOnLoad(getXml);
</script>
  </head>
  <body>
<table>
  <tr><td id="label2006" style="">2006</td><td id="label2007">2007</td></tr>
  <tr><td id="chart2006" style="width:500px;height:300px"></td><td id="chart2007" style="width:500px;height:300px"></td></tr>
  <tr><td id="label2008" style="">2008</td><td id="label2009">2009</td></tr>
  <tr><td id="chart2008" style="width:500px;height:300px"></td><td id="chart2009" style="width:500px;height:300px"></td></tr>
</table>
  </body>
</html>

