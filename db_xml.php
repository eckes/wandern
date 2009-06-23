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
define("XMLTAG_WALK",       "walks");


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

    if($_REQUEST[dst_min] != "egal")
    {
        if($len < $_REQUEST[dst_min]) return false;
    }

    if($_REQUEST[dst_max] != "egal")
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

    if(!$_REQUEST[Region1] || !$_REQUEST[Region2] || !$_REQUEST[Region3] || !$_REQUEST[Region4])
    {
        $lat = getVal($a_walk, XMLTAG_LAT);
        $lon = getVal($a_walk, XMLTAG_LON);

        /* NOT Region 1 */
        if(!$_REQUEST[Region1])
        {
            if(($lat >= HOMELAT) && ($lon <= HOMELON)) return false;
        }

        /* NOT Region 2 */
        if(!$_REQUEST[Region2])
        {
            if(($lat >= HOMELAT) && ($lon >= HOMELON)) return false;
        }

        /* NOT Region 3 */
        if(!$_REQUEST[Region3])
        {
            if(($lat <= HOMELAT) && ($lon <= HOMELON)) return false;
        }

        /* NOT Region 4 */
        if(!$_REQUEST[Region4])
        {
            if(($lat <= HOMELAT) && ($lon >= HOMELON)) return false;
        }
    }
    return true;
}

function db_init()
{
    $xml_db_path = "db/db.xml";
    $dom = new DomDocument;
    $dom->preserveWhiteSpace = FALSE;
    $dom->load(realpath($xml_db_path));
    return $dom;
}

function db_cleanup($a_dom)
{
    return;
}

function db_getElements($a_dom)
{
    $walks = $a_dom->getElementsByTagName(XMLTAG_WALK);
    $retval = array();
    foreach($walks as $walk)
    {
        $entry[Tag]         = getVal($walk, XMLTAG_TAG);
        if($_SESSION['validUser'] == true) 
        {
            $walkedWalks = loadWalks($_SESSION['userName']);
            /* filter walked walks only for registered users! */
            if(!$_REQUEST[showwalked])
            {
                if(isset($walkedWalks))
                {
                    if(array_key_exists($entry[Tag],$walkedWalks))
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
