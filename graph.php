<?php
/*
  OpenPom $Revision$
  $HeadURL$
 
  Copyright 2010, Exosec
  Licensed under GPL Version 2.
  http://www.gnu.org/licenses/
 
  $Date$
*/

session_start();
if (!isset($_SESSION['USER'])) die();
require_once("config.php");
require_once("utils.php");
require_once("lang.php");
$pat = '/^[a-zA-Z0-9._-]{1,100}$/' ;



/* query string: host */
if (isset($_GET['host']) && preg_match($pat, $_GET['host'])) {
  $host = $_GET['host'];
} else {
  die('Call error: host');
}

/* query string: svc */
if (isset($_GET['svc'])) {
  if (preg_match($pat, $_GET['svc'])) {
    $svc = $_GET['svc'];
  } else {
    die('Call error: svc');
  }
} else {
  $svc = null;
}

/* graph producer */
$graph = get_graph('status', $host, $svc);
if (is_null($graph)) {
  die('Error: no graph target, configure GRAPH_STATUS variable');
}

/* query string: period */
if (isset($_GET['period'])) {
  if (!preg_match('/(day|week|month|year)/', $_GET['period'])) {
    die('Call error: period');
  }
} else {
  $_GET['period'] = 'day';
}
$period = $_GET['period'];
$graph .= '&t1=' . strtotime('-1 ' . $period);
$graph .= '&t2=' . time();

?>
<!DCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
  <head>
  <title><?php echo $host; if (!is_null($svc)) { echo " &#160;&mdash;&#160; ".$svc; } ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta http-equiv="CACHE-CONTROL" content="NO-CACHE" />
    <meta http-equiv="PRAGMA" content="NO-CACHE" />                                       
    <link rel="StyleSheet" href="style.css" type="text/css" />
    <style type="text/css">
    div {
      margin: 2px;
      margin-bottom: 20px;
    }
    
    img {
      border: 1px solid #8A8A8A;
    }
    
    a.<?= $period ?> {
      font-weight: bold;
    }
    </style>
  </head>
  <body>
    <center>
      <div>
        <?php 
          $uri = 'graph.php?host=' . $host;
          if (!is_null($svc)) {
            $uri .= '&svc=' . $svc;
          }
        ?>
        
        [ <a class="day" href="<?= $uri ?>&period=day"><?= ucfirst(lang($MYLANG, 'today')) ?></a> ]&#160;&#160; 
        [ <a class="week" href="<?= $uri ?>&period=week"><?= ucfirst(lang($MYLANG, 'week')) ?></a> ]&#160;&#160;
        [ <a class="month" href="<?= $uri ?>&period=month"><?= ucfirst(lang($MYLANG, 'month')) ?></a> ]&#160;&#160;
        [ <a class="year" href="<?= $uri ?>&period=year"><?= ucfirst(lang($MYLANG, 'year')) ?></a> ]
      </div>
      
      <img src="<?= $graph ?>" />
    </center>
  </body>
</html>
