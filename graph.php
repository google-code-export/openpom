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
require_once("utils.php");
require_once("lang.php");
$pat = '/[' . preg_quote($ILLEGAL_CHAR) . ']/' ;


/* query string: host */
if (isset($_GET['host']) && !preg_match($pat, $_GET['host'])) {
  $host = $_GET['host'];
} else {
  die('Call error: host');
}

/* query string: service */
if (isset($_GET['service']) && !preg_match($pat, $_GET['service'])) {
  $service = $_GET['service'];
} else {
  $service = '';
}

/* graph image */
$graph = get_graph('status', $host, $service);
if (empty($graph)) {
  die('Error: no graph target');
}

/* query string: period */
if (isset($_GET['period'])) {
  $period = $_GET['period'];
} else {
  $period = $GRAPH_POPUP_DEFAULT;
}
if (!in_array($period, array_keys($GRAPH_POPUP_PERIODS))) {
  die('Call error: period');
}

/* add start/end parameters to the query string of image URI */
$start = strtotime($GRAPH_POPUP_PERIODS[$period][0]);
$end = strtotime($GRAPH_POPUP_PERIODS[$period][1]);

if ($start === false || $start == -1) {
  die('Error: bad period start string');
}
if ($end === false || $end == -1) {
  die('Error: bad period end string');
}
if ($start > $end) {
  die('Error: start date is after end date');
}

$graph .= (strpos($graph, '?') === false ? '?' : '&')
  . $GRAPH_POPUP_PARAM_START . '=' . $start . '&'
  . $GRAPH_POPUP_PARAM_END . '=' . $end;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
  <head>
  <title><?php echo $host; if (!empty($service)) { echo " &#160;&mdash;&#160; ".$service; } ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta http-equiv="CACHE-CONTROL" content="NO-CACHE" />
    <meta http-equiv="PRAGMA" content="NO-CACHE" />                                       
    <link rel="StyleSheet" href="style.css" type="text/css" />
    <style type="text/css">
    div { margin-top: 20px; margin-bottom: 30px; }
    img { border: 1px solid #8A8A8A;        }
    </style>
  </head>
  <body>
    <center>
      <div>
        <?php
          $top = '';
          $uri = $_SERVER['PHP_SELF'] 
            . '?host=' . $host 
            . '&service=' . $service;

          foreach (array_keys($GRAPH_POPUP_PERIODS) as $p) {
            if (!empty($top)) {
              $top .= '&#160;&#160;';
            }
            $top .= '[ <a href="' . $uri . '&period=' . $p . '">'
              . ($p == $period ? '<b>' . $p . '</b>' : $p)
              . '</a> ]';
          }

          echo $top;
        ?>
      </div>
      
      <img src="<?php echo $graph ?>" />
    </center>
  </body>
</html>
