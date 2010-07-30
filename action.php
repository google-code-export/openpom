<?
/*
  OpenPom $Revision$
  $HeadURL$
 
  Copyright 2010, Exosec
  Licensed under GPL Version 2.
  http://www.gnu.org/licenses/
 
  $Date$

  Sylvain Choisnard - schoisnard@exosec.fr                                                 
*/
 
  if (!isset($_SESSION['USER'])) die(); ?>

  <form action="index.php<?=$MY_GET?>" method="post" name="action" />
    <a class="ack" href="#" id="acklink"><input type="button" name="ack"
      value="<?=ucfirst($LANG[$MYLANG]['acknowledge'])?>" /></a>

    <a class="down" href="#" id="downlink"><input type="button" name="down"
      value="<?=ucfirst($LANG[$MYLANG]['downtime'])?>" /></a>

    <input type="submit" name="recheck"
      value="<?=ucfirst($LANG[$MYLANG]['recheck'])?>" />

    <input type="submit" name="disable"
      value="<?=ucfirst($LANG[$MYLANG]['disable'])?>" 
      onClick="EvalSound('sound1')" title="<?=ucfirst($LANG[$MYLANG]['disable_title'])?>" />

    <input type="submit" name="reset"
      value="<?=ucfirst($LANG[$MYLANG]['reset'])?>" title="<?=ucfirst($LANG[$MYLANG]['reset_title'])?>" />

    <a class="comment" href="#" id="commentlink"><input type="button" name="comment"
      value="<?=ucfirst($LANG[$MYLANG]['comment0'])?>" /></a>
