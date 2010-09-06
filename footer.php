<?php
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
    <div align="center">
      <?php echo $CODENAME?> - <?php echo $VERSION?> - GPL -
      <a href="http://www.exosec.fr/" target="_blank">Exosec</a> - 2010 - 
      <?php echo $LANG[$MYLANG]['querytime']?> <?php echo sprintf($str_query_time, $query_time)?>
    </div>
  <?php if ($_SESSION['FRAME'] == 1) { ?>
  </td></tr></table>
  <?php } ?>
  </body>
  <script>
    refresh  = document.getElementById("refreshspan");
    autorefresh("<?php echo $MY_GET?>");
    document.getElementById("acklink").href = "ack.php?num=<?php echo $nb_rows?>";
    document.getElementById("downlink").href = "down.php?num=<?php echo $nb_rows?>";
    document.getElementById("optlink").href = "option.php";
    document.getElementById("commentlink").href = "comment.php?num=<?php echo $nb_rows?>";
  </script>
</html> 
