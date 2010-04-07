<?php 

define("SEARCH_PATH", "/kml/Document/Placemark/LineString/coordinates");

/* TODO
 *
 * - Prepare to handle an AJAX request for getting the track data
 * - Return the trackdata as string (like below), so that the show.php can do an eval() in order
 *   to create the PolyLine.
 */

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
  $basepath = "kml/" . $a_id;
  $kmlpath   = $basepath . ".kml";
  $trackpath = $basepath . ".polyline";
  $result = null;
  if(!file_exists($kmlpath))
  {
    return null;
  }
  $xmlstr = file_get_contents($kmlpath);
  $xmlstr = str_replace('xmlns=', 'ns=', $xmlstr);
  $xml = simplexml_load_string($xmlstr);
  $result = $xml->xpath(SEARCH_PATH);
  if(count($result) == 1)
  {
    $result = $result[0];
    $retarr = array();
    $tmparr = preg_split('/[\s]+/', $result, 0, PREG_SPLIT_NO_EMPTY);
    $latlng = array();
    $retstr = 'new google.maps.Polyline([';
    for($i = 0; $i < count($tmparr); $i+=3)
    {
      if($i)
      {
        $retstr = $retstr . ', ';
      }
      $coord = preg_split('/,/', $tmparr[$i], 0, PREG_SPLIT_NO_EMPTY);
      $latlng[lng] = $coord[0];
      $latlng[lat] = $coord[1];
      array_push($retarr, $latlng);
      $retstr = $retstr . 'new google.maps.LatLng(' . $coord[1] . ', ' . $coord[0] . ")";
    }
    $retstr = $retstr . "]);";
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
  if(file_exists($trackpath))
  {
    return file_get_contents($trackpath);
  }
  else if(file_exists($kmlpath))
  {
    return parse_track($a_id);
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
