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

  if (basename($_SERVER['SCRIPT_NAME']) != "index.php") die() ; 
  if (!isset($_SESSION['USER'])) die();
?>

  <script type="text/javascript" src="js/jquery-1.4.2.min.js"></script> 
  <script type="text/javascript" src="js/jquery.colorbox.js"></script>
  <script type="text/javascript" src="js/scripts.js"></script>

  <table width="100%" class="filter">
      <tr>
        <th class="filter" width="4px" style="white-space:nowrap;">
	  <span title="<?=ucfirst($LANG[$MYLANG]['refreshing'])?>" id="refreshspan"></span>
        </th>
        <th class="filter" width="50px">
          <a class="refresh" href="<?=$MY_GET?>" alt="<?=ucfirst($LANG[$MYLANG]['refresh'])?>" title="<?=ucfirst($LANG[$MYLANG]['refresh'])?>"></a>
        </th>
        <th class="filters" width="1">
	<form method="get" class="filt" name="filt" action="?filter=1" id="filt"> 
          <select name="level" onChange='add_input("filter","filt"); this.form.submit();'>
            <? 
              for ($sub_level=1; $sub_level <= 6; $sub_level++) { 
            ?>
            <option value="<?=$sub_level?>" <?=($sub_level==$LEVEL)?"selected":""?>><?=$sub_level?> - <?=ucfirst($LANG[$MYLANG]['level'.$sub_level])?></option>
            <? } ?>
          </select>
        </th>
        <th class="filter" width="1">
          <input type="text" name="filtering" value="<?=$FILTER?>" id="filtering" title="<?=ucfirst($LANG[$MYLANG]['search'])?>"/>
        </th>
        <th class="filter" width="1">
          <input type="submit" name="filter" id="filter"
            value="<?=ucfirst($LANG[$MYLANG]['filter'])?>" />
        </th>
        <th class="filter" width="1">
	  <input type="submit" name="clear" value="<?=ucfirst($LANG[$MYLANG]['clear'])?>" onclick='filtering.value="";' />
	</form>
        </th>
        <th class="monitor">
          <a href="?monitor"><?=ucfirst($LANG[$MYLANG]['mode'])?></a>
        </th>
        <th width="200px" style="white-space:nowrap;" title="<?=$LANG[$MYLANG]['meter']?>">
          C=<?=$hit_critical?> W=<?=$hit_warning?> U=<?=$hit_unknown?> 
          D=<?=$hit_down?> A=<?=$hit_ack?> T=<?=$hit_any?> 
        </th>
        <? if ($FIRST >= $LINE_BY_PAGE) { ?>
        <th width="1">
          <a href="<?=$MY_GET_NO_NEXT?>&prev=<?=$FIRST-$LINE_BY_PAGE?>"><?=ucfirst($LANG[$MYLANG]['prev'])?></a> 
        </th>
        <? } ?>
        <th width="1" style="white-space:nowrap;">
          <? echo $FIRST."-".($FIRST+$nb_rows)."/".$hit_any; ?>
        </th>
        <? if ($nb_rows >= $LINE_BY_PAGE) { ?>
        <th width="1">
          <a href="<?=$MY_GET_NO_NEXT?>&next=<?=$FIRST+$LINE_BY_PAGE?>"><?=ucfirst($LANG[$MYLANG]['next'])?></a>
        </th>
        <? } ?>
        <th width="1">
          <a class="option" href="#" id="optlink"><?=ucfirst($LANG[$MYLANG]['option'])?></a>
        </th>
      </tr>
    </table>

