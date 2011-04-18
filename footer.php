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
  
  <div style="margin-top: 3px;">
    <div style="position: absolute; text-align: center; width: 100%; padding-top: -1px;">
      <?= $CODENAME ?> &mdash; 
      <?= $VERSION ?> &mdash; 
      GPL &mdash;
      <a href="http://www.exosec.fr/" target="_blank">Exosec</a> &mdash;
      2010&thinsp;-&thinsp;2011 &mdash; 
      <?= ucfirst(lang($MYLANG, 'querytime')) ?> <?= sprintf($str_query_time, $query_time) ?>
    </div>
    
    <? if (isset($_GET['monitor'])) { ?>
      <div style="position: relative; z-index: 1; display: inline-block;">
        <?= ucfirst(lang($MYLANG, 'refreshing')) ?>
        <b><span id="refreshspan"></span></b>&#160;<?= lang($MYLANG, 'second') ?>
        &mdash;
        <a href="index.php"><?php echo ucfirst(lang($MYLANG, 'mode0'))?></a>
      </div>
    <? } ?>
  </div>
  
  <?php if ($_SESSION['FRAME'] == 1) { ?>
    </td></tr></table>
  <?php } ?>
  </body>
  
  <script>
    autorefresh();
    /*if (document.getElementById("acklink") != null) {
      document.getElementById("acklink").href = "ack.php?num=<?php echo $nb_rows?>";
      document.getElementById("downlink").href = "down.php?num=<?php echo $nb_rows?>";
      document.getElementById("optlink").href = "option.php";
      document.getElementById("commentlink").href = "comment.php?num=<?php echo $nb_rows?>";
    }*/
    $("#filtering").focus(function() {
      filtering_has_focus = true ;
    });
    $("#filtering").focusout(function() {
      filtering_has_focus = false ;
    });
  </script>
</html> 
