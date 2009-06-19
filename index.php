<?php
require_once('../login/common.php');

$error = '0';

if (isset($_POST['submitBtn'])){
	// Get user input
	$username = isset($_POST['username']) ? $_POST['username'] : '';
	$password = isset($_POST['password']) ? $_POST['password'] : '';
        
	// Try to login the user
	$error = loginUser($username,$password);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <title>Select your walks</title>
    <META http-equiv="content-type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" type="text/css" href="../css/spring.css">
    <style type="text/css">
      fieldset{border:1px solid #6cb0bd};
    </style>
  </head>
  <body>
<p class="loginhead">
<?php
if($_SESSION['validUser'] == true) 
{
    echo("<b>Logged in as " . $_SESSION['userName'] . "</b>");
?>
    | <a href="usersettings.php">Settings</a> | <a href="../login/logout.php">Logout</a>
<?php
} 
else
{
?>
<a href="../login/login.php">Login</a> | <a href="../login/register.php">Register</a>
<?php
}
?>
    </p>
    <div style="width:250pt;float:left;">
      <form action="show.php" method="post">
        <fieldset>
          <legend>Allgemein</legend>
          Entfernung von
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
          <legend>Sonstiges</legend>
          <input type="checkbox" name="showwalked" value="yes"> Zeige gelaufene<br>
          <input type="checkbox" name="showoneway" value="yes"> Zeige Streckenwanderungen<br>
          <select name="buch" size="1">
            <option>Alle Bücher</option>
<?php
// Open the users settings file and read the books that the user owns
    require_once('common.php');

    function writeBookLine($a_title)
    {
        echo "<option>" . strtoupper($a_title) . "</option>";
    }
    /* if we have a logged in user, take his settings */
    if(isset($_SESSION['userName']))
    {
        $settings = loadSettings($_SESSION['userName']);

        /* step through the settings and check which books the user owns */
        foreach($g_booklist AS $thebook)
        {
            if($settings[$thebook] == "yes")
            {
                writeBookLine($thebook);
            }
        }
    }
    /* no logged in user, take all the books */
    else
    {
        foreach($g_booklist AS $thebook)
        {
            writeBookLine($thebook);
        }
    }
?>
          </select>
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
