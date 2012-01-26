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
  case 0: $CHKTYPE = "Active";  break;
  case 1: $CHKTYPE = "Passive"; break;
}
switch ($st_data['CHECKENABLE']) {
  case 0: $CHECKENABLE = "disabled";  break;
  case 1: $CHECKENABLE = "enabled";   break;
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

$LASTCHANGEDIFF       = $st_data['LASTCHANGEDIFF'];
$LASTCHANGE           = $st_data['LASTCHANGE'];
$LASTCHECKTIMEDIFF    = $st_data['LASTCHECKTIMEDIFF'];
$LASTCHECKTIME        = $st_data['LASTCHECKTIME'];
$LASTTIMEOKDIFF       = $st_data['LASTTIMEOKDIFF'];
$LASTTIMEOK           = $st_data['LASTTIMEOK'];
$NEXTCHECKTIME        = $st_data['NEXTCHECKTIME'];
$NEXTCHECKTIMEDIFF    = $st_data['NEXTCHECKTIMEDIFF'];
$OUTPUT               = $st_data['OUTPUT'];
$CURATTEMP            = $st_data['CURATTEMP'];
$MAXATTEMP            = $st_data['MAXATTEMP'];
$NORMALINTERVAL       = $st_data['NORMALINTERVAL'];
$RETRYINTERVAL        = $st_data['RETRYINTERVAL'];
$LATENCY              = $st_data['LATENCY'];
$EXEC_TIME            = $st_data['EXEC_TIME'];
$NOTIF                = $st_data['NOTIF'];
$PERCENT              = $st_data['PERCENT'];
$GROUPS               = $st_data['GROUPES'] ;
$CONTACTGROUP         = $st_data['CONTACTGROUP'] ;
$UPDATETIMEDIFF       = $st_data['UPDATETIMEDIFF'];
$UPDATETIME           = $st_data['UPDATETIME'];
$LASTNOTIFY           = $st_data['LASTNOTIFY'];
$COUNTNOTIFY          = $st_data['COUNTNOTIFY'];
$NEXTTIMENOTIFYDIFF   = $st_data['NEXTTIMENOTIFYDIFF'];
$NEXTTIMENOTIFY       = $st_data['NEXTTIMENOTIFY'];
$CHECKNAME            = $st_data['CHECKNAME'];
$ACK                  = $st_data['ACK'];
$DOWNTIME             = $st_data['DOWNTIME'];
$DISABLECHECK         = $st_data['DISABLECHECK'] ;
$ACKCOMMENT           = explode(';', $st_data['ACKCOMMENT'], 2);
$DOWNCOMMENT          = explode(';', $st_data['DOWNCOMMENT'], 3);
$NOTIFCOMMENT         = explode(';', $st_data['NOTIFCOMMENT'], 2);
$DISABLECHECKCOMMENT  = explode(';', $st_data['DISABLECHECKCOMMENT'], 2);
$COMMENT              = explode(';', $st_data['COMMENT'], 2);

if (isset($NOTIFCOMMENT[1]) && preg_match('/^~[^:]+:(.*)$/', $NOTIFCOMMENT[1], $cap)) {
  $NOTIFCOMMENT[1] = $cap[1];
}
if (isset($DISABLECHECKCOMMENT[1]) && preg_match('/^~[^:]+:(.*)$/', $DISABLECHECKCOMMENT[1], $cap)) {
  $DISABLECHECKCOMMENT[1] = $cap[1];
}

if ( (! isset($ACKCOMMENT[0]) ) || (empty($ACKCOMMENT[0])) ) $ACKCOMMENT[0] = "N/A" ;
if (! isset($ACKCOMMENT[1]) ) $ACKCOMMENT[1] = "N/A" ;
if ( (! isset($DOWNCOMMENT[0]) ) || (empty($DOWNCOMMENT[0])) ) $DOWNCOMMENT[0] = "N/A" ;
if (! isset($DOWNCOMMENT[1]) ) $DOWNCOMMENT[1] = "N/A" ;
if (! isset($DOWNCOMMENT[2]) ) $DOWNCOMMENT[2] = "N/A" ;
if ( (! isset($NOTIFCOMMENT[0]) ) || (empty($NOTIFCOMMENT[0])) ) $NOTIFCOMMENT[0] = "N/A" ;
if (! isset($NOTIFCOMMENT[1]) ) $NOTIFCOMMENT[1] = "N/A" ;
if ( (! isset($DISABLECHECKCOMMENT[0]) ) || (empty($DISABLECHECKCOMMENT[0])) ) $DISABLECHECKCOMMENT[0] = "N/A" ;
if (! isset($DISABLECHECKCOMMENT[1]) ) $DISABLECHECKCOMMENT[1] = "N/A" ;
if ( (! isset($COMMENT[0]) ) || (empty($COMMENT[0])) ) $COMMENT[0] = "N/A" ;
if (! isset($COMMENT[1]) ) $COMMENT[1] = "N/A" ;

?>
<?php if (isset($_GET['fix'])) { ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
  <head>
    <title><?php echo "$SERVICE " . lang($MYLANG, 'on') . " $HOSTNAME" ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $ENCODING ?>" />
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
          <?php echo ucfirst(lang($MYLANG, 'machine')) ?> |
          <?php echo ucfirst(lang($MYLANG, 'service')) ?>
        </th>
        <td>
          <div <?php if (!isset($_GET['fix'])) { ?> style="padding-right: 20px;" <?php } ?>>
            <?php echo $SERVICE ?>
            <?php echo lang($MYLANG, 'on') ?>
            <?php echo $HOSTNAME ?> (<?php echo $ADDRESS ?>)
          </div>
        </td>
      </tr>
      <?php if ( ($STATUSPOPIN['curstat'] == 1) && (isset($_SESSION['STATUS']['curstat'])) ) { ?>
      <tr>
        <th><?php echo ucfirst(lang($MYLANG, 'curstat')) ?></th>
        <td>
          <div class="<?php echo $COLOR ?>">
            <?php echo $STATUS ?> (<?php echo printtime($LASTCHANGEDIFF) ?>)
          </div>
        </td>
      </tr>
      <?php } ?>
      <?php if ( ($STATUSPOPIN['outputstatus'] == 1) && (isset($_SESSION['STATUS']['outputstatus'])) ) { ?>
      <tr>
        <th><?php echo ucfirst(lang($MYLANG, 'stinfo')) ?></th>
        <td><?php echo $OUTPUT ?></td>
      </tr>
      <?php } ?>
      <?php if ( ($STATUSPOPIN['checkstatus'] == 1) && (isset($_SESSION['STATUS']['checkstatus'])) ) { ?>
      <tr>
        <th><?php echo ucfirst(lang($MYLANG, 'checkstatus'))?></th>
        <td><?php echo $CURATTEMP?>/<?php echo $MAXATTEMP?> 
          | <?php echo $NORMALINTERVAL ?>m/<?php echo $RETRYINTERVAL ?>m | 
          <?php echo printtime($LASTCHECKTIMEDIFF) ?></td>
      </tr>
      <?php } ?>
      <?php if ( ($STATUSPOPIN['lastok'] == 1) && (isset($_SESSION['STATUS']['lastok'])) ) { ?>
      <tr>
        <th><?php echo ucfirst(lang($MYLANG, 'lastok')) ?></th>
          <td><?php if ( (substr($LASTTIMEOK, 0, 4) == "1970") || ($STATUS == "OK") ) echo "N/A" ;
                  else echo $LASTTIMEOK." (".printtime($LASTTIMEOKDIFF).")"; ?>
        </td>
      </tr>
      <?php } ?>
      <?php if ( ($STATUSPOPIN['nextcheck'] == 1) && (isset($_SESSION['STATUS']['nextcheck'])) ) { ?>
      <tr>
        <th><?php echo ucfirst(lang($MYLANG, 'nextcheck'))?></th>
        <td><?php if (printtime($NEXTCHECKTIMEDIFF) < 0) echo "N/A" ; else { echo $NEXTCHECKTIME ; echo " (".printtime($NEXTCHECKTIMEDIFF).")" ; } ?></td>
      </tr>
      <?php } ?>
      <?php if ( ($STATUSPOPIN['checkinfo'] == 1) && (isset($_SESSION['STATUS']['checkinfo'])) ) { ?>
      <tr>
        <th><?php echo ucfirst(lang($MYLANG, 'chktyp')) ?> | <?php echo ucfirst(lang($MYLANG, 'checkname')) ?></th>
        <td><?php echo $CHKTYPE." (".lang($MYLANG, $CHECKENABLE).")"?> | <?php echo $CHECKNAME ?></td>
      </tr>
      <?php } ?>
      <?php if ( ($STATUSPOPIN['checktime'] == 1) && (isset($_SESSION['STATUS']['checktime'])) ) { ?>
      <tr>
        <th><?php echo ucfirst(lang($MYLANG, 'latency'))?> | <?php echo lang($MYLANG, 'duration') ?></th>
        <td><?php echo $LATENCY?> | <?php echo $EXEC_TIME?></td>
      </tr>
      <?php } ?>
      <?php if ( ($STATUSPOPIN['laststatus'] == 1) && (isset($_SESSION['STATUS']['laststatus'])) ) { ?>
      <tr>
        <th><?php echo ucfirst(lang($MYLANG, 'lastchange'))?> | <?php echo ucfirst(lang($MYLANG, 'lastup'))?></th>
        <td><?php echo printtime($LASTCHANGEDIFF) ?> | <?php echo printtime($UPDATETIMEDIFF) ?></td>
      </tr>
      <?php } ?>
      <?php if ( ($STATUSPOPIN['flapping'] == 1) && (isset($_SESSION['STATUS']['flapping'])) ) { ?>
      <tr>
        <th><?php echo ucfirst(lang($MYLANG, 'flapping'))?> ?</th>
        <td><?php echo $FLAPPING?><?php if ($st_data['FLAPPING'] != 2) { echo " ($PERCENT% ".lang($MYLANG, 'state_change').")"; } ?></td>
      </tr>
      <?php } ?>
      <tr>
      <?php if ( ($STATUSPOPIN['groupstatus'] == 1) && (isset($_SESSION['STATUS']['groupstatus'])) ) { ?>
      <tr>
        <th><?php echo ucfirst(lang($MYLANG, 'groupstatus')) ?></th>
        <td><?php echo $GROUPS ?> | <?php echo $CONTACTGROUP ?></td>
      <tr>
      <?php } ?>
      <?php if ( ($STATUSPOPIN['notifystatus'] == 1) && (isset($_SESSION['STATUS']['notifystatus'])) ) { ?>
      <tr>
        <th><?php echo ucfirst(lang($MYLANG, 'notifystatus')) ?></th>
        <td><?php if ($COUNTNOTIFY == 0) echo "N/A (0)" ; else echo $LASTNOTIFY." (".$COUNTNOTIFY.") " ; ?> | 
            <?php if ($NEXTTIMENOTIFYDIFF <= 0) echo "N/A"; else echo printtime($NEXTTIMENOTIFYDIFF) ; ?>
        </td>
      <tr>
      <?php } ?>
      
      <?php if ( ($STATUSPOPIN['ackstatus'] == 1) && (isset($_SESSION['STATUS']['ackstatus'])) && ($ACK && count($ACKCOMMENT) > 1) ) { ?>
        <tr>
          <th>
            <img class="inline-middle" src="img/flag_ack.gif" />
            <span class="inline-middle" >(<?php echo $ACKCOMMENT[0]?>)</span>
          </th>
          <td><?php echo $ACKCOMMENT[1]?></td>
        </tr>
      <?php } ?>
      
      <?php if ( ($STATUSPOPIN['downstatus'] == 1) && (isset($_SESSION['STATUS']['downstatus'])) && ($DOWNTIME && count($DOWNCOMMENT) > 2) ) { ?>
        <tr>
          <th>
            <img class="inline-middle" src="img/flag_downtime.png" />
            <span class="inline-middle" >(<?php echo $DOWNCOMMENT[0]?>)</span>
          </th>
          <td><?php echo $DOWNCOMMENT[2]?> (<?php echo lang($MYLANG, 'end') ?> <?php echo $DOWNCOMMENT[1] ?>)</td>
        </tr>
      <?php } ?>
      
      <?php if ( ($STATUSPOPIN['notifystatus'] == 1) && (isset($_SESSION['STATUS']['notifystatus'])) && (!$NOTIF && count($NOTIFCOMMENT) > 1) ) { ?>
        <tr>
          <th>
            <img class="inline-middle" src="img/flag_notify.png" />
            <span class="inline-middle" >(<?php echo $NOTIFCOMMENT[0]?>)</span>
          </th>
          <td><?php echo $NOTIFCOMMENT[1]?></td>
        </tr>
      <?php } ?>

      <?php if ( ($STATUSPOPIN['disacheck'] == 1) && (isset($_SESSION['STATUS']['disacheck'])) && ($DISABLECHECK == 0) ) { ?>
        <tr>
          <th>
            <img class="inline-middle" src="img/flag_disablecheck.png" />
            <span class="inline-middle" >(<?php echo $DISABLECHECKCOMMENT[0]?>)</span>
          </th>
          <td><?php echo $DISABLECHECKCOMMENT[1]?></td>
        </tr>
      <?php } ?>
      
      <?php if ( ($STATUSPOPIN['comment'] == 1) && (isset($_SESSION['STATUS']['comment'])) && (count($COMMENT) > 1) ) { ?>
        <tr>
          <th>
            <img class="inline-middle" src="img/flag_comment.gif" />
            <span class="inline-middle">(<?php echo $COMMENT[0]?>)</span>
          </th>
          <td><?php echo $COMMENT[1]?></td>
        </tr>
      <?php } ?>
      
      <tr>
        <th><?php echo ucfirst(lang($MYLANG, 'group')) ?></th>
        <td><?php echo $GROUPS ?></td>
      </tr>
      <?php } ?>
      <?php if ( ($STATUSPOPIN['history'] == 1) && (isset($_SESSION['STATUS']['history'])) && ( count($_SESSION['HISTORY']) > 0 ) ) { ?>
      <tr>
        <th><?php echo ucfirst(lang($MYLANG, 'history')) ?></th>
        <td><a href="#" onClick="return pop('history.php?id=<?php echo $id?>&type=<?php echo $type ?>&host=<?php echo $HOSTNAME ?>&svc=<?php echo $SERVICE ?>', 'history-<?php echo $id?>', '<?php echo $HISTORY_POPUP_WIDTH ?>', '<?php echo $HISTORY_POPUP_HEIGHT ?>')"><?php echo ucfirst(lang($MYLANG, 'show_history')) ?></a></td>
      </tr>
      <?php } ?>
      
      <?php if ( ($STATUSPOPIN['graph'] == 1) && ( isset($_SESSION['STATUS']['graph']) ) ) $g = get_graph('status', $HOSTNAME, $SERVICE); ?>
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
