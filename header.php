<?php
/*
  OpenPom $Revision$
  $HeadURL$
 
  Copyright 2010, Exosec
  Licensed under GPL Version 2.
  http://www.gnu.org/licenses/
 
  $Date$
*/
 
  if (basename($_SERVER['SCRIPT_NAME']) != "index.php") die() ; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">                                                                  
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
  <head>
    <title>
      <?php echo $CODENAME?> E=<?php echo $hit_critical?> W=<?php echo $hit_warning?> U=<?php echo $hit_unknown?> T=<?php echo $hit_any?>
    </title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta http-equiv="CACHE-CONTROL" content="NO-CACHE" />
    <meta http-equiv="PRAGMA" content="NO-CACHE" /> 
    <link rel="stylesheet" type="text/css" href="style.css" />
    <script type="text/javascript" src="js/jquery-1.4.4.min.js"></script>
    <script type="text/javascript" src="js/jquery.colorbox.js"></script>
    <script type='text/javascript' src="js/XMLHttpRequest-IE.js"></script>
    <script type='text/javascript' src="js/func.js"></script>
    <script type="text/javascript" src="js/scripts.js"></script>
    <script type="text/javascript">
      var current_data_displayed = null;
      var cache    = new Array();
      var refresh; 
      var mytime   = <?php echo ($REFRESHTIME+1)?>;
      var filter   = "<?php echo $FILTER?>";
      var cur_id;
      var it = null;
      var filtering_has_focus = false;
      var lastChecked = null;
      var accept_action = true;
      
      var popup = $('\
        <div>\
          <img style="margin: 20px;" src="img/loading.gif" />\
        </div>\
      ').css({
        'display': 'none',
        'background-color': 'white', 
        'position': 'fixed', 
        'top': '10px',
        'right': '10px',
        'border': '1px solid #666',
        'border-radius': '2px',
        '-moz-border-radius': '2px',
        '-khtml-border-radius': '2px',
        '-webkit-border-radius': '2px',
        /*'behavior': 'url(\'PIE.htc\')',*/
        'z-index': '88888'
      });
      
      document.onclick = hide_data;
    </script>
  </head>
  <body>
    <?php if ($_SESSION['FRAME'] == 1) { ?>
    <table width="100%" height="100%" class="frame" id="type_<?php echo $framecolor?>">
      <tr>
        <td valign="top">
    <?php } ?>

