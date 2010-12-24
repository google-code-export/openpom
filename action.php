<?php
/*
  OpenPom $Revision$
  $HeadURL$
 
  Copyright 2010, Exosec
  Licensed under GPL Version 2.
  http://www.gnu.org/licenses/
 
  $Date$
*/
 
  if (!isset($_SESSION['USER'])) die(); ?>

  <form action="index.php<?php echo $MY_GET?>" method="post" name="action" />
  <table class="action">
    <tr>
      <td><a class="ack" href="#" id="acklink"><input type="button" name="ack" value="<?php echo ucfirst(lang($MYLANG, 'acknowledge'))?>" /></a></td>
      <td><a class="down" href="#" id="downlink"><input type="button" name="down" value="<?php echo ucfirst(lang($MYLANG, 'downtime'))?>" /></a></td>
      <td><input type="submit" name="recheck" value="<?php echo ucfirst(lang($MYLANG, 'recheck'))?>" /></td>
      <td><input type="submit" name="disable" value="<?php echo ucfirst(lang($MYLANG, 'disable'))?>" onClick="EvalSound('sound1')" title="<?php echo ucfirst(lang($MYLANG, 'disable_title'))?>" /></td>
      <td><input type="submit" name="reset" value="<?php echo ucfirst(lang($MYLANG, 'reset'))?>" title="<?php echo ucfirst(lang($MYLANG, 'reset_title'))?>" /></td>
      <td><a class="comment" href="#" id="commentlink"><input type="button" name="comment" value="<?php echo ucfirst(lang($MYLANG, 'comment0'))?>" /></a></td>
      <td style="text-align:right; width:100%; white-space:nowrap;"><a class="<?php echo $global_notif; ?>" href="<?php echo $MY_GET.'&'.$global_notif;?>"><?php echo ucfirst(lang($MYLANG, $global_notif))?></a></td>
      <?php if (isset($LOG)) { ?>
      <td style="text-align:right; width:100%;"><a href="#" onClick='pop("<?php echo $LOG; ?>","nagios_log",600,500);'><?php echo ucfirst(lang($MYLANG, 'show_log'))?></a></td>
      <?php } ?>
    </tr>
  </table>
