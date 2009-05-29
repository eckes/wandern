<?php 
?>
<html>
    <head>
    </head>
    <body>
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
                <input type="checkbox" name="nur_leichtes" value="yes"> nur leichtes Gel�nde<br>
                <input type="checkbox" name="nur_huegeliges" value="yes"> nur h�geliges Gel�nde<br>
                <input type="checkbox" name="kein_leichtes" value="yes"> kein leichtes Gel�nde<br>
                <input type="checkbox" name="kein_huegeliges" value="yes"> kein h�geliges Gel�nde<br>
                <input type="checkbox" name="kein_anstrengendes" value="yes"> kein anstrengendes Gel�nde<br>
                <input type="checkbox" name="kein_steiles" value="yes"> kein steiles Gel�nde<br>
            </fieldset>
            <fieldset>
                <legend>Sonstiges</legend>
                <input type="checkbox" name="showwalked" value="yes"> Zeige gelaufene<br>
                <select name="buch" size="1">
                    <option>Alle B�cher</option>
                    <option>MLUW1</option>
                    <option>MLUW2</option>
                    <option>FUW1</option>
                </select>
            </fieldset>
            <button type="submit">submit</button>
        </form>
    </body>
</html>
