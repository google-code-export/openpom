<?php
/*
  OpenPom $Revision$
  $HeadURL$
 
  Copyright 2010, Exosec
  Licensed under GPL Version 2.
  http://www.gnu.org/licenses/
 
  $Date$
*/


/*
echo "<pre>";
print_r($_GET);
echo "</pre>";
*/

session_start();
if (!isset($_SESSION['USER'])) die();
require_once("config.php");
require_once("lang.php");
require_once("query-status.php");
require_once("utils.php");

/* Process post data */

if (isset($_GET['type']))
  $TYPE = $_GET['type'];
else
  die("no type");

if (isset($_GET['id']))
  $ID = $_GET['id'];
else
  die("no id");

/*  SQL */

$MY_ID = mysql_escape_string($ID);

if($TYPE == "svc")
  $QUERY = str_replace('define_my_id',$MY_ID,$QUERY_SVC);
else 
  $QUERY = str_replace('define_my_id',$MY_ID,$QUERY_HOST);

mysql_connect($SQL_HOST, $SQL_USER, $SQL_PASSWD);
mysql_select_db($SQL_DB);

$st_rep = mysql_query($QUERY);

if (!$st_rep)
  die("\nInvalid request : " . mysql_error() . "\n\n");

$st_data = mysql_fetch_array($st_rep, MYSQL_ASSOC);

if ( ! isset($st_data['ADDRESS']) ){

?>
  <table class="popuptable">
    <tr><th><b><?php echo ucfirst(lang($MYLANG, 'reload'))?></b></th></tr>
  </table>
<?php
  exit (0);
}

switch($st_data['STATE']) {
  case 0: $STATUS = "OK";       $COLOR = $OK;        break;
  case 1: $STATUS = "WARNING";  $COLOR = $WARNING;   break;
  case 2: $STATUS = "CRITICAL"; $COLOR = $CRITICAL;  break;
  case 3: $STATUS = "UNKNOWN";  $COLOR = $UNKNOWN;   break;
}
switch ($st_data['CHKTYPE']) {
  case 0: $CHKTYPE = "ACTIVE";  break;
  case 1: $CHKTYPE = "PASSIVE"; break;
}
switch ($st_data['FLAPPING']) {
  case 1: $FLAPPING = "YES"; break;
  case 0: $FLAPPING = "NO";  break;
  case 2: $FLAPPING = "N/A"; break;
}

$ADDRESS           = $st_data['ADDRESS'];
$HOSTNAME          = $st_data['HOSTNAME'];

if (empty($st_data['SERVICE']))
  $SERVICE         = "PING";
else
  $SERVICE         = $st_data['SERVICE'];

$LASTCHANGEDIFF    = $st_data['LASTCHANGEDIFF'];
$LASTCHANGE        = $st_data['LASTCHANGE'];
$LASTCHECKTIMEDIFF = $st_data['LASTCHECKTIMEDIFF'];
$LASTCHECKTIME     = $st_data['LASTCHECKTIME'];
$OUTPUT            = $st_data['OUTPUT'];
$CURATTEMP         = $st_data['CURATTEMP'];
$MAXATTEMP         = $st_data['MAXATTEMP'];
$LATENCY           = $st_data['LATENCY'];
$EXEC_TIME         = $st_data['EXEC_TIME'];
$NOTIF             = $st_data['NOTIF'];
$PERCENT           = $st_data['PERCENT'];
$UPDATETIMEDIFF    = $st_data['UPDATETIMEDIFF'];
$UPDATETIME        = $st_data['UPDATETIME'];
$ACK               = $st_data['ACK'];
$DOWN              = $st_data['DOWNTIME'];
$ACKCOMMENT        = explode(';', $st_data['ACKCOMMENT']);
$DOWNCOMMENT       = explode(';', $st_data['DOWNCOMMENT']);
$COMMENT           = explode(';', $st_data['COMMENT']);
?>
<?php if (isset($_GET['fix'])) { ?>
<!DCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
  <head>
    <title><?php echo $HOSTNAME." ".$SERVICE; ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta http-equiv="CACHE-CONTROL" content="NO-CACHE" />
    <meta http-equiv="PRAGMA" content="NO-CACHE" />                                       
    <link rel="StyleSheet" href="style.css" type="text/css" />
    <script type='text/javascript' src='js/func.js'></script>
  </head>
  <body>
<table class="popuptable<?php echo $COLOR?>" id="popuptable">
<?php } else { ?>
<table class="popuptable<?php echo $COLOR?>" id="popuptable" onmouseover='clearInterval(it); it = setInterval("hide_data()", 5000);'>
<?php } ?>
  <tr><th><?php echo ucfirst(lang($MYLANG, 'host'))?></th><td><?php echo $HOSTNAME?> (<?php echo $ADDRESS?>)</td><td align="right" border="0"><a href="#" onClick='pop("status.php?type=<?php echo $TYPE; ?>&id=<?php echo $ID; ?>&fix","<?php echo $HOSTNAME." ".$SERVICE?>",document.getElementById("popuptable").offsetParent.offsetWidth,document.getElementById("popuptable").offsetParent.offsetHeight);'><img src="img/popup.png" border="0" alt"<?php echo ucfirst(lang($MYLANG, 'fixed'))?>" title="<?php echo ucfirst(lang($MYLANG, 'fixed'))?>" /></a></td></tr>
  <tr><th class="<?php echo $COLOR?>dark"><?php echo ucfirst(lang($MYLANG, 'curstat'))?></th><td colspan="2"><?php echo $STATUS?> (for <?php echo $LASTCHANGEDIFF?>)</td></tr>
  <tr><th><?php echo ucfirst(lang($MYLANG, 'stinfo'))?></th><td colspan="2"><?php echo linebreak($OUTPUT)?></td></tr>
  <tr><th><?php echo ucfirst(lang($MYLANG, 'curat'))?></th><td colspan="2"><?php echo $CURATTEMP?>/<?php echo $MAXATTEMP?></td></tr>
  <tr><th><?php echo ucfirst(lang($MYLANG, 'last'))?></th><td colspan="2"><?php echo $LASTCHECKTIME?> (<?php echo $LASTCHECKTIMEDIFF?>)</td></tr>
  <tr><th><?php echo ucfirst(lang($MYLANG, 'chktyp'))?></th><td colspan="2"><?php echo $CHKTYPE?></td></tr>
  <tr><th><?php echo ucfirst(lang($MYLANG, 'latency'))?> / <?php echo ucfirst(lang($MYLANG, 'duration'))?></th><td colspan="2"><?php echo $LATENCY?> / <?php echo $EXEC_TIME?></td></tr>
  <tr><th><?php echo ucfirst(lang($MYLANG, 'lastchange'))?></th><td colspan="2"><?php echo $LASTCHANGE?></td></tr>
  <tr><th><?php echo ucfirst(lang($MYLANG, 'flapping'))?> ?</th><td colspan="2"><?php echo $FLAPPING?><?php if ($st_data['FLAPPING'] != 2) { echo " ($PERCENT% state change)"; } ?></td></tr>
  <tr><th><?php echo ucfirst(lang($MYLANG, 'lastup'))?></th><td colspan="2"><?php echo $UPDATETIME?> (<?php echo $UPDATETIMEDIFF?> ago)</td></tr>
  <?php if ( ($ACK == 1) && (isset($ACKCOMMENT[1])) ) { ?>
  <tr><th><img src="img/ack.gif" alt="ack comment" /> (<?php echo $ACKCOMMENT[0]?>) </th><td colspan="2"><?php echo $ACKCOMMENT[1]?></td></tr>
  <?php } ?>
  <?php if ( ($DOWN == 1) && (isset($DOWNCOMMENT[1])) ) { ?>
  <tr><th><img src="img/downtime.gif" alt="downtime comment" /> (<?php echo $DOWNCOMMENT[0]?>) </th><td colspan="2"><?php echo $DOWNCOMMENT[1]?>, <?php echo ucfirst(lang($MYLANG, 'end_down'))?> <?php echo $DOWNCOMMENT[2]?></td></tr>
  <?php } ?>
  <?php if (isset($COMMENT[1])) { ?>
  <tr><th><img src="img/comment.gif" alt="comment" /> (<?php echo $COMMENT[0]?>) </th><td colspan="2"><?php echo $COMMENT[1]?></td></tr>
  <?php } ?>
  <?php if (isset($GRAPH_STATUS)) {
    $graph = str_replace('_HOSTNAME_',$HOSTNAME,$GRAPH_STATUS);
    if ($TYPE == "host")
      $graph = str_replace('&service=_SERVICE_','',$graph);
    else
      $graph = str_replace('_SERVICE_',$SERVICE,$graph);
  ?>
  <tr><td colspan="3"><img src="<?php echo $graph; ?>" width="550px" height="300px"></td></tr>
  <?php } ?>
</table>
<?php if (isset($_GET['fix'])) { ?>
</body>
</html>
<?php } ?>
<?php
/*  free resources */
mysql_free_result($st_rep);
mysql_close();
?>
