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
    <a class="ack" href="#" id="acklink"><input type="button" name="ack"
      value="<?php echo ucfirst(lang($MYLANG, 'acknowledge'))?>" /></a>

    <a class="down" href="#" id="downlink"><input type="button" name="down"
      value="<?php echo ucfirst(lang($MYLANG, 'downtime'))?>" /></a>

    <input type="submit" name="recheck"
      value="<?php echo ucfirst(lang($MYLANG, 'recheck'))?>" />

    <input type="submit" name="disable"
      value="<?php echo ucfirst(lang($MYLANG, 'disable'))?>" 
      onClick="EvalSound('sound1')" title="<?php echo ucfirst(lang($MYLANG, 'disable_title'))?>" />

    <input type="submit" name="reset"
      value="<?php echo ucfirst(lang($MYLANG, 'reset'))?>" title="<?php echo ucfirst(lang($MYLANG, 'reset_title'))?>" />

    <a class="comment" href="#" id="commentlink"><input type="button" name="comment"
      value="<?php echo ucfirst(lang($MYLANG, 'comment0'))?>" /></a>
