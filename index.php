<?php 
    global $g_homelat, $g_homelon;
    $g_homelat = 49.414630;
    $g_homelon = 11.031539;
?>
<html>
    <head>
        <script type="text/javascript" src="http://www.google.com/jsapi?key=ABQIAAAARoTP-aPC3X-J7A6v_c-RrRSliXv-vXMxLfXbWpmDAJtGYmmjPhRn1xN7Ce6w66WX49UMmCdujbpuzA"></script>
        <script type="text/javascript" src="../js/gs_sortable.js"></script>
        <script type="text/javascript">
        google.load("maps", "2");
        var g_LAT_MIN       = 0;
        var g_LAT_MAX       = 0;
        var g_LON_MIN       = 0;
        var g_LON_MAX       = 0;
        var g_MAPCENTER     = 0;
        var g_INITIALIZED   = 0;
        var g_MAPBOUNDS;
        var g_MANAGER;
        var g_MAP;

        function updateCenter(a_long, a_lat)
        {
            g_LAT_MAX = Math.max(a_lat, g_LAT_MAX);
            if(0 != g_LAT_MIN)
            {
                g_LAT_MIN = Math.min(a_lat, g_LAT_MIN);
            }
            else
            {
                g_LAT_MIN = g_LAT_MAX;
            }
            g_LON_MAX = Math.max(a_long, g_LON_MAX);
            if(0 != g_LON_MIN)
            {
                g_LON_MIN = Math.min(a_long, g_LON_MIN);
            }
            else
            {
                g_LON_MIN = g_LON_MAX;
            }
            var lat = (g_LAT_MIN + g_LAT_MAX)/2;
            var lon = (g_LON_MIN + g_LON_MAX)/2;
            g_MAPCENTER = new google.maps.LatLng(lat,lon);
        }

        function showMark(a_long, a_lat, a_text)
        {
            var pos     = new google.maps.LatLng(a_lat, a_long);
            var options = { title: a_text };
            var mark    = new google.maps.Marker(pos, options);
            g_MAPBOUNDS.extend(pos);
            updateCenter(a_long, a_lat);
            g_MANAGER.addMarker(mark, 10);
            g_MANAGER.refresh();
            GEvent.addListener(mark, "click", function(){mark.openInfoWindowHtml(a_text);});
        }

        function initialize() {
            if(g_INITIALIZED) return;
            g_MAP = new google.maps.Map2(document.getElementById("map"));
            g_MANAGER = new google.maps.MarkerManager(g_MAP);
            g_MAPBOUNDS = new google.maps.LatLngBounds();
            showMark(<?=$g_homelon?>,<?=$g_homelat?>, "Daheim");
            g_MAP.setCenter(g_MAPCENTER, g_MAP.getBoundsZoomLevel(g_MAPBOUNDS));
            g_INITIALIZED = 1;
        }

        //google.setOnLoadCallback(initialize);


        var TSort_Data = new Array ('walks', 's', 's', 'f', 'f');
        var TSort_Classes = new Array ('table_odd', 'table_even');
        tsRegister();

        </script>
        <style type="text/css">
          .table_odd{
                    background:#6cb0bd;
                    color:white;}
          .table_even{
                    background:white;
                    color:#6cb0bd;}
        </style>
    </head>
    <body>
        <table id="walks">
            <thead>
                <tr><th>Tag</th><th>Name</th><th>Laenge</th><th>Dauer</th></tr>
            </thead>
        <div id="map" style="width: 400px; height: 400px"></div>

<?php
    $sql = "SELECT * FROM `walks` LIMIT 0, 10 ";
    $sql_host = "localhost";
    $sql_user = "root";
    $sql_pass = "";
    $db = mysql_connect($sql_host, $sql_user, $sql_pass);

    mysql_select_db('wandern', $db)
        or die ("selecting db failed\n");
    $res = mysql_query($sql, $db);
    $i = 0;

    while($row=mysql_fetch_array($res))
    {
        echo "<tr><td>$row[Tag] $row[Lon] $row[Lat]</td><td>$row[Name]</td><td>$row[Laenge]</td><td>$row[Dauer]</td></tr>\r\n";
    }
    echo "</table>";

    echo "<script type=\"text/javascript\">\n";
    echo "initialize();";
    $res = mysql_query($sql, $db);
    while($row=mysql_fetch_array($res))
    {
        echo "showMark($row[Lat], $row[Lon], '$row[Name]');\n";
    }
    echo "g_MAP.setCenter(g_MAPCENTER, g_MAP.getBoundsZoomLevel(g_MAPBOUNDS));\n";
    echo "</script>";

    mysql_close($db);
?>
    </body>
</html>
