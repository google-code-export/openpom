<?php
/*
  OpenPom $Revision$
  $HeadURL$
 
  Copyright 2010, Exosec
  Licensed under GPL Version 2.
  http://www.gnu.org/licenses/
 
  $Date$

  Sylvain Choisnard - schoisnard@exosec.fr                                                 
*/


/*
echo "<pre>";
print_r($_POST);
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
    <tr><th><b><?php echo ucfirst($LANG[$MYLANG]['reload'])?></b></th></tr>
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
$ACKCOMMENT        = explode(';', $st_data['ACKCOMMENT']);
$DOWNCOMMENT       = explode(';', $st_data['DOWNCOMMENT']);
$COMMENT           = explode(';', $st_data['COMMENT']);
?>

<table class="popuptable<?php echo $COLOR?>" id="popuptable">
  <tr><th><?php echo ucfirst($LANG[$MYLANG]['host'])?></th><td><?php echo $HOSTNAME?> (<?php echo $ADDRESS?>)</td></tr>
  <tr><th class="<?php echo $COLOR?>dark"><?php echo ucfirst($LANG[$MYLANG]['curstat'])?></th><td><?php echo $STATUS?> (for <?php echo $LASTCHANGEDIFF?>)</td></tr>
  <tr><th><?php echo ucfirst($LANG[$MYLANG]['stinfo'])?></th><td><?php echo linebreak($OUTPUT)?></td></tr>
  <tr><th><?php echo ucfirst($LANG[$MYLANG]['curat'])?></th><td><?php echo $CURATTEMP?>/<?php echo $MAXATTEMP?></td></tr>
  <tr><th><?php echo ucfirst($LANG[$MYLANG]['last'])?></th><td><?php echo $LASTCHECKTIME?> (<?php echo $LASTCHECKTIMEDIFF?>)</td></tr>
  <tr><th><?php echo ucfirst($LANG[$MYLANG]['chktyp'])?></th><td><?php echo $CHKTYPE?></td></tr>
  <tr><th><?php echo ucfirst($LANG[$MYLANG]['latency'])?> / <?php echo ucfirst($LANG[$MYLANG]['duration'])?></th><td><?php echo $LATENCY?> / <?php echo $EXEC_TIME?></td></tr>
  <tr><th><?php echo ucfirst($LANG[$MYLANG]['lastchange'])?></th><td><?php echo $LASTCHANGE?></td></tr>
  <tr><th><?php echo ucfirst($LANG[$MYLANG]['flapping'])?> ?</th><td><?php echo $FLAPPING?><?php if ($st_data['FLAPPING'] != 2) { echo " ($PERCENT% state change)"; } ?></td></tr>
  <tr><th><?php echo ucfirst($LANG[$MYLANG]['lastup'])?></th><td><?php echo $UPDATETIME?> (<?php echo $UPDATETIMEDIFF?> ago)</td></tr>
  <?php if (isset($ACKCOMMENT[1])) { ?>
  <tr><th><img src="img/ack.gif" alt="ack comment" /> (<?php echo $ACKCOMMENT[0]?>) </th><td><?php echo $ACKCOMMENT[1]?></td></tr>
  <?php } ?>
  <?php if (isset($DOWNCOMMENT[1])) { ?>
  <tr><th><img src="img/downtime.gif" alt="downtime comment" /> (<?php echo $DOWNCOMMENT[0]?>) </th><td><?php echo $DOWNCOMMENT[1]?>, <?php echo ucfirst($LANG[$MYLANG]['end_down'])?> <?php echo $DOWNCOMMENT[2]?></td></tr>
  <?php } ?>
  <?php if (isset($COMMENT[1])) { ?>
  <tr><th><img src="img/comment.gif" alt="comment" /> (<?php echo $COMMENT[0]?>) </th><td><?php echo $COMMENT[1]?></td></tr>
  <?php } ?>
</table>

<?php
/*  free resources */
mysql_free_result($st_rep);
mysql_close();
?>
