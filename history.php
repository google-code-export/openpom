<?php
/*
  OpenPOM

  Copyright 2010, Exosec
  Licensed under GPL Version 2.
  http://www.gnu.org/licenses/
*/

require_once("config.php");
session_name( str_replace(" ", "", $CODENAME) ) ;
session_start();
if (!isset($_SESSION['USER'])) die();
require_once("lang.php");
require_once("query-history.php");
require_once("utils.php");
special_char();

if ( (!isset($_GET['id'])) || (!is_numeric($_GET['id'])) ||
     (!isset($_GET['type'])) || (!isset($_GET['host'])) || (!isset($_GET['svc']))
   )
  die('bad arguments');

$id     = $_GET['id'];
$type   = $_GET['type'] ;
$host   = $_GET['host'] ;
$svc    = $_GET['svc'] ;
$MY_GET = "&id=".$id."&type=".$type."&host=".$host."&svc=".$svc ;

if ( ! isset($_SESSION['STEP']) )  $MY_STEP = 100 ;
else $MY_STEP = $_SESSION['STEP'] ;

if (isset($_GET['first']) &&
    is_numeric($_GET['first']) &&
    $_GET['first'] >= 0)
  $MY_FIRST = $_GET['first'];
else
  $MY_FIRST = 0;

if (!($dbconn = mysql_connect($SQL_HOST, $SQL_USER, $SQL_PASSWD)))
  die('cannot connect to db');
if (!mysql_select_db($SQL_DB, $dbconn))
  die('cannot select db');

$quoted_id = mysql_real_escape_string($id, $dbconn);
$query = str_replace('define_my_id',    $quoted_id, $QUERY_HISTORY[$type]);
$query = str_replace('define_my_first', $MY_FIRST, $query) ;
$query = str_replace('define_my_step',  $MY_STEP, $query) ;
$query = str_replace('define_my_submax', $MY_FIRST + $MY_STEP, $query) ;

foreach ($HISTORY AS $h => $v) {
  if ( isset($_SESSION['HISTORY'][$h]) )
    $query = str_replace('define_my_'.$h, $v, $query) ;
  else
    $query = str_replace('define_my_'.$h, 0, $query) ;
}

//echo "<pre>" ;
//echo $query ;
//echo "</pre>" ;

if (!($rep = mysql_query($query, $dbconn)))
  die('query failed: ' . mysql_error($dbconn));
$nb_rows = mysql_num_rows($rep) ;
$MY_LAST = $MY_FIRST + $nb_rows;

if ($nb_rows == $MY_STEP)
  $next = '<span class="icon-btn icon-next"
                 onclick="window.location.href=\'?'.$MY_GET.'&first='.($MY_FIRST + $MY_STEP).'\'"
                 title="'.ucfirst(lang($MYLANG, 'next')).'"></span>';
else
    $next = '';

if ($MY_FIRST > 0)
  $prev = '<span class="icon-btn icon-prev"
                 onclick="window.location.href=\'?'.$MY_GET.'&first='.($MY_FIRST - $MY_STEP).'\'"
                 title="'.ucfirst(lang($MYLANG, 'prev')).'"></span>';
else
  $prev = '' ;

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
      <h1><?php echo ucfirst(lang($MYLANG, 'historyfor'))." ".$host." : ".$svc ; ?></h1>
      <div style="display: inline; text-align: left; margin:0; padding: 0;"><?php echo $prev ; ?> &nbsp; </div>
      <?php echo ($MY_FIRST + 1) . " - " . $MY_LAST; ?>
      <div style="display: inline; text-align: right; margin:0; padding: 0;"> &nbsp; <?php echo $next ; ?></div>
    </center>
    &nbsp; <table id='alert' class="history" border=1>
<?php
if ($nb_rows < 1) {
  mysql_close($dbconn) ;
  echo "N/A" ;
  echo "  </body>" ;
  echo "</html>" ;
  die() ;
}

$first_line = 0 ;
while ( $row = mysql_fetch_array($rep, MYSQL_ASSOC) ) {
  //print_r($row) ;
  if ($first_line == 0) {
    echo "<tr>" ;
    foreach ($row AS $kh => $vh) {
      if ( ($kh == "state_type") || ($kh == "color") ) continue ;
      if ($kh == 'entry_time')
        echo '<th><span class="col_sort_down">'.ucfirst(lang($MYLANG, $kh)).'</span></th>';
      else
        echo '<th class="'.$kh.'"><span class="col_no_sort">'.ucfirst(lang($MYLANG, $kh)).'</span></th>';
    }
    echo "</tr>" ;
    $first_line = 1 ;
  } //end foreach
  $class = "" ;
  $tds = array() ;

  foreach ($row AS $k => $v) {
    if ($k == "author_name") {
        $v = trim($v, "()");
        if (strtolower($v) == 'nagios process')
            $v = 'nagios';
    }

    if ($k == "color") {
      if ( ($type == "svc") && ($v == 2) ) $class = "red";
      else if ( ($type == "svc") && ($v == 1) ) $class = "yellow";
      else if ( ($type == "svc") && ($v == 3) ) $class = "orange";
      else if ($type == "svc") $class = "green" ;
      else if ( ($type == "host") && ( ($v == 2) || ($v == 1) ) ) $class = "red";
      else if ($type == "host") $class = "green" ;
      else $class = "green" ;
      continue ;
    }
    else if ($k == "state_type") {
      if ($v == 0) $class .= " soft" ;
      continue ;
    }
    else if ($k == "type") {
      if ( ($v == 'ack') || ($v == "comment") )
        $v = "<img src='img/flag_".$v.".gif' width='12px' height='12px' />" ;
      else if ($v == "downtime")
        $v = "<img src='img/flag_".$v.".png' width='12px' height='12px' />" ;
      else if ($v == "notify")
        $v = "<img src='img/disa_notif.png' width='12px' height='12px' />" ;
      else if ($v == 'statehistory') {
        if      (substr($class, 0, 3) == "red") $v = "<img src='img/flag_critical.png' width='12px' height='12px' />" ;
        else if (substr($class, 0, 6) == "yellow") $v = "<img src='img/flag_warning.png' width='12px' height='12px' />" ;
        else if (substr($class, 0, 6) == "orange") $v = "<img src='img/flag_unknown.png' width='12px' height='12px' />" ;
        else if (substr($class, 0, 5) == "green") $v = "<img src='img/flag_ok.png' width='12px' height='12px' />" ;
        else $v = "" ;
      }
      else if ($v == "flapping")
        $v = "<img src='img/flapping.gif' width='12px' height='12px' />" ;
      else $v = "" ;
    }
    else if ( ($k == "outputstatus") && (preg_match('/^(~[^:]+):(.*)$/', $v, $cap)) ) {
      $v = $cap[2] ;
      if ($cap[1] == "~disable")
        $tds[0] = "<td>
          <img src='img/flag_notify.png' width='12px' height='12px' /></td>" ;
    }
    $tds[] = '<td class="'.$k.'">'.$v.'</td>';
  } //end foreach
  echo "<tr class='".$class."'>" ;
  foreach ($tds AS $td) echo $td ;
  echo "</tr>" ;
} //end while

echo "</table>" ;

mysql_free_result($rep) ;
mysql_close($dbconn) ;
?>
    <br><center>
      <div style="display: inline; text-align: left; margin:0; padding: 0;"><?php echo $prev ; ?> &nbsp; </div>
      <?php echo ($MY_FIRST + 1) . " - " . $MY_LAST; ?>
      <div style="display: inline; text-align: right; margin:0; padding: 0;"> &nbsp; <?php echo $next ; ?></div>
    </center>
    <br>
  </body>
</html>
