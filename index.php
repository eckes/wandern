<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <title>Select your walks</title>
    <link rel="stylesheet" type="text/css" href="../css/spring.css">
    <META http-equiv="content-type" content="text/html; charset=UTF-8">
    <style type="text/css">
      fieldset{border:1px solid #6cb0bd};
    </style>
  </head>
  <body>
    <div style="width:250pt;">
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
            <option>MLUW1</option>
            <option>MLUW2</option>
            <option>FUW1</option>
            <option>FUW2</option>
            <option>FUW3</option>
            <option>NW2</option>
          </select>
        </fieldset>
        <button type="submit">submit</button>
      </form>
    </div>
  </body>
</html>
