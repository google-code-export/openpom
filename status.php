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
    <tr><th><b><?= ucfirst(lang($MYLANG, 'reload'))?></b></th></tr>
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
  $SERVICE         = "--host--";
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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
  <head>
    <title><?= $HOSTNAME ?> &#160;&mdash;&#160; <?= $SERVICE ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta http-equiv="CACHE-CONTROL" content="NO-CACHE" />
    <meta http-equiv="PRAGMA" content="NO-CACHE" />                                       
    <link rel="stylesheet" type="text/css" href="style.css" />
    <script type="text/javascript" src="js/jquery-1.5.1.min.js"></script>
    <script type='text/javascript' src='js/func.js'></script>
  </head>
  <body>
<?php } ?>
  
  <!--
  <div class="<?= $COLOR ?>dark" style="padding: 1px;">
  -->
  <div class="box-content" style="background-color: white;">
  
  
    <?php if (isset($_GET['fix'])) { ?>
      <table id="popup">
    <?php } else { ?>
      <table id="popup" onmouseover="if (it != null) { clearInterval(it); } it = setInterval(hide_data, 5000);">
    <?php } ?>
    
      <tr>
        <th><?= ucfirst(lang($MYLANG, 'host')) ?></th>
        <td><?= $HOSTNAME ?> (<?= $ADDRESS ?>)</td>
        <td align="right" border="0" style="padding: 0; margin: 0;">
          <a  href="#" 
              onClick="return pop(
                  'status.php?type=<?= $TYPE; ?>&id=<?= $ID; ?>&fix',
                  '<?= $TYPE ?><?= $ID ?>',
                  $('table#popup').parent().outerWidth(), 
                  $('table#popup').parent().outerHeight());">
            <img src="img/popup.png" 
                 border="0" 
                 alt"<?= ucfirst(lang($MYLANG, 'fixed')) ?>" 
                 title="<?= ucfirst(lang($MYLANG, 'fixed')) ?>" />
          </a>
        </td>
      </tr>
      <tr>
        <th><?= ucfirst(lang($MYLANG, 'curstat')) ?></th>
        <td colspan="2">
          <div class="<?= $COLOR ?>">
            <?= $STATUS ?> (for <?= $LASTCHANGEDIFF ?>)
          </div>
        </td>
      </tr>
      <tr>
        <th><?= ucfirst(lang($MYLANG, 'stinfo')) ?></th>
        <td colspan="2"><?= linebreak($OUTPUT) ?></td>
      </tr>
      <tr>
        <th><?= ucfirst(lang($MYLANG, 'curat'))?></th>
        <td colspan="2"><?= $CURATTEMP?>/<?= $MAXATTEMP?></td>
      </tr>
      <tr>
        <th><?= ucfirst(lang($MYLANG, 'last'))?></th>
        <td colspan="2"><?= $LASTCHECKTIME?> (<?= $LASTCHECKTIMEDIFF?>)</td>
      </tr>
      <tr>
        <th><?= ucfirst(lang($MYLANG, 'chktyp'))?></th>
        <td colspan="2"><?= $CHKTYPE?></td>
      </tr>
      <tr>
        <th><?= ucfirst(lang($MYLANG, 'latency'))?> / <?= ucfirst(lang($MYLANG, 'duration'))?></th>
        <td colspan="2"><?= $LATENCY?> / <?= $EXEC_TIME?></td>
      </tr>
      <tr>
        <th><?= ucfirst(lang($MYLANG, 'lastchange'))?></th>
        <td colspan="2"><?= $LASTCHANGE?></td>
      </tr>
      <tr>
        <th><?= ucfirst(lang($MYLANG, 'flapping'))?> ?</th>
        <td colspan="2"><?= $FLAPPING?><?php if ($st_data['FLAPPING'] != 2) { echo " ($PERCENT% state change)"; } ?></td>
      </tr>
      <tr>
        <th><?= ucfirst(lang($MYLANG, 'lastup'))?></th>
        <td colspan="2"><?= $UPDATETIME?> (<?= $UPDATETIMEDIFF?> ago)</td>
      </tr>
      
      <?php if ( ($ACK == 1) && (isset($ACKCOMMENT[1])) ) { ?>
        <tr>
          <th style="margin: 0; padding: 1px 3px;">
            <img style="display: inline-block; vertical-align: middle;" height="12" width="12" src="img/flag_ack.gif" alt="ack comment" />
            <span style="display: inline-block; vertical-align: middle;">(<?= $ACKCOMMENT[0]?>)</span>
          </th>
          <td colspan="2"><?= $ACKCOMMENT[1]?></td>
        </tr>
      <?php } ?>
      
      <?php if ( ($DOWN == 1) && (isset($DOWNCOMMENT[1])) ) { ?>
        <tr>
          <th style="margin: 0; padding: 1px 3px;">
            <img style="display: inline-block; vertical-align: middle;" height="12" width="12" src="img/flag_downtime.png" alt="downtime comment" />
            <span style="display: inline-block; vertical-align: middle;">(<?= $DOWNCOMMENT[0]?>)</span>
          </th>
          <td colspan="2"><?= $DOWNCOMMENT[1]?>, (<?= lang($MYLANG, 'end_down') ?> <?= $DOWNCOMMENT[2] ?>)</td>
        </tr>
      <?php } ?>
      
      <?php if (isset($COMMENT[1])) { ?>
        <tr>
          <th style="margin: 0; padding: 1px 3px;">
            <img style="display: inline-block; vertical-align: middle;" height="12" width="12" src="img/flag_comment.gif" alt="ack comment" />
            <span style="display: inline-block; vertical-align: middle;">(<?= $COMMENT[0]?>)</span>
          </th>
          <td colspan="2"><?= $COMMENT[1]?></td>
        </tr>
      <?php } ?>
        
      <? $g = get_graph('status', $HOSTNAME, $SERVICE); ?>
      <? if (!is_null($g)) { ?>
      
        <tr>
          <th style="height: 6px; background: none; border: none; border-top: 1px solid #E0E5D3;"></th>
          <td colspan="2"></td>
        </tr>
        <tr>
          <td colspan="3" style="padding: 0; margin: 0; vertical-align: bottom; height: 100%;">
            <img style="vertical-align: bottom; padding: 0; margin: 0;" src="<?= $g ?>">
          </td>
        </tr>
        
      <? } ?>
        
    </table>
  </div>
  <!--
  </div>
  -->
  
<?php if (isset($_GET['fix'])) { ?>
  </body>
</html>
<?php } ?>


<?php
/*  free resources */
mysql_free_result($st_rep);
mysql_close();
?>
