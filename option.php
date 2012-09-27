<?php
/*
  OpenPOM
 
  Copyright 2010, Exosec
  Licensed under GPL Version 2.
  http://www.gnu.org/licenses/
*/

require_once("config.php");
session_name($CODENAME);
session_start();
if (!isset($_SESSION['USER'])) die();
require_once("lang.php");
if (preg_match('/[?&]{1}filtering=([^&]+)/',$_SERVER['HTTP_REFERER'], $refilt))
  $qfilt = urldecode($refilt[1]);
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
  
  <div class="box-content" id="box-option">
    <form action="" method="get" onsubmit="return valid_option(this);">
    <table>
        <tr>
          <th>
            <?php echo ucfirst(lang($MYLANG, 'refreshing0')) ?>
          </th>
          <td colspan="3">
            <input type="text" maxlength="4" 
                   name="refresh" id="refresh"
                   value="<?php echo $_SESSION['REFRESH'] ?>" />
            &#160;<?php echo lang($MYLANG, 'second')?>
            (min 10, max 3600)
          </td>
        </tr>
        <tr>
          <th>
            <?php echo ucfirst(lang($MYLANG, 'lang')) ?>
          </th>
          <td colspan="3">
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
          <td colspan="3">
            <input type="text" maxlength="3" name="defaultstep" 
                   value="<?php echo $_SESSION['STEP'] ?>" />
            &#160;(min 1, max 999)
          </td>
        </tr>
        <tr>
          <th>
            <?php echo ucfirst(lang($MYLANG, 'level')) ?>
          </th>
          <td colspan="3">
            <select name="defaultlevel">
            <?php for ($sub_level=1; $sub_level <= $MAXLEVEL; $sub_level++) { ?>
              <option value="<?php echo $sub_level?>" <?php echo ($sub_level==$_SESSION['LEVEL'])?"selected":""?>>
              <?php if ($sub_level < 8) echo $sub_level.")&nbsp;" ; else echo "&nbsp;&nbsp;&nbsp;&nbsp;" ; ?> 
              <?php echo ucfirst(lang($MYLANG, 'level'.$sub_level))?></option>
            <?php } ?>
            </select>
          </td>
        </tr>
        <tr>
          <th>
            <?php echo ucfirst(lang($MYLANG, 'maxlen_stinfo')) ?>
          </th>
          <td colspan="3">
            <input type="text" maxlength="3" 
                   name="maxlen_stinfo" 
                   value="<?php echo $_SESSION['MAXLEN_STINFO'] ?>" />
            &#160;(min 1, max 999)
          </td>
        </tr>
        <tr>
          <th>
            <?php echo ucfirst(lang($MYLANG, 'maxlen_host')) ?>
          </th>
          <td colspan="3">
            <input type="text" maxlength="3" 
                   name="maxlen_host" 
                   value="<?php echo $_SESSION['MAXLEN_HOST'] ?>" />
            &#160;(min 1, max 999)
          </td>
        </tr>
        <tr>
          <th>
            <?php echo ucfirst(lang($MYLANG, 'maxlen_svc')) ?>
          </th>
          <td colspan="3">
            <input type="text" maxlength="3" 
                   name="maxlen_svc" 
                   value="<?php echo $_SESSION['MAXLEN_SVC'] ?>" />
            &#160;(min 1, max 999)
          </td>
        </tr>
        <tr>
          <th>
            <?php echo ucfirst(lang($MYLANG, 'maxlen_groups')) ?>
          </th>
          <td colspan="3">
            <input type="text" maxlength="3" 
                   name="maxlen_groups" 
                   value="<?php echo $_SESSION['MAXLEN_GROUPS'] ?>" />
            &#160;(min 1, max 999)
          </td>
        </tr>
        <tr>
          <th>
            <?php echo ucfirst(lang($MYLANG, 'fontsize')) ?>
          </th>
          <td colspan="3">
            <input type="text" maxlength="3" 
                   name="fontsize" 
                   value="<?php echo $_SESSION['FONTSIZE'] ?>" />
            &#160;(min 1, max 100)
          </td>
        </tr>
        <tr>
          <th>
            <?php echo ucfirst(lang($MYLANG, 'quicksearch')) ?>
          </th>
          <td colspan="3">
            <input type="checkbox" name="quicksearch" value="0" 
                   <?php echo ($_SESSION['QUICKSEARCH'] == 1) ? 'checked' : '' ?> 
                   style="vertical-align: middle;" />
          </td>
        </tr>
        <tr>
          <th>
            <?php echo ucfirst(lang($MYLANG, 'statuspopin')) ?>
          </th>
          <td colspan="3">
            <label for="showall" style="vertical-align: middle;"><?php echo ucfirst(lang($MYLANG, 'statusall')) ?></label>
            <input type="checkbox" name="showall" id="showall" value="1" 
                   <?php echo (isset($_SESSION['STATUS']['all'])) ? 'checked' : '' ?> 
                   style="vertical-align: middle;" 
                   onclick="if (this.checked) {
                              document.getElementById('showlimit').readOnly=true; 
                              document.getElementById('showlimit').className='readonly'; 
                            }
                            else {
                              document.getElementById('showlimit').readOnly=false;
                              document.getElementById('showlimit').className=''; 
                            };" />
            &nbsp;&nbsp; 
            <?php echo ucfirst(lang($MYLANG, 'statuslimit')) ?>
            <input type="text" maxlength="2" 
                   name="showlimit" id="showlimit"
                   value="<?php echo $_SESSION['STATUS']['limit'] ?>" <?php if (isset($_SESSION['STATUS']['all'])) echo "readonly='readonly' class='readonly' " ; ?>/>
            &nbsp;&nbsp; 
            <label for="showgraph" style="vertical-align: middle;"><?php echo ucfirst(lang($MYLANG, 'showgraph')) ?></label>
            <input type="checkbox" name="showgraph" id="showgraph" value="1" 
                   <?php echo (isset($_SESSION['STATUS']['graph'])) ? 'checked' : '' ?> 
                   style="vertical-align: middle;" />
          </td>
        </tr>
        
        <tr>
          <th>
            <?php echo ucfirst(lang($MYLANG, 'frame')) ?>
          </th>
          <td colspan="3">
            <input type="checkbox" name="frame" value="0" 
                   <?php echo ($_SESSION['FRAME'] == 0) ? 'checked' : '' ?> 
                   style="vertical-align: middle;" />
          </td>
        </tr>
        <tr>
          <th>
            <?php echo ucfirst(lang($MYLANG, 'cols')) ?>
          </th>
          <td>
            <?php
            $count = count($COLS)-1;
            $i = 0;
            foreach($COLS AS $key => $val) { 
              if ($key != "machine") {
                if (intval($count/2) == $i++) echo '</td><td colspan="2">';
              ?>
              
              <input type="checkbox" name="<?php echo $key ?>" id="<?php echo $key ?>" 
                     value="1" 
                     style="vertical-align: middle;"
                     <?php echo (!isset($_SESSION[$key])) ? 'checked' : '' ?> />
              <label for="<?php echo $key ?>" style="vertical-align: middle;"><?php echo ucfirst(lang($MYLANG, $key)) ?></label><br />
              
              <?php
              }
            } ?>
          </td>
        </tr>
        <tr>
          <th>
            <?php echo ucfirst(lang($MYLANG, 'historydisplay')) ?>
          </th>
          <td>
            <?php
            $count = count($HISTORY)-1;
            $i = 0;
            foreach($HISTORY AS $key => $val) { 
              if ($val == 0) {
                $count -= 1;
                continue ;
              }
              ?>
              
              <input type="checkbox" name="<?php echo $key ?>" id="<?php echo $key ?>" 
                     value="1" 
                     style="vertical-align: middle;"
                     <?php echo (isset($_SESSION['HISTORY'][$key])) ? 'checked' : '' ?> />
              <label for="<?php echo $key ?>" style="vertical-align: middle;"><?php echo ucfirst(lang($MYLANG, $key)) ?></label><br />
              
              <?php
              if ($i == (int) ($count / 2) ) echo '</td><td colspan="2">';
              $i++ ;
            } ?>
          </td>
        </tr>
        
        <tr>
          <td class="height-14"></td>
          <td class="height-14"></td>
          <td class="height-14"></td>
          <td class="height-14"></td>
        </tr>
        
        
        <tr>
          <td></td>
          <td colspan="2">
            <input type="submit" name="option" value="OK" />&#160;
            <input type="button" name="cancel" 
                   value="<?php echo ucfirst(lang($MYLANG, 'cancel')) ?>"
                   onclick="$.fn.colorbox.close();" />&#160;
          </td>
          <td align="right">
            <input type="submit" name="reset" value="<?php echo ucfirst(lang($MYLANG, 'reset0')) ?>" />
          </td>
        </tr>
      </table>
      
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
      $('#refresh').focus(); 
    }, 500);
  </script>

