<?php
/*
  OpenPOM
 
  Copyright 2010, Exosec
  Licensed under GPL Version 2.
  http://www.gnu.org/licenses/
*/


if (basename($_SERVER['SCRIPT_NAME']) != "index.php") die();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
    <head>
        <title>
            <?php echo $CODENAME?>
            C=<?php echo $glob_critical?>
            W=<?php echo $glob_warning?>
            U=<?php echo $glob_unknown?>
            O=<?php echo $glob_ok ?>
        </title>

        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $ENCODING ?>" />
        <meta http-equiv="Cache-Control" content="no-cache" />
        <meta http-equiv="Pragma" content="no-cache" />

        <link rel="stylesheet" type="text/css" href="style.css" />

        <!--[if lte IE 8]>
            <link rel="stylesheet" type="text/css" href="style-lte-ie8.css" />
        <![endif]-->

        <!--[if lte IE 7]>
            <link rel="stylesheet" type="text/css" href="style-lte-ie7.css" />
        <![endif]-->

        <script type="text/javascript" src="js/jquery.min.js"></script>
        <script type="text/javascript" src="js/jquery.colorbox.js"></script>
        <script type="text/javascript" src="js/lib.js"></script>
        <script type="text/javascript">
            var body_class = [];

<?php if (isset($_GET['monitor'])) { ?>
            body_class.push('monitor');
<?php } ?>

            if (window != window.top)
                body_class.push('framed');

            var current_data_displayed = null;
            var cache = [];
            var mytime = <?php echo intval($REFRESHTIME) ?>;
            var filter = "<?php echo $FILTER ?>";
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
            ').css({  'display': 'none',
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

    <body>
        <script type="text/javascript">
        if (body_class.length)
            $('body').addClass(body_class.join(' '));
        </script>

<?php if ($_SESSION['FRAME'] == 1) { ?>
        <table width="100%" height="100%" class="frame" id="type_<?php echo $framecolor?>">
            <tr>
                <td valign="top">
<?php } ?>
