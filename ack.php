<?php
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
  <form action="" name="ack" method="post" id="ack">
    <script>
      getallselectline(<?php echo $nb_rows?>,"ack");
     </script>
    <table class="popact">
      <tr><th colspan="2"><?php echo ucfirst($LANG[$MYLANG]['acknowledge'])?></th></tr>
      <tr>
        <td><?php echo ucfirst($LANG[$MYLANG]['comment'])?></td>
        <td>
          <input type="text" name="comment" id="comment" />
          <input type="hidden" name="ack" value="Ok" />
        </td>
      </tr>
      <tr>
        <td class="submitline" colspan="2">
          <input type="submit" name="ack" value="Ok" />
          <input type="submit" name="track" value="<?php echo ucfirst($LANG[$MYLANG]['track'])?>" onclick="this.form.comment.value='<?php echo $LANG[$MYLANG]['track']?> ' + this.form.comment.value" />
          <input type="button" name="cancel" value="<?php echo ucfirst($LANG[$MYLANG]['cancel'])?>" onclick="$.fn.colorbox.close();" />
        </td>
      </tr>
    </table>
    </form>
  </div>
  <script>setTimeout("document.getElementById('comment').focus()", 500, null)</script>

