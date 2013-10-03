<?php
/*
  OpenPOM

  Copyright 2010, Exosec
  Licensed under GPL Version 2.
  http://www.gnu.org/licenses/
*/

require_once("config.php");
require_once("query.php");

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
    <h2><?php echo ucfirst(_('option'))?></h2>
  </div>

  <div class="box-content" id="box-option">
<?php

if (!init_columns($err)) {
    echo "$err</div>";
    exit(1);
}

?>
    <form action="" method="get" onsubmit="return valid_option(this);">
    <table>
        <tr>
          <th>
            <?php echo ucfirst(_('refreshing0')) ?>
          </th>
          <td colspan="3">
            <input type="text" maxlength="4"
                   name="refresh" id="refresh"
                   value="<?php echo $_SESSION['REFRESH'] ?>" />
            &#160;<?php echo _('second')?>
            (min 10, max 3600)
          </td>
        </tr>
        <tr>
          <th>
            <?php echo ucfirst(_('lang')) ?>
          </th>
          <td colspan="3">
            <?php foreach($LANG AS $lang => $display) { ?>
              <input type="radio" name="i18n" 
                     id="<?php echo $lang ?>"
                     value="<?php echo $lang ?>"
                     <?php echo ($_SESSION['LANG'] == $lang) ? 'checked' : ''?>
                     style="vertical-align: middle;" />

              <label for="<?php echo $lang ?>" style="vertical-align: middle;">
                <?php echo $display ?>
              </label>&#160;&#160;
            <?php } ?>
          </td>
        </tr>
        <tr>
          <th>
            <?php echo ucfirst(_('step')) ?>
          </th>
          <td colspan="3">
            <input type="text" maxlength="3" name="defaultstep"
                   value="<?php echo $_SESSION['STEP'] ?>" />
            &#160;(min 1, max 999)
          </td>
        </tr>
        <tr>
          <th>
            <?php echo ucfirst(_('level')) ?>
          </th>
          <td colspan="3">
            <select name="defaultlevel">
            <?php for ($sub_level=1; $sub_level <= $MAXLEVEL; $sub_level++) { ?>
              <option value="<?php echo $sub_level?>" <?php echo ($sub_level==$_SESSION['LEVEL'])?"selected":""?>>
              <?php if ($sub_level < 8) echo $sub_level.")&nbsp;" ; else echo "&nbsp;&nbsp;&nbsp;&nbsp;" ; ?>
              <?php echo ucfirst(_('level'.$sub_level))?></option>
            <?php } ?>
            </select>
          </td>
        </tr>

<?php
foreach ($COLUMN_DEFINITION as $col => $def) {
    if (!isset($def['lmax']))
        continue;
?>

        <tr>
          <th>
            <?php echo ucfirst(_('maxlen')) ?>
            <?php echo ucfirst(_col($col)) ?>
          </th>
          <td colspan="3">
            <input type="text" maxlength="3"
                   name="maxlen_<?php echo $col ?>"
                   value="<?php echo isset($_SESSION["MAXLEN_$col"])
                                    ? $_SESSION["MAXLEN_$col"] : $def['lmax'] ?>" />
            &#160;(min 1, max 999)
          </td>
        </tr>

<?php
} /* end foreach column */
?>

        <tr>
          <th>
            <?php echo ucfirst(_('fontsize')) ?>
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
            <?php echo ucfirst(_('quicksearch')) ?>
          </th>
          <td colspan="3">
            <input type="checkbox" name="quicksearch" value="0"
                   <?php echo ($_SESSION['QUICKSEARCH'] == 1) ? 'checked' : '' ?>
                   style="vertical-align: middle;" />
          </td>
        </tr>
        <tr>
          <th>
            <?php echo ucfirst(_('statuspopin')) ?>
          </th>
          <td colspan="3">
            <label for="showall" style="vertical-align: middle;"><?php echo ucfirst(_('statusall')) ?></label>
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
            <?php echo ucfirst(_('statuslimit')) ?>
            <input type="text" maxlength="2"
                   name="showlimit" id="showlimit"
                   value="<?php echo $_SESSION['STATUS']['limit'] ?>" <?php if (isset($_SESSION['STATUS']['all'])) echo "readonly='readonly' class='readonly' " ; ?>/>
            &nbsp;&nbsp;
            <label for="showgraph" style="vertical-align: middle;"><?php echo ucfirst(_('showgraph')) ?></label>
            <input type="checkbox" name="showgraph" id="showgraph" value="1"
                   <?php echo (isset($_SESSION['STATUS']['graph'])) ? 'checked' : '' ?>
                   style="vertical-align: middle;" />
          </td>
        </tr>

        <tr>
          <th>
            <?php echo ucfirst(_('frame')) ?>
          </th>
          <td colspan="3">
            <input type="checkbox" name="frame" value="0"
                   <?php echo ($_SESSION['FRAME'] == 0) ? 'checked' : '' ?>
                   style="vertical-align: middle;" />
          </td>
        </tr>
        <tr>
          <th>
            <?php echo ucfirst(_('cols')) ?>
          </th>
          <td>
            <?php
            $columns = array();

            foreach ($COLUMN_DEFINITION as $col => $def) {
                if (($def['opts'] & (COL_MUST_DISPLAY|COL_NO_USER_PREF)))
                    continue;

                if (isset($_SESSION["COLS_$col"]))
                    $columns[$col] = (bool)$_SESSION["COLS_$col"];
                else if (($def['opts'] & COL_ENABLED))
                    $columns[$col] = true;
                else
                    $columns[$col] = false;
            }

            $middle = ceil(count($columns) / 2);
            $i = 0;
            foreach ($columns as $col => $display) {
              if ($i == $middle) echo '</td><td colspan="2">';
              $i++;
              ?>

              <input type="checkbox"
                     name="<?php echo "defaultcols_$col" ?>"
                     id="<?php echo "defaultcols_$col" ?>"
                     value="1"
                     style="vertical-align: middle;"
                     <?php echo $display ? 'checked' : '' ?> />
              <label for="<?php echo "defaultcols_$col" ?>"
                     style="vertical-align: middle;"><?php echo _col($col) ?></label><br />

            <?php } ?>
          </td>
        </tr>
        <tr>
          <th>
            <?php echo ucfirst(_('historydisplay')) ?>
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
              <label for="<?php echo $key ?>" style="vertical-align: middle;"><?php echo ucfirst(_($key)) ?></label><br />

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
                   value="<?php echo ucfirst(_('cancel')) ?>"
                   onclick="$.fn.colorbox.close();" />&#160;
          </td>
          <td align="right">
            <input type="submit" name="reset" value="<?php echo ucfirst(_('reset0')) ?>" />
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
