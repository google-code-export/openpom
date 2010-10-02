<?php
session_start();
if (!isset($_SESSION['USER'])) die();
require_once("config.php");
require_once("lang.php");
$pat = '/[a-zA-Z0-9._-]{1,100}/' ;
if ( (!preg_match($pat, $_GET['host'])) || (!preg_match($pat, $_GET['svc'])) )
  die();
$HOST = $_GET['host'];
$SVC  = $_GET['svc'];
if ($SVC == "--host--") $service = "";
else $service = "&service=".$SVC;
if ( (!isset($_GET['period'])) || (!preg_match('/(today|week|month|year)/',$_GET['period'])) )
  $period = 'week';
else $period = $_GET['period'];
?>
<!DCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
  <head>
    <title><?php echo $HOST." ".$SVC; ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta http-equiv="CACHE-CONTROL" content="NO-CACHE" />
    <meta http-equiv="PRAGMA" content="NO-CACHE" />                                       
    <link rel="StyleSheet" href="style.css" type="text/css" />
    <script type='text/javascript' src='js/func.js'></script>
  </head>
  <body>
    <div><center>
      <a class="graph" href="graph.php?&host=<?php echo $HOST?>&svc=<?php echo $SVC?>&period=today"><?php echo ucfirst(lang($MYLANG, 'today'))?></a>
      <a class="graph" href="graph.php?&host=<?php echo $HOST?>&svc=<?php echo $SVC?>&period=week"><?php echo ucfirst(lang($MYLANG, 'week'))?></a>
      <a class="graph" href="graph.php?&host=<?php echo $HOST?>&svc=<?php echo $SVC?>&period=month"><?php echo ucfirst(lang($MYLANG, 'month'))?></a>
      <a class="graph" href="graph.php?&host=<?php echo $HOST?>&svc=<?php echo $SVC?>&period=year"><?php echo ucfirst(lang($MYLANG, 'year'))?></a>
    </center></div>
    <?php if ($period == 'today') { ?>
    <img src="<?php echo $GRAPH_POPUP?>?createimage&host=<?php echo $HOST?><?php echo $service?>&backtrack=4&zoom=4&t1=<?php echo strtotime("-1 day")?>&t2=<?php echo time()?>" border="0" />
    <?php } else if ($period == 'week') { ?>
    <img src="<?php echo $GRAPH_POPUP?>?createimage&host=<?php echo $HOST?><?php echo $service?>&backtrack=4&zoom=4&t1=<?php echo strtotime("-1 week")?>&t2=<?php echo time()?>" border="0" />
    <?php } else if ($period == 'month') { ?>
    <img src="<?php echo $GRAPH_POPUP?>?createimage&host=<?php echo $HOST?><?php echo $service?>&backtrack=4&zoom=4&t1=<?php echo strtotime("-1 month")?>&t2=<?php echo time()?>" border="0" />
    <?php } else if ($period == 'year') { ?>
    <img src="<?php echo $GRAPH_POPUP?>?createimage&host=<?php echo $HOST?><?php echo $service?>&backtrack=4&zoom=4&t1=<?php echo strtotime("-1 year")?>&t2=<?php echo time()?>" border="0" />
    <?php } ?>
  </body>
</html>
