<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <title>Statistik</title>
    <META http-equiv="content-type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" type="text/css" href="../css/style.php">
    <script type="text/javascript" src="/scripts/dojo-release-1.3.2/dojo/dojo.js"
      djConfig="parseOnLoad: true, isDebug: false">
      <script type="text/javascript">
        loadcb = function(a_data) { alert(a_data); }

function getStatistics()
{
  var xhrArgs = {url: '/wandern/stats2.php', handleAs: "xml", load:loadcb, error: function(error) { alert("An unexpected error occurred: " + error); }};
  var deferred = dojo.xhrGet(xhrArgs);
}

dojo.addOnLoad(getStatistics);
alert("go");
</script>
  </head>
  <body>
  </body>
</html>

