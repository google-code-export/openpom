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
    <?= $CODENAME ?> <span class="ndash">&ndash;</span> 
    <?= $VERSION ?> <span class="ndash">&ndash;</span> 
    GPL <span class="ndash">&ndash;</span>
    <a href="http://www.exosec.fr/" target="_blank">Exosec</a> <span class="ndash">&ndash;</span>
    2010&thinsp;-&thinsp;2011 <span class="ndash">&ndash;</span> 
    <?= ucfirst(lang($MYLANG, 'querytime')) ?> <?= sprintf($str_query_time, $query_time) ?>
    
    <? if (isset($_GET['monitor'])) { ?>
      <span class="ndash">&ndash;</span> 
      <?= ucfirst(lang($MYLANG, 'refreshing')) ?>
      <b><span id="refreshspan"></span></b>&#160;<?= lang($MYLANG, 'second') ?>
      <span class="ndash">&ndash;</span>
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
