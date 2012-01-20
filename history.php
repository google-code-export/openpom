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
require_once("query-history.php");
require_once("utils.php");
special_char();

if ( (!isset($_GET['id'])) || (!is_numeric($_GET['id'])) ||
     (!isset($_GET['type'])) || (!isset($_GET['host'])) || (!isset($_GET['svc'])) 
   ) 
  die('bad arguments');

$id   = $_GET['id'];
$type = $_GET['type'] ;
$host = $_GET['host'] ;
$svc  = $_GET['svc'] ;

$history = array( 'ack', 'down', 'com', 'notify', 'state', 'flap' ) ;

if (!($dbconn = mysql_connect($SQL_HOST, $SQL_USER, $SQL_PASSWD))) 
  die('cannot connect to db');
if (!mysql_select_db($SQL_DB, $dbconn)) 
  die('cannot select db');
$quoted_id = mysql_real_escape_string($id, $dbconn);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
  <head>
    <title><?php echo ucfirst(lang($MYLANG, 'history')) ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta http-equiv="CACHE-CONTROL" content="NO-CACHE" />
    <meta http-equiv="PRAGMA" content="NO-CACHE" />                                       
    <link rel="stylesheet" type="text/css" href="style.css" />
    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="js/lib.js"></script>
  </head>
  <body>
<?php
echo "<center><h1>".ucfirst(lang($MYLANG, 'historyfor'))." ".$host." : ".$svc."</center></h1>" ;
echo "<table border='0' width='100%'><tr>" ;
foreach ($history AS $hist) {
  echo "<th> <a href='#".$hist."'>".ucfirst(lang($MYLANG, $hist))."</a> </th>" ;
}
echo "</tr></table>" ;

foreach ($history AS $hist) {
  /* find query */
  if (!isset($QUERY_HISTORY[$hist][$type])) {
    die('no query');
  }
  /*  SQL */
  $query = str_replace('define_my_id', $quoted_id, $QUERY_HISTORY[$hist][$type]);
  //echo $query."<br>" ;
  /* perform query */
  if (!($rep = mysql_query($query, $dbconn)))
    die('query failed: ' . mysql_error($dbconn));
  if (!($head = mysql_fetch_array($rep, MYSQL_ASSOC))) {
    echo "<br>".ucfirst(lang($MYLANG, 'nohistory'))." ".lang($MYLANG, $hist) ;
    continue ;
  }
  else {
    echo "<br><center><b><a name='".$hist."'>".ucfirst(lang($MYLANG, $hist))."</a></b></center><table id='alert' border=1><tr>" ;
    $first_line = "" ;
    $class = "" ;
    foreach ($head AS $k => $v) {
      if ( ($k == "color") && (is_numeric($v)) ) { 
        if ( ($type == "svc") && ($v == 2) ) $class = "red";
        else if ( ($type == "svc") && ($v == 1) ) $class = "yellow";
        else if ( ($type == "svc") && ($v == 3) ) $class = "orange";
        else if ($type == "svc") $class = "green" ;
        else if ( ($type == "host") && ( ($v == 2) || ($v == 1) ) ) $class = "red";
        else if ($type == "host") $class = "green" ;
        else $class = "" ;
        continue ;
      }
      echo "<th class='white'>".ucfirst(lang($MYLANG, $k))."</th>" ;
      $first_line .= "<td class='".$class."'>".$v."</td>" ;
    }
    echo "</tr><tr>".$first_line."</tr>" ;
    while ( $row = mysql_fetch_array($rep, MYSQL_ASSOC) ) {
      $class = "" ;
      echo "<tr>" ;
      foreach ($row AS $k => $v) {
        if ( ($k == "color") && (is_numeric($v)) ) { 
          if ( ($type == "svc") && ($v == 2) ) $class = "red";
          else if ( ($type == "svc") && ($v == 1) ) $class = "yellow";
          else if ( ($type == "svc") && ($v == 3) ) $class = "orange";
          else if ($type == "svc") $class = "green" ;
          else if ( ($type == "host") && ( ($v == 2) || ($v == 1) ) ) $class = "red";
          else if ($type == "host") $class = "green" ;
          else $class = "" ;
          continue ;
        }
        echo "<td class='".$class."'>".$v."</td>" ;
      } //end foreach
      echo "</tr>" ;
    } //end while
    echo "</table>" ;
    mysql_free_result($rep) ;
  } //end else
} //end foreach
mysql_close($dbconn) ;
?>
    <br>
  </body>
</html>
