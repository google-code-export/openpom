<?php
/*
  OpenPOM
 
  Copyright 2010, Exosec
  Licensed under GPL Version 2.
  http://www.gnu.org/licenses/
*/


session_start();
if (!isset($_SESSION['USER'])) die();
require_once("config.php");
require_once("lang.php");
require_once("query-status.php");
require_once("utils.php");
special_char();

/* requires host/svc in arg1
 * requires status id in arg2 
 */
if (!isset($_GET['arg1']) || !isset($_GET['arg2'])) {
  die('bad arguments');
}

$type = $_GET['arg1'];
$id = $_GET['arg2'];

/* find query */
if (!isset($QUERY_STATUS[$type])) {
  die('no query');
}


/*  SQL */
if (!($dbconn = mysql_connect($SQL_HOST, $SQL_USER, $SQL_PASSWD))) 
  die('cannot connect to db');
if (!mysql_select_db($SQL_DB, $dbconn)) 
  die('cannot select db');

$quoted_id = mysql_real_escape_string($id, $dbconn);
$query = str_replace('define_my_id', $quoted_id, $QUERY_STATUS[$type]);

/* perform query */
if (!($st_rep = mysql_query($query, $dbconn)))
  die('query failed: ' . mysql_error($dbconn));

if (!($st_data = mysql_fetch_array($st_rep, MYSQL_ASSOC)))
  die('query returned no data');



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
  case 0: $FLAPPING = strtoupper(lang($MYLANG, 'no'));  break;
  case 1: $FLAPPING = strtoupper(lang($MYLANG, 'yes')); break;
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
$NEXTCHECKTIME     = $st_data['NEXTCHECKTIME'];
$NEXTCHECKTIMEDIFF = $st_data['NEXTCHECKTIMEDIFF'];
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
$DOWNTIME          = $st_data['DOWNTIME'];
$DISABLECHECK      = $st_data['DISABLECHECK'] ;
$ACKCOMMENT        = explode(';', $st_data['ACKCOMMENT'], 2);
$DOWNCOMMENT       = explode(';', $st_data['DOWNCOMMENT'], 3);
$NOTIFCOMMENT      = explode(';', $st_data['NOTIFCOMMENT'], 2);
$COMMENT           = explode(';', $st_data['COMMENT'], 2);
$DISABLECHECKCOMMENT = explode(';', $st_data['DISABLECHECKCOMMENT'], 2);


if (isset($NOTIFCOMMENT[1]) && preg_match('/^~[^:]+:(.*)$/', $NOTIFCOMMENT[1], $cap)) {
  $NOTIFCOMMENT[1] = $cap[1];
}
if (isset($DISABLECHECKCOMMENT[1]) && preg_match('/^~[^:]+:(.*)$/', $DISABLECHECKCOMMENT[1], $cap)) {
  $DISABLECHECKCOMMENT[1] = $cap[1];
}


?>
<?php if (isset($_GET['fix'])) { ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
  <head>
    <title><?php echo "$SERVICE " . lang($MYLANG, 'on') . " $HOSTNAME" ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta http-equiv="CACHE-CONTROL" content="NO-CACHE" />
    <meta http-equiv="PRAGMA" content="NO-CACHE" />                                       
    <link rel="stylesheet" type="text/css" href="style.css" />
    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="js/lib.js"></script>
  </head>
  <body>
<?php } ?>
  
  
  <div class="box-content" id="box-popup">
    
    <?php if (isset($_GET['fix'])) { ?>
      <table id="popup">
      
    <?php } else { ?>
      <a  id="fix"
          href="#" 
          onClick="return pop(
              '<?php echo $_SERVER['REQUEST_URI'] ?>&fix',
              'popup_nagios_<?php echo $type ?>_<?php echo $id ?>', 
              $('table#popup').parent().outerWidth(), 
              $('table#popup').parent().outerHeight());">
        
        <img src="img/popup.png" 
             border="0" 
             alt="<?php echo ucfirst(lang($MYLANG, 'fixed')) ?>" 
             title="<?php echo ucfirst(lang($MYLANG, 'fixed')) ?>" />
      </a>
      
      <table id="popup" onmouseover="if (it != null) { clearInterval(it); } it = setInterval(hide_data, 5000);">
    <?php } ?>
    
      <tr>
        <th>
          <?php echo ucfirst(lang($MYLANG, 'service')) ?> /
          <?php echo lang($MYLANG, 'machine') ?>
        </th>
        <td>
          <div <?php if (!isset($_GET['fix'])) { ?> style="padding-right: 20px;" <?php } ?>>
            <?php echo $SERVICE ?>
            <?php echo lang($MYLANG, 'on') ?>
            <?php echo $HOSTNAME ?> (<?php echo $ADDRESS ?>)
          </div>
        </td>
      </tr>
      <tr>
        <th><?php echo ucfirst(lang($MYLANG, 'curstat')) ?></th>
        <td>
          <div class="<?php echo $COLOR ?>">
            <?php echo $STATUS ?> (<?php echo printtime($LASTCHANGEDIFF) ?>)
          </div>
        </td>
      </tr>
      <tr>
        <th><?php echo ucfirst(lang($MYLANG, 'stinfo')) ?></th>
        <td><?php echo $OUTPUT ?></td>
      </tr>
      <tr>
        <th><?php echo ucfirst(lang($MYLANG, 'curat'))?></th>
        <td><?php echo $CURATTEMP?>/<?php echo $MAXATTEMP?></td>
      </tr>
      <tr>
        <th><?php echo ucfirst(lang($MYLANG, 'last'))?></th>
        <td><?php echo $LASTCHECKTIME?> (<?php echo printtime($LASTCHECKTIMEDIFF) ?>)</td>
      </tr>
      <tr>
        <th><?php echo ucfirst(lang($MYLANG, 'next_check'))?></th>
        <td><?php if (printtime($NEXTCHECKTIMEDIFF) < 0) echo "N/A" ; else { echo $NEXTCHECKTIME ; echo " (".printtime($NEXTCHECKTIMEDIFF).")" ; } ?></td>
      </tr>
      <tr>
        <th><?php echo ucfirst(lang($MYLANG, 'chktyp'))?></th>
        <td><?php echo $CHKTYPE?></td>
      </tr>
      <tr>
        <th><?php echo ucfirst(lang($MYLANG, 'latency'))?> / <?php echo lang($MYLANG, 'duration') ?></th>
        <td><?php echo $LATENCY?> / <?php echo $EXEC_TIME?></td>
      </tr>
      <tr>
        <th><?php echo ucfirst(lang($MYLANG, 'lastchange'))?></th>
        <td><?php echo $LASTCHANGE?></td>
      </tr>
      <tr>
        <th><?php echo ucfirst(lang($MYLANG, 'flapping'))?> ?</th>
        <td><?php echo $FLAPPING?><?php if ($st_data['FLAPPING'] != 2) { echo " ($PERCENT% ".lang($MYLANG, 'state_change').")"; } ?></td>
      </tr>
      <tr>
        <th><?php echo ucfirst(lang($MYLANG, 'lastup'))?></th>
        <td><?php echo $UPDATETIME?> (<?php echo printtime($UPDATETIMEDIFF) ?>)</td>
      </tr>
      
      <?php if ($ACK && count($ACKCOMMENT) > 1) { ?>
        <tr>
          <th>
            <img class="inline-middle" src="img/flag_ack.gif" />
            <span class="inline-middle" >(<?php echo $ACKCOMMENT[0]?>)</span>
          </th>
          <td><?php echo $ACKCOMMENT[1]?></td>
        </tr>
      <?php } ?>
      
      <?php if ($DOWNTIME && count($DOWNCOMMENT) > 2) { ?>
        <tr>
          <th>
            <img class="inline-middle" src="img/flag_downtime.png" />
            <span class="inline-middle" >(<?php echo $DOWNCOMMENT[0]?>)</span>
          </th>
          <td><?php echo $DOWNCOMMENT[2]?> (<?php echo lang($MYLANG, 'end') ?> <?php echo $DOWNCOMMENT[1] ?>)</td>
        </tr>
      <?php } ?>
      
      <?php if (!$NOTIF && count($NOTIFCOMMENT) > 1) { ?>
        <tr>
          <th>
            <img class="inline-middle" src="img/flag_notify.png" />
            <span class="inline-middle" >(<?php echo $NOTIFCOMMENT[0]?>)</span>
          </th>
          <td><?php echo $NOTIFCOMMENT[1]?></td>
        </tr>
      <?php } ?>

      <?php if ( ($DISABLECHECK == 0) && ($CHECKTYPE == 0) ) { ?>
        <tr>
          <th>
            <img class="inline-middle" src="img/flag_disablecheck.png" />
            <span class="inline-middle" >(<?php echo $DISABLECHECKCOMMENT[0]?>)</span>
          </th>
          <td><?php echo $DISABLECHECKCOMMENT[1]?></td>
        </tr>
      <?php } ?>
      
      <?php if (count($COMMENT) > 1) { ?>
        <tr>
          <th>
            <img class="inline-middle" src="img/flag_comment.gif" />
            <span class="inline-middle">(<?php echo $COMMENT[0]?>)</span>
          </th>
          <td><?php echo $COMMENT[1]?></td>
        </tr>
      <?php } ?>
      
      <tr>
        <th><?php echo ucfirst(lang($MYLANG, 'history')) ?></th>
        <td><a href="#" onClick="return pop('history.php?id=<?php echo $id?>&type=<?php echo $type ?>&host=<?php echo $HOSTNAME ?>&svc=<?php echo $SERVICE ?>', 'history-<?php echo $id?>', '<?php echo $HISTORY_POPUP_WIDTH ?>', '<?php echo $HISTORY_POPUP_HEIGHT ?>')"><?php echo ucfirst(lang($MYLANG, 'show_history')) ?></a></td>
      </tr>
      
      <?php $g = get_graph('status', $HOSTNAME, $SERVICE); ?>
      <?php if (!empty($g)) { ?>
      
        <tr>
          <th style="height: 6px; background: none; border: none; border-top: 1px solid #E0E5D3;"></th>
          <td></td>
        </tr>
        <tr>
          <td colspan="3" style="padding: 0; margin: 0; vertical-align: bottom; height: 100%;">
            <img style="vertical-align: bottom; padding: 0; margin: 0;"
                 <?php if ($POPIN_FIT_TO_GRAPH_WIDTH && !isset($_GET['fix'])) { ?>
                 onload="status_popin_resize($(this).outerWidth() + 12);"
                 <?php } ?>
                 src="<?php echo $g ?>" />
          </td>
        </tr>
        
      <?php } ?>
      
    </table>
  </div>
  
<?php if (isset($_GET['fix'])) { ?>
  </body>
</html>
<?php } ?>


<?php
/*  free resources */
mysql_free_result($st_rep);
mysql_close($dbconn);
?>
