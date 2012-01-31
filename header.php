<?php
/*
  OpenPOM
 
  Copyright 2010, Exosec
  Licensed under GPL Version 2.
  http://www.gnu.org/licenses/
*/
 
  if (basename($_SERVER['SCRIPT_NAME']) != "index.php") die() ; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">                                                                  
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
  <head>
    <title>
      <?php echo $CODENAME?> E=<?php echo $hit_critical?> W=<?php echo $hit_warning?> U=<?php echo $hit_unknown?> T=<?php echo $hit_any?>
    </title>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $ENCODING ?>" />
    <meta http-equiv="CACHE-CONTROL" content="NO-CACHE" />
    <meta http-equiv="PRAGMA" content="NO-CACHE" /> 
    
    <link rel="stylesheet" type="text/css" href="style.css" />
    <!--[if lte IE 8]>
        <link rel="stylesheet" type="text/css" href="style-ie.css" />
    <![endif]-->

    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="js/jquery.colorbox.js"></script>
    <script type="text/javascript" src="js/lib.js"></script>
    <script type="text/javascript">
      var current_data_displayed = null;
      var cache    = new Array();
      var mytime   = <?php echo intval($REFRESHTIME) ?>;
      var filter   = "<?php echo $FILTER ?>";
      var cur_id;
      var it = null;
      var filtering_has_focus = false;
      var lastChecked = null;
      var accept_action = true;
      var popin_initial_width = <?php echo intval($POPIN_INITIAL_WIDTH) ?>;
      
      var jpopin = $('\
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
        'z-index': '88888'
      });
      
      document.onclick = hide_data;
    </script>
  </head>
  <body<?php if (isset($_GET['monitor'])) echo ' class="monitor"'; ?>>
    <?php if ($_SESSION['FRAME'] == 1) { ?>
    <table width="100%" height="100%" class="frame" id="type_<?php echo $framecolor?>">
      <tr>
        <td valign="top">
    <?php } ?>

