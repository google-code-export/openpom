<?
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
    <tr><th><b><?=ucfirst($LANG[$MYLANG]['reload'])?></b></th></tr>
  </table>
<?
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

<table class="popuptable<?=$COLOR?>" id="popuptable">
  <tr><th><?=ucfirst($LANG[$MYLANG]['host'])?></th><td><?=$HOSTNAME?> (<?=$ADDRESS?>)</td></tr>
  <tr><th class="<?=$COLOR?>dark"><?=ucfirst($LANG[$MYLANG]['curstat'])?></th><td><?=$STATUS?> (for <?=$LASTCHANGEDIFF?>)</td></tr>
  <tr><th><?=ucfirst($LANG[$MYLANG]['stinfo'])?></th><td><?=linebreak($OUTPUT)?></td></tr>
  <tr><th><?=ucfirst($LANG[$MYLANG]['curat'])?></th><td><?=$CURATTEMP?>/<?=$MAXATTEMP?></td></tr>
  <tr><th><?=ucfirst($LANG[$MYLANG]['last'])?></th><td><?=$LASTCHECKTIME?> (<?=$LASTCHECKTIMEDIFF?>)</td></tr>
  <tr><th><?=ucfirst($LANG[$MYLANG]['chktyp'])?></th><td><?=$CHKTYPE?></td></tr>
  <tr><th><?=ucfirst($LANG[$MYLANG]['latency'])?> / <?=ucfirst($LANG[$MYLANG]['duration'])?></th><td><?=$LATENCY?> / <?=$EXEC_TIME?></td></tr>
  <tr><th><?=ucfirst($LANG[$MYLANG]['lastchange'])?></th><td><?=$LASTCHANGE?></td></tr>
  <tr><th><?=ucfirst($LANG[$MYLANG]['flapping'])?> ?</th><td><?=$FLAPPING?><? if ($st_data['FLAPPING'] != 2) { echo " ($PERCENT% state change)"; } ?></td></tr>
  <tr><th><?=ucfirst($LANG[$MYLANG]['lastup'])?></th><td><?=$UPDATETIME?> (<?=$UPDATETIMEDIFF?> ago)</td></tr>
  <? if (isset($ACKCOMMENT[1])) { ?>
  <tr><th><img src="img/ack.gif" alt="ack comment" /> (<?=$ACKCOMMENT[0]?>) </th><td><?=$ACKCOMMENT[1]?></td></tr>
  <? } ?>
  <? if (isset($DOWNCOMMENT[1])) { ?>
  <tr><th><img src="img/downtime.gif" alt="downtime comment" /> (<?=$DOWNCOMMENT[0]?>) </th><td><?=$DOWNCOMMENT[1]?>, <?=ucfirst($LANG[$MYLANG]['end_down'])?> <?=$DOWNCOMMENT[2]?></td></tr>
  <? } ?>
  <? if (isset($COMMENT[1])) { ?>
  <tr><th><img src="img/comment.gif" alt="comment" /> (<?=$COMMENT[0]?>) </th><td><?=$COMMENT[1]?></td></tr>
  <? } ?>
</table>

<?
/*  free resources */
mysql_free_result($st_rep);
mysql_close();
?>
