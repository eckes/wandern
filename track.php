<?php 

// descendant:: indicates an optional element in the xpath search string
define("SEARCH_PATH1", "/kml/Document/Folder/Placemark/LineString/coordinates");
define("SEARCH_PATH2", "/kml/Document/Placemark/LineString/coordinates");

$keys = array_keys($_REQUEST);
foreach($keys AS $thekey)
{
  if($thekey == "gettrack")
  {
    print(get_track($_REQUEST[$thekey]));
    return;
  }
}

function parse_track($a_id)
{
  $basepath   = "kml/" . $a_id;
  $kmlpath    = $basepath . ".kml";
  $trackpath  = $basepath . ".polyline";
  $result     = null;

  $weight     = 4;
  $opacity    = 1;

  if(!file_exists($kmlpath))
  {
    return null;
  }
  $xmlstr = file_get_contents($kmlpath);
  $xmlstr = str_replace('xmlns=', 'ns=', $xmlstr);
  $xml = simplexml_load_string($xmlstr);
  $result = $xml->xpath(SEARCH_PATH2);
  if(count($result) == 0)
  {
    // try the long one
    $result = $xml->xpath(SEARCH_PATH1);
  }
  if(count($result) == 1)
  {
    $result = $result[0];
    $tmparr = preg_split('/[\s]+/', $result, 0, PREG_SPLIT_NO_EMPTY);
    $retstr = 'new google.maps.Polyline([';
    for($i = 0; $i < count($tmparr); $i+=3)
    {
      if($i)
      {
        $retstr = $retstr . ', ';
      }
      $coord = preg_split('/,/', $tmparr[$i], 0, PREG_SPLIT_NO_EMPTY);
      $retstr = $retstr . 'new google.maps.LatLng(' . $coord[1] . ', ' . $coord[0] . ")";
    }
    $retstr = $retstr . "], '#" . dechex(mt_rand(0, (int)0xFFFFFF)) . "', $weight, $opacity);";
  }
  // write the track out to the compiled file
  $fh = fopen($trackpath, 'w') or die("can't open output file $trackpath");
  fwrite($fh, $retstr);
  fclose($fh);
  return $retstr;
  /*
   *'new google.maps.Polyline([ new google.maps.LatLng(49.4962466666667, 10.80186), new google.maps.LatLng(49.4964216666667, 10.8028566666667) ], "#ff0000", 5, 1);'
   */
}

function get_track($a_id)
{
  $basepath = "kml/" . $a_id;
  $kmlpath   = $basepath . ".kml";
  $trackpath = $basepath . ".polyline";
  if(   !file_exists($trackpath)
      ||(filemtime(__FILE__) > filemtime($trackpath))
      ||(filemtime($kmlpath) > filemtime($trackpath))
    )
  {
    return parse_track($a_id);
  }
  else
  {
    return file_get_contents($trackpath);
  }
}

function has_track($a_id)
{
  $basepath = "kml/" . $a_id;
  $kmlpath   = $basepath . ".kml";
  $trackpath = $basepath . ".polyline";
  return(file_exists($kmlpath) || file_exists($trackpath));
}

?>
