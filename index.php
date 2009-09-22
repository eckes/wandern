<?php
require_once('../login/common.php');
require_once('../css/colors.php');
require_once('common.php');
require('constants.php');

    function writeBookLine($a_title, $a_description)
    {
        echo '<input type="checkbox" name="book_' . $a_title . '" value="yes" checked="checked"> ' . $a_description . '<br>';
    }

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <title>Select your walks</title>
    <META http-equiv="content-type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" type="text/css" href="../css/style.php">
    <style type="text/css">
    fieldset{border:1px solid <?=$col_accent?>};
    </style>
  </head>
  <body>
<?php require('loginhead.php'); ?>
    <div style="width:250pt;float:left;">
      <form action="show.php" method="post">
        <fieldset>
          <legend>Allgemein</legend>
          Länge von
          <select name="dst_min" size="1">
            <option>egal</option>
            <option>10</option>
            <option>14</option>
            <option>16</option>
            <option>18</option>
            <option>20</option>
          </select>
          bis
          <select name="dst_max" size="1">
            <option>egal</option>
            <option>20</option>
            <option>18</option>
            <option>16</option>
            <option>14</option>
            <option>10</option>
          </select>
          km
        </fieldset>
        <fieldset>
          <legend>Charakter</legend>
          <input type="checkbox" name="nur_leichtes" value="yes"> nur leichtes Gelände<br>
          <input type="checkbox" name="nur_huegeliges" value="yes"> nur hügeliges Gelände<br>
          <input type="checkbox" name="kein_leichtes" value="yes"> kein leichtes Gelände<br>
          <input type="checkbox" name="kein_huegeliges" value="yes"> kein hügeliges Gelände<br>
          <input type="checkbox" name="kein_anstrengendes" value="yes"> kein anstrengendes Gelände<br>
          <input type="checkbox" name="kein_steiles" value="yes"> kein steiles Gelände<br>
        </fieldset>
        <fieldset>
          <legend>Region</legend>
          <table>
            <tr><td>NW</td><td><input type="checkbox" name="Region1" value="yes" checked></td><td></td><td><input type="checkbox" name="Region2" value="yes" checked></td><td>NO</td></tr>
            <tr><td></td><td></td><td><img src="images/home.png"></td><td></td><td></td></tr>
            <tr><td>SW</td><td><input type="checkbox" name="Region3" value="yes" checked></td><td></td><td><input type="checkbox" name="Region4" value="yes" checked></td><td>SO</td></tr>
          </table>
        </fieldset>
        <fieldset>
          <legend>Bücher</legend>
<?php
// Open the users settings file and read the books that the user owns
    /* if we have a logged in user, take his settings */
    if( ($_SESSION['validUser'] == true) && (isset($_SESSION['userName'])))
    {
        if(null != $_SESSION['settings'])
        {
            /* step through the settings and check which books the user owns */
            foreach($g_booklist AS $thebook)
            {
                if($_SESSION['settings'][$thebook] == "yes")
                {
                    writeBookLine($thebook, $g_booktitles[$thebook]);
                }
            }
        }
        else
        {
            echo '<b>No books specified. Select at least one in the <a href="usersettings.php">Settings</a></b>';
        }
    }
    /* no logged in user, take all the books */
    else
    {
        foreach($g_booklist AS $thebook)
        {
            writeBookLine($thebook, $g_booktitles[$thebook]);
        }
    }
?>
        </fieldset>
        <fieldset>
          <legend>Sonstiges</legend>
<?php
    if( ($_SESSION['validUser'] == true) && (isset($_SESSION['userName'])))
    {
          echo '<input type="checkbox" name="showwalked" value="yes"> Zeige gelaufene<br>';
    }
?>
          <input type="checkbox" name="showoneway" value="yes"> Zeige Streckenwanderungen<br>
        </fieldset>
        <button type="submit">submit</button>
      </form>
    </div>
      <img src="images/wanderparkplatz_gross.png" style="margin-left:20px;" height="450px"/>
    </div>
  </body>
</html>
<!-- vim:encoding=utf-8:
-->
