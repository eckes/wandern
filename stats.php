<?php
require_once('../login/common.php');
require_once('../css/colors.php');
require_once('common.php');
require_once('constants.php');
require_once('db_xml.php');
require('loginhead.php');

define ('I1MAX', 10);
define ('I2MAX', 14);
define ('I3MAX', 18);
define ('I4MAX', 20);

$domarray = db_init();
$names = array();
$lengths = array();
foreach($domarray as $dom)
{
  $_tmp[i1] = 0;
  $_tmp[i2] = 0;
  $_tmp[i3] = 0;
  $_tmp[i4] = 0;
  $_tmp[i5] = 0;
  $walks = $dom->getElementsByTagName(XMLTAG_WALK);
  $name  = $dom->getElementsByTagName("book")->item(0)->getAttribute("shortname");
  array_push($names, $name);
  foreach($walks as $thewalk)
  {
    $len      = $thewalk->getElementsByTagName(XMLTAG_LENGTH)->item(0)->nodeValue;
    if($len <= I1MAX)
    {
      $_tmp[i1]++;
    }
    elseif($len <= I2MAX)
    {
      $_tmp[i2]++;
    }
    elseif($len <= I3MAX)
    {
      $_tmp[i3]++;
    }
    elseif($len <= I4MAX)
    {
      $_tmp[i4]++;
    }
    else
    {
      $_tmp[i5]++;
    }
  }
  array_push($lengths, $_tmp);
}
db_cleanup($domarray);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <META http-equiv="content-type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" type="text/css" href="/scripts/dojo-release-1.3.2/dijit/themes/tundra/tundra.css">
        <style type="text/css">
            body, html { font-family:helvetica,arial,sans-serif; font-size:90%; }
        </style>
    <script type="text/javascript" src="/scripts/dojo-release-1.3.2/dojo/dojo.js"
    djConfig="parseOnLoad: true, isDebug: false">
    </script>
  </head>
  <body>
<?php
      foreach($names as $name)
      {
        print('<h1>Statistics for '. $name . '</h1><div id="chart' . $name . '" style="width: 200px; height: 200px;"></div><div id="legend' . $name . '"></div>');
      }
?>
  </body>
    <script type="text/javascript">

      dojo.require("dojox.charting.Chart2D");
      dojo.require("dojox.charting.plot2d.Pie");
      dojo.require("dojox.charting.action2d.Highlight");
      dojo.require("dojox.charting.action2d.MoveSlice");
      dojo.require("dojox.charting.action2d.Tooltip");
      dojo.require("dojox.charting.themes.MiamiNice");
      dojo.require("dojox.charting.widget.Legend");

      dojo.addOnLoad(function() {
<?php
      $name = array_pop($names);
      while($name)
      {
        $len = array_pop($lengths);
        $pc = 'pie' . $name;
        $legend = 'legend' . $name;
echo <<<END
            /* START CHART */
            var $pc = new dojox.charting.Chart2D("chart$name");
            $pc.setTheme(dojox.charting.themes.MiamiNice);
            $pc.addPlot("default", {type: "Pie", radius: 60});
            $pc.addSeries("Series A", [
                  { y: $len[i1], tooltip: "<= 10" },
                  { y: $len[i2], tooltip: "10 < x <= 14" },
                  { y: $len[i3], tooltip: "14 < x <= 18" },
                  { y: $len[i4], tooltip: "18 < x <= 20" },
                  { y: $len[i5], tooltip: "> 20" } 
                ]);
            var anim_a = new dojox.charting.action2d.MoveSlice($pc, "default");
            var anim_b = new dojox.charting.action2d.Highlight($pc, "default");
            var anim_c = new dojox.charting.action2d.Tooltip($pc, "default");
            $pc.render();
            //var $legend = new dojox.charting.widget.Legend({chart: $pc}, "legend$name");
            /* END CHART */

END;
        $name = array_pop($names);
      }

?>
      });
    </script>
</html>
