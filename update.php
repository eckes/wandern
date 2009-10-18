<?php
require_once('../login/common.php');
require_once('../css/colors.php');
require_once('common.php');
require_once('constants.php');
require_once('db_xml.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <META http-equiv="content-type" content="text/html; charset=UTF-8">
  </head>

<?php

/* TODO:
 * - create some text lines from the xml files
 * - open the constants.php
 * - search for the tag marking the creation block
 * - enter the lines there (replace existing ones)
 * */
$domarray = db_init();

if(false == $out = fopen("db/xml/filelist.txt", "wb"))
    die("Opening filelist file failed\n");
  foreach($domarray as $dom)
  {
      $book = $dom->getElementsByTagName("book")->item(0);
      $short = $book->getAttribute("shortname");
      $long  = $book->getAttribute("longname");
      $thumb = $book->getAttribute("thumbnail");
      $image = $book->getAttribute("image");

      $line = "$short,$long,$thumb,$image\n";
      fwrite($out, $line);
      print("Added line: $line<br>");
  }
db_cleanup($domarray);
fclose($out);
?>
<body></body>
</html>

