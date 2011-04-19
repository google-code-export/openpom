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
?>

  <div class="box-title box-title-default">
    <h2><?php echo ucfirst(lang($MYLANG, 'downtime')) ?></h2>
  </div>
  
  <div class="box-content" id="down">
    <form action="" method="post" onsubmit="return valid_down(this, '<?php echo rawurlencode($ILLEGAL_CHAR); ?>');">
      <table>
        <tr>
          <th><?php echo ucfirst(lang($MYLANG, 'start_time')) ?></th>
          <td>
            <input id="start" 
                   type="text" 
                   name="start"  
                   value="<?php echo strftime("%d-%m-%Y %H:%M", strtotime("+1 minute")) ?>" />
          </td>
        </tr>
        <tr>
          <th><?php echo ucfirst(lang($MYLANG, 'end_down')) ?></th>
          <td>
            <input id="end" 
                   type="text" 
                   name="end"  
                   value="<?php echo strftime("%d-%m-%Y %H:%M", strtotime("+2 hour +1 minute"))?>" />
          </td>
        </tr>
        <tr>
          <th><?php echo ucfirst(lang($MYLANG, 'fix')) ?></th>
          <td>
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
          <td>
            <input type="text" 
                   name="comment" 
                   maxlength="64"
                   id="comment"
                   value="" />
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
            <input type="submit" name="down" value="Ok" />
            &#160;&#160;&#160;
            <input type="button" name="cancel" 
                   value="<?php echo ucfirst(lang($MYLANG, 'cancel')) ?>"
                   onclick="$.fn.colorbox.close();" />
          </td>
        </tr>
      </table>
    </form>
  </div>
  
  <script>setTimeout("document.getElementById('comment').focus()", 500, null)</script> 

