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

require_once("config.php");
session_set_cookie_params($COOKIE_LIFETIME);
session_start();
if (!isset($_SESSION['USER'])) die();
require_once("lang.php");
if (preg_match('/[?&]{1}filtering=([^&]+)/',$_SERVER['HTTP_REFERER'], $refilt))
  $qfilt = $refilt[1];
if (preg_match('/[?&]{1}sort=([^&]+)/',$_SERVER['HTTP_REFERER'], $resort)) {
  if (preg_match('/[?&]{1}order=([01]+)/',$_SERVER['HTTP_REFERER'], $reorder))
    $order = $reorder[1];
  $sort = $resort[1];
}
if (preg_match('/[?&]{1}next=([0-9]+)/',$_SERVER['HTTP_REFERER'], $renext))
  $next = $renext[1];
if (preg_match('/[?&]{1}prev=([0-9]+)/',$_SERVER['HTTP_REFERER'], $reprev))
  $prev = $reprev[1];
if (preg_match('/[?&]{1}level=([0-9]+)/',$_SERVER['HTTP_REFERER'], $relevel))
  $level = $relevel[1];
?>
  <script type='text/javascript' src='js/func.js'></script>

  <div class="popact" id="popack">
  <form action="" name="fopt" method="get" class="fopt" id="fopt">
    <table class="popact">
      <tr><th colspan="2"><?=ucfirst($LANG[$MYLANG]['option'])?></th></tr>
      <tr>
        <td><?=ucfirst($LANG[$MYLANG]['refreshing0'])?></td>
        <td>
          <input type="text" size="4" maxlength="4" name="refresh" id="refresh"
            value="<?=$_SESSION['REFRESH']?>" />
          <?=$LANG[$MYLANG]['second']?> (min 10, max 3600)
        </td>
      </tr>
      <tr>
        <td><?=ucfirst($LANG[$MYLANG]['lang'])?></td>
        <td><? foreach(array_keys($LANG) AS $lang) { ?>
          <input type="radio" name="lang" value="<?=$lang?>" <?=($_SESSION['LANG']==$lang)?"checked":""?> /> <?=$lang?>
          <? } ?>
        </td>
      </tr>
      <tr>
        <td><?=ucfirst($LANG[$MYLANG]['step'])?></td>
        <td>
          <input type="text" size="3" maxlength="3" name="step" value="<?=$_SESSION['STEP']?>" /> (min 1, max 999)
        </td>
      </tr>
      <tr>
        <td><?=ucfirst($LANG[$MYLANG]['level'])?></td>
        <td>
          <input type="text" size="1" maxlength="1" name="defaultlevel" value="<?=$_SESSION['LEVEL']?>" /> 
        </td>
      </tr>
      <tr>
        <td><?=ucfirst($LANG[$MYLANG]['maxlentd'])?></td>
        <td>
          <input type="text" size="3" maxlength="3" name="maxlentd" value="<?=$_SESSION['MAXLENTD']?>" /> (min 1, max 999)
        </td>
      </tr>
      <tr>
        <td><?=ucfirst($LANG[$MYLANG]['fontsize'])?></td>
        <td>
          <input type="text" size="3" maxlength="3" name="fontsize" value="<?=$_SESSION['FONTSIZE']?>" /> (min 1, max 100)
        </td>
      </tr>
      <tr>
        <td><?=ucfirst($LANG[$MYLANG]['frame'])?></td>
        <td>
          <input type="checkbox" name="frame" value="0" <?=($_SESSION['FRAME']==0)?"checked":""?> />
        </td>
      </tr>
      <tr>
        <td><?=ucfirst($LANG[$MYLANG]['cols'])?></td>
        <td>
          <? foreach($COLS AS $key => $val) { 
               if (preg_match('/^(flag|duration|last|stinfo|group)$/', $key)) {
                 $pattern = ",".$key.",";
          ?>
          <input type="checkbox" name="<?=$key?>" value="<?=$key?>" <?=(preg_match($pattern,$_SESSION['COLS']))?"checked":""?> /> <?=ucfirst($LANG[$MYLANG][$key])?><br />
          <? } } ?>
        </td>
      </tr>
      <tr>
        <td class="submitline" colspan="2">
          <input type="hidden" name="stop" value="stop" id="check_1" checked />
          <? if (isset($qfilt)) { ?>
          <input type="hidden" name="filtering" value="<?=$qfilt?>" />
          <input type="hidden" name="filter" value="1" />
          <? } ?>
          <? if (isset($next)) { ?>
          <input type="hidden" name="next" value="<?=$next?>" />
          <? } ?>
          <? if (isset($prev)) { ?>
          <input type="hidden" name="prev" value="<?=$prev?>" />
          <? } ?>
          <? if (isset($sort)) { ?>
          <input type="hidden" name="sort" value="<?=$sort?>" />
          <? } ?>
          <? if (isset($order)) { ?>
          <input type="hidden" name="order" value="<?=$order?>" />
          <? } ?>
          <? if (isset($level)) { ?>
          <input type="hidden" name="level" value="<?=$level?>" />
          <? } ?>
          <input type="submit" name="option" value="Ok" />
          <input type="submit" name="cancel" value="<?=ucfirst($LANG[$MYLANG]['cancel'])?>" onclick="this.form.method='post'" /> 
          <input type="submit" name="reset" value="<?=ucfirst($LANG[$MYLANG]['reset0'])?>" /> 
        </td>
      </tr>
    </table>
    </form>
  </div>
  <script>setTimeout("document.getElementById('refresh').focus()", 500, null)</script>

