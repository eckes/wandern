<?php 
    global $g_homelat, $g_homelon;
    $g_homelat = 49.414630;
    $g_homelon = 11.031539;
    define("DBTYPE", "XML");

        /* TODO: 
         * - extend the google markermanager class so that the markers can be resolved by providing an id
         * */

    function writeTableLine($a_val1, $a_val2)
    {
        echo <<<END
            <tr>
                <td><input type="checkbox" checked name="tag" id="$a_val1[Tag]_cb" value="$a_val1[Tag]" onchange="cbChanged('$a_val1[Tag]'"> $a_val1[Tag]</td>
                <td><a href="javascript:showInfo('$a_val1[Tag]');">$a_val1[Name]</a></td>
                <td>$a_val1[Laenge]</td>
                <td>$a_val1[Dauer]</td>
                <td>$a_val1[Charakter]</td>
                <td id="$a_val1[Tag]_dst"></td>
            </tr>
END;
    }

    function writeScriptLine($a_val1, $a_val2)
    {
        echo "addMark($a_val1[Lat], $a_val1[Lon], '$a_val1[Name]', '$a_val1[Tag]', ";
        if($a_val1[Datum] != "0000-00-00")
        {
            echo "g_WALKED_ICON, ";
        }
        else
        {
            echo "g_ICON, ";
        }
        echo "$a_val1[Laenge], $a_val1[Dauer], '$a_val1[Charakter]'";
        echo ");\n";
    }

    if(DBTYPE=="MYSQL")
    {
        include("db_mysql.php");
    }
    else if(DBTYPE=="XML")
    {
        include("db_xml.php");
    }
    else
    {
        die("unknown db type defined");
    }

?>
<html>
    <head>
        <script type="text/javascript" src="http://www.google.com/jsapi?key=ABQIAAAARoTP-aPC3X-J7A6v_c-RrRSliXv-vXMxLfXbWpmDAJtGYmmjPhRn1xN7Ce6w66WX49UMmCdujbpuzA"></script>
        <script type="text/javascript" src="../js/gs_sortable.js"></script>
        <script type="text/javascript">
        google.load("maps", "2");

        var g_INITIALIZED       = 0;
        var g_MARKERLIST        = new MarkerList();
        var g_PLANNERJOBS       = new Array();
        var g_CURRENTJOB        = null;
        var g_DIRECTIONS        = null;
        var g_HOME              = null;
        var g_MAPBOUNDS         = null;
        var g_MANAGER           = null;
        var g_MAP               = null;
        var g_ICON              = null;
        var g_WALKED_ICON       = null;

        /* Our MarkEntry class */
        function MarkEntry(a_tag, a_marker, a_description)
        {
            this.m_tag      = a_tag;
            this.m_marker   = a_marker;
            this.m_desc     = a_description;
            this.m_tmpImage = null;
        }

        /**
         * CTor for a new MarkerList
         * */
        function MarkerList()
        {
            this.entries = new Array();
        }

        /**
         * Adds the given entry as new element to the MarkerList
         * */
        MarkerList.prototype.push = function(a_entry)
        {
            this.entries.push(a_entry);
        }

        /**
         * Returns the entry on the given index 
         * */
        MarkerList.prototype.get = function(a_index)
        {
            return this.entries[a_index];
        }

        /**
         * Returns the number of elements 
         * */
        MarkerList.prototype.length = function()
        {
            return this.entries.length;
        }

        /**
         * Searches for an entry with the given ID and returns it.
         * */
        MarkerList.prototype.search = function(a_id)
        {
            var i = 0;
            var me = null;
            for(i = 0; i < this.entries.length; i++)
            {
                me = this.get(i);
                if(null == me)
                {
                    continue; /* skip the gap */
                }
                if(a_id == me.m_tag)
                {
                    return me;
                }
            }
            return null;
        }

        /*--- createInfoString() ------------------------------------------------------ createInfoString() ---*/
        /**
         *  @brief   Creates the info string from the given parameters
         *
         *  @param   a_text     Text to be shown on the info string (title)
         *  @param   a_len      Length of the walk in km
         *  @param   a_dur      Duration of the walk in h
         *  @param   a_char     Character of the walk
         *  @param   a_tag      Tag of the walk
         *
         *  @return  The created HTML statement
         */
        /*--- createInfoString() ------------------------------------------------------ createInfoString() ---*/
        function createInfoString(a_text, a_len, a_dur, a_char, a_tag, a_pos)
        {
            var l_info = "<b>" + a_text + "</b><br>" + a_len + "km | " + a_dur + "h<br>";
            if(a_char)
            {
                l_info = l_info + a_char + "<br>";
            }
            l_info = l_info + "<span id='" + a_tag +"_infodst'><a href=\"javascript:distCalc('" + a_pos +"' , '" + a_tag + "', '_infodst')\">dist</a></span> | ";
            l_info = l_info + "<a href=\"javascript:doHide(\'" + a_tag +"\')\">hide</a>";
            return l_info;
        }

        function infoWindowClosedCB(a_markEntry)
        {
            a_markEntry.m_marker.setImage(a_markEntry.m_tmpImage);
            a_markEntry.m_tmpImage = null;
        }

        /*--- showInfo() ---------------------------------------------------------------------- showInfo() ---*/
        /**
         *  @brief   Shows the information at the mark identified by the given tag
         *
         *  @param   a_tag  Tag identifying the mark to show the information for
         *
         *  @return  nothing
         */
        /*--- showInfo() ---------------------------------------------------------------------- showInfo() ---*/
        function showInfo(a_tag)
        {
            var me = g_MARKERLIST.search(a_tag);
            if(null == me)
            {
                alert("no entry for tag " + a_tag);
            }
            me.m_marker.openInfoWindowHtml(me.m_desc);
            GEvent.addListener(me.m_marker, "infowindowclose", function(){infoWindowClosedCB(me)});
            me.m_tmpImage = me.m_marker.getIcon().image;
            me.m_marker.setImage("images/wanderparkplatz_selected.png");
        }

        /*--- addMark() ------------------------------------------------------------------------ addMark() ---*/
        /**
         *  @brief   Adds a new mark to the map using the given information
         *
         *  @param    a_long  Longitude of the mark position
         *  @param    a_lat   Latitude of the mark position
         *  @param    a_text  Text to be displayed for the mark (the name of the tour)
         *  @param    a_tag   Tag uniquely identifying the walk
         *  @param    a_icon  Icon to use for the mark
         *  @param    a_len   Length in km of the walk
         *  @param    a_dur   Duration of the walk in hours
         *  @param    a_char  Character of the walk (easy, steep, whatever)
         *
         *  @return  nothing
         */
        /*--- addMark() ------------------------------------------------------------------------ addMark() ---*/
        function addMark(a_long, a_lat, a_text, a_tag, a_icon, a_len, a_dur, a_char)
        {
            var pos     = new google.maps.LatLng(a_lat, a_long);
            var options = {title: a_text, bouncy: true, icon:a_icon};
            var l_mark  = new google.maps.Marker(pos, options);
            var l_info = createInfoString(a_text, a_len, a_dur, a_char, a_tag, pos);

            /* add the mark to our markerlist */
            var me = new MarkEntry(a_tag, l_mark, l_info);
            g_MARKERLIST.push(me);
            me = null;

            g_MAPBOUNDS.extend(pos);
            g_MANAGER.addMarker(l_mark, 1);
            GEvent.addListener(l_mark, "click", function(){showInfo(a_tag)});

            var dst = a_tag + "_dst";
            document.getElementById(dst).innerHTML = "<a href=\"javascript:distCalc('" + pos + "','" + a_tag +"', '_dst')\">calculate</a>";
        }

        function PlannerJob(a_tag, a_query, a_descId)
        {
            this.m_tag      = a_tag;
            this.m_query    = a_query;
            this.m_descId   = a_descId;
        }

        /**
         * Calculates the distance from home to the given position identified by the given tag
         * */
        function distCalc(a_pos, a_tag, a_suffix)
        {
            var l_query = "from: " + g_HOME + " to: " + a_pos;
            g_CURRENTJOB = new PlannerJob(a_tag, l_query, a_tag + a_suffix);
            document.getElementById(g_CURRENTJOB.m_descId).innerHTML = "working...";
            g_DIRECTIONS.load(l_query);
        }

        function dirLoadedCB()
        {
            /* finish the running job */
            if(g_CURRENTJOB)
            {
                document.getElementById(g_CURRENTJOB.m_descId).innerHTML = g_DIRECTIONS.getDistance().html;
                delete g_CURRENTJOB;
                g_CURRENTJOB = null;
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

        /*--- initialize() ------------------------------------------------------------------ initialize() ---*/
        /**
         *  @brief   Init function. Gets executed only once upon page load
         *
         *  @return  nothing
         */
        /*--- initialize() ------------------------------------------------------------------ initialize() ---*/
        function initialize()
        {
            if(g_INITIALIZED) return;
            g_INITIALIZED = 1;

            g_MAP = new google.maps.Map2(document.getElementById("map"));
            g_MAP.addControl(new google.maps.MapTypeControl());
            g_MAP.addControl(new google.maps.SmallZoomControl());
            g_MAP.addMapType(G_PHYSICAL_MAP);
            g_MANAGER       = new google.maps.MarkerManager(g_MAP);
            g_MAPBOUNDS     = new google.maps.LatLngBounds();

            g_ICON          = new google.maps.Icon();
            g_ICON.image                    = "images/wanderparkplatz.png"; 
            g_ICON.iconSize                 = new google.maps.Size(21,32);
            g_ICON.iconAnchor               = new google.maps.Point(0,36);
            g_ICON.infoWindowAnchor         = new google.maps.Point(10.5,2);
            g_ICON.shadow                   = "images/wanderparkplatz_schatten.png";
            g_ICON.shadowSize               = new google.maps.Size(79,32);

            g_WALKED_ICON   = new google.maps.Icon();
            g_WALKED_ICON.image             = "images/wanderparkplatz_hell.png"; 
            g_WALKED_ICON.iconSize          = new google.maps.Size(21.5,32);
            g_WALKED_ICON.iconAnchor        = new google.maps.Point(0,36);
            g_WALKED_ICON.infoWindowAnchor  = new google.maps.Point(10.5,2);
            g_WALKED_ICON.shadow            = "images/wanderparkplatz_schatten.png";
            g_WALKED_ICON.shadowSize        = new google.maps.Size(79,32);

            g_DIRECTIONS    = new google.maps.Directions();
            GEvent.addListener(g_DIRECTIONS, "load", dirLoadedCB);

            g_HOME       = new google.maps.LatLng(<?=$g_homelat?>, <?=$g_homelon?>);
        }

        function hideAll()
        {
            var i = 0;
            for (i = 0; i < g_MARKERLIST.length(); i++)
            {
                me = g_MARKERLIST.get(i);
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
            for (i = 0; i < g_MARKERLIST.length(); i++)
            {
                me = g_MARKERLIST.get(i);
                if(null == me)
                {
                    continue; /* skip the gap */
                }
                doShow(me.m_tag);
            }
        }

        function doShow(a_name)
        {
            document.getElementById(a_name + "_cb").checked = true;
            return cbChanged(a_name);
        }

        function doHide(a_name)
        {
            document.getElementById(a_name + "_cb").checked = false;
            return cbChanged(a_name);
        }

        function cbChanged(a_name)
        {
            var bChecked = document.getElementById(a_name + "_cb").checked;
            var me      = null;
            var i       = 0;
            me = g_MARKERLIST.search(a_name);
            if(null == me)
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


        var TSort_Data = new Array ('walks', 's', 's', 'f', 'f', 's', 's');
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
    <body onunload="GUnload()">
        <div>
            <div id="map" style="width: 800px; height: 600px"></div>
            <a href="javascript:hideAll();">hide all</a> 
            <a href="javascript:showAll();">show all</a> 
        </div>
        <table id="walks">
            <thead>
                <tr><th>Tag</th><th>Name</th><th>Laenge</th><th>Dauer</th><th>Charakterisik</th><th>Entfernung</th></tr>
            </thead>

<?php
    /* XXX This block gets the elements to show! XXX */
    $res = db_init();
    $elements = db_getElements($res);
    db_cleanup($res);
    /* XXX This block gets the elements to show! XXX */

    array_walk($elements, writeTableLine);

echo <<<END
    </table>
    <script type="text/javascript">
        initialize();
        showHome('Daheim');
END;

    array_walk($elements, writeScriptLine);

echo <<<END
        g_MAP.setCenter(g_MAPBOUNDS.getCenter(), g_MAP.getBoundsZoomLevel(g_MAPBOUNDS));
    </script>
END;
?>
    </body>
</html>
