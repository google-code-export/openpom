<?php 
/*
  OpenPom $Revision$
  $HeadURL$
 
  Copyright 2010, Exosec
  Licensed under GPL Version 2.
  http://www.gnu.org/licenses/
 
  $Date$
*/

  if (basename($_SERVER['SCRIPT_NAME']) != "index.php") die() ; 
  if (!isset($_SESSION['USER'])) die();
?>

  <script type="text/javascript" src="js/jquery-1.4.2.min.js"></script> 
  <script type="text/javascript" src="js/jquery.colorbox.js"></script>
  <script type="text/javascript" src="js/scripts.js"></script>

  <table width="100%" class="filter">
      <tr>
        <th class="filter" width="4px" style="white-space:nowrap;">
	  <span title="<?php echo ucfirst($LANG[$MYLANG]['refreshing'])?>" id="refreshspan"></span>
        </th>
        <th class="filter" width="50px" style="white-space:nowrap;">
          <a class="refresh" href="<?php echo $MY_GET?>" alt="<?php echo ucfirst($LANG[$MYLANG]['refresh'])?>" title="<?php echo ucfirst($LANG[$MYLANG]['refresh'])?>"></a>
        </th>
        <th class="filters" width="1" style="white-space:nowrap;">
	<form method="get" class="filt" name="filt" action="?filter=1" id="filt"> 
          <select name="level" onChange='add_input("filter","filt"); this.form.submit();'>
            <?php 
              for ($sub_level=1; $sub_level <= 6; $sub_level++) { 
            ?>
            <option value="<?php echo $sub_level?>" <?php echo ($sub_level==$LEVEL)?"selected":""?>><?php echo $sub_level?> - <?php echo ucfirst($LANG[$MYLANG]['level'.$sub_level])?></option>
            <?php } ?>
          </select>
        </th>
        <th class="filter" width="1" style="white-space:nowrap;">
          <input type="text" name="filtering" value="<?php echo $FILTER?>" id="filtering" title="<?php echo ucfirst($LANG[$MYLANG]['search'])?>"/>
        </th>
        <th class="filter" width="1" style="white-space:nowrap;">
          <input type="submit" name="filter" id="filter"
            value="<?php echo ucfirst($LANG[$MYLANG]['filter'])?>" />
        </th>
        <th class="filter" width="1" style="white-space:nowrap;">
	  <input type="submit" name="clear" value="<?php echo ucfirst($LANG[$MYLANG]['clear'])?>" onclick='filtering.value="";' />
	</form>
        </th>
        <th class="monitor" style="white-space:nowrap;">
          <a href="?monitor"><?php echo ucfirst($LANG[$MYLANG]['mode'])?></a>
        </th>
        <th width="200px" style="white-space:nowrap;" title="<?php echo $LANG[$MYLANG]['meter']?>">
          C=<?php echo $hit_critical?> W=<?php echo $hit_warning?> U=<?php echo $hit_unknown?> 
          D=<?php echo $hit_down?> A=<?php echo $hit_ack?> T=<?php echo $hit_any?> 
        </th>
        <?php if ($FIRST >= $LINE_BY_PAGE) { ?>
        <th width="1" style="white-space:nowrap;">
          <a href="<?php echo $MY_GET_NO_NEXT?>&prev=<?php echo $FIRST-$LINE_BY_PAGE?>"><?php echo ucfirst($LANG[$MYLANG]['prev'])?></a> 
        </th>
        <?php } ?>
        <th width="1" style="white-space:nowrap;">
          <?php echo $FIRST."-".($FIRST+$nb_rows)."/".$hit_any; ?>
        </th>
        <?php if ($nb_rows >= $LINE_BY_PAGE) { ?>
        <th width="1" style="white-space:nowrap;">
          <a href="<?php echo $MY_GET_NO_NEXT?>&next=<?php echo $FIRST+$LINE_BY_PAGE?>"><?php echo ucfirst($LANG[$MYLANG]['next'])?></a>
        </th>
        <?php } ?>
        <th width="1" style="white-space:nowrap;">
          <a class="option" href="#" id="optlink"><?php echo ucfirst($LANG[$MYLANG]['option'])?></a>
        </th>
      </tr>
    </table>

