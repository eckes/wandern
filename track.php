<?php 

define("SEARCH_PATH", "/kml/Document/Placemark/LineString/coordinates");

/* TODO
 *
 * - Prepare to handle an AJAX request for getting the track data
 * - Return the trackdata as string (like below), so that the show.php can do an eval() in order
 *   to create the PolyLine.
 */

function get_track($a_id)
{
  $path   = "kml/" . $a_id . ".kml";
  $result = null;
  if(file_exists($path))
  {
    $xmlstr = file_get_contents($path);
    $xmlstr = str_replace('xmlns=', 'ns=', $xmlstr);
    $xml = simplexml_load_string($xmlstr);
    $result = $xml->xpath(SEARCH_PATH);
    if(count($result) == 1)
    {
      $result = $result[0];
      $retarr = array();
      $tmparr = preg_split('/[\s]+/', $result, 0, PREG_SPLIT_NO_EMPTY);
      $latlng = array();
      for($i = 0; $i < count($tmparr); $i+=3)
      {
        $coord = preg_split('/,/', $tmparr[$i], 0, PREG_SPLIT_NO_EMPTY);
        $latlng[lng] = $coord[0];
        $latlng[lat] = $coord[1];
        array_push($retarr, $latlng);
      }
    }
  }
  return $retarr;
  /*
    GPolyLine([
      {lat:LAT, long:LONG},
      {lat:LAT, long:LONG},
      {lat:LAT, long:LONG},
      {lat:LAT, long:LONG},
      {lat:LAT, long:LONG}
      ]);
   */
}

?>
