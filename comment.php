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
  <form action="" name="comment" method="post" id="com" onSubmit='return valid_form();'>
    <script>
      getallselectline(<?php echo $nb_rows?>,"comment");
     </script>
    <table class="popact">
      <tr><th colspan="2"><?php echo ucfirst(lang($MYLANG, 'comment0'))?></th></tr>
      <tr>
        <td><?php echo ucfirst(lang($MYLANG, 'comment'))?></td>
        <td>
          <input type="text" name="comment" id="comment" />
          <input type="hidden" name="comment_persistent" value="Ok" />
        </td>
      </tr>
      <tr>
        <td class="submitline" colspan="2">
          <input type="submit" name="comment_persistent" value="Ok" />
          <input type="button" name="cancel" value="<?php echo ucfirst(lang($MYLANG, 'cancel'))?>" onclick="$.fn.colorbox.close();" />
        </td>
      </tr>
    </table>
    </form>
  </div>
  <script>setTimeout("document.getElementById('comment').focus()", 500, null)</script>

