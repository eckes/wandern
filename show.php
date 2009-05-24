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

        var G_PLANNER_IDLE      = 0;
        var G_PLANNER_WORKING   = 1;

        var g_PLANNER_STATE     = G_PLANNER_IDLE;
        var g_LAT_MIN           = 0;
        var g_LAT_MAX           = 0;
        var g_LON_MIN           = 0;
        var g_LON_MAX           = 0;
        var g_INITIALIZED       = 0;
        var g_MARKERLIST        = new Array();
        var g_PLANNERJOBS       = new Array();
        var g_CURRENTJOB        = null;
        var g_DIRECTIONS        = null;
        var g_CALC_DISTANCES    = false;
        var g_HOME;
        var g_MAPBOUNDS;
        var g_MANAGER;
        var g_MAP;
        var g_ICON;
        var g_WALKED_ICON;

        /* Our MarkEntry class */
        function MarkEntry(a_tag, a_marker)
        {
            this.m_tag      = a_tag;
            this.m_marker   = a_marker;
        }

        function addMark(a_long, a_lat, a_text, a_tag, a_Icon, a_len, a_dur)
        {
            var pos     = new google.maps.LatLng(a_lat, a_long);
            var options = {title: a_text, bouncy: true, icon:a_Icon};
            var mark    = new google.maps.Marker(pos, options);

            /* hide the mark and add it to our markerlist */
            var me = new MarkEntry(a_tag, mark);
            g_MARKERLIST.push(me);
            me = null;

            g_MAPBOUNDS.extend(pos);
            g_MANAGER.addMarker(mark, 10);
            var l_info = "<i>" + a_text + "</i><br>" + a_len + "km | " + a_dur + "h | <a href=\"javascript:doHide(\'" + a_tag +"\')\">hide</a>";
            GEvent.addListener(mark, "click", function(){mark.openInfoWindowHtml(l_info);});

            if(g_CALC_DISTANCES)
            {
                distCalc(pos, a_tag);
            }
            else
            {
                var dst = a_tag + "_dst";
                document.getElementById(dst).innerHTML = "<i>disabled</i>";
            }
        }

        /* Functions to calculate distances below */
        function distCalc(a_pos, a_tag)
        {
            var l_query = "from: " + g_HOME + " to: " + a_pos;
            var pj = new PlannerJob(a_tag, l_query);
            if(g_PLANNER_STATE == G_PLANNER_IDLE)
            {
                g_PLANNER_STATE = G_PLANNER_WORKING;
                g_CURRENTJOB    = pj;
                pj = null;
                var dst = g_CURRENTJOB.m_tag + "_dst";
                document.getElementById(dst).innerHTML = "working...";
                g_DIRECTIONS.load(g_CURRENTJOB.m_query);
            }
            else
            {
                g_PLANNERJOBS.push(pj);
                pj = null;
            }
        }

        function PlannerJob(a_tag, a_query)
        {
            this.m_tag      = a_tag;
            this.m_query    = a_query;
        }

        function dirLoadedCB()
        {
            /* finish the running job */
            if(g_CURRENTJOB)
            {
                var dst = g_CURRENTJOB.m_tag + "_dst";
                document.getElementById(dst).innerHTML = g_DIRECTIONS.getDistance().html;
            }
            /* start a new one */
            if(0 == g_PLANNERJOBS.length)
            {
                alert("done");
                g_PLANNER_STATE = G_PLANNER_IDLE;
            }
            else
            {
                g_CURRENTJOB = g_PLANNERJOBS.pop();
                g_DIRECTIONS.load(g_CURRENTJOB.m_query);
                var dst = g_CURRENTJOB.m_tag + "_dst";
                document.getElementById(dst).innerHTML = "working...";
            }
        }
        /* Functions to calculate distances above */

        function showHome(a_text)
        {
            var homeIcon = new google.maps.Icon();
            homeIcon.image              = "images/home.png";
            homeIcon.iconSize           = new google.maps.Size(22,22);
            homeIcon.iconAnchor         = new google.maps.Point(0,25);
            homeIcon.infoWindowAnchor   = new google.maps.Point(11,0);

            var options = {title: a_text, icon:homeIcon};
            var mark    = new google.maps.Marker(g_HOME, options);
            g_MAPBOUNDS.extend(g_HOME);
            g_MANAGER.addMarker(mark, 10);
            g_MANAGER.refresh();
            GEvent.addListener(mark, "click", function(){mark.openInfoWindowHtml(a_text);});
        }

        function initialize() {
            if(g_INITIALIZED) return;
            g_INITIALIZED = 1;

            g_MAP = new google.maps.Map2(document.getElementById("map"));
            g_MANAGER = new google.maps.MarkerManager(g_MAP);
            g_MAPBOUNDS = new google.maps.LatLngBounds();

            g_ICON = new google.maps.Icon();
            g_ICON.image                = "images/wanderparkplatz.png"; 
            g_ICON.iconSize             = new google.maps.Size(21.5,32);
            g_ICON.iconAnchor           = new google.maps.Point(0,36);
            g_ICON.infoWindowAnchor     = new google.maps.Point(5,2);

            g_WALKED_ICON = new google.maps.Icon();
            g_WALKED_ICON.image             = "images/wanderparkplatz_hell.png"; 
            g_WALKED_ICON.iconSize          = new google.maps.Size(21.5,32);
            g_WALKED_ICON.iconAnchor        = new google.maps.Point(0,36);
            g_WALKED_ICON.infoWindowAnchor  = new google.maps.Point(5,2);

            if(g_CALC_DISTANCES)
            {
                g_DIRECTIONS = new google.maps.Directions();
                GEvent.addListener(g_DIRECTIONS, "load", dirLoadedCB);
            }

            g_HOME       = new google.maps.LatLng(<?=$g_homelat?>, <?=$g_homelon?>);
        }

        function hideAll()
        {
            var i = 0;
            for (i = 0; i < g_MARKERLIST.length; i++)
            {
                me = g_MARKERLIST[i];
                if(null == me)
                {
                    continue; /* skip the gap */
                }
                doHide(me.m_tag);
            }
        }

        function showAll()
        {
            var i = 0;
            for (i = 0; i < g_MARKERLIST.length; i++)
            {
                me = g_MARKERLIST[i];
                if(null == me)
                {
                    continue; /* skip the gap */
                }
                doShow(me.m_tag);
            }
        }

        function doShow(a_name)
        {
            document.getElementById(a_name).checked = true;
            return cbChanged(a_name);
        }

        function doHide(a_name)
        {
            document.getElementById(a_name).checked = false;
            return cbChanged(a_name);
        }

        function cbChanged(a_name)
        {
            var bChecked = document.getElementById(a_name).checked;
            var me      = null;
            var i       = 0;
            for(i = 0; i < g_MARKERLIST.length; i++)
            {
                me = g_MARKERLIST[i];
                if(null == me)
                {
                    continue; /* skip the gap */
                }
                if(a_name == me.m_tag)
                {
                    break;
                }
            }
            if(g_MARKERLIST.length <= i)
            {
                alert("not found");
            }
            if(false == bChecked)
            {
                me.m_marker.hide();
                me.m_marker.closeInfoWindow();
            }
            else
            {
                me.m_marker.show();
            }
        }

        //google.setOnLoadCallback(initialize);


        var TSort_Data = new Array ('walks', 's', 's', 'f', 'f', 's');
        var TSort_Classes = new Array ('table_odd', 'table_even');
        var TSort_Initial = 0;
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
        <div>
            <div id="map" style="width: 800px; height: 600px"></div>
            <a href="javascript:g_MAP.zoomIn();">zoom in</a> 
            <a href="javascript:g_MAP.zoomOut();">zoom out</a> 
            <a href="javascript:hideAll();">hide all</a> 
            <a href="javascript:showAll();">show all</a> 
        </div>
        <table id="walks">
            <thead>
                <tr><th>Tag</th><th>Name</th><th>Laenge</th><th>Dauer</th><th>Entfernung</th></tr>
            </thead>

<?php
    function build_query()
    {
        print_r($_REQUEST);
        $l_sql = "SELECT * FROM `walks` WHERE";
        if(!$_REQUEST[showwalked])
        {
            $l_sql = $l_sql . "(`DATUM` = 0000-00-00)";
        }
        else
        {
            $l_sql = $l_sql . "(1)";
        }
        $l_sql = $l_sql . " AND ";
        if($_REQUEST[kein_huegeliges])
        {
            $l_sql = $l_sql . "(`Charakter` NOT LIKE '%hügelig%')";
        }
        else
        {
            $l_sql = $l_sql . "(1)";
        }
        return $l_sql;
    }

    //$sql = "SELECT * FROM `walks` LIMIT 00, 10 ";
    $sql = build_query();
    echo $sql;
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
        echo "<tr><td><input type=\"checkbox\" checked name=\"tag\" id=\"$row[Tag]\" value=\"$row[Tag]\" onchange=\"cbChanged('$row[Tag]')\"> $row[Tag]</td><td>$row[Name]</td><td>$row[Laenge]</td><td>$row[Dauer]</td><td id=\"$row[Tag]_dst\"></td></tr>\r\n";
    }
    echo "</table>";

    echo "<script type=\"text/javascript\">\n";
    echo "initialize();\n";
    echo "showHome('Daheim');\n";
    $res = mysql_query($sql, $db);
    while($row=mysql_fetch_array($res))
    {
        echo "addMark($row[Lat], $row[Lon], '$row[Name]', '$row[Tag]', ";
        if($row[Datum] != "0000-00-00")
        {
            echo "g_WALKED_ICON, ";
        }
        else
        {
            echo "g_ICON, ";
        }
        echo "$row[Laenge], $row[Dauer]";
        echo ");\n";
    }
    echo "g_MAP.setCenter(g_MAPBOUNDS.getCenter(), g_MAP.getBoundsZoomLevel(g_MAPBOUNDS));\n";
    echo "</script>";

    mysql_close($db);
?>
    </body>
</html>
