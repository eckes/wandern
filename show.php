<?php 
/* vim: set sw=2 ts=2: */
require_once('login/common.php');
require_once('css/colors.php');
require_once('common.php');

require_once('track.php');

require_once("constants.php");

$_mapkeys = array(
  'wandern.local'                       => 'ABQIAAAAmBVEol_t03fhiKgj23pnxRRPE6zGAdcAee_379GZpk22qefl8BR-CR6pD4WJqreTN3CM9IC4f5euew',
  'localhost'                           => 'ABQIAAAARoTP-aPC3X-J7A6v_c-RrRSliXv-vXMxLfXbWpmDAJtGYmmjPhRn1xN7Ce6w66WX49UMmCdujbpuzA',
  'wandern.erlmann.org'                 => 'ABQIAAAAmBVEol_t03fhiKgj23pnxRTkkbJCeO6ii5m69kVMcdeUjJKMhhQkkHpli0m8UI8YsURTyhmKudFiXwS',
  'wandern.web52.server111.dns-was.de'  => 'ABQIAAAAmBVEol_t03fhiKgj23pnxRQPqy_wHDqwkqj89uJgmv7DjbBGzRROelaVuziZuhdzigtLODSd-26xyw'
);


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

// writes the given value as JSON object
function writeScriptLine($a_val1)
{
  echo "'$a_val1[Tag]': {";
  echo "'lat':$a_val1[Lat],";
  echo "'lng':$a_val1[Lon],";
  echo "'name':'$a_val1[Name]',";
  echo "'length':$a_val1[Laenge],";
  echo "'dur':$a_val1[Dauer],";
  echo "'ch':'$a_val1[Charakter]',";

  if(isset($a_val1[Datum]) && ($a_val1[Datum] != "0000-00-00") )
  {
    echo "'isWalked':true,";
  }
  else
  {
    echo "'isWalked':false,";
  }
  echo "'hasTrack':";
  echo (has_track($a_val1[Tag]))?'true':'false';
  echo "},\n";
}

include("db_xml.php");

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <title>Search Results</title>
        <META http-equiv="content-type" content="text/html; charset=UTF-8">
        <link rel="stylesheet" type="text/css" href="css/style.php">
<?php
// get correct key to use
  echo '<script type="text/javascript" src="http://www.google.com/jsapi?key=';
  echo $_mapkeys[$_SERVER[HTTP_HOST]];
  echo '"></script>';
?>
        <script type="text/javascript" src="js/gs_sortable.js"></script>
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
var g_OPTIONS           = new Array(); /**< Options passed from the selection page                  */

/* ------------------------------------------------------------------------------------------------ */
/* BEGIN configuration of the table-sorting-and-striping script                                     */
/* ------------------------------------------------------------------------------------------------ */
var TSort_Data = new Array ('walks', 's', 's', 'f', 'f', 's', 's' <?php if($_SESSION['validUser'] == true) { echo ", ''"; } ?>);
var TSort_Classes = new Array ('table_odd', 'table_even');
var TSort_Initial = 0;
tsRegister();
/* ------------------------------------------------------------------------------------------------ */
/* END configuration of the table-sorting-and-striping script                                       */
/* ------------------------------------------------------------------------------------------------ */

/* ------------------------------------------------------------------------------------------------ */
/* BEGIN Class MarkEntry                                                                            */
/* ------------------------------------------------------------------------------------------------ */
function MarkEntry(a_marker, a_walk)
{
  var _marker     = a_marker;
  var _hidden     = false;
  var _walk       = a_walk;
  this.m_id       = _walk.id;
  this.m_title    = _walk.name;
  this.m_listener = GEvent.addListener(_marker, "infowindowclose", function(){infoWindowClosedCB(_walk.id);});
  this.m_track    = null;
  if(this.m_id != "home")
  {
    var _line     = $('#'+this.m_id)[0];
    var _lc       = [_line.className, _line.className + '_hl'];
    var _icons    = [_marker.getIcon().image, "images/wanderparkplatz_selected.png"];
    var _desc     = null
  }

  /** PRIVILEGED: sets the state of the marker, has access to private functions */
  this.setState = function(a_state)
  {
    _line.className = _lc[a_state];
    _setImage(_icons[a_state]);
  }

  /** PRIVILEGED: access to the _hidden variable */
  this.isHidden = function(){return _hidden;}
  this.show     = function(){_hidden = false;}
  this.hide     = function(){_hidden = true;}

  /** PRIVILEGED: access to the _marker variable */
  this.getLatLng = function(){return _marker.getLatLng();}
  this.getMarker = function(){return _marker;}

  /** shows the info of the marker */
  this.showInfo = function()
  {
    if(!_desc){_desc = this.makeInfo(_walk);}
    _marker.openInfoWindowHtml(_desc);
    this.highlight();
  }

  /** update the icon for a certain state */
  this.setIcon  = function(a_state, a_image) { _icons[a_state] = a_image; }

  /** PRIVATE: sets the image for this marker */
  var _setImage = function(a_image) { !_hidden && _marker.setImage(a_image); }
}

// STATIC VARIABLES
MarkEntry.STATE_NORMAL     = 0;
MarkEntry.STATE_HIGHLIGHT  = 1;

MarkEntry.prototype.makeInfo = function(a_walk) {
    var l_image = "images/" + a_walk.id.toLowerCase().substr(0, a_walk.id.length-3) + "_small.png";
    var l_info  = "<img src='" + l_image + "' alt='" + a_walk.id + "' title='" + a_walk.id + "' style='float:left;padding-right:4px;'>";
    l_info = l_info + "<b>" + a_walk.name + "</b><br>" + a_walk.length + "km | " + a_walk.dur + "h<br>";
    if(a_walk.ch)
    {
      l_info = l_info + a_walk.ch + "<br>";
    }
    l_info = l_info + "<span id='" + a_walk.id +"_infodst'><a href=\"javascript:distCalc('" + a_walk.id + "', '_infodst')\">Entfernung</a></span> | ";
    l_info = l_info + "<a href=\"javascript:g_MARKERLIST.hide(\'" + a_walk.id +"\')\">Verbergen</a>";
    if(a_walk.hasTrack)
    {
      l_info = l_info + " | <a href=\"javascript:g_MARKERLIST.showTrack('" + a_walk.id + "')\">Zeige Track</a>";
    }
<?php
if(checkSession())
{
  echo 'l_info = l_info + "<p style=\'display:inline;\' id=\'walkedlink_" + a_walk.id   + "\'> | <a href=\"javascript:g_MARKERLIST.markAsWalked(\'" + a_walk.id +"\')\">Gelaufen</a></p>";';
}
?>
return l_info;
}


// set the marker state highlighted/normal
MarkEntry.prototype.highlight = function() {this.setState(MarkEntry.STATE_HIGHLIGHT);}
MarkEntry.prototype.normal = function() {this.setState(MarkEntry.STATE_NORMAL);}

// Display the track for the walk
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

// Hide the track
MarkEntry.prototype.hideTrack = function() {
  this.m_track && this.m_track.hide();
  this.normal();
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
 * @param  a_options Options to be set for the MarkerList. The list needs:
 *                   - map (g_MAP)
 *                   - normalImage
 *                   - walkedImage
 *                   - highlightImage
 */
function MarkerList(a_options)
{
  this.entries    = new Array();
  this.manager    = new MarkerManager(a_options.map);
  this.bounds     = new google.maps.LatLngBounds();

  var _opts = a_options;
  this.normalIcon = new MyIcon(_opts.normalImage);
  this.walkedIcon = new MyIcon(_opts.walkedImage);
}

// Searches for an entry with the given ID and returns it. 
MarkerList.prototype.search = function(a_id) { return this.entries[a_id]; }
// Returns the center point of the bounds of all the markers
MarkerList.prototype.getCenter = function() {return this.bounds.getCenter();}
// Accessor for the bounds object of the MarkerList
MarkerList.prototype.getBounds = function(){return this.bounds;}
// Checks if the given marker is hideable
MarkerList.prototype.hideable = function(a_me) { return ((null != a_me) && ('home' != a_me.m_id) && (!a_me.isHidden())); }
// Checks if the given marker is showable
MarkerList.prototype.showable = function(a_me) { return ((null != a_me) && ('highlight' != a_me.m_id) && (a_me.isHidden())); }

/** 
 * Adds the given entry as new element to the MarkerList
 *
 * @param a_entry    MarkerEntry object to be added to the list
 */
MarkerList.prototype.push = function(a_entry)
{
  this.entries[a_entry.m_id] = a_entry;
  this.bounds.extend(a_entry.getLatLng());
  this.manager.addMarker(a_entry.getMarker(), 0, 19);
}

/**
 *  @brief   Adds a new mark to the map using the given information
 *
 *  @param    a_walk      The walk including its id
 *
 *  @return  nothing
 */
MarkerList.prototype.addWalk = function (a_walk)
{
  var pos     = new google.maps.LatLng(a_walk.lat, a_walk.lng);
  var icon = (a_walk.isWalked)?this.walkedIcon:this.normalIcon;
  var options = {title: a_walk.name, bouncy: true, icon:icon};
  var l_mark  = new google.maps.Marker(pos, options);

  /* add the mark to our markerlist */
  var me = new MarkEntry(l_mark, a_walk);
  this.push(me);

  GEvent.addListener(l_mark, "click", function(){me.showInfo()});

  $("#"+a_walk.id+"_dst").html("<a href=\"javascript:distCalc('" + a_walk.id +"', '_dst')\">Berechnen</a>");
}

MarkerList.prototype.removeWalk = function (a_id)
{
  var me = this.search(a_id);
  me.hide();
  this.manager.removeMarker(me.getMarker());
  delete this.entries[a_id];
}

/** 
 * Shows the marker with the given name 
 *
 * @param a_id     ID of the marker to show
 */
MarkerList.prototype.show = function(a_id)
{
  var me      = this.search(a_id);
  if(this.showable(me))
  {
    this.manager.addMarker(me.getMarker(), 1);
    me.show();
  }
  $('#'+a_id+'_cb').attr('checked', true);
}

// Hides the marker with the given name 
MarkerList.prototype.hide = function(a_id)
{
  var me      = this.search(a_id);
  if(this.hideable(me))
  {
    me.hide();
    this.manager.removeMarker(me.getMarker());
  }
  $('#'+a_id+'_cb').attr('checked', false);
}

// Displays all the markers of the list 
MarkerList.prototype.showAll = function() {
  var id = null;
  for (id in this.entries) {
    this.show(id);
  }
}

// Hides all the markers of the list 
MarkerList.prototype.hideAll = function() {
  var id = null;
  for (id in this.entries) {
    this.hide(id);
  }
}

/** Highlights the given marker */
MarkerList.prototype.highlight = function(a_id) {
  var me = this.search(a_id);
  me && me.highlight();
}

MarkerList.prototype.normal = function(a_id) {
  var me = this.search(a_id);
  me && me.normal();
}

MarkerList.prototype.setIcon = function (a_id, a_state, a_icon)
{
  var me = this.search(a_id);
  me && me.setIcon(a_state, a_icon);
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

  me.highlight();
  me.showInfo();
  link_update(a_id, null);
}

MarkerList.prototype.markAsWalked = function (a_id)
{
  var me = this.search(a_id);
  if(me)
  {
    if(confirm('Wanderung\n\n"' + me.m_title + '"\n\nals gelaufen markieren?'))
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
function infoWindowClosedCB(a_id)
{
  g_MARKERLIST.normal(a_id);
  g_MARKERLIST.hideTrack(a_id);
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
  var me = new MarkEntry(mark, {id:"home", name:"Daheim"});
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
    if(false == e.checked) { g_MARKERLIST.hide(a_id); }
    else { g_MARKERLIST.show(a_id); }
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
  if( (typeof(g_OPTIONS.showwalked) != 'undefined') && (g_OPTIONS.showwalked))
  {
    g_MARKERLIST.setIcon(id, MarkEntry.STATE_NORMAL, g_MARKERLIST.walkedIcon.image);
    $('#' + id + '_isWalked').fadeOut();
    $('#walkedlink_' + id ).fadeOut();
  }
  else
  {
    g_MARKERLIST.removeWalk(id);
    $('#' + id).fadeOut();
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
      if(   ("length" == key) || ("" == key) )
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
  g_HOME       = new google.maps.LatLng(<?=$_SESSION['settings']['lat']?>, <?=$_SESSION['settings']['lon']?>);

  g_MAP = new google.maps.Map2(document.getElementById("map"));
  g_MAP.setCenter(g_HOME, 0, G_NORMAL_MAP);
  g_MAP.addControl(new google.maps.MapTypeControl());
  g_MAP.addControl(new google.maps.SmallZoomControl());
  g_MAP.addMapType(G_PHYSICAL_MAP);
  GEvent.addListener(g_MAP, "moveend", function(){link_update(null, null);});
  GEvent.addListener(g_MAP, "zoomend", function(a_old,a_new) { link_update(null,a_new);});

  var options = {map: g_MAP, 
                 normalImage:"images/wanderparkplatz.png",
                 walkedImage:"images/wanderparkplatz_hell.png",
                 shadowImage:"images/wanderparkplatz_schatten.png",
                 highlightImage:"images/wanderparkplatz_selected.png"};
  g_MARKERLIST    = new MarkerList(options);

  g_DIRECTIONS    = new google.maps.Directions();

  GEvent.addListener(g_DIRECTIONS, "load", dirLoadedCB);
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
echo "var walklist = {\n";
array_walk($elements, writeScriptLine);
echo "};\n";

echo <<<END
$.each(walklist, function(key, value){value.id = key; g_MARKERLIST.addWalk(value);});
delete walklist;

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
