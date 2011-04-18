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
  
  <div class="box-title box-title-default">
    <h2><?= ucfirst(lang($MYLANG, 'option'))?></h2>
  </div>
  
  <div class="box-content" id="option">
    <form action="" method="get" onsubmit="return valid_option(this);">
    <table>
        <tr>
          <th>
            <?= ucfirst(lang($MYLANG, 'refreshing0')) ?>
          </th>
          <td colspan="2">
            <input type="text" maxlength="4" 
                   name="refresh" id="refresh"
                   value="<?= $_SESSION['REFRESH'] ?>" />
            <?= lang($MYLANG, 'second')?>
            (min 10, max 3600)
          </td>
        </tr>
        <tr>
          <th>
            <?= ucfirst(lang($MYLANG, 'lang')) ?>
          </th>
          <td colspan="2">
            <? foreach(array_keys($LANG) AS $lang) { ?>
              <input type="radio" name="lang" 
                     id="<?= $lang ?>"
                     value="<?= $lang ?>"
                     <?= ($_SESSION['LANG'] == $lang) ? 'checked' : ''?>
                     style="vertical-align: middle;" />
                     
              <label for="<?= $lang ?>" style="vertical-align: middle;">
                <?= $lang ?>
              </label>&#160;&#160;
            <? } ?>
          </td>
        </tr>
        <tr>
          <th>
            <?= ucfirst(lang($MYLANG, 'step')) ?>
          </th>
          <td colspan="2">
            <input type="text" maxlength="3" name="step" 
                   value="<?= $_SESSION['STEP'] ?>" />
            (min 1, max 999)
          </td>
        </tr>
        <tr>
          <th>
            <?= ucfirst(lang($MYLANG, 'level')) ?>
          </th>
          <td colspan="2">
            <input type="text" maxlength="1" 
                   name="defaultlevel" 
                   value="<?= $_SESSION['LEVEL'] ?>" /> 
          </td>
        </tr>
        <tr>
          <th>
            <?= ucfirst(lang($MYLANG, 'maxlentd')) ?>
          </th>
          <td colspan="2">
            <input type="text" maxlength="3" 
                   name="maxlentd" 
                   value="<?= $_SESSION['MAXLENTD'] ?>" />
            (min 1, max 999)
          </td>
        </tr>
        <tr>
          <th>
            <?= ucfirst(lang($MYLANG, 'fontsize')) ?>
          </th>
          <td colspan="2">
            <input type="text" maxlength="3" 
                   name="fontsize" 
                   value="<?= $_SESSION['FONTSIZE'] ?>" />
            (min 1, max 100)
          </td>
        </tr>
        <tr>
          <th>
            <?= ucfirst(lang($MYLANG, 'frame')) ?>
          </th>
          <td colspan="2">
            <input type="checkbox" name="frame" value="0" 
                   <?= ($_SESSION['FRAME'] == 0) ? 'checked' : '' ?> 
                   style="vertical-align: middle;" />
          </td>
        </tr>
        <tr>
          <th style="vertical-align: top; padding-top: 3px;">
            <?= ucfirst(lang($MYLANG, 'cols')) ?>
          </th>
          <td style="vertical-align: top; padding-top: 3px;">
            <?php
            $count = count($COLS)-1;
            $i = 0;
            foreach($COLS AS $key => $val) { 
              if ($key != "machine") {
                if (intval($count/2) == $i++) echo '</td><td>';
              ?>
              
              <input type="checkbox" name="<?= $key ?>" id="<?= $key ?>" 
                     value="<?= $key ?>" 
                     style="vertical-align: middle;"
                     <?= (isset($_SESSION[$key])) ? 'checked' : '' ?> />
              <label for="<?= $key ?>" style="vertical-align: middle;">
                <?= ucfirst(lang($MYLANG, $key)) ?>
              </label>
              <br />
              
              <?
              }
            } ?>
          </td>
        </tr>
        <tr>
          <th style="height: 14px; background: none; border: none; border-top: 1px solid #E0E5D3;"></th>
          <td colspan="2"></td>
        </tr>
        <tr>
          <th style="border: none; border-top: 1px solid #E0E5D3; background: none; padding-top: 6px;">
          </th>
          <td colspan="2" style="border: none; border-top: 1px solid #E0E5D3; background: none; padding-bottom: 0; padding-top: 6px;">
            <input type="submit" name="option" value="Ok" />
            <input type="button" name="cancel" 
                   value="<?= ucfirst(lang($MYLANG, 'cancel')) ?>"
                   onclick="$.fn.colorbox.close();" />
            &#160;&#160;&#160;
            <input type="submit" name="reset" value="<?= ucfirst(lang($MYLANG, 'reset0')) ?>" />
          </td>
        </tr>
      </table>
      
      <input type="hidden" name="stop" value="stop" id="check_1" checked />
      <?php if (isset($qfilt)) { ?>
      <input type="hidden" name="filtering" value="<?php echo $qfilt?>" />
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
      
    </form>
  </div>
  
  <script type="text/javascript">
    setTimeout(function() { 
      $('refresh').focus(); 
    }, 500);
  </script>

