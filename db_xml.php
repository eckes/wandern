<?php 
/** XML Tag constants */
define("XMLTAG_TAG",        "Tag");
define("XMLTAG_NAME",       "Name");
define("XMLTAG_LENGTH",     "Laenge");
define("XMLTAG_DUR",        "Dauer");
define("XMLTAG_CHAR",       "Charakter");
define("XMLTAG_LAT",        "Lat");
define("XMLTAG_LON",        "Lon");
define("XMLTAG_DATE",       "Datum");
define("XMLTAG_WALK",       "walks");

function getVal($a_walk, $a_name)
{
    return $a_walk->getElementsByTagName($a_name)->item(0)->nodeValue;
}

function match($a_walk)
{
    $len    = getVal($a_walk, XMLTAG_LENGTH);
    $date   = getVal($a_walk, XMLTAG_DATE);
    $char   = getVal($a_walk, XMLTAG_CHAR);
    $tag    = getVal($a_walk, XMLTAG_TAG);
    /* evaluate the request here and decide if the element matches or not */
    if(!$_REQUEST[showwalked])
    {
        if($date != "0000-00-00") return false;
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

    if($_REQUEST[buch] != "Alle Bücher")
    {
        if(!stristr($tag, $_REQUEST[buch])) return false;
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
        if(false == match($walk))
        {
            continue;
        }
        $entry[Tag]         = getVal($walk, XMLTAG_TAG);
        $entry[Name]        = getVal($walk, XMLTAG_NAME);
        $entry[Laenge]      = getVal($walk, XMLTAG_LENGTH);
        $entry[Dauer]       = getVal($walk, XMLTAG_DUR);
        $entry[Charakter]   = getVal($walk, XMLTAG_CHAR);
        $entry[Lat]         = getVal($walk, XMLTAG_LAT);
        $entry[Lon]         = getVal($walk, XMLTAG_LON);
        $entry[Datum]       = getVal($walk, XMLTAG_DATE);
        array_push($retval, $entry);
    }
    return $retval;
}
?>
