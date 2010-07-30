<?
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
session_set_cookie_params($COOKIE_LIFETIME);
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
    <form action="" name="down" method="post" id="down">
    <script>getallselectline(<?=$nb_rows?>,"down");</script>
      <table>
        <tr><th colspan="3"><?=ucfirst($LANG[$MYLANG]['downtime'])?></th></tr>
        <tr>
          <td><?=ucfirst($LANG[$MYLANG]['duration'])?></td>
          <td><input id="time" type="text" name="time" style="width: 100px;" value="" /></td>
          <td><?=ucfirst($LANG[$MYLANG]['hour'])?></td>
        </tr>
        <tr>
          <td><?=ucfirst($LANG[$MYLANG]['comment'])?></td>
          <input type="hidden" name="down" value="Ok" />
          <td colspan="2"><input type="text" name="comment" id="comment" /></td>
        </tr>
        <tr>
          <td class="submitline" colspan="3">
            <input type="submit" name="down" value="Ok" />
            <input type="submit" name="cancel" value="<?=ucfirst($LANG[$MYLANG]['cancel'])?>" onclick="this.form.comment.value=''" />
          </td>
        </tr>
      </table>
    </form>
  </div>
  <script>setTimeout("document.getElementById('time').focus()", 500, null)</script> 

