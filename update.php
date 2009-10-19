<?php
require_once('../login/common.php');
require_once('../css/colors.php');
require_once('common.php');
require_once('constants.php');
require_once('db_xml.php');
require('loginhead.php');

if(    !isset($_SESSION['validUser'])
    || !isset($_SESSION['userName']) 
    || ($_SESSION['userName'] != "eckes")
  )
{
    die("only eckes might call this page!");
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <META http-equiv="content-type" content="text/html; charset=UTF-8">
  </head>

<?php
$domarray = db_init();
if(false == $out = fopen("fragment.php", "wb"))
    die("Opening filelist file failed\n");
fwrite($out, "<?php\n");
foreach($domarray as $dom)
{
    $book = $dom->getElementsByTagName("book")->item(0);
    $short = "\"" . $book->getAttribute("shortname") . "\"" ;
    $long  = "\"" . $book->getAttribute("longname")  . "\"" ;
    $thumb = "\"" . $book->getAttribute("thumbnail") . "\"" ;
    $image = "\"" . $book->getAttribute("image")     . "\"" ;

    $line = "\$g_books[] = new Book($short,$long,$thumb,$image);\n";
    fwrite($out, $line);
    print("Added line: $line<br>");
}
fwrite($out, "?>");

db_cleanup($domarray);
fclose($out);
?>
<body></body>
</html>
