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
    <h2><?php echo ucfirst(lang($MYLANG, 'comment0')) ?></h2>
  </div>
  
  <div class="box-content" id="ack">
    <form action="" method="post" onsubmit="return valid_comment(this, '<?php echo rawurlencode($ILLEGAL_CHAR); ?>');">
      <!-- BEGIN IE FIX -->
      <!-- 1x text input and submit disables submit on Enter -->
      <div style="display: none;">
        <input type="text" name="dummy_IE_FIX" />
      </div>
      <!-- END IE FIX -->

      <table>
        <tr>
          <th><?php echo ucfirst(lang($MYLANG, 'comment')) ?></th>
          <td><input type="text" maxlength="64" name="comment" id="comment" /></td>
        </tr>
        
        <tr>
          <th style="height: 14px; background: none; border: none; border-top: 1px solid #E0E5D3;"></th>
          <td colspan="2"></td>
        </tr>
        <tr>
          <th style="border: none; border-top: 1px solid #E0E5D3; background: none; padding-top: 6px;">
          </th>
          <td colspan="2" style="border: none; border-top: 1px solid #E0E5D3; background: none; padding-bottom: 0; padding-top: 6px;">
            <input type="submit" name="comment_persistent" value="Ok" />
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

