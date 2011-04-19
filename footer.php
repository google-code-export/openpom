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
  
  <div style="margin-top: 3px; text-align: center;">
    <?= $CODENAME ?> &ndash; 
    <?= $VERSION ?> &ndash; 
    GPL &ndash;
    <a href="http://www.exosec.fr/" target="_blank">Exosec</a> &ndash;
    2010&thinsp;-&thinsp;2011 &ndash; 
    <?= ucfirst(lang($MYLANG, 'querytime')) ?> <?= sprintf($str_query_time, $query_time) ?>
    
    <? if (isset($_GET['monitor'])) { ?>
      &ndash; 
      <?= ucfirst(lang($MYLANG, 'refreshing')) ?>
      <b><span id="refreshspan"></span></b>&#160;<?= lang($MYLANG, 'second') ?>
      &ndash;
      <a href="index.php"><?php echo ucfirst(lang($MYLANG, 'mode0'))?></a>
    <? } ?>
  </div>
  
  <?php if ($_SESSION['FRAME'] == 1) { ?>
    </td></tr></table>
  <?php } ?>
  </body>
  
  <script>
    autorefresh();
    $("#filtering").focus(function() {
      filtering_has_focus = true ;
    });
    $("#filtering").focusout(function() {
      filtering_has_focus = false ;
    });
  </script>
</html> 
