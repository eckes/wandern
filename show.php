<?php 
    require_once('../login/common.php');
    require_once('../css/colors.php');
    require_once('common.php');

    require_once("constants.php");

    if(checkSession())
    {
        $_SESSION['settings'] = loadSettings($_SESSION['userName']); 
    } 
    else
    {
        $_SESSION['settings'] = loadSettings('anonymous'); 
    }

    /* check if someone clicked the "walked" button */
    $keys = array_keys($_POST);
    foreach($keys AS $thekey)
    {
        $needle = stristr($thekey, '_isWalked');
        if($needle)
        {
            $id = substr($thekey, 0, (strlen($thekey)-strlen($needle)));
            $res = editWalk($_SESSION['userName'], $id, 'walked');
            if(isset($_SESSION['orig_request']))
            {
                $_REQUEST = $_SESSION['orig_request'];
                unset($_SESSION['orig_request']);
            }
        }
    }

    function writeTableLine($a_val1, $a_val2)
    {
        if( (!isset($a_val1[Charakter])) || ($a_val1[Charakter] =='') )
        {
            $a_val1[Charakter] = '&nbsp;';
        }
        echo <<<END
            <tr id="$a_val1[Tag]">
                <td><input type="checkbox" checked name="tag" id="$a_val1[Tag]_cb" value="$a_val1[Tag]" onchange="cbChanged('$a_val1[Tag]')"> $a_val1[Tag]</td>
                <td><a href="javascript:showInfo('$a_val1[Tag]');"><span id="$a_val1[Tag]_name">$a_val1[Name]</span></a></td>
                <td>$a_val1[Laenge]</td>
                <td>$a_val1[Dauer]</td>
                <td>$a_val1[Charakter]</td>
                <td id="$a_val1[Tag]_dst"></td>
END;
                if($_SESSION['validUser'] == true) 
                {
                    if($a_val1[Datum]=="0000-00-00")
                    {
                        echo <<<END
                            <td id="$a_val1[Tag]_isWalked"><button name="$a_val1[Tag]_isWalked" type="button" value="Gelaufen" onclick="markAsWalked('$a_val1[Tag]');">Gelaufen</button></td>
END;
                    }
                    else
                    {
                        echo "<td>&nbsp;</td>";
                    }
                }
            echo "</tr>\n";
    }

    function writeScriptLine($a_val1, $a_val2)
    {
        echo "addMark($a_val1[Lon], $a_val1[Lat], '$a_val1[Name]', '$a_val1[Tag]', ";

        if(isset($a_val1[Datum]) && ($a_val1[Datum] != "0000-00-00") )
        {
            echo "g_WALKED_ICON, ";
        }
        else
        {
            echo "g_ICON, ";
        }
        echo "$a_val1[Laenge], $a_val1[Dauer], '$a_val1[Charakter]'";
        echo ");\n";

        echo "Datum: $a_val1[Datum]\n";
    }

    include("db_xml.php");

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <title>Search Results</title>
        <META http-equiv="content-type" content="text/html; charset=UTF-8">
        <link rel="stylesheet" type="text/css" href="../css/style.php">
        <script type="text/javascript" src="http://www.google.com/jsapi?key=ABQIAAAARoTP-aPC3X-J7A6v_c-RrRSliXv-vXMxLfXbWpmDAJtGYmmjPhRn1xN7Ce6w66WX49UMmCdujbpuzA"></script>
        <script type="text/javascript" src="../js/gs_sortable.js"></script>
        <script type="text/javascript">
        google.load("maps", "2");

        /* ------------------------------------------------------------------------------------------------ */
        /* Globals                                                                                          */
        /* ------------------------------------------------------------------------------------------------ */
        var g_INITIALIZED       = 0;    /**< Prevents from double-inits                                     */
        var g_MARKERLIST        = null; /**< List taking all our marks                                      */
        var g_CURRENTJOB        = null; /**< The distance-calculation currently active                      */
        var g_DIRECTIONS        = null; /**< Also for distance calculation                                  */
        var g_HOME              = null; /**< Coordinates of home sweet home                                 */
        var g_MAP               = null; /**< Map to take everything                                         */
        var g_ICON              = null; /**< The normal marker for standard walks                           */
        var g_WALKED_ICON       = null; /**< The marker for already walked walks                            */
        var g_HIGHLIGHT         = null; /**< The highlight marker that gets moved dynamically               */

        /* ------------------------------------------------------------------------------------------------ */
        /* BEGIN configuration of the table-sorting-and-striping script                                     */
        /* ------------------------------------------------------------------------------------------------ */
        var TSort_Data = new Array ('walks', 's', 's', 'f', 'f', 's', 's'
<?php
if($_SESSION['validUser'] == true) 
{
    echo ", ''";
}
?>
);
        var TSort_Classes = new Array ('table_odd', 'table_even');
        var TSort_Initial = 0;
        tsRegister();
        /* ------------------------------------------------------------------------------------------------ */
        /* END configuration of the table-sorting-and-striping script                                       */
        /* ------------------------------------------------------------------------------------------------ */

        /* ------------------------------------------------------------------------------------------------ */
        /* BEGIN Class MarkEntry                                                                            */
        /* ------------------------------------------------------------------------------------------------ */
        function MarkEntry(a_id, a_marker, a_description)
        {
            this.m_id       = a_id;
            this.m_marker   = a_marker;
            this.m_desc     = a_description;
            this.length     = 0;
        }
        /* ------------------------------------------------------------------------------------------------ */
        /* END Class MarkEntry                                                                              */
        /* ------------------------------------------------------------------------------------------------ */

        /* ------------------------------------------------------------------------------------------------ */
        /* BEGIN Class PlannerJob                                                                           */
        /* ------------------------------------------------------------------------------------------------ */
        function PlannerJob(a_id, a_query, a_descId)
        {
            this.m_id       = a_id;
            this.m_query    = a_query;
            this.m_descId   = a_descId;
        }
        /* ------------------------------------------------------------------------------------------------ */
        /* END Class PlannerJob                                                                             */
        /* ------------------------------------------------------------------------------------------------ */

        /* ------------------------------------------------------------------------------------------------ */
        /* BEGIN Class MarkerList                                                                           */
        /* ------------------------------------------------------------------------------------------------ */
        /** 
         * CTor for a new MarkerList 
         *
         * @param  a_map   Map to take the markers
         */
        function MarkerList(a_map)
        {
            this.entries    = new Array();
            this.manager    = new google.maps.MarkerManager(a_map);
            this.bounds     = new google.maps.LatLngBounds();
        }

        /** 
         * Adds the given entry as new element to the MarkerList
         *
         * @param a_entry    MarkerEntry object to be added to the list
         */
        MarkerList.prototype.push = function(a_entry)
        {
            this.entries.push(a_entry);
            this.bounds.extend(a_entry.m_marker.getLatLng());
            this.manager.addMarker(a_entry.m_marker, 1);
            this.length = this.entries.length;
        }

        /** 
         * Returns the center point of the bounds of all the markers
         *
         * @return  The center of the map as google.maps.LatLng object
         */
        MarkerList.prototype.getCenter = function()
        {
            return this.bounds.getCenter();
        }

        /**
         * Accessor for the bounds object of the MarkerList
         *
         * @return  The bounds of the map as google.maps.LatLngBounds object
         */
        MarkerList.prototype.getBounds = function()
        {
            return this.bounds;
        }

        /** 
         * Returns the entry on the given index
         *
         * @param a_index   Index of the MarkerEntry to return
         *
         * @return the MarkerEntry stored at the given index
         */
        MarkerList.prototype.get = function(a_index)
        {
            return this.entries[a_index];
        }

        /**
         * Searches for an entry with the given ID and returns it. 
         *
         * @param   a_id    ID of the entry to search for
         *
         * @return the MarkEntry identified by the given ID
         */

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
                if(a_id == me.m_id)
                {
                    me.index = i;
                    return me;
                }
            }
            return null;
        }

        /** 
         * Shows the marker with the given name 
         *
         * @param a_id     ID of the marker to show
         */
        MarkerList.prototype.show = function(a_id)
        {
            var me      = this.search(a_id);
            if(me)
            {
                me.m_marker.show();
            }
            var e = document.getElementById(a_id + "_cb");
            if(e)
            {
                e.checked = true;
            }
        }

        /**
         * Hides the marker with the given name 
         *
         * @param a_id   ID of the marker to hide
         */
        MarkerList.prototype.hide = function(a_id)
        {
            var me      = this.search(a_id);
            if(me)
            {
                me.m_marker.hide();
            }

            if(g_HIGHLIGHT)
            {
                g_HIGHLIGHT.closeInfoWindow();
                g_HIGHLIGHT.hide();
            }
            var e = document.getElementById(a_id + "_cb");
            if(e)
            {
                e.checked = false;
            }
        }

        /** Displays all the markers of the list */
        MarkerList.prototype.showAll = function()
        {
            var i = 0;
            for (i = 0; i < this.length; i++)
            {
                me = this.get(i);
                if( (null == me) || (me.m_id == "highlight") )
                {
                    continue; /* skip the gap and the highlight marker */
                }
                this.show(me.m_id);
            }
        }

        /** Hides all the markers of the list */
        MarkerList.prototype.hideAll = function()
        {
            var i = 0;
            for (i = 0; i < this.length; i++)
            {
                me = this.get(i);
                if( (null == me) || (me.m_id == "home") )
                {
                    continue; /* skip the gap and the home marker */
                }
                this.hide(me.m_id);
            }
        }
        /* ------------------------------------------------------------------------------------------------ */
        /* END Class MarkerList                                                                             */
        /* ------------------------------------------------------------------------------------------------ */

        /* ------------------------------------------------------------------------------------------------ */
        /* BEGIN Class MyIcon                                                                               */
        /* ------------------------------------------------------------------------------------------------ */
        MyIcon.prototype = new google.maps.Icon();  /* Default CTor of the parent class */
        MyIcon.prototype.constructor = MyIcon;      /* assign our own CTor              */
        /* CTor of the MyIcon class */
        function MyIcon(a_foreground)
        {
            this.image                    = a_foreground; 
            this.iconSize                 = new google.maps.Size(21,32);
            this.iconAnchor               = new google.maps.Point(0,36);
            this.infoWindowAnchor         = new google.maps.Point(10.5,2);
            this.shadow                   = "images/wanderparkplatz_schatten.png";
            this.shadowSize               = new google.maps.Size(79,32);
        }
        /* ------------------------------------------------------------------------------------------------ */
        /* END Class MyIcon                                                                                 */
        /* ------------------------------------------------------------------------------------------------ */

        /* ------------------------------------------------------------------------------------------------ */
        /* BEGIN Helper Methods                                                                             */
        /* ------------------------------------------------------------------------------------------------ */
        /*--- createInfoString() ------------------------------------------------------ createInfoString() ---*/
        /**
         *  @brief   Creates the info string from the given parameters
         *
         *  @param   a_text     Text to be shown on the info string (title)
         *  @param   a_len      Length of the walk in km
         *  @param   a_dur      Duration of the walk in h
         *  @param   a_char     Character of the walk
         *  @param   a_id       Tag of the walk
         *
         *  @return  The created HTML statement
         */
        /*--- createInfoString() ------------------------------------------------------ createInfoString() ---*/
        function createInfoString(a_text, a_len, a_dur, a_char, a_id)
        {
            var l_image = "images/" + a_id.toLowerCase().substr(0, a_id.length-3) + "_small.png";
            var l_info  = "<img src='" + l_image + "' alt='" + a_id + "' title='" + a_id + "' style='float:left;padding-right:4px;'>";
            l_info = l_info + "<b>" + a_text + "</b><br>" + a_len + "km | " + a_dur + "h<br>";
            if(a_char)
            {
                l_info = l_info + a_char + "<br>";
            }
            l_info = l_info + "<span id='" + a_id +"_infodst'><a href=\"javascript:distCalc('" + a_id + "', '_infodst')\">Entfernung</a></span> | ";
            l_info = l_info + "<a href=\"javascript:g_MARKERLIST.hide(\'" + a_id +"\')\">Verbergen</a>";

<?php
    if(checkSession())
    {
            echo 'l_info = l_info + " | <a href=\"javascript:markAsWalked(\'" + a_id +"\')\">Gelaufen</a>";';
    }
?>
            return l_info;
        }

        function infoWindowClosedCB()
        {
            var line = document.getElementById(g_HIGHLIGHT._id);
            g_HIGHLIGHT._id = "";

            if(line)
            {
                line.className=line.className.substr(0, line.className.length-3);
            }
            g_HIGHLIGHT.hide();
        }

        /*--- showInfo() ---------------------------------------------------------------------- showInfo() ---*/
        /**
         *  @brief   Shows the information at the mark identified by the given tag
         *
         *  @param   a_id  Tag identifying the mark to show the information for
         *
         *  @return  nothing
         */
        /*--- showInfo() ---------------------------------------------------------------------- showInfo() ---*/
        function showInfo(a_id)
        {
            var me = g_MARKERLIST.search(a_id);
            if(null == me)
            {
                alert("no entry for tag " + a_id);
            }
            if(null == g_HIGHLIGHT)
            {
                createHighlight();
            }

            if(g_HIGHLIGHT._id != "")
            {
                infoWindowClosedCB();
            }
            var line = document.getElementById(a_id);
            line.className=line.className + "_hl";

            g_HIGHLIGHT.setLatLng(me.m_marker.getLatLng());
            g_HIGHLIGHT.openInfoWindowHtml(me.m_desc);
            g_HIGHLIGHT.show();
            g_HIGHLIGHT._id = a_id;
            GEvent.addListener(g_HIGHLIGHT, "infowindowclose", function(){infoWindowClosedCB()});
        }

        /*--- addMark() ------------------------------------------------------------------------ addMark() ---*/
        /**
         *  @brief   Adds a new mark to the map using the given information
         *
         *  @param    a_long  Longitude of the mark position
         *  @param    a_lat   Latitude of the mark position
         *  @param    a_text  Text to be displayed for the mark (the name of the tour)
         *  @param    a_id    Tag uniquely identifying the walk
         *  @param    a_icon  Icon to use for the mark
         *  @param    a_len   Length in km of the walk
         *  @param    a_dur   Duration of the walk in hours
         *  @param    a_char  Character of the walk (easy, steep, whatever)
         *
         *  @return  nothing
         */
        /*--- addMark() ------------------------------------------------------------------------ addMark() ---*/
        function addMark(a_long, a_lat, a_text, a_id, a_icon, a_len, a_dur, a_char)
        {
            var pos     = new google.maps.LatLng(a_lat, a_long);
            var options = {title: a_text, bouncy: true, icon:a_icon};
            var l_mark  = new google.maps.Marker(pos, options);
            var l_info  = createInfoString(a_text, a_len, a_dur, a_char, a_id);

            /* add the mark to our markerlist */
            var me = new MarkEntry(a_id, l_mark, l_info);
            g_MARKERLIST.push(me);
            me = null;

            GEvent.addListener(l_mark, "click", function(){showInfo(a_id)});

            var dst = a_id + "_dst";
            document.getElementById(dst).innerHTML = "<a href=\"javascript:distCalc('" + a_id +"', '_dst')\">Berechnen</a>";
        }

        /*--- showHome() ---------------------------------------------------------------------- showHome() ---*/
        /**
         *  @brief   Creates the home mark identified by a cute little red heart :-)
         *
         *  @param   a_text     The title to show when hovering over the icon
         */
        /*--- showHome() ---------------------------------------------------------------------- showHome() ---*/
        function showHome(a_text)
        {
            var homeIcon = new google.maps.Icon();
            homeIcon.image              = "images/home.png";
            homeIcon.iconSize           = new google.maps.Size(22,22);
            homeIcon.iconAnchor         = new google.maps.Point(0,25);
            homeIcon.infoWindowAnchor   = new google.maps.Point(11,0);

            var options = {title: a_text, icon:homeIcon};
            var mark    = new google.maps.Marker(g_HOME, options);
            mark.bindInfoWindowHtml("<h1><i>Daheim</i></h1><p>Home sweet Home</p>");
            var me = new MarkEntry("home", mark, "Daheim");
            g_MARKERLIST.push(me);
        }

        /*--- getZIndex() -------------------------------------------------------------------- getZIndex() ---*/
        /**
         *  @brief   I've got no clue why we need this callback function, but I can say, when it's not present,
         *               the highlighting mark doesn't go to foreground. So, it's better to leave it here.
         *
         *  @param   a_mark     the mark for which the zIndex shall be returned
         *
         *  @return  The new zIndex, as the google docs say
         */
        /*--- getZIndex() -------------------------------------------------------------------- getZIndex() ---*/
        function getZIndex(a_mark)
        {
            return 0;
        }

        /*--- createHighlight() -------------------------------------------------------- createHighlight() ---*/
        /**
         *  @brief   Creates the highlighting mark that gets moved to the currently selected mark
         */
        /*--- createHighlight() -------------------------------------------------------- createHighlight() ---*/
        function createHighlight()
        {
            var hiIcon  = new MyIcon("images/wanderparkplatz_selected.png");
            var options = {icon:hiIcon, zIndexProcess:getZIndex};
            g_HIGHLIGHT = new google.maps.Marker(g_HOME, options);
            g_HIGHLIGHT._id = "";
            g_HIGHLIGHT.hide();
            var me = new MarkEntry("highlight", g_HIGHLIGHT, "HL");
            g_MARKERLIST.push(me);
        }

        /*--- cbChanged() -------------------------------------------------------------------- cbChanged() ---*/
        /**
         *  @brief   Gets called whenever a checkbox gets selected or de-selected
         *
         *  @param   a_id   ID of the element that has changed
         */
        /*--- cbChanged() -------------------------------------------------------------------- cbChanged() ---*/
        function cbChanged(a_id)
        {
            var e = document.getElementById(a_id + "_cb");
            if(e)
            {
                if(false == e.checked)
                {
                    g_MARKERLIST.hide(a_id);
                }
                else
                {
                    g_MARKERLIST.show(a_id);
                }
            }
        }

        /*--- markAsWalked() -------------------------------------------------------------- markAsWalked() ---*/
        /**
         *  @brief   does some little preparation before marking the walk with the given ID as walked
         *
         *  @param   a_id  ID of the walk to be marked as walked
         *
         *  @return  A MWEB_RESULT
         */
        /*--- markAsWalked() -------------------------------------------------------------- markAsWalked() ---*/
        function markAsWalked(a_id)
        {
            var theName = document.getElementById(a_id + "_name").innerHTML;
            if(confirm('Wanderung\n\n"' + theName + '"\n\nals gelaufen markieren?'))
            {
                var theForm = document.walktable;
                var btn     = a_id + "_isWalked";
                var hidden = '<input type="hidden" name="' + btn + '" value="' + btn + '"></input>';
                /* add the information about which button was clicked to the form as a hidden input */
                theForm.innerHTML = theForm.innerHTML.concat(hidden);
                theForm.submit();
            }
        }
        /* ------------------------------------------------------------------------------------------------ */
        /* END Helper Methods                                                                               */
        /* ------------------------------------------------------------------------------------------------ */

        /* ------------------------------------------------------------------------------------------------ */
        /* BEGIN Methods to calculate distances                                                             */
        /* ------------------------------------------------------------------------------------------------ */
        /**
         * Calculates the distance from home to the given position identified by the given id
         * */
        function distCalc(a_id, a_suffix)
        {
            var me = g_MARKERLIST.search(a_id);
            if(!me)alert("Entry not found for ID " + a_id);
            var l_query = "from: " + g_HOME + " to: " + me.m_marker.getLatLng();
            g_CURRENTJOB = new PlannerJob(a_id, l_query, a_id + a_suffix);
            document.getElementById(g_CURRENTJOB.m_descId).innerHTML = "working...";
            g_DIRECTIONS.load(l_query);
        }

        /** Callback getting called when a direction was loaded successfully */
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
        /* ------------------------------------------------------------------------------------------------ */
        /* END Methods to calculate distances                                                               */
        /* ------------------------------------------------------------------------------------------------ */

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

            g_MARKERLIST    = new MarkerList(g_MAP);

            g_ICON          = new MyIcon("images/wanderparkplatz.png");
            g_WALKED_ICON   = new MyIcon("images/wanderparkplatz_hell.png");
            g_DIRECTIONS    = new google.maps.Directions();

            GEvent.addListener(g_DIRECTIONS, "load", dirLoadedCB);

            g_HOME       = new google.maps.LatLng(<?=$_SESSION['settings']['lat']?>, <?=$_SESSION['settings']['lon']?>);
        }
        </script>
        <style type="text/css">
          .table_odd{
              background:<?=$col_accent?>;
                    color:<?=$col_body?>;}
          .table_even{
                    background:<?=$col_body?>;
                    color:<?=$col_accent?>;}
          .table_odd_hl td {
              border-top:2px solid <?=$col_hlight?>;
                    border-bottom:2px solid <?=$col_hlight?>;
                    background:<?=$col_hlight2?>;
                    color:<?=$col_body?>;}
          .table_odd_hl a:link{background:<?=$col_hlight2?>;color:<?=$col_body?>;}
          .table_even_hl td {
                    border-top:2px solid <?=$col_hlight?>;
                    border-bottom:2px solid <?=$col_hlight?>;
                    background:<?=$col_body?>;
                    color:<?=$col_hlight2?>;}
          .table_even_hl a:link{background:<?=$col_body?>;color:<?=$col_hlight2?>;}
        </style>
    </head>
    <body onunload="GUnload()">
<?php
    require_once('loginhead.php');
    if($_SESSION['validUser'] == true) 
    {
        $_SESSION['orig_request'] = $_REQUEST;
    }
?>
        <div>
            <div id="map" style="width: 800px; height: 600px"></div>
            <a href="javascript:g_MARKERLIST.hideAll();">Alle verbergen</a> 
            <a href="javascript:g_MARKERLIST.showAll();">Alle anzeigen</a> 
        </div>
        <form name="walktable" action="" method="post">
            <table id="walks">
                <thead>
                    <tr><th>Tag</th><th>Name</th><th>Laenge</th><th>Dauer</th><th>Charakterisik</th><th>Entfernung</th>
<?php
if($_SESSION['validUser'] == true) 
{
    echo "<th></th>";
}
?>
                </tr>
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
    </form>

    <script type="text/javascript">
        initialize();
        showHome('Daheim');
END;

    array_walk($elements, writeScriptLine);

echo <<<END
        g_MAP.setCenter(g_MARKERLIST.getCenter(), g_MAP.getBoundsZoomLevel(g_MARKERLIST.getBounds()));
    </script>
END;
?>
    </body>
</html>
