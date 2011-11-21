<?php
/*
  OpenPOM
 
  Copyright 2010, Exosec
  Licensed under GPL Version 2.
  http://www.gnu.org/licenses/
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
    <h2><?php echo ucfirst(lang($MYLANG, 'option'))?></h2>
  </div>
  
  <div class="box-content" id="option">
    <form action="" method="get" onsubmit="return valid_option(this);">
    <table>
        <tr>
          <th>
            <?php echo ucfirst(lang($MYLANG, 'refreshing0')) ?>
          </th>
          <td colspan="2">
            <input type="text" maxlength="4" 
                   name="refresh" id="refresh"
                   value="<?php echo $_SESSION['REFRESH'] ?>" />
            <?php echo lang($MYLANG, 'second')?>
            (min 10, max 3600)
          </td>
        </tr>
        <tr>
          <th>
            <?php echo ucfirst(lang($MYLANG, 'lang')) ?>
          </th>
          <td colspan="2">
            <?php foreach(array_keys($LANG) AS $lang) { ?>
              <input type="radio" name="lang" 
                     id="<?php echo $lang ?>"
                     value="<?php echo $lang ?>"
                     <?php echo ($_SESSION['LANG'] == $lang) ? 'checked' : ''?>
                     style="vertical-align: middle;" />
                     
              <label for="<?php echo $lang ?>" style="vertical-align: middle;">
                <?php echo $lang ?>
              </label>&#160;&#160;
            <?php } ?>
          </td>
        </tr>
        <tr>
          <th>
            <?php echo ucfirst(lang($MYLANG, 'step')) ?>
          </th>
          <td colspan="2">
            <input type="text" maxlength="3" name="step" 
                   value="<?php echo $_SESSION['STEP'] ?>" />
            (min 1, max 999)
          </td>
        </tr>
        <tr>
          <th>
            <?php echo ucfirst(lang($MYLANG, 'level')) ?>
          </th>
          <td colspan="2">
            <input type="text" maxlength="1" 
                   name="defaultlevel" 
                   value="<?php echo $_SESSION['LEVEL'] ?>" /> 
          </td>
        </tr>
        <tr>
          <th>
            <?php echo ucfirst(lang($MYLANG, 'maxlen_stinfo')) ?>
          </th>
          <td colspan="2">
            <input type="text" maxlength="3" 
                   name="maxlen_stinfo" 
                   value="<?php echo $_SESSION['MAXLEN_STINFO'] ?>" />
            (min 1, max 999)
          </td>
        </tr>
        <tr>
          <th>
            <?php echo ucfirst(lang($MYLANG, 'maxlen_host')) ?>
          </th>
          <td colspan="2">
            <input type="text" maxlength="3" 
                   name="maxlen_host" 
                   value="<?php echo $_SESSION['MAXLEN_HOST'] ?>" />
            (min 1, max 999)
          </td>
        </tr>
        <tr>
          <th>
            <?php echo ucfirst(lang($MYLANG, 'maxlen_svc')) ?>
          </th>
          <td colspan="2">
            <input type="text" maxlength="3" 
                   name="maxlen_svc" 
                   value="<?php echo $_SESSION['MAXLEN_SVC'] ?>" />
            (min 1, max 999)
          </td>
        </tr>
        <tr>
          <th>
            <?php echo ucfirst(lang($MYLANG, 'maxlen_groups')) ?>
          </th>
          <td colspan="2">
            <input type="text" maxlength="3" 
                   name="maxlen_groups" 
                   value="<?php echo $_SESSION['MAXLEN_GROUPS'] ?>" />
            (min 1, max 999)
          </td>
        </tr>
        <tr>
          <th>
            <?php echo ucfirst(lang($MYLANG, 'fontsize')) ?>
          </th>
          <td colspan="2">
            <input type="text" maxlength="3" 
                   name="fontsize" 
                   value="<?php echo $_SESSION['FONTSIZE'] ?>" />
            (min 1, max 100)
          </td>
        </tr>
        <tr>
          <th>
            <?php echo ucfirst(lang($MYLANG, 'frame')) ?>
          </th>
          <td colspan="2">
            <input type="checkbox" name="frame" value="0" 
                   <?php echo ($_SESSION['FRAME'] == 0) ? 'checked' : '' ?> 
                   style="vertical-align: middle;" />
          </td>
        </tr>
        <tr>
          <th style="vertical-align: top; padding-top: 3px;">
            <?php echo ucfirst(lang($MYLANG, 'cols')) ?>
          </th>
          <td style="vertical-align: top; padding-top: 3px;">
            <?php
            $count = count($COLS)-1;
            $i = 0;
            foreach($COLS AS $key => $val) { 
              if ($key != "machine") {
                if (intval($count/2) == $i++) echo '</td><td>';
              ?>
              
              <input type="checkbox" name="<?php echo $key ?>" id="<?php echo $key ?>" 
                     value="<?php echo $key ?>" 
                     style="vertical-align: middle;"
                     <?php echo (isset($_SESSION[$key])) ? 'checked' : '' ?> />
              <label for="<?php echo $key ?>" style="vertical-align: middle;">
                <?php echo ucfirst(lang($MYLANG, $key)) ?>
              </label>
              <br />
              
              <?php
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
            <input type="submit" name="option" value="OK" />
            <input type="button" name="cancel" 
                   value="<?php echo ucfirst(lang($MYLANG, 'cancel')) ?>"
                   onclick="$.fn.colorbox.close();" />
            &#160;&#160;&#160;
            <input type="submit" name="reset" value="<?php echo ucfirst(lang($MYLANG, 'reset0')) ?>" />
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

