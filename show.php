<?php 
require_once('../login/common.php');
require_once('../css/colors.php');
require_once('common.php');

require_once('track.php');

require_once("constants.php");

if(checkSession())
{
  $_SESSION['settings'] = loadSettings($_SESSION['userName']); 
} 
else
{
  $_SESSION['settings'] = loadSettings('anonymous'); 
}

function writeOptionLine($a_val, $a_key)
{
  echo "g_OPTIONS['" . $a_key . "'] = " . boolToString($a_val) . ";\n";
}

function writeTableLine($a_val1)
{
  if( (!isset($a_val1[Charakter])) || ($a_val1[Charakter] =='') )
  {
    $a_val1[Charakter] = '&nbsp;';
  }
  
  echo <<<END
            <tr id="$a_val1[Tag]">
                <td><input type="checkbox" checked name="tag" id="$a_val1[Tag]_cb" value="$a_val1[Tag]" onchange="cbChanged('$a_val1[Tag]')"> $a_val1[Tag]</td>
                <td><a href="javascript:g_MARKERLIST.showInfo('$a_val1[Tag]');"><span id="$a_val1[Tag]_name">$a_val1[Name]</span></a></td>
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
                            <td id="$a_val1[Tag]_isWalked"><button name="$a_val1[Tag]_isWalked" type="button" value="Gelaufen" onclick="g_MARKERLIST.markAsWalked('$a_val1[Tag]');">Gelaufen</button></td>
END;
    }
    else
    {
      echo "<td>&nbsp;</td>";
    }
  }
  echo "</tr>\n";
}

function writeScriptLine($a_val1)
{
  echo "g_WALKLIST['$a_val1[Tag]'] = {";
  echo "lat:$a_val1[Lat],";
  echo "lon:$a_val1[Lon],";
  echo "name:'$a_val1[Name]',";
  echo "length:$a_val1[Laenge],";
  echo "dur:$a_val1[Dauer],";
  echo "ch:'$a_val1[Charakter]',";

  if(isset($a_val1[Datum]) && ($a_val1[Datum] != "0000-00-00") )
  {
    echo "icon:g_WALKED_ICON,";
  }
  else
  {
    echo "icon:g_ICON, ";
  }
  echo "hasTrack:";
  echo (has_track($a_val1[Tag]))?'true':'false';
  echo "};\n";
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
google.load("maps", "2.S");
google.load("jquery", "1.4.2");
</script>

        <script type="text/javascript" src="/scripts/markermanager.js"></script>

<script type="text/javascript">

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
var g_OPTIONS           = new Array(); /**< Options passed from the selection page                  */
var g_WALKLIST          = new Array(); /**< All the walks as an Array                               */

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
function MarkEntry(a_id, a_marker, a_description, a_title)
{
  this.m_id       = a_id;
  this.m_marker   = a_marker;
  this.m_desc     = a_description;
  this.m_title    = a_title;
  this.m_hidden   = false;
  this.m_listener = null;
  this.m_track    = null;
  this.m_lc       = null;
  this.m_line     = null;
  this.length     = 0;
}

MarkEntry.STATE_NORMAL     = 0;
MarkEntry.STATE_HIGHLIGHT  = 1;

/** sets the image of the associated marker */
MarkEntry.prototype.setImage = function(a_image) { this.m_marker.setImage(a_image); }
/** returns the hidden state */
MarkEntry.prototype.isHidden = function() { return this.m_hidden; }
/** sets the hidden state to true */
MarkEntry.prototype.hide = function() { this.m_hidden = true; }
/** sets the hidden state to false */
MarkEntry.prototype.show = function() { this.m_hidden = false; }
/** returns the coordinates of the marker */
MarkEntry.prototype.getLatLng = function(){return this.m_marker.getLatLng();}
/** highlights the marker */
MarkEntry.prototype.highlight = function() { 
  if(!this.m_lc)
  {
    this.m_line = $('#'+this.m_id)[0];
    this.m_lc = [this.m_line.className, this.m_line.className + '_hl'];
  }
  this.m_line.className = this.m_lc[MarkEntry.STATE_HIGHLIGHT];
  this.setImage("images/wanderparkplatz_selected.png"); 
}

/** restores the normal icon of the marker */
MarkEntry.prototype.normal = function() { 
  this.m_line.className = this.m_lc[MarkEntry.STATE_NORMAL];
  if(!this.m_hidden) this.setImage(g_WALKLIST[this.m_id].icon.image); 
}
/** shows the info of the marker */
MarkEntry.prototype.showInfo = function()
{
  this.m_marker.openInfoWindowHtml(this.m_desc);
  var id = this.m_id;
  this.highlight();
  if(!this.m_listener)
  {
    this.m_listener = GEvent.addListener(this.m_marker, "infowindowclose", function(){infoWindowClosedCB(id);});
  }
}

MarkEntry.prototype.showTrack = function()
{
  if(this.m_track)
  {
    this.m_track.show();
  }
  else
  {
    var options = { url: "track.php", 
                    data:{gettrack: this.m_id},
                    context: this,
                    dataType: "text",
                    cache: false,
                    success: function(msg){
                      this.m_track = eval(msg);
                      g_MAP.addOverlay(this.m_track);
                      this.m_track.show();
                    }
                  };
    $.ajax(options);
  }
}

MarkEntry.prototype.hideTrack = function() { if(this.m_track){this.m_track.hide();} }

MarkEntry.prototype.markAsWalked = function ()
{
  if(confirm('Wanderung\n\n"' + this.m_title + '"\n\nals gelaufen markieren?'))
  {
    var options = { url: "editwalk.php", 
                    data:{id: this.m_id, walked:'true'},
                    context: this.m_id,
                    dataType: "text",
                    cache: false,
                    type: 'GET',
                    error: function(a_req, a_txt, a_err){alert("Failed with error " + a_err +" ("+a_txt+")");},
                    success: markAsWalkedCB };
    $.ajax(options);
  }
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
  this.manager    = new MarkerManager(a_map);
  this.bounds     = new google.maps.LatLngBounds();
  this.length     = 0;
}

/** 
 * Adds the given entry as new element to the MarkerList
 *
 * @param a_entry    MarkerEntry object to be added to the list
 */
MarkerList.prototype.push = function(a_entry)
{
  //this.entries.push(a_entry);
  this.entries[a_entry.m_id] = a_entry;
  this.bounds.extend(a_entry.m_marker.getLatLng());
  this.manager.addMarker(a_entry.m_marker, 0, 19);
  this.length++;
}

/**
 *  @brief   Adds a new mark to the map using the given information
 *
 *  @param    a_id        Tag uniquely identifying the walk
 *  @param    a_walk      The rest of the walk information
 *
 *  @return  nothing
 */
MarkerList.prototype.addWalk = function (a_id, a_walk)
{
  var pos     = new google.maps.LatLng(a_walk.lat, a_walk.lon);
  var options = {title: a_walk.name, bouncy: true, icon:a_walk.icon};
  var l_mark  = new google.maps.Marker(pos, options);
  var l_info  = createInfoString(a_walk.name, a_walk.length, a_walk.dur, a_walk.ch, a_id, a_walk.hasTrack);

  /* add the mark to our markerlist */
  var me = new MarkEntry(a_id, l_mark, l_info, a_walk.name);
  this.push(me);

  GEvent.addListener(l_mark, "click", function(){me.showInfo()});

  $("#"+a_id+"_dst").html("<a href=\"javascript:distCalc('" + a_id +"', '_dst')\">Berechnen</a>");
}

MarkerList.prototype.removeWalk = function (a_id)
{
  var me = this.search(a_id);
  me.hide();
  this.manager.removeMarker(me.m_marker);
  delete this.entries[a_id];
}

/** 
 * Returns the center point of the bounds of all the markers
 *
 * @return  The center of the map as google.maps.LatLng object
 */
MarkerList.prototype.getCenter = function() {return this.bounds.getCenter();}

/**
 * Accessor for the bounds object of the MarkerList
 *
 * @return  The bounds of the map as google.maps.LatLngBounds object
 */
MarkerList.prototype.getBounds = function(){return this.bounds;}

/** 
 * Returns the entry on the given index
 *
 * @param a_index   Index of the MarkerEntry to return
 *
 * @return the MarkerEntry stored at the given index
 */
MarkerList.prototype.get = function(a_index){return this.entries[a_index];}

/**
 * Searches for an entry with the given ID and returns it. 
 *
 * @param   a_id    ID of the entry to search for
 *
 * @return the MarkEntry identified by the given ID
 */

MarkerList.prototype.search = function(a_id)
{
  return this.entries[a_id];
}

/** 
 * Shows the marker with the given name 
 *
 * @param a_id     ID of the marker to show
 */
MarkerList.prototype.show = function(a_id)
{
  var me      = this.search(a_id);
  if(me && me.isHidden())
  {
    this.manager.addMarker(me.m_marker, 1);
    me.show();
  }
  $('#'+a_id+'_cb').attr('checked', true);
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
    me.hide();
    this.manager.removeMarker(me.m_marker);
  }
  $('#'+a_id+'_cb').attr('checked', false);
}

/** Displays all the markers of the list */
MarkerList.prototype.showAll = function()
{
  var id = null;
  var me = null;
  for (id in this.entries)
  {
    me = this.entries[id];
    if( (null == me) || (me.m_id == "highlight") || (me.isHidden() == false) )
    {
      continue; /* skip the gap and the highlight marker */
    }
    this.show(id);
  }
}

/** Hides all the markers of the list */
MarkerList.prototype.hideAll = function()
{
  for (id in this.entries)
  {
    me = this.entries[id];
    if( (null == me) || (me.m_id == "home") || (me.isHidden() != false) )
    {
      continue; /* skip the gap and the home marker */
    }
    this.hide(id);
  }
}

/** Highlights the given marker */
MarkerList.prototype.highlight = function(a_id)
{
  var me = this.search(a_id);
  if(me)
  {
    me.highlight();
  }
}

/** Reverts the icon of the given marker */
MarkerList.prototype.normal = function(a_id)
{
  var me = this.search(a_id);
  if(me)
  {
    me.normal();
  }
}

MarkerList.prototype.showTrack = function(a_id)
{
  var me = this.search(a_id);
  if(null == me)
  {
    alert("showTrack: no entry for tag " + a_id);
  }
  me.showTrack();
}

MarkerList.prototype.hideTrack = function(a_id)
{
  var me = this.search(a_id);
  if(null == me)
  {
    alert("hideTrack no entry for tag " + a_id);
  }
  me.hideTrack();
}

MarkerList.prototype.showInfo = function(a_id)
{
  var me = this.search(a_id);
  if(null == me)
  {
    alert("no entry for tag " + a_id);
  }
  this.highlight(a_id);
  //var line = document.getElementById(a_id);
  //line.className=line.className + "_hl";

  me.showInfo();
  link_update(a_id, null);
}

MarkerList.prototype.markAsWalked = function (a_id)
{
  var me = this.search(a_id);
  if(me)
  {
    me.markAsWalked();
  }
}
/* ------------------------------------------------------------------------------------------------ */
/* END Class MarkerList                                                                             */
/* ------------------------------------------------------------------------------------------------ */

/* ------------------------------------------------------------------------------------------------ */
/* BEGIN Class MyIcon                                                                               */
/* ------------------------------------------------------------------------------------------------ */
function MyIcon(a_foreground)
{
  var that = new google.maps.Icon();
  that.image                    = a_foreground; 
  that.iconSize                 = new google.maps.Size(21,32);
  that.iconAnchor               = new google.maps.Point(0,36);
  that.infoWindowAnchor         = new google.maps.Point(10.5,2);
  that.shadow                   = "images/wanderparkplatz_schatten.png";
  that.shadowSize               = new google.maps.Size(79,32);
  return that;
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
function createInfoString(a_text, a_len, a_dur, a_char, a_id, a_hasTrack)
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
  if(a_hasTrack)
  {
    l_info = l_info + " | <a href=\"javascript:g_MARKERLIST.showTrack('" + a_id + "')\">Zeige Track</a>";
  }

<?php
if(checkSession())
{
  echo 'l_info = l_info + " | <a href=\"javascript:g_MARKERLIST.markAsWalked(\'" + a_id +"\')\">Gelaufen</a>";';
}
?>
return l_info;
}

function infoWindowClosedCB(a_id)
{
  var line = document.getElementById(a_id);
  g_MARKERLIST.hideTrack(a_id);
  g_MARKERLIST.normal(a_id);
  link_update("", null);
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

/* callback being called when marking a walk as walked has completed on the server */
function markAsWalkedCB(a_data, a_text, a_req)
{
  var id = this;
  if(a_req.status!=200)
  {
    alert("Request reports an error:" + a_req.status + " (" + a_req.statusText + ")");
  }
  // first of all, the node must disappear from the map AND the table 
  g_MARKERLIST.removeWalk(id);
  if( (typeof(g_OPTIONS.showwalked) != 'undefined') && (g_OPTIONS.showwalked))
  {
    g_WALKLIST[id].icon = g_WALKED_ICON;
    g_MARKERLIST.addWalk(id, g_WALKLIST[id]);
    $('#' + id + '_isWalked').fadeOut();
  }
  else
  {
    // the table row can only be removed when the marker disappears.
    // otherwise, we'll need the row for further use...
    $('#' + id).fadeOut();
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
  if(confirm('Wanderung\n\n"' + g_MARKERLIST.search(a_id).m_title + '"\n\nals gelaufen markieren?'))
  {
    var options = { url: "editwalk.php", 
                    data:{id: a_id, walked:'true'},
                    context: a_id,
                    dataType: "text",
                    cache: false,
                    type: 'GET',
                    error: function(a_req, a_txt, a_err){alert("Failed with error " + a_err +" ("+a_txt+")");},
                    success: markAsWalkedCB };
    $.ajax(options);
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
  var l_query = "from: " + g_HOME + " to: " + me.getLatLng();
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

function link_update(a_highlight, a_zoomLevel)
{
    var l = document.getElementById("viewlink");
    var base = l.baseURI + "?";
    var query = l.search.substr(1).split("&");
    var idx;
    var arr = new Array();
    for(var i = 0; i < query.length; i++)
    {
      idx = query[i].split("=");
      arr[idx[0]] = idx[1];
    }

    if(null != a_highlight)
    {
      arr['highlight'] = a_highlight;
    }
    if(null != a_zoomLevel)
    {
      arr['zoomLevel'] = a_zoomLevel;
    }

    if(g_MAP.getCenter())
    {
      arr['mapcenter'] = g_MAP.getCenter().toUrlValue();
    }

    for(var key in arr)
    {
      if(   ("length" == key)
         || (""       == key) )
      {
        continue;
      }
      base = base + key + "=" + arr[key] + "&";
    }

    document.getElementById("viewlink").href = base;
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
  g_MAP.setMapType(G_NORMAL_MAP);
  g_MAP.setCenter(new GLatLng(50, -98), 3);

  GEvent.addListener(g_MAP, "moveend", function(){link_update(null, null);});
  GEvent.addListener(g_MAP, "zoomend", function(a_old,a_new)
  {
      link_update(null,a_new);

  });

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

$baseLink = "http://" . $_SERVER[HTTP_HOST] . $_SERVER[PHP_SELF] . "?";
foreach(array_keys($_REQUEST) as $thekey)
{
  if($thekey == "PHPSESSID")
  {
    continue;
  }
  $baseLink .= $thekey . "=" . $_REQUEST[$thekey] . "&";
}

?>
        <div>
            <div id="map" style="width: 800px; height: 600px"></div>
            <a href="javascript:g_MARKERLIST.hideAll();">Alle verbergen</a> 
            <a href="javascript:g_MARKERLIST.showAll();">Alle anzeigen</a> 
            <a id="viewlink" href="<?=$baseLink?>">Link</a>
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

array_walk($_REQUEST, writeOptionLine);

// put the request context into a javascript object to be able to access the information lateron
array_walk($elements, writeScriptLine);

echo <<<END
      for (id in g_WALKLIST)
      {
        g_MARKERLIST.addWalk(id, g_WALKLIST[id]);
      }

END;

/* evaluate the REQUEST. If we have a zoomLevel or can calc a center, use this one, otherwise, use the computable one! */
  if(isset($_REQUEST[mapcenter]))
  {
    echo "var center = new google.maps.LatLng($_REQUEST[mapcenter]);";
  }
  else
  {
    echo "var center = g_MARKERLIST.getCenter();";
  }

  if(isset($_REQUEST[zoomLevel]))
  {
    echo "var zoomLevel = Number($_REQUEST[zoomLevel]);";
  }
  else
  {
    echo "var zoomLevel = g_MAP.getBoundsZoomLevel(g_MARKERLIST.getBounds());";
  }

echo <<<END
      link_update(null, zoomLevel);
      g_MAP.setCenter(center, zoomLevel);
END;

  if(isset($_REQUEST[highlight]))
  {
    echo "g_MARKERLIST.showInfo('$_REQUEST[highlight]');";
  }
?>
    </script>
  </body>
</html>
