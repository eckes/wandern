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
  /*
  $retval = get_track($a_val1[Tag]);
  print_r($retval);
   */
  
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
  if(has_track($a_val1[Tag]))
  {
    echo ', true';
  }
  else
  {
    echo ', false';
  }
  echo ");\n";
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
google.load("jquery", "1.4.2");

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
  this.m_hidden   = false;
  this.m_track    = null;
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
    me.m_hidden = false;
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
    me.m_hidden = true;
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
  for (var i = 0; i < this.length; i++)
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
  for (var i = 0; i < this.length; i++)
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
    l_info = l_info + " | <a href=\"javascript:showTrack('" + a_id + "')\">Zeige Track</a>";
  }

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
  hideTrack(g_HIGHLIGHT._id);
  g_HIGHLIGHT._id = "";

  if(line)
  {
    line.className=line.className.substr(0, line.className.length-3);
  }
  g_HIGHLIGHT.hide();

  link_update("", null);
}

function showTrack(a_id)
{
  var me = g_MARKERLIST.search(a_id);
  if(null == me)
  {
    alert("no entry for tag " + a_id);
  }
  if(me.m_track)
  {
    me.m_track.show();
  }
  else
  {
    if(a_id=='FUW2_06')
    {
      evalstr = "new google.maps.Polyline([new google.maps.LatLng(49.4962466666667, 10.80186), new google.maps.LatLng(49.4964216666667, 10.8028566666667), new google.maps.LatLng(49.4964033333333, 10.803705), new google.maps.LatLng(49.4964216666667, 10.8045866666667), new google.maps.LatLng(49.4963383333333, 10.8055383333333), new google.maps.LatLng(49.49604, 10.80629), new google.maps.LatLng(49.49597499999999, 10.8064366666667), new google.maps.LatLng(49.49585, 10.8064883333333), new google.maps.LatLng(49.495495, 10.8064833333333), new google.maps.LatLng(49.4953266666667, 10.8065516666667), new google.maps.LatLng(49.49592666666671, 10.8065466666667), new google.maps.LatLng(49.496105, 10.8071516666667), new google.maps.LatLng(49.4956783333333, 10.8076833333333), new google.maps.LatLng(49.4951633333333, 10.808325), new google.maps.LatLng(49.4946366666667, 10.8090083333333), new google.maps.LatLng(49.4941833333333, 10.8096466666667), new google.maps.LatLng(49.4937666666667, 10.810215), new google.maps.LatLng(49.49321333333329, 10.8103616666667), new google.maps.LatLng(49.4926483333333, 10.8103283333333), new google.maps.LatLng(49.49216500000001, 10.81002), new google.maps.LatLng(49.4915966666667, 10.809985), new google.maps.LatLng(49.49113, 10.8103316666667), new google.maps.LatLng(49.49049, 10.81066), new google.maps.LatLng(49.49000499999999, 10.8110166666667), new google.maps.LatLng(49.489605, 10.8117116666667), new google.maps.LatLng(49.4893033333333, 10.8115333333333), new google.maps.LatLng(49.4887733333333, 10.81132), new google.maps.LatLng(49.488285, 10.8109183333333), new google.maps.LatLng(49.4878733333333, 10.8103033333333), new google.maps.LatLng(49.48748, 10.8097216666667), new google.maps.LatLng(49.48713333333329, 10.80897), new google.maps.LatLng(49.4867516666667, 10.8082766666667), new google.maps.LatLng(49.48629, 10.8077933333333), new google.maps.LatLng(49.4860416666667, 10.80712), new google.maps.LatLng(49.4858133333333, 10.8075033333333), new google.maps.LatLng(49.4854133333333, 10.808265), new google.maps.LatLng(49.4852183333333, 10.8080833333333), new google.maps.LatLng(49.48498833333331, 10.8071616666667), new google.maps.LatLng(49.4846883333333, 10.8063283333333), new google.maps.LatLng(49.48434, 10.8060283333333), new google.maps.LatLng(49.4837533333333, 10.806335), new google.maps.LatLng(49.48330000000001, 10.8065716666667), new google.maps.LatLng(49.4827616666667, 10.80698), new google.maps.LatLng(49.48228, 10.807395), new google.maps.LatLng(49.4817766666667, 10.8080016666667), new google.maps.LatLng(49.48146500000001, 10.8087183333333), new google.maps.LatLng(49.4813883333333, 10.80936), new google.maps.LatLng(49.4812233333333, 10.810265), new google.maps.LatLng(49.481125, 10.8112416666667), new google.maps.LatLng(49.4811133333333, 10.8122933333333), new google.maps.LatLng(49.481075, 10.8132966666667), new google.maps.LatLng(49.481015, 10.8142933333333), new google.maps.LatLng(49.480725, 10.8151483333333), new google.maps.LatLng(49.4803316666667, 10.8160016666667), new google.maps.LatLng(49.4797666666667, 10.8161883333333), new google.maps.LatLng(49.4791483333333, 10.816025), new google.maps.LatLng(49.47870333333331, 10.8158766666667), new google.maps.LatLng(49.4781066666667, 10.8156483333333), new google.maps.LatLng(49.4776883333333, 10.8156866666667), new google.maps.LatLng(49.4776883333333, 10.8156766666667), new google.maps.LatLng(49.4776466666667, 10.815655), new google.maps.LatLng(49.477585, 10.8156983333333), new google.maps.LatLng(49.47735999999999, 10.8158233333333), new google.maps.LatLng(49.4766, 10.8158716666667), new google.maps.LatLng(49.47586, 10.8155866666667), new google.maps.LatLng(49.4752, 10.815265), new google.maps.LatLng(49.4748416666667, 10.8154783333333), new google.maps.LatLng(49.4748083333333, 10.81553), new google.maps.LatLng(49.4746816666667, 10.81626), new google.maps.LatLng(49.47459, 10.8169333333333), new google.maps.LatLng(49.47438, 10.8178633333333), new google.maps.LatLng(49.4741283333333, 10.818775), new google.maps.LatLng(49.4739266666667, 10.8197333333333), new google.maps.LatLng(49.473755, 10.8206733333333), new google.maps.LatLng(49.47358, 10.8216366666667), new google.maps.LatLng(49.4734733333333, 10.822265), new google.maps.LatLng(49.4733816666667, 10.822635), new google.maps.LatLng(49.4732133333333, 10.8235133333333), new google.maps.LatLng(49.4731183333333, 10.8243866666667), new google.maps.LatLng(49.4729683333333, 10.825205), new google.maps.LatLng(49.4723733333333, 10.82535), new google.maps.LatLng(49.47191666666669, 10.825475), new google.maps.LatLng(49.4718283333333, 10.8261516666667), new google.maps.LatLng(49.4718516666667, 10.82704), new google.maps.LatLng(49.4715766666667, 10.8276066666667), new google.maps.LatLng(49.471245, 10.8283016666667), new google.maps.LatLng(49.47084, 10.82872), new google.maps.LatLng(49.47051999999999, 10.8292433333333), new google.maps.LatLng(49.4701883333333, 10.83001), new google.maps.LatLng(49.4698633333333, 10.8308133333333), new google.maps.LatLng(49.46936833333331, 10.83125), new google.maps.LatLng(49.4687383333333, 10.8313033333333), new google.maps.LatLng(49.46828, 10.8319333333333), new google.maps.LatLng(49.4681816666667, 10.83203), new google.maps.LatLng(49.46819, 10.8320283333333), new google.maps.LatLng(49.4680866666667, 10.8321416666667), new google.maps.LatLng(49.4680716666667, 10.8321166666667), new google.maps.LatLng(49.4681133333333, 10.8319916666667), new google.maps.LatLng(49.468155, 10.8320966666667), new google.maps.LatLng(49.46825333333331, 10.8330266666667), new google.maps.LatLng(49.4683533333333, 10.83394), new google.maps.LatLng(49.4684983333333, 10.8347866666667), new google.maps.LatLng(49.4687533333333, 10.8355483333333), new google.maps.LatLng(49.46898333333329, 10.8363716666667), new google.maps.LatLng(49.46911166666669, 10.8372966666667), new google.maps.LatLng(49.469185, 10.8382733333333), new google.maps.LatLng(49.46924666666671, 10.839265), new google.maps.LatLng(49.46931, 10.8402733333333), new google.maps.LatLng(49.46942833333329, 10.841225), new google.maps.LatLng(49.469665, 10.84209), new google.maps.LatLng(49.469665, 10.842215), new google.maps.LatLng(49.4697, 10.84224), new google.maps.LatLng(49.469745, 10.84239), new google.maps.LatLng(49.47001999999999, 10.8432933333333), new google.maps.LatLng(49.4701616666667, 10.8436816666667), new google.maps.LatLng(49.4702566666667, 10.8436883333333), new google.maps.LatLng(49.47018, 10.8442616666667), new google.maps.LatLng(49.4698833333333, 10.8448216666667), new google.maps.LatLng(49.4698516666667, 10.8451166666667), new google.maps.LatLng(49.4698633333333, 10.8448433333333), new google.maps.LatLng(49.4702833333333, 10.8440916666667), new google.maps.LatLng(49.47061166666669, 10.84385), new google.maps.LatLng(49.47065, 10.8446283333333), new google.maps.LatLng(49.4708483333333, 10.845595), new google.maps.LatLng(49.471065, 10.8465066666667), new google.maps.LatLng(49.4712066666667, 10.8474316666667), new google.maps.LatLng(49.4713816666667, 10.84841), new google.maps.LatLng(49.4715916666667, 10.84932), new google.maps.LatLng(49.4718666666667, 10.8502166666667), new google.maps.LatLng(49.4721216666667, 10.851105), new google.maps.LatLng(49.4723816666667, 10.8519933333333), new google.maps.LatLng(49.4725066666667, 10.8523766666667), new google.maps.LatLng(49.47274, 10.85234), new google.maps.LatLng(49.47324, 10.852), new google.maps.LatLng(49.473785, 10.852155), new google.maps.LatLng(49.4743083333333, 10.852295), new google.maps.LatLng(49.4749216666667, 10.852165), new google.maps.LatLng(49.4755483333333, 10.8520183333333), new google.maps.LatLng(49.4761316666667, 10.8517033333333), new google.maps.LatLng(49.4767233333333, 10.8513883333333), new google.maps.LatLng(49.4773566666667, 10.85137), new google.maps.LatLng(49.47795166666669, 10.8512383333333), new google.maps.LatLng(49.47855000000001, 10.8510883333333), new google.maps.LatLng(49.4792133333333, 10.850975), new google.maps.LatLng(49.47984, 10.8506866666667), new google.maps.LatLng(49.4804183333333, 10.85048), new google.maps.LatLng(49.48099499999999, 10.8503916666667), new google.maps.LatLng(49.4816016666667, 10.8504683333333), new google.maps.LatLng(49.48217833333331, 10.85065), new google.maps.LatLng(49.4827533333333, 10.8507483333333), new google.maps.LatLng(49.48335333333329, 10.850775), new google.maps.LatLng(49.48389, 10.850715), new google.maps.LatLng(49.4844783333333, 10.8506433333333), new google.maps.LatLng(49.48499999999999, 10.8504266666667), new google.maps.LatLng(49.48552000000001, 10.849995), new google.maps.LatLng(49.4861333333333, 10.8501316666667), new google.maps.LatLng(49.48674, 10.85039), new google.maps.LatLng(49.4873383333333, 10.8502916666667), new google.maps.LatLng(49.4879066666667, 10.8498566666667), new google.maps.LatLng(49.48851333333331, 10.8495516666667), new google.maps.LatLng(49.4891316666667, 10.8497033333333), new google.maps.LatLng(49.489685, 10.8500216666667), new google.maps.LatLng(49.49025, 10.8504416666667), new google.maps.LatLng(49.4903033333333, 10.8513133333333), new google.maps.LatLng(49.490185, 10.8522283333333), new google.maps.LatLng(49.49007, 10.85317), new google.maps.LatLng(49.4899566666667, 10.8541633333333), new google.maps.LatLng(49.49034833333331, 10.8548416666667), new google.maps.LatLng(49.4908683333333, 10.855405), new google.maps.LatLng(49.49141999999999, 10.85593), new google.maps.LatLng(49.49197, 10.8562866666667), new google.maps.LatLng(49.4919733333333, 10.85627), new google.maps.LatLng(49.4922783333333, 10.8564166666667), new google.maps.LatLng(49.49287833333329, 10.8563266666667), new google.maps.LatLng(49.4934533333333, 10.8563083333333), new google.maps.LatLng(49.4941183333333, 10.8563566666667), new google.maps.LatLng(49.49476166666671, 10.8561816666667), new google.maps.LatLng(49.49533000000001, 10.8560366666667), new google.maps.LatLng(49.49550666666671, 10.8565466666667), new google.maps.LatLng(49.49601, 10.85683), new google.maps.LatLng(49.4964716666667, 10.857265), new google.maps.LatLng(49.49707, 10.8577316666667), new google.maps.LatLng(49.49754, 10.8583866666667), new google.maps.LatLng(49.497925, 10.859135), new google.maps.LatLng(49.49839000000001, 10.8596), new google.maps.LatLng(49.49878, 10.8600633333333), new google.maps.LatLng(49.49879, 10.8600966666667), new google.maps.LatLng(49.49879833333331, 10.86011), new google.maps.LatLng(49.49884, 10.860105), new google.maps.LatLng(49.4989616666667, 10.8602133333333), new google.maps.LatLng(49.49941333333329, 10.8609183333333), new google.maps.LatLng(49.4998433333333, 10.861645), new google.maps.LatLng(49.5002516666667, 10.862355), new google.maps.LatLng(49.50071, 10.8629483333333), new google.maps.LatLng(49.5013083333333, 10.862965), new google.maps.LatLng(49.5019, 10.862845), new google.maps.LatLng(49.502475, 10.8626016666667), new google.maps.LatLng(49.5030433333333, 10.862315), new google.maps.LatLng(49.50356666666671, 10.86246), new google.maps.LatLng(49.5040933333333, 10.8624666666667), new google.maps.LatLng(49.5045016666667, 10.861655), new google.maps.LatLng(49.5048983333333, 10.8609416666667), new google.maps.LatLng(49.5053016666667, 10.8603916666667), new google.maps.LatLng(49.5058933333333, 10.8604416666667), new google.maps.LatLng(49.50627833333329, 10.8609383333333), new google.maps.LatLng(49.5064666666667, 10.8608233333333), new google.maps.LatLng(49.5069233333333, 10.8602083333333), new google.maps.LatLng(49.507385, 10.859575), new google.maps.LatLng(49.5078766666667, 10.8590083333333), new google.maps.LatLng(49.5084233333333, 10.8585716666667), new google.maps.LatLng(49.5090183333333, 10.8583433333333), new google.maps.LatLng(49.50950999999999, 10.85786), new google.maps.LatLng(49.50949166666669, 10.8570083333333), new google.maps.LatLng(49.5094033333333, 10.857025), new google.maps.LatLng(49.5094416666667, 10.8560516666667), new google.maps.LatLng(49.5097883333333, 10.85574), new google.maps.LatLng(49.5103066666667, 10.855325), new google.maps.LatLng(49.5104033333333, 10.855325), new google.maps.LatLng(49.51068166666671, 10.8546283333333), new google.maps.LatLng(49.510875, 10.853805), new google.maps.LatLng(49.5110516666667, 10.852955), new google.maps.LatLng(49.51109, 10.8520616666667), new google.maps.LatLng(49.51112000000001, 10.8511716666667), new google.maps.LatLng(49.5111966666667, 10.8503883333333), new google.maps.LatLng(49.5112716666667, 10.8494866666667), new google.maps.LatLng(49.51134499999999, 10.84855), new google.maps.LatLng(49.51136333333329, 10.84848), new google.maps.LatLng(49.51136333333329, 10.8484766666667), new google.maps.LatLng(49.51133, 10.84845), new google.maps.LatLng(49.51134499999999, 10.8484816666667), new google.maps.LatLng(49.5113533333333, 10.8484633333333), new google.maps.LatLng(49.51134499999999, 10.84801), new google.maps.LatLng(49.5113416666667, 10.8473083333333), new google.maps.LatLng(49.51141, 10.846385), new google.maps.LatLng(49.5113033333333, 10.8453983333333), new google.maps.LatLng(49.511235, 10.8444133333333), new google.maps.LatLng(49.51118166666671, 10.843465), new google.maps.LatLng(49.5112183333333, 10.8425133333333), new google.maps.LatLng(49.5112383333333, 10.8416133333333), new google.maps.LatLng(49.5112883333333, 10.840675), new google.maps.LatLng(49.5112883333333, 10.83985), new google.maps.LatLng(49.51141, 10.83892), new google.maps.LatLng(49.5115283333333, 10.83801), new google.maps.LatLng(49.51166166666669, 10.837065), new google.maps.LatLng(49.51186, 10.8361633333333), new google.maps.LatLng(49.5120666666667, 10.835315), new google.maps.LatLng(49.51227166666669, 10.8344166666667), new google.maps.LatLng(49.51241, 10.8335716666667), new google.maps.LatLng(49.512585, 10.832685), new google.maps.LatLng(49.5128066666667, 10.8318666666667), new google.maps.LatLng(49.5130083333333, 10.8309766666667), new google.maps.LatLng(49.51302333333331, 10.8301283333333), new google.maps.LatLng(49.513115, 10.8291516666667), new google.maps.LatLng(49.5131116666667, 10.8282416666667), new google.maps.LatLng(49.513195, 10.8273566666667), new google.maps.LatLng(49.5131833333333, 10.8271116666667), new google.maps.LatLng(49.513275, 10.82665), new google.maps.LatLng(49.5133133333333, 10.8258266666667), new google.maps.LatLng(49.5131383333333, 10.824955), new google.maps.LatLng(49.51287000000001, 10.824145), new google.maps.LatLng(49.5130616666667, 10.8231233333333), new google.maps.LatLng(49.513165, 10.82217), new google.maps.LatLng(49.5132516666667, 10.8212183333333), new google.maps.LatLng(49.5132183333333, 10.8202633333333), new google.maps.LatLng(49.5131116666667, 10.8193), new google.maps.LatLng(49.5128933333333, 10.8183433333333), new google.maps.LatLng(49.512665, 10.81747), new google.maps.LatLng(49.512165, 10.8169), new google.maps.LatLng(49.5116533333333, 10.81626), new google.maps.LatLng(49.511105, 10.81579), new google.maps.LatLng(49.510495, 10.8156816666667), new google.maps.LatLng(49.5098833333333, 10.8155966666667), new google.maps.LatLng(49.5092666666667, 10.8155016666667), new google.maps.LatLng(49.50887666666669, 10.8148383333333), new google.maps.LatLng(49.50880833333329, 10.8139016666667), new google.maps.LatLng(49.5088116666667, 10.8129133333333), new google.maps.LatLng(49.508675, 10.811985), new google.maps.LatLng(49.5084566666667, 10.811135), new google.maps.LatLng(49.5081366666667, 10.8103633333333), new google.maps.LatLng(49.5077633333333, 10.80961), new google.maps.LatLng(49.50719, 10.8096483333333), new google.maps.LatLng(49.50663, 10.80993), new google.maps.LatLng(49.50608, 10.8101766666667), new google.maps.LatLng(49.5055, 10.810355), new google.maps.LatLng(49.50491333333331, 10.810535), new google.maps.LatLng(49.50433333333329, 10.8107016666667), new google.maps.LatLng(49.5037266666667, 10.81077), new google.maps.LatLng(49.50313166666669, 10.81067), new google.maps.LatLng(49.502495, 10.8105083333333), new google.maps.LatLng(49.5018766666667, 10.8103716666667), new google.maps.LatLng(49.5013, 10.8100983333333), new google.maps.LatLng(49.5007166666667, 10.80973), new google.maps.LatLng(49.500325, 10.80916), new google.maps.LatLng(49.50003, 10.8083233333333), new google.maps.LatLng(49.49976, 10.807535), new google.maps.LatLng(49.4993016666667, 10.8069733333333), new google.maps.LatLng(49.49886000000001, 10.806345), new google.maps.LatLng(49.49838333333329, 10.8058116666667), new google.maps.LatLng(49.49781, 10.8056733333333), new google.maps.LatLng(49.49729166666669, 10.805825), new google.maps.LatLng(49.49701, 10.80507), new google.maps.LatLng(49.49667, 10.804385), new google.maps.LatLng(49.4964783333333, 10.8035666666667), new google.maps.LatLng(49.49639166666671, 10.8027016666667), new google.maps.LatLng(49.4962766666667, 10.8018666666667), new google.maps.LatLng(49.49637666666669, 10.8012033333333), new google.maps.LatLng(49.4964483333333, 10.8011616666667)]);"
      me.m_track = eval(evalstr);
      g_MAP.addOverlay(me.m_track);
    }
  }
}

function hideTrack(a_id)
{
  var me = g_MARKERLIST.search(a_id);
  if(null == me)
  {
    alert("no entry for tag " + a_id);
    return;
  }
  if(me.m_track)
  {
    me.m_track.hide();
  }
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

  link_update(a_id, null);
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
function addMark(a_long, a_lat, a_text, a_id, a_icon, a_len, a_dur, a_char, a_hasTrack)
{
  var pos     = new google.maps.LatLng(a_lat, a_long);
  var options = {title: a_text, bouncy: true, icon:a_icon};
  var l_mark  = new google.maps.Marker(pos, options);
  var l_info  = createInfoString(a_text, a_len, a_dur, a_char, a_id, a_hasTrack);

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

/*--- createHighlight() -------------------------------------------------------- createHighlight() ---*/
/**
 *  @brief   Creates the highlighting mark that gets moved to the currently selected mark
 */
/*--- createHighlight() -------------------------------------------------------- createHighlight() ---*/
function createHighlight()
{
  var hiIcon  = new MyIcon("images/wanderparkplatz_selected.png");
  var options = {icon:hiIcon, zIndexProcess:function(a){return 0;}};
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

  GEvent.addListener(g_MAP, "moveend", function(){link_update(null, null);});
  GEvent.addListener(g_MAP, "zoomend", function(a_old,a_new){link_update(null,a_new);});

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

array_walk($elements, writeScriptLine);

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
    echo "showInfo('$_REQUEST[highlight]');";
  }
?>
    </script>
  </body>
</html>
