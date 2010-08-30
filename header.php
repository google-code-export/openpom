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
 
  if (basename($_SERVER['SCRIPT_NAME']) != "index.php") die() ; ?>
<!DCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">                                                                  
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
  <head>
    <title>
      <?=$CODENAME?> E=<?=$hit_critical?> W=<?=$hit_warning?> U=<?=$hit_unknown?> T=<?=$hit_any?>
    </title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta http-equiv="CACHE-CONTROL" content="NO-CACHE" />
    <meta http-equiv="PRAGMA" content="NO-CACHE" /> 
    <link rel="StyleSheet" href="style.css" type="text/css" />
    <script type='text/javascript' src='js/XMLHttpRequest-IE.js'></script>
    <script type='text/javascript' src='js/func.js'></script>
    <script>
      var current_data_displayed;
      var cache    = new Array();
      var popup;
      var refresh; 
      var mytime   = <?=($REFRESHTIME+1)?>;
      var filter   = "<?=$FILTER?>";
    </script>
  </head>
  <body>
    <div id="popup"><img src="img/ajax_loading.gif" /></div>
    <script>
      popup = document.getElementById("popup");
      popup.style.visibility = "hidden";                                                  
      document.onmousemove = WhereMouse;
    </script>
    <? if ($_SESSION['FRAME'] == 1) { ?>
    <table width="100%" height="100%" class="frame" id="type_<?=$framecolor?>">
      <tr>
        <td valign="top">
    <? } ?>

