<?php
require_once('../login/common.php');
require_once('../css/colors.php');
require_once('common.php');
require('constants.php');

    function writeBookLine($a_title, $a_description)
    {
        echo '<input type="checkbox" name="book_' . $a_title . '" id="book_' . $a_title . '"value="yes" checked="checked"> <label for="book_' . $a_title . '">' . $a_description . '</label><br>';
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
          <input type="checkbox" id="nur_leichtes" name="nur_leichtes" value="yes"><label for="nur_leichtes"> nur leichtes Gelände</label><br>
          <input type="checkbox" id="nur_huegeliges" name="nur_huegeliges" value="yes"><label for="nur_huegeliges"> nur hügeliges Gelände</label><br>
          <input type="checkbox" id="kein_leichtes" name="kein_leichtes" value="yes"><label for="kein_leichtes"> kein leichtes Gelände</label><br>
          <input type="checkbox" id="kein_huegeliges" name="kein_huegeliges" value="yes"><label for="kein_huegeliges"> kein hügeliges Gelände</label><br>
          <input type="checkbox" id="kein_anstrengendes" name="kein_anstrengendes" value="yes"><label for="kein_anstrengendes"> kein anstrengendes Gelände</label><br>
          <input type="checkbox" id="kein_steiles" name="kein_steiles" value="yes"><label for="kein_steiles"> kein steiles Gelände</label><br>
        </fieldset>
        <fieldset>
          <legend>Region</legend>
          <table>
            <tr>
              <td><label for="Region1">NW</label></td><td><input type="checkbox" id="Region1" name="Region1" value="yes" checked></td><td></td>
              <td><input type="checkbox" id="Region2" name="Region2" value="yes" checked></td><td><label for="Region2">NO</label></td></tr>
            <tr><td></td><td></td><td><img src="images/home.png"></td><td></td><td></td></tr>
            <tr><td><label for="Region3">SW</label></td><td><input type="checkbox" id="Region3" name="Region3" value="yes" checked></td><td></td><td><input type="checkbox" id="Region4" name="Region4" value="yes" checked></td><td><label for="Region4">SO</label></td></tr>
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
          echo '<input type="checkbox" name="showwalked" id="showwalked" value="yes"> <label for="showwalked">Zeige gelaufene</label><br>';
    }
?>
          <input type="checkbox" name="showoneway" id="showoneway" value="yes"> <label for="showoneway">Zeige Streckenwanderungen</label><br>
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
