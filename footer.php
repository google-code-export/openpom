<?php
/*
  OpenPOM

  Copyright 2010, Exosec
  Licensed under GPL Version 2.
  http://www.gnu.org/licenses/
*/

  if (basename($_SERVER['SCRIPT_NAME']) != "index.php") die() ; ?>

  <div style="margin-top: 3px; text-align: center;">
    <?php echo $CODENAME ?> <span class="ndash">&ndash;</span>
    <?php echo $VERSION ?> <span class="ndash">&ndash;</span>
    GPL <span class="ndash">&ndash;</span>
    <a href="http://pom-monitoring.com/" target="_blank">Exosec</a> <span class="ndash">&ndash;</span>
    2010&thinsp;-&thinsp;<?php echo date('Y') ?> <span class="ndash">&ndash;</span>
    <?php echo ucfirst(_('querytime')) ?> <?php echo sprintf($str_query_time, $query_time) ?>

  </div>

  <?php if ($_SESSION['FRAME'] == 1) { ?>
    </td></tr></table>
  <?php } ?>
  </body>

  <script type="text/javascript">
    $("#filtering").focus(function() {
      filtering_has_focus = true ;
    });
    $("#filtering").focusout(function() {
      filtering_has_focus = false ;
    });

    /* start refresh countdown */
    window.setTimeout(autorefresh, 1000);
  </script>
</html>
