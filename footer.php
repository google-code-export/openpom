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
  <div align="center" style="margin-top: 5px;">
      <?php echo $CODENAME?> &mdash; <?php echo $VERSION?> &mdash; GPL &mdash;
      <a href="http://www.exosec.fr/" target="_blank">Exosec</a> &mdash; 2010&thinsp;-&thinsp;2011 &mdash; 
      <?= ucfirst(lang($MYLANG, 'querytime')) ?> <?= sprintf($str_query_time, $query_time) ?>
    </div>
  <?php if ($_SESSION['FRAME'] == 1) { ?>
  </td></tr></table>
  <?php } ?>
  </body>
  <script>
    autorefresh();
    if (document.getElementById("acklink") != null) {
      document.getElementById("acklink").href = "ack.php?num=<?php echo $nb_rows?>";
      document.getElementById("downlink").href = "down.php?num=<?php echo $nb_rows?>";
      document.getElementById("optlink").href = "option.php";
      document.getElementById("commentlink").href = "comment.php?num=<?php echo $nb_rows?>";
    }
    $("#filtering").focus(function() {
      filtering_has_focus = true ;
    });
    $("#filtering").focusout(function() {
      filtering_has_focus = false ;
    });
  </script>
</html> 
