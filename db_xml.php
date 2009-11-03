<?php 
require_once('../login/common.php');
require_once("constants.php");
require_once("common.php");
/** XML Tag constants */
define("XMLTAG_TAG",        "Tag");
define("XMLTAG_NAME",       "Name");
define("XMLTAG_LENGTH",     "Laenge");
define("XMLTAG_DUR",        "Dauer");
define("XMLTAG_CHAR",       "Charakter");
define("XMLTAG_LAT",        "Lat");
define("XMLTAG_LON",        "Lon");
define("XMLTAG_ONEWAY",     "Streckenwanderung");
define("XMLTAG_WALK",       "walk");

if (!function_exists('fnmatch')) {
  function fnmatch($pattern, $string) {
    return @preg_match('/^' . strtr(addcslashes($pattern, '\\.+^$(){}=!<>|'), array('*' => '.*', '?' => '.?')) . '$/i', $string);
  }
}


function getVal($a_walk, $a_name)
{
  return $a_walk->getElementsByTagName($a_name)->item(0)->nodeValue;
}

function match($a_walk)
{
  $len    = getVal($a_walk, XMLTAG_LENGTH);
  $char   = getVal($a_walk, XMLTAG_CHAR);
  $tag    = getVal($a_walk, XMLTAG_TAG);
  /* evaluate the request here and decide if the element matches or not */

  if(!$_REQUEST[showoneway])
  {
    if(0 != getVal($a_walk, XMLTAG_ONEWAY)) return false;
  }

  if(isset($_REQUEST[dst_min]) && ($_REQUEST[dst_min] != "egal"))
  {
    if($len < $_REQUEST[dst_min]) return false;
  }

  if(isset($_REQUEST[dst_max]) && ($_REQUEST[dst_max] != "egal"))
  {
    if($len > $_REQUEST[dst_max]) return false;
  }

  if($_REQUEST[kein_huegeliges])
  {
    if(strstr($char, "hügelig")) return false;
  }

  if($_REQUEST[kein_anstrengendes])
  {
    if(strstr($char, "anstrengend")) return false;
  }

  if($_REQUEST[kein_steiles])
  {
    if(strstr($char, "steil")) return false;
  }

  if($_REQUEST[nur_leichtes])
  {
    if($char&&($char != "leichtes Gelände")) return false;
  }

  $tmp = explode('_', $tag);
  $key = "book_" . strtolower($tmp[0]);
  if(!array_key_exists($key, $_REQUEST))
  {
    return false;
  }

  if(    ( $_REQUEST[Region1] ||  $_REQUEST[Region2] ||  $_REQUEST[Region3] ||  $_REQUEST[Region4])
      && (!$_REQUEST[Region1] || !$_REQUEST[Region2] || !$_REQUEST[Region3] || !$_REQUEST[Region4])
    )
  {
    $lat = getVal($a_walk, XMLTAG_LAT);
    $lon = getVal($a_walk, XMLTAG_LON);
    if($_SESSION['validUser'] == true) 
    {
        $_SESSION['settings'] = loadSettings($_SESSION['userName']); 
    } 
    else
    {
        $_SESSION['settings'] = loadSettings('anonymous'); 
    }
    $settings = $_SESSION['settings'];

    /* NOT Region 1 */
    if(!$_REQUEST[Region1])
    {
      if(($lat >= $settings['lat']) && ($lon <= $settings['lon'])) return false;
    }

    /* NOT Region 2 */
    if(!$_REQUEST[Region2])
    {
      if(($lat >= $settings['lat']) && ($lon >= $settings['lon'])) return false;
    }

    /* NOT Region 3 */
    if(!$_REQUEST[Region3])
    {
      if(($lat <= $settings['lat']) && ($lon <= $settings['lon'])) return false;
    }

    /* NOT Region 4 */
    if(!$_REQUEST[Region4])
    {
      if(($lat <= $settings['lat']) && ($lon >= $settings['lon'])) return false;
    }
  }
  return true;
}

function db_init()
{
  /* instead of loading only one database, search the "db" directory 
   * for ALL files named xml, build a DOM from each of these files and return
   * an array of DOMs */
  /* new one */
  if ($handle = opendir('db/xml'))
  {
    $files = array();
    while (false !== ($file = readdir($handle)))
    {
      //if ($file != "." && $file != ".." && $file != "")
      if(fnmatch("*.xml", $file))
      {
        array_push($files, $file);
      }
    }
    closedir($handle);
  }
  $dom = array();
  foreach($files as $thefile)
  {
    $tmpdom = new DomDocument;
    $tmpdom->preserveWhiteSpace = FALSE;
    $tmpdom->load(realpath("db/xml/" . $thefile));
    array_push($dom, $tmpdom);
  }
  return $dom;
}

function db_cleanup($a_dom)
{
  $tmparray = array($a_dom);
  /* NEW BEGIN */
  $tmp = array_pop($tmparray);
  while($tmp)
  {
    unset($tmp);
    $tmp = array_pop($tmparray);
  }
  unset($a_dom);
  /* NEW END */
  return;
}

function db_getElements($a_dom)
{
  $walks = array();
  foreach($a_dom as $thedom)
  {
    $elements = $thedom->getElementsByTagName(XMLTAG_WALK);
    foreach($elements as $thewalk)
    {
      array_push($walks, $thewalk);
    }
  }
  $retval = array();
  foreach($walks as $walk)
  {
    $entry[Tag]         = getVal($walk, XMLTAG_TAG);
    if($_SESSION['validUser'] == true) 
    {
      /* filter walked walks only for registered users! */
      $walkedWalks = loadWalks($_SESSION['userName']);
      if(!$_REQUEST[showwalked])
      {
        if(isset($walkedWalks))
        {
          if(array_key_exists(strtoupper($entry[Tag]),$walkedWalks))
          {
            continue; /* walk already walked */
          }
        }
      }
    }
    if(false == match($walk))
    {
      continue;
    }
    $entry[Name]        = getVal($walk, XMLTAG_NAME);
    $entry[Laenge]      = getVal($walk, XMLTAG_LENGTH);
    $entry[Dauer]       = getVal($walk, XMLTAG_DUR);
    $entry[Charakter]   = getVal($walk, XMLTAG_CHAR);
    $entry[Lat]         = getVal($walk, XMLTAG_LAT);
    $entry[Lon]         = getVal($walk, XMLTAG_LON);
    if(isset($walkedWalks))
    {
      if(isset($walkedWalks[$entry[Tag]]))
      {
        $entry[Datum]       = $walkedWalks[$entry[Tag]];
      }
      else
      {
        $entry[Datum]       = "0000-00-00";
      }
    }
    else
    {
      $entry[Datum]       = "0000-00-00";
    }
    array_push($retval, $entry);
  }
  return $retval;
}
?>
