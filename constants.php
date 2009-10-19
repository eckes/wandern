<?php 

class Book
{
  var $m_id;
  var $m_name;
  var $m_thumbnail;
  var $m_image;

  function Book($a_id, $a_name, $a_thumbnail = null, $a_image = null)
  {
    $this->m_id         = $a_id;
    $this->m_name       = $a_name;
    $this->m_thumbnail  = $a_thumbnail;
    $this->m_image      = $a_image;
  }
}

define ('DEFAULTLAT', 49.450520);
define ('DEFAULTLON', 11.080480);

/* This array holds all our books... */
$g_books = array();

include_once('fragment.php');

global $g_books;

?>
