<?php
/*
  OpenPom $Revision$
  $HeadURL$
 
  Copyright 2010, Exosec
  Licensed under GPL Version 2.
  http://www.gnu.org/licenses/
 
  $Date$
*/

require_once("config.php");
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
      <tr><th colspan="2"><?php echo ucfirst(lang($MYLANG, 'option'))?></th></tr>
      <tr>
        <td><?php echo ucfirst(lang($MYLANG, 'refreshing0'))?></td>
        <td>
          <input type="text" size="4" maxlength="4" name="refresh" id="refresh"
            value="<?php echo $_SESSION['REFRESH']?>" />
          <?php echo lang($MYLANG, 'second')?> (min 10, max 3600)
        </td>
      </tr>
      <tr>
        <td><?php echo ucfirst(lang($MYLANG, 'lang'))?></td>
        <td><?php foreach(array_keys($LANG) AS $lang) { ?>
          <input type="radio" name="lang" value="<?php echo $lang?>" <?php echo ($_SESSION['LANG']==$lang)?"checked":""?> /> <?php echo $lang?>
          <?php } ?>
        </td>
      </tr>
      <tr>
        <td><?php echo ucfirst(lang($MYLANG, 'step'))?></td>
        <td>
          <input type="text" size="3" maxlength="3" name="step" value="<?php echo $_SESSION['STEP']?>" /> (min 1, max 999)
        </td>
      </tr>
      <tr>
        <td><?php echo ucfirst(lang($MYLANG, 'level'))?></td>
        <td>
          <input type="text" size="1" maxlength="1" name="defaultlevel" value="<?php echo $_SESSION['LEVEL']?>" /> 
        </td>
      </tr>
      <tr>
        <td><?php echo ucfirst(lang($MYLANG, 'maxlentd'))?></td>
        <td>
          <input type="text" size="3" maxlength="3" name="maxlentd" value="<?php echo $_SESSION['MAXLENTD']?>" /> (min 1, max 999)
        </td>
      </tr>
      <tr>
        <td><?php echo ucfirst(lang($MYLANG, 'fontsize'))?></td>
        <td>
          <input type="text" size="3" maxlength="3" name="fontsize" value="<?php echo $_SESSION['FONTSIZE']?>" /> (min 1, max 100)
        </td>
      </tr>
      <tr>
        <td><?php echo ucfirst(lang($MYLANG, 'frame'))?></td>
        <td>
          <input type="checkbox" name="frame" value="0" <?php echo ($_SESSION['FRAME']==0)?"checked":""?> />
        </td>
      </tr>
      <tr>
        <td><?php echo ucfirst(lang($MYLANG, 'cols'))?></td>
        <td>
          <?php foreach($COLS AS $key => $val) { 
               if (preg_match('/^(flag|duration|last|stinfo|group)$/', $key)) {
                 $pattern = ",".$key.",";
          ?>
          <input type="checkbox" name="<?php echo $key?>" value="<?php echo $key?>" <?php echo (preg_match($pattern,$_SESSION['COLS']))?"checked":""?> /> <?php echo ucfirst(lang($MYLANG, $key))?><br />
          <?php } } ?>
        </td>
      </tr>
      <tr>
        <td class="submitline" colspan="2">
          <input type="hidden" name="stop" value="stop" id="check_1" checked />
          <?php if (isset($qfilt)) { ?>
          <input type="hidden" name="filtering" value="<?php echo $qfilt?>" />
          <input type="hidden" name="filter" value="1" />
          <?php } ?>
          <?php if (isset($next)) { ?>
          <input type="hidden" name="next" value="<?php echo $next?>" />
          <?php } ?>
          <?php if (isset($prev)) { ?>
          <input type="hidden" name="prev" value="<?php echo $prev?>" />
          <?php } ?>
          <?php if (isset($sort)) { ?>
          <input type="hidden" name="sort" value="<?php echo $sort?>" />
          <?php } ?>
          <?php if (isset($order)) { ?>
          <input type="hidden" name="order" value="<?php echo $order?>" />
          <?php } ?>
          <?php if (isset($level)) { ?>
          <input type="hidden" name="level" value="<?php echo $level?>" />
          <?php } ?>
          <input type="submit" name="option" value="Ok" />
          <input type="button" name="cancel" value="<?php echo ucfirst(lang($MYLANG, 'cancel'))?>" onclick="$.fn.colorbox.close();" /> 
          <input type="submit" name="reset" value="<?php echo ucfirst(lang($MYLANG, 'reset0'))?>" /> 
        </td>
      </tr>
    </table>
    </form>
  </div>
  <script>setTimeout("document.getElementById('refresh').focus()", 500, null)</script>

