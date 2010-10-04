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
if ( (!isset($_GET['num'])) || (!is_numeric($_GET['num'])) )
  die();
else
  $nb_rows = $_GET['num'];
?>
  <script type='text/javascript' src='js/func.js'></script>
  <div class="popact" id="popack">
    <form action="" name="down" method="post" id="down" onSubmit='return valid_form();'>
    <script>
     if (getallselectline(<?php echo $nb_rows?>,"down") == false)
       $.fn.colorbox.close();
    </script>
      <table>
        <tr><th colspan="2"><?php echo ucfirst(lang($MYLANG, 'downtime'))?></th></tr>
        <tr>
          <td><?php echo ucfirst(lang($MYLANG, 'start_time'))?></td>
          <td><input id="start" type="text" name="start" style="width: 150px;" value="<?php echo strftime("%d-%m-%Y %H:%M", strtotime("+1 minute"))?>" /></td>
        </tr>
        <tr>
          <td><?php echo ucfirst(lang($MYLANG, 'end_down'))?></td>
          <td><input id="end" type="text" name="end" style="width: 150px;" value="<?php echo strftime("%d-%m-%Y %H:%M", strtotime("+2 hour +1 minute"))?>" /></td>
        </tr>
        <tr>
          <td><?php echo ucfirst(lang($MYLANG, 'fix'))?></td>
          <td><input id="hour" type="text" name="hour" style="width:50px;" value="" /> <?php echo ucfirst(lang($MYLANG, 'hour'))?> <input id="minute" type="text" name="minute" style="width:50px;" value="" /> <?php echo ucfirst(lang($MYLANG, 'minutes'))?></td>
        </tr>
        <tr>
          <td><?php echo ucfirst(lang($MYLANG, 'comment'))?></td>
          <input type="hidden" name="down" value="Ok" />
          <td colspan="2"><input type="text" name="comment" id="comment" style="width:250px;" /></td>
        </tr>
        <tr>
          <td class="submitline" colspan="2">
            <input type="submit" name="down" value="Ok" />
            <input type="button" name="cancel" value="<?php echo ucfirst(lang($MYLANG, 'cancel'))?>" onclick="$.fn.colorbox.close();" />
          </td>
        </tr>
        <tr>
        </tr>
      </table>
    </form>
  </div>
  <script>setTimeout("document.getElementById('comment').focus()", 500, null)</script> 

