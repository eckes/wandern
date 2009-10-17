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

/* __START_BOOK_CREATION__ */
$g_books[]  = new Book("mluw1", "Mit Mit Lenkrad und Wanderstab I");
$g_books[]  = new Book("mluw2", "Mit Mit Lenkrad und Wanderstab II");
$g_books[]   = new Book("fuw1", "Fahren und Wandern 1");
$g_books[]   = new Book("fuw2", "Fahren und Wandern 2");
$g_books[]   = new Book("fuw3", "Fahren und Wandern 3");
$g_books[]    = new Book("nw2",  "Nürnberger Wanderziele II");
/* __END_BOOK_CREATION__ */

global $g_books;

?>
