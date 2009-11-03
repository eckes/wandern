<?php
header('Content-Type: text/xml; charset=utf-8');

require_once('../login/common.php');
require_once('../css/colors.php');
require_once('common.php');
require_once('constants.php');
require_once('db_xml.php');
//require('loginhead.php');


/* TODO
 * - Depending on the year:
 *      o which book
 *      o how many kilometers 
 *      */

/* open up the user file */

class YearData
{
  var $m_year;
  var $m_books;
  var $m_months;
  var $m_overall;
  var $m_walks;

  function YearData($a_year)
  {
    $this->m_year     = $a_year;
    $this->m_books    = array();
    $this->m_months   = array();
    $this->m_overall  = 0;
    $this->m_walks    = 0;
  }

  function add_walk($a_book, $a_month, $a_len)
  {
    $this->m_overall          += $a_len;
    $this->m_books[$a_book]   += $a_len;
    $this->m_months[$a_month] += $a_len;
    $this->m_walks++;
  }

  function to_xml()
  {
    $doc = new DomDocument('1.0');
    // create root node
    $root = $doc->createElement('year');
    $root = $doc->appendChild($root);

    $tmp = $doc->createElement('theyear');
    $val = $doc->createTextNode($this->m_year);
    $tmp->appendChild($val);
    $root->appendChild($tmp);

    $tmp = $doc->createElement('count');
    $val = $doc->createTextNode($this->m_walks);
    $tmp->appendChild($val);
    $root->appendChild($tmp);

    $tmp = $doc->createElement('overall');
    $val = $doc->createTextNode($this->m_overall);
    $tmp->appendChild($val);
    $root->appendChild($tmp);

    return $doc->saveXML();
  }
}

function get_walk_length($a_name, $a_domlist)
{
  $len  = 0;
  $_a = explode("_", $a_name);
  $sn   = strtolower($_a[0]);
  foreach($a_domlist as $dom)
  {
    $bookshort = strtolower($dom->getElementsByTagName("book")->item(0)->getAttribute("shortname"));
    if($sn==$bookshort)
    {
      $xpath = new DOMXPath($dom);
      $query = '/book/walk[Tag = "' . strtolower($a_name) . '"]/Laenge';
      $res = $xpath->query($query);
      if(0 == $res->length)
      {
        $query = '/book/walk[Tag = "' . strtoupper($a_name) . '"]/Laenge';
        $res = $xpath->query($query);
      }
      return($res->item(0)->nodeValue);
    }
  }
  return -1;
}

$username = 'eckes';
$lines = array();
$data = array();

$_tmp = file("usersettings/" . $username . ".walks");
foreach($_tmp as $line)
{
  $line = trim($line);
  $_a = explode(" ", $line);
  $_b = explode("-", $_a[1]);
  $_d = explode("_", $_a[0]);
  $_c[book]   = strtolower($_d[0]);
  $_c[walk]   = $_a[0];
  $_c[year]   = $_b[0];
  $_c[month]  = $_b[1];
  $_c[day]    = $_b[2];
  array_push($lines, $_c);
}

$domlist = db_init();
foreach($lines as &$line)
{
  $theyear = null;
  $line[length] = get_walk_length($line[walk], $domlist);

  foreach($data as &$_tmpyear)
  {
    if($_tmpyear->m_year == $line[year])
    {
      $theyear = $_tmpyear;
      break;
    }
  }
  if(null == $theyear)
  {
    $theyear = new YearData($line[year]);
    array_push($data, $theyear);
  }

  $theyear->add_walk($line[book], $line[month], $line[length]);
}

db_cleanup($domlist);

$doc = new DomDocument('1.0');
$root = $doc->createElement('statistics');
$root = $doc->appendChild($root);

foreach($data as $theyear)
{
  $year = $doc->createElement('year');
  $root->appendChild($year);

  $tmp = $doc->createElement('theyear', $theyear->m_year);
  $year->appendChild($tmp);

  $tmp = $doc->createElement('count', $theyear->m_walks);
  $year->appendChild($tmp);

  $tmp = $doc->createElement('overall', $theyear->m_overall);
  $year->appendChild($tmp);

  $months = $doc->createElement('months');
  $year->appendChild($months);

  $keys = array_keys($theyear->m_months);

  foreach($keys as $key)
  {
    $month = $doc->createElement('month');
    $att = $doc->createAttribute('nr');
    $val = $doc->createTextNode($key);
    $att->appendChild($val);
    $month->appendChild($att);

    $att = $doc->createAttribute('length');
    $val = $doc->createTextNode($theyear->m_months[$key]);
    $att->appendChild($val);
    $month->appendChild($att);

    $months->appendChild($month);
  }

  $books = $doc->createElement('books');
  $year->appendChild($books);

  $keys = array_keys($theyear->m_books);

  foreach($keys as $key)
  {
    $book = $doc->createElement('book');
    $att = $doc->createAttribute('id');
    $val = $doc->createTextNode($key);
    $att->appendChild($val);
    $book->appendChild($att);

    $att = $doc->createAttribute('length');
    $val = $doc->createTextNode($theyear->m_books[$key]);
    $att->appendChild($val);
    $book->appendChild($att);

    $books->appendChild($book);
  }
}

print($doc->saveXML());
?>
