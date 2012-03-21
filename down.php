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
?>

  <div class="box-title box-title-default">
    <h2><?php echo ucfirst(lang($MYLANG, 'downtime')) ?></h2>
  </div>
  
  <div class="box-content" id="box-down">
    <form action="" method="post" onsubmit="return valid_down(this, '<?php echo rawurlencode($ILLEGAL_CHAR); ?>');">
      <table>
        <tr>
          <th><?php echo ucfirst(lang($MYLANG, 'start_time')) ?></th>
          <td colspan="2">
            <input id="start" 
                   type="text" 
                   name="start"  
                   value="<?php echo strftime("%d-%m-%Y %H:%M", strtotime("+1 minute")) ?>" />
          </td>
        </tr>
        <tr>
          <th><?php echo ucfirst(lang($MYLANG, 'end_down')) ?></th>
          <td colspan="2">
            <input id="end" 
                   type="text" 
                   name="end"  
                   value="<?php echo strftime("%d-%m-%Y %H:%M", strtotime("+2 hour +1 minute"))?>" />
          </td>
        </tr>
        <tr>
          <th><?php echo ucfirst(lang($MYLANG, 'durationnow')) ?></th>
          <td colspan="2">
            <input id="hour" 
                   type="text" 
                   name="hour"  
                   value="" />
            <?php echo lang($MYLANG, 'hour') ?>
            
            &#160;&#160;&#160;
            <input id="minute" 
                   type="text" 
                   name="minute"  
                   value="" />
            <?php echo lang($MYLANG, 'minutes') ?>
          </td>
        </tr>
        <tr>
          <th><?php echo ucfirst(lang($MYLANG, 'comment')) ?></th>
          <td colspan="2">
            <input type="text" 
                   name="comment" 
                   maxlength="64"
                   id="comment"
                   value="" />
          </td>
        </tr>
        
        
        <tr>
          <td class="height-14"></td>
          <td class="height-14"></td>
          <td class="height-14"></td>
        </tr>
        
        
        <tr>
          <td></td>
          <td>
            <input type="hidden" name="action" value="down" />
            <input type="submit" value="OK" />&#160;
            <input type="submit" 
                   value="<?php echo ucfirst(lang($MYLANG, 'track')) ?>"
                   onclick="append_track(this.form);" />&#160;
          </td>
          <td align="right">
            <input type="button" 
                   value="<?php echo ucfirst(lang($MYLANG, 'cancel')) ?>"
                   onclick="$.fn.colorbox.close();" />
          </td>
        </tr>
      </table>
    </form>
  </div>
  
  <script type="text/javascript">
    setTimeout(function () { $('#comment').focus(); }, 500);
  </script>
