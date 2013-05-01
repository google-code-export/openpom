<?php
/*
  OpenPOM
 
  Copyright 2010, Exosec
  Licensed under GPL Version 2.
  http://www.gnu.org/licenses/
*/

require_once("config.php");
session_name($CODENAME);
session_start();
if (!isset($_SESSION['USER'])) die();
require_once("lang.php");
require_once("query-logs.php");
require_once("utils.php");
special_char();

if ( ! isset($_SESSION['STEP']) )  $MY_STEP = 100 ;
else $MY_STEP = $_SESSION['STEP'] ;
if ( ( ! isset($_GET['prev']) ) && (! isset($_GET['next'])) )  $MY_FIRST = 0 ;
else if ( (isset($_GET['next'])) && (is_numeric($_GET['next'])) ) $MY_FIRST = $_GET['next'] ;
else if ( (isset($_GET['prev'])) && (is_numeric($_GET['prev'])) ) $MY_FIRST = $_GET['prev'] ;

if (!($dbconn = mysql_connect($SQL_HOST, $SQL_USER, $SQL_PASSWD)))
  die('cannot connect to db');
if (!mysql_select_db($SQL_DB, $dbconn))
  die('cannot select db');
$query = str_replace('define_my_first', $MY_FIRST, $QUERY_LOGS) ;
$query = str_replace('define_my_step',  $MY_STEP, $query) ;
$query = str_replace('define_my_submax',  $MY_FIRST + $MY_STEP,  $query) ;

$first_line = 0 ;
if (!($rep = mysql_query($query, $dbconn)))
  die('query failed: ' . mysql_error($dbconn));
$array_total_rows = mysql_fetch_row( mysql_query( "$QUERY_LOGS_TOTAL", $dbconn ) );
$total_rows       = $array_total_rows[0] ;
$nb_rows = mysql_num_rows($rep) ;

$cnext = $MY_FIRST + $MY_STEP ;
if ( ($nb_rows < $total_rows) && ($cnext < $total_rows) )
  $next = '<span class="icon-btn icon-next"
          onclick="window.location.href=\'?next='.$cnext.'\'"
                  title="'.ucfirst(lang($MYLANG, 'next')).'"></span>' ;
else $next = "" ;
$cprev = $MY_FIRST - $MY_STEP ;
if ($cprev >= 0)
  $prev = '<span class="icon-btn icon-prev"
          onclick="window.location.href=\'?prev='.$cprev.'\'"
                  title="'.ucfirst(lang($MYLANG, 'prev')).'"></span>' ;
else $prev = "" ;

if ( ($total_rows % $MY_STEP) == 0 )
$nb_pages = (int) ($total_rows / $MY_STEP) ;
else
$nb_pages = (int) ($total_rows / $MY_STEP) + 1 ;
$cur_page = (int) ($MY_FIRST / $MY_STEP) + 1 ;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
  <head>
    <title><?php echo ucfirst(lang($MYLANG, 'history')) ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $ENCODING ?>" />
    <meta http-equiv="CACHE-CONTROL" content="NO-CACHE" />
    <meta http-equiv="PRAGMA" content="NO-CACHE" />                                       
    <link rel="stylesheet" type="text/css" href="style.css" />
    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="js/lib.js"></script>
  </head>
  <body>
    <center>
      <h1>Nagios <?php echo ucfirst(lang($MYLANG, 'history')); ?></h1>
      <div style="display: inline; text-align: left; margin:0; padding: 0;"><?php echo $prev ; ?> &nbsp; </div>
      <?php if ($nb_pages > 1) echo $cur_page." / ".$nb_pages ; ?>
      <div style="display: inline; text-align: right; margin:0; padding: 0;"> &nbsp; <?php echo $next ; ?></div>
    </center>
    &nbsp; <table id='alert' class="logs" border=1>
<?php
if ($nb_rows < 1) {
  mysql_close($dbconn) ;
  echo "N/A" ;
  echo "  </body>" ;
  echo "</html>" ;
  die() ;
}

while ( $row = mysql_fetch_array($rep, MYSQL_ASSOC) ) {
  if ($first_line == 0) {
    echo "<tr>" ;
    foreach ($row AS $kh => $vh) {
      if ($kh == 'entry_time')
        echo '<th class="'.$kh.'"><span class="col_sort_down">'.ucfirst(lang($MYLANG, $kh)).'</span></th>';
      else
        echo '<th class="'.$kh.'"><span class="col_no_sort">'.ucfirst(lang($MYLANG, $kh)).'</span></th>';
    }
    echo "</tr>" ;
    $first_line = 1 ;
  } //end foreach
  $tds = array() ;
  foreach ($row AS $k => $v) {
    if ($k == "type") {
      if      ( ($v == 2) || ($v == 6) || ($v == 64) )
        $v = "<img src='img/info.png' width='12px' height='12px' />" ;
      else if ( $v == 100 ) 
        $v = "<img src='img/start.gif' width='12px' height='12px' />" ;
      else if ( $v == 102 ) 
        $v = "<img src='img/restart.gif' width='12px' height='12px' />" ;
      else if ( $v == 103 ) 
        $v = "<img src='img/stop.gif' width='12px' height='12px' />" ;
      else if ($v == 512) 
        $v = "<img src='img/command.png' width='12px' height='12px' />" ;
      else 
        $v = "<img src='img/info.png' width='12px' height='12px' />" ;
    }
    if ($k == "outputstatus") {
      $tmptd = "" ;
      if      ( preg_match('/^EXTERNAL COMMAND: /' ,$v) )
        $tmptd .= "<img src='img/command.png' width='12px' height='12px' />" ;
      if ( preg_match('/ FLAPPING ALERT: /' ,$v) )
        $tmptd .= "<img src='img/flapping.gif' width='12px' height='12px' />" ;
      if ( preg_match('/(SCHEDULE_(HOST|SVC)_DOWNTIME| DOWNTIME )/' ,$v) )
        $tmptd .= "<img src='img/flag_downtime.png' width='12px' height='12px' />" ;
      if ( preg_match('/ACKNOWLEDGE_/' ,$v) )
        $tmptd .= "<img src='img/flag_ack.gif' width='12px' height='12px' />" ;
      if ( preg_match('/DISABLE_(SVC|HOST)_NOTIFICATIONS/' ,$v) )
        $tmptd .= "<img src='img/flag_notify.png' width='12px' height='12px' />" ;
      if ( preg_match('/ADD_(SVC|HOST)_COMMENT/' ,$v) )
        $tmptd .= "<img src='img/flag_comment.gif' width='12px' height='12px' />" ;
      if ( preg_match('/DISABLE_(SVC|HOST)_CHECK/' ,$v) )
        $tmptd .= "<img src='img/flag_no_active.png' width='12px' height='12px' />" ;
      if ( preg_match('/DISABLE_PASSIVE_(SVC|HOST)_CHECKS/' ,$v) )
        $tmptd .= "<img src='img/flag_no_passive.png' width='12px' height='12px' />" ;
      if ( preg_match('/(HOST|SERVICE) NOTIFICATION: /' ,$v) )
        $tmptd .= "<img src='img/disa_notif.png' width='12px' height='12px' />" ;
      if ( preg_match('/CUSTOM_(HOST|SVC)_NOTIFICATION;/' ,$v) )
        $tmptd .= "<img src='img/disa_notif.png' width='12px' height='12px' />" ;
      if      ( preg_match('/[ ]?HOST /' ,$v) )
        $tmptd .= "<img src='img/flag_host.png' width='12px' height='12px' />" ;
      else if ( preg_match('/[ ]?SERVICE /' ,$v) )
        $tmptd .= "<img src='img/flag_svc.png' width='12px' height='12px' />" ;
      if      ( preg_match('/[; (]?CRITICAL[) ;]{1}/' ,$v) )
        $tmptd .= "<img src='img/flag_critical.png' width='12px' height='12px' />" ;
      else if ( preg_match('/[; (]?WARNING[) ;]{1}/' ,$v) )
        $tmptd .= "<img src='img/flag_warning.png' width='12px' height='12px' />" ;
      else if ( preg_match('/[; (]?UNKNOWN[) ;]{1}/' ,$v) )
        $tmptd .= "<img src='img/flag_unknown.png' width='12px' height='12px' />" ;
      else if ( preg_match('/[; (]?(OK|UP)[) ;]{1}/' ,$v) )
        $tmptd .= "<img src='img/flag_ok.png' width='12px' height='12px' />" ;
      if ( preg_match('/(Caught SIGHUP, restarting)/', $v) )
        $tmptd .= "<img src='img/restart.gif' width='12px' height='12px' />" ;
      else if ( preg_match('/(Successfully shutdown|Caught SIGTERM, shutting down)/', $v) )
        $tmptd .= "<img src='img/stop.gif' width='12px' height='12px' />" ;
      if ($tmptd != "") $tds[0] = "<td>".$tmptd."</td>" ;
      $v = htmlspecialchars($v);
    }
    $tds[] = '<td class="'.$k.'">'.$v.'</td>';
  }
  echo "<tr class='showlog'>" ;
  foreach ($tds AS $td) echo $td ;
  echo "</tr>" ;
}
echo "</table>" ;

mysql_free_result($rep) ;
mysql_close($dbconn) ;
?>
    <br><center>
      <div style="display: inline; text-align: left; margin:0; padding: 0;"><?php echo $prev ; ?> &nbsp; </div>
      <?php if ($nb_pages > 1) echo $cur_page." / ".$nb_pages ; ?>
      <div style="display: inline; text-align: right; margin:0; padding: 0;"> &nbsp; <?php echo $next ; ?></div>
    </center>
    <br>
  </body>
</html>

