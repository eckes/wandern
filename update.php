<?php
require_once('../login/common.php');
require_once('../css/colors.php');
require_once('common.php');
require_once('constants.php');
require_once('db_xml.php');
?>

<?php

/* TODO:
 * - create some text lines from the xml files
 * - open the constants.php
 * - search for the tag marking the creation block
 * - enter the lines there (replace existing ones)
 * */
$domarray = db_init();

db_cleanup($domarray);
?>

